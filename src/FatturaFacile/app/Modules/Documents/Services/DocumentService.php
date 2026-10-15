<?php

namespace App\Modules\Documents\Services;

use App\Modules\Documents\Models\Attachment;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DocumentService
{
    public function __construct(
        private TenantContext $tenantContext
    ) {}

    /**
     * Store private attachment associated with any ERP model.
     */
    public function storeAttachment(
        Model $attachable,
        UploadedFile $file,
        int $companyId,
        string $disk = 'local'
    ): Attachment {
        $tenantId = $this->tenantContext->getTenantId() ?? $attachable->tenant_id;
        $directory = "tenants/{$tenantId}/attachments";
        $path = $file->store($directory, $disk);

        return Attachment::create([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'user_id' => Auth::id(),
            'attachable_type' => get_class($attachable),
            'attachable_id' => $attachable->getKey(),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'disk' => $disk,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);
    }

    /**
     * Authorize and stream download of private attachment.
     */
    public function downloadAttachment(Attachment $attachment): StreamedResponse
    {
        $currentTenantId = $this->tenantContext->getTenantId();

        if ($currentTenantId !== null && $attachment->tenant_id !== $currentTenantId) {
            throw new AccessDeniedHttpException('Unauthorized access to attachment belonging to another tenant.');
        }

        if (! Storage::disk($attachment->disk)->exists($attachment->file_path)) {
            abort(404, 'File not found on storage.');
        }

        return Storage::disk($attachment->disk)->download($attachment->file_path, $attachment->file_name);
    }
}

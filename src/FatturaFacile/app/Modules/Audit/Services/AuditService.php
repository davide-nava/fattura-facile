<?php

namespace App\Modules\Audit\Services;

use App\Modules\Audit\Models\AuditLog;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public function log(
        string $action,
        Model $model,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $companyId = null
    ): AuditLog {
        /** @var TenantContext $context */
        $context = app(TenantContext::class);
        $tenantId = $context->getTenantId() ?? $model->tenant_id ?? null;

        // Strip sensitive fields
        $sensitive = ['password', 'remember_token', 'secret', 'api_token'];
        if ($oldValues) {
            foreach ($sensitive as $key) {
                unset($oldValues[$key]);
            }
        }
        if ($newValues) {
            foreach ($sensitive as $key) {
                unset($newValues[$key]);
            }
        }

        return AuditLog::create([
            'tenant_id' => $tenantId,
            'company_id' => $companyId ?? ($model->company_id ?? null),
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => substr(Request::userAgent() ?? '', 0, 255),
            'created_at' => now(),
        ]);
    }
}

<?php

namespace App\Modules\CrmContacts\Handlers;

use App\Modules\Audit\Services\AuditService;
use App\Modules\CrmContacts\Commands\UpdateContactPersonCommand;
use App\Modules\CrmContacts\Models\ContactPerson;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Support\Facades\DB;

class UpdateContactPersonHandler
{
    public function __construct(
        private AuditService $auditService,
        private TenantContext $tenantContext
    ) {}

    public function handle(UpdateContactPersonCommand $command): ContactPerson
    {
        return DB::transaction(function () use ($command) {
            $contact = ContactPerson::findOrFail($command->contactPersonId);
            $tenantId = $this->tenantContext->getTenantId() ?? $contact->tenant_id;
            $data = $command->data;

            if ($data->isPrimary && $data->contactableType && $data->contactableId) {
                ContactPerson::where('tenant_id', $tenantId)
                    ->where('contactable_type', $data->contactableType)
                    ->where('contactable_id', $data->contactableId)
                    ->where('id', '!=', $contact->id)
                    ->update(['is_primary' => false]);
            }

            $oldValues = $contact->toArray();
            $contact->update($data->toArray());

            if (! empty($data->groupIds)) {
                $contact->groups()->syncWithPivotValues($data->groupIds, ['tenant_id' => $tenantId]);
            }

            $this->auditService->log(
                action: 'updated',
                model: $contact,
                oldValues: $oldValues,
                newValues: $contact->fresh()->toArray()
            );

            return $contact;
        });
    }
}

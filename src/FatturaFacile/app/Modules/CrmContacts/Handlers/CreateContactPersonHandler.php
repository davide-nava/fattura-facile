<?php

namespace App\Modules\CrmContacts\Handlers;

use App\Modules\Audit\Services\AuditService;
use App\Modules\Companies\Models\Company;
use App\Modules\CrmContacts\Commands\CreateContactPersonCommand;
use App\Modules\CrmContacts\Models\ContactPerson;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Support\Facades\DB;

class CreateContactPersonHandler
{
    public function __construct(
        private AuditService $auditService,
        private TenantContext $tenantContext
    ) {}

    public function handle(CreateContactPersonCommand $command): ContactPerson
    {
        return DB::transaction(function () use ($command) {
            $data = $command->data;
            $company = Company::findOrFail($data->companyId);
            $tenantId = $this->tenantContext->getTenantId() ?? $company->tenant_id;

            if ($data->isPrimary && $data->contactableType && $data->contactableId) {
                ContactPerson::where('tenant_id', $tenantId)
                    ->where('contactable_type', $data->contactableType)
                    ->where('contactable_id', $data->contactableId)
                    ->update(['is_primary' => false]);
            }

            $contact = ContactPerson::create(array_merge($data->toArray(), [
                'tenant_id' => $tenantId,
            ]));

            if (! empty($data->groupIds)) {
                $contact->groups()->syncWithPivotValues($data->groupIds, ['tenant_id' => $tenantId]);
            }

            $this->auditService->log(
                action: 'created',
                model: $contact,
                newValues: $contact->toArray()
            );

            return $contact;
        });
    }
}

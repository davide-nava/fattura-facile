<?php

namespace App\Modules\CrmContacts\Handlers;

use App\Modules\Audit\Services\AuditService;
use App\Modules\Companies\Models\Company;
use App\Modules\CrmContacts\Commands\CreateContactGroupCommand;
use App\Modules\CrmContacts\Models\ContactGroup;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Support\Facades\DB;

class CreateContactGroupHandler
{
    public function __construct(
        private AuditService $auditService,
        private TenantContext $tenantContext
    ) {}

    public function handle(CreateContactGroupCommand $command): ContactGroup
    {
        return DB::transaction(function () use ($command) {
            $data = $command->data;
            $company = Company::findOrFail($data->companyId);
            $tenantId = $this->tenantContext->getTenantId() ?? $company->tenant_id;

            $group = ContactGroup::create(array_merge($data->toArray(), [
                'tenant_id' => $tenantId,
            ]));

            $this->auditService->log(
                action: 'created',
                model: $group,
                newValues: $group->toArray()
            );

            return $group;
        });
    }
}

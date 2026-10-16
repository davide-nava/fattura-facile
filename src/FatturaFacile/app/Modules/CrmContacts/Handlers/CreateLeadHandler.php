<?php

namespace App\Modules\CrmContacts\Handlers;

use App\Modules\Audit\Services\AuditService;
use App\Modules\Companies\Models\Company;
use App\Modules\CRM\Models\Lead;
use App\Modules\CrmContacts\Commands\CreateLeadCommand;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Support\Facades\DB;

class CreateLeadHandler
{
    public function __construct(
        private AuditService $auditService,
        private TenantContext $tenantContext
    ) {}

    public function handle(CreateLeadCommand $command): Lead
    {
        return DB::transaction(function () use ($command) {
            $data = $command->data;
            $company = Company::findOrFail($data->companyId);
            $tenantId = $this->tenantContext->getTenantId() ?? $company->tenant_id;

            $lead = Lead::create(array_merge($data->toArray(), [
                'tenant_id' => $tenantId,
            ]));

            if (! empty($data->groupIds)) {
                $lead->groups()->syncWithPivotValues($data->groupIds, ['tenant_id' => $tenantId]);
            }

            $this->auditService->log(
                action: 'created',
                model: $lead,
                newValues: $lead->toArray()
            );

            return $lead;
        });
    }
}

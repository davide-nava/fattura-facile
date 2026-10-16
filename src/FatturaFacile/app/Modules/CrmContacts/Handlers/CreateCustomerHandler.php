<?php

namespace App\Modules\CrmContacts\Handlers;

use App\Modules\Audit\Services\AuditService;
use App\Modules\Companies\Models\Company;
use App\Modules\Companies\Models\DocumentSequence;
use App\Modules\CrmContacts\Commands\CreateCustomerCommand;
use App\Modules\Customers\Models\Customer;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Support\Facades\DB;

class CreateCustomerHandler
{
    public function __construct(
        private AuditService $auditService,
        private TenantContext $tenantContext
    ) {}

    public function handle(CreateCustomerCommand $command): Customer
    {
        return DB::transaction(function () use ($command) {
            $data = $command->data;
            $company = Company::findOrFail($data->companyId);
            $tenantId = $this->tenantContext->getTenantId() ?? $company->tenant_id;

            $code = $data->code;
            if (empty($code)) {
                $code = DocumentSequence::getNextNumber($tenantId, $data->companyId, 'customer', 'CUST-');
            }

            $customer = Customer::create(array_merge($data->toArray(), [
                'tenant_id' => $tenantId,
                'code' => $code,
            ]));

            if (! empty($data->groupIds)) {
                $customer->groups()->syncWithPivotValues($data->groupIds, ['tenant_id' => $tenantId]);
            }

            $this->auditService->log(
                action: 'created',
                model: $customer,
                newValues: $customer->toArray()
            );

            return $customer;
        });
    }
}

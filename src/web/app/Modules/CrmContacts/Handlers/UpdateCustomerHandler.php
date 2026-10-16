<?php

namespace App\Modules\CrmContacts\Handlers;

use App\Modules\Audit\Services\AuditService;
use App\Modules\CrmContacts\Commands\UpdateCustomerCommand;
use App\Modules\Customers\Models\Customer;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Support\Facades\DB;

class UpdateCustomerHandler
{
    public function __construct(
        private AuditService $auditService,
        private TenantContext $tenantContext
    ) {}

    public function handle(UpdateCustomerCommand $command): Customer
    {
        return DB::transaction(function () use ($command) {
            $customer = Customer::findOrFail($command->customerId);
            $tenantId = $this->tenantContext->getTenantId() ?? $customer->tenant_id;
            $oldValues = $customer->toArray();

            $customer->update($command->data->toArray());

            if (! empty($command->data->groupIds)) {
                $customer->groups()->syncWithPivotValues($command->data->groupIds, ['tenant_id' => $tenantId]);
            }

            $this->auditService->log(
                action: 'updated',
                model: $customer,
                oldValues: $oldValues,
                newValues: $customer->fresh()->toArray()
            );

            return $customer;
        });
    }
}

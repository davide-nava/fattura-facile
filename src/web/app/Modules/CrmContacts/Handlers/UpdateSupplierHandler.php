<?php

namespace App\Modules\CrmContacts\Handlers;

use App\Modules\Audit\Services\AuditService;
use App\Modules\CrmContacts\Commands\UpdateSupplierCommand;
use App\Modules\Suppliers\Models\Supplier;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Support\Facades\DB;

class UpdateSupplierHandler
{
    public function __construct(
        private AuditService $auditService,
        private TenantContext $tenantContext
    ) {}

    public function handle(UpdateSupplierCommand $command): Supplier
    {
        return DB::transaction(function () use ($command) {
            $supplier = Supplier::findOrFail($command->supplierId);
            $tenantId = $this->tenantContext->getTenantId() ?? $supplier->tenant_id;
            $oldValues = $supplier->toArray();

            $supplier->update($command->data->toArray());

            if (! empty($command->data->groupIds)) {
                $supplier->groups()->syncWithPivotValues($command->data->groupIds, ['tenant_id' => $tenantId]);
            }

            $this->auditService->log(
                action: 'updated',
                model: $supplier,
                oldValues: $oldValues,
                newValues: $supplier->fresh()->toArray()
            );

            return $supplier;
        });
    }
}

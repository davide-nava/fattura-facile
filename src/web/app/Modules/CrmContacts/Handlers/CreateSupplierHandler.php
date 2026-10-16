<?php

namespace App\Modules\CrmContacts\Handlers;

use App\Modules\Audit\Services\AuditService;
use App\Modules\Companies\Models\Company;
use App\Modules\Companies\Models\DocumentSequence;
use App\Modules\CrmContacts\Commands\CreateSupplierCommand;
use App\Modules\Suppliers\Models\Supplier;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Support\Facades\DB;

class CreateSupplierHandler
{
    public function __construct(
        private AuditService $auditService,
        private TenantContext $tenantContext
    ) {}

    public function handle(CreateSupplierCommand $command): Supplier
    {
        return DB::transaction(function () use ($command) {
            $data = $command->data;
            $company = Company::findOrFail($data->companyId);
            $tenantId = $this->tenantContext->getTenantId() ?? $company->tenant_id;

            $code = $data->code;
            if (empty($code)) {
                $code = DocumentSequence::getNextNumber($tenantId, $data->companyId, 'supplier', 'SUP-');
            }

            $supplier = Supplier::create(array_merge($data->toArray(), [
                'tenant_id' => $tenantId,
                'code' => $code,
            ]));

            if (! empty($data->groupIds)) {
                $supplier->groups()->syncWithPivotValues($data->groupIds, ['tenant_id' => $tenantId]);
            }

            $this->auditService->log(
                action: 'created',
                model: $supplier,
                newValues: $supplier->toArray()
            );

            return $supplier;
        });
    }
}

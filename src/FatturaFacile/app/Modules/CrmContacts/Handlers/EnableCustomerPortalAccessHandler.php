<?php

namespace App\Modules\CrmContacts\Handlers;

use App\Modules\Audit\Services\AuditService;
use App\Modules\CrmContacts\Commands\EnableCustomerPortalAccessCommand;
use App\Modules\CrmContacts\Models\CustomerPortalUser;
use App\Modules\Customers\Models\Customer;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnableCustomerPortalAccessHandler
{
    public function __construct(
        private AuditService $auditService,
        private TenantContext $tenantContext
    ) {}

    public function handle(EnableCustomerPortalAccessCommand $command): CustomerPortalUser
    {
        return DB::transaction(function () use ($command) {
            $customer = Customer::findOrFail($command->customerId);
            $tenantId = $this->tenantContext->getTenantId() ?? $customer->tenant_id;
            $companyId = $customer->company_id;

            $name = $command->name ?? $customer->name;

            /** @var CustomerPortalUser $portalUser */
            $portalUser = CustomerPortalUser::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'email' => $command->email,
                ],
                [
                    'company_id' => $companyId,
                    'customer_id' => $customer->id,
                    'contact_person_id' => $command->contactPersonId,
                    'name' => $name,
                    'password' => $command->password,
                    'access_token' => Str::random(64),
                    'status' => 'active',
                ]
            );

            $this->auditService->log(
                action: 'portal_access_enabled',
                model: $portalUser,
                newValues: [
                    'customer_id' => $customer->id,
                    'email' => $command->email,
                    'status' => 'active',
                ]
            );

            return $portalUser;
        });
    }
}

<?php

namespace App\Modules\CrmContacts\Handlers;

use App\Modules\Audit\Services\AuditService;
use App\Modules\Companies\Models\DocumentSequence;
use App\Modules\CRM\Models\Lead;
use App\Modules\CrmContacts\Commands\ConvertLeadToCustomerCommand;
use App\Modules\CrmContacts\Models\ContactPerson;
use App\Modules\Customers\Models\Customer;
use App\Modules\Tenancy\Services\TenantContext;
use DomainException;
use Illuminate\Support\Facades\DB;

class ConvertLeadToCustomerHandler
{
    public function __construct(
        private AuditService $auditService,
        private TenantContext $tenantContext
    ) {}

    public function handle(ConvertLeadToCustomerCommand $command): Customer
    {
        return DB::transaction(function () use ($command) {
            $lead = Lead::with('opportunities')->findOrFail($command->leadId);

            if ($lead->status === 'converted' || $lead->converted_customer_id !== null) {
                throw new DomainException("Lead #{$lead->id} has already been converted to a customer.");
            }

            $tenantId = $this->tenantContext->getTenantId() ?? $lead->tenant_id;
            $companyId = $lead->company_id;

            $code = $command->customerCode;
            if (empty($code)) {
                $code = DocumentSequence::getNextNumber($tenantId, $companyId, 'customer', 'CUST-');
            }

            $customerName = ! empty($lead->company_name) ? $lead->company_name : $lead->contact_name;

            // 1. Create the new customer
            $customer = Customer::create([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'code' => $code,
                'name' => $customerName,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'currency' => $command->currency,
                'payment_term_days' => $command->paymentTermDays,
                'is_active' => true,
                'notes' => "Converted from Lead #{$lead->id} ({$lead->title})",
            ]);

            // 2. Create primary contact person if contact name exists
            if (! empty($lead->contact_name)) {
                $nameParts = explode(' ', trim($lead->contact_name), 2);
                $firstName = $nameParts[0];
                $lastName = $nameParts[1] ?? '';

                ContactPerson::create([
                    'tenant_id' => $tenantId,
                    'company_id' => $companyId,
                    'contactable_type' => Customer::class,
                    'contactable_id' => $customer->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'is_primary' => true,
                    'is_active' => true,
                ]);
            }

            // 3. Link opportunities associated with this lead to the new customer
            $lead->opportunities()->update([
                'customer_id' => $customer->id,
            ]);

            // 4. Update lead status to converted
            $lead->update([
                'status' => 'converted',
                'converted_customer_id' => $customer->id,
                'converted_at' => now(),
            ]);

            $this->auditService->log(
                action: 'created_from_lead',
                model: $customer,
                newValues: [
                    'lead_id' => $lead->id,
                    'customer_id' => $customer->id,
                ]
            );

            return $customer;
        });
    }
}

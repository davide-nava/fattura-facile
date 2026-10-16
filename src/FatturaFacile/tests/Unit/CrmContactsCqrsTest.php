<?php

namespace Tests\Unit;

use App\Models\User;
use App\Modules\Companies\Models\Company;
use App\Modules\CRM\Models\Opportunity;
use App\Modules\CrmContacts\Commands\AssignContactToGroupCommand;
use App\Modules\CrmContacts\Commands\ConvertLeadToCustomerCommand;
use App\Modules\CrmContacts\Commands\CreateContactGroupCommand;
use App\Modules\CrmContacts\Commands\CreateContactPersonCommand;
use App\Modules\CrmContacts\Commands\CreateCustomerCommand;
use App\Modules\CrmContacts\Commands\CreateLeadCommand;
use App\Modules\CrmContacts\Commands\CreateSupplierCommand;
use App\Modules\CrmContacts\Commands\EnableCustomerPortalAccessCommand;
use App\Modules\CrmContacts\Commands\UpdateContactPersonCommand;
use App\Modules\CrmContacts\Commands\UpdateCustomerCommand;
use App\Modules\CrmContacts\Commands\UpdateSupplierCommand;
use App\Modules\CrmContacts\DTOs\ContactGroupData;
use App\Modules\CrmContacts\DTOs\ContactPersonData;
use App\Modules\CrmContacts\DTOs\CustomerData;
use App\Modules\CrmContacts\DTOs\LeadData;
use App\Modules\CrmContacts\DTOs\SupplierData;
use App\Modules\CrmContacts\Handlers\AssignContactToGroupHandler;
use App\Modules\CrmContacts\Handlers\ConvertLeadToCustomerHandler;
use App\Modules\CrmContacts\Handlers\CreateContactGroupHandler;
use App\Modules\CrmContacts\Handlers\CreateContactPersonHandler;
use App\Modules\CrmContacts\Handlers\CreateCustomerHandler;
use App\Modules\CrmContacts\Handlers\CreateLeadHandler;
use App\Modules\CrmContacts\Handlers\CreateSupplierHandler;
use App\Modules\CrmContacts\Handlers\EnableCustomerPortalAccessHandler;
use App\Modules\CrmContacts\Handlers\GetContactOverviewHandler;
use App\Modules\CrmContacts\Handlers\GetCustomerSalesStatsHandler;
use App\Modules\CrmContacts\Handlers\GetPortalCustomerDashboardHandler;
use App\Modules\CrmContacts\Handlers\UpdateContactPersonHandler;
use App\Modules\CrmContacts\Handlers\UpdateCustomerHandler;
use App\Modules\CrmContacts\Handlers\UpdateSupplierHandler;
use App\Modules\CrmContacts\Models\ContactGroup;
use App\Modules\CrmContacts\Models\ContactPerson;
use App\Modules\CrmContacts\Queries\GetContactOverviewQuery;
use App\Modules\CrmContacts\Queries\GetCustomerSalesStatsQuery;
use App\Modules\CrmContacts\Queries\GetPortalCustomerDashboardQuery;
use App\Modules\Customers\Models\Customer;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Sales\Models\Quote;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantContext;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmContactsCqrsTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Company $company;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'CQRS Tenant',
            'slug' => 'cqrs-tenant',
            'domain' => 'cqrs.test',
            'is_active' => true,
        ]);

        $this->company = Company::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'CQRS Enterprise AG',
            'currency' => 'CHF',
            'country' => 'CH',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'CQRS Admin',
            'email' => 'admin@cqrs.test',
            'password' => bcrypt('password'),
            'current_tenant_id' => $this->tenant->id,
            'is_superadmin' => true,
        ]);

        app(TenantContext::class)->setTenant($this->tenant);
    }

    public function test_create_and_update_customer_handlers(): void
    {
        $group = ContactGroup::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Key Accounts',
            'type' => 'customer',
        ]);

        $createHandler = app(CreateCustomerHandler::class);
        $customerData = new CustomerData(
            companyId: $this->company->id,
            name: 'Stark Industries',
            email: 'tony@stark.com',
            phone: '+41 44 999 8877',
            city: 'Zurich',
            country: 'CH',
            groupIds: [$group->id]
        );

        $customer = $createHandler->handle(new CreateCustomerCommand($customerData));
        $this->assertNotNull($customer->id);
        $this->assertStringStartsWith('CUST-', $customer->code);
        $this->assertCount(1, $customer->groups);

        // Update customer
        $updateHandler = app(UpdateCustomerHandler::class);
        $updateData = new CustomerData(
            companyId: $this->company->id,
            name: 'Stark Industries International',
            city: 'Geneva',
            groupIds: [$group->id]
        );

        $updated = $updateHandler->handle(new UpdateCustomerCommand($customer->id, $updateData));
        $this->assertEquals('Stark Industries International', $updated->name);
        $this->assertEquals('Geneva', $updated->city);
    }

    public function test_create_and_update_supplier_handlers(): void
    {
        $createHandler = app(CreateSupplierHandler::class);
        $supplierData = new SupplierData(
            companyId: $this->company->id,
            name: 'Wayne Enterprises Supply',
            email: 'procurement@wayne.com',
            city: 'Basel'
        );

        $supplier = $createHandler->handle(new CreateSupplierCommand($supplierData));
        $this->assertNotNull($supplier->id);
        $this->assertStringStartsWith('SUP-', $supplier->code);

        $updateHandler = app(UpdateSupplierHandler::class);
        $updateData = new SupplierData(
            companyId: $this->company->id,
            name: 'Wayne Logistics AG',
            city: 'Bern'
        );

        $updated = $updateHandler->handle(new UpdateSupplierCommand($supplier->id, $updateData));
        $this->assertEquals('Wayne Logistics AG', $updated->name);
        $this->assertEquals('Bern', $updated->city);
    }

    public function test_lead_lifecycle_and_conversion_handler(): void
    {
        $createHandler = app(CreateLeadHandler::class);
        $leadData = new LeadData(
            companyId: $this->company->id,
            title: 'Enterprise ERP Deal',
            contactName: 'Peter Parker',
            companyName: 'Daily Bugle',
            email: 'peter@bugle.com',
            phone: '+41 78 555 4321',
            status: 'qualified',
            assignedTo: $this->user->id
        );

        $lead = $createHandler->handle(new CreateLeadCommand($leadData));
        $this->assertNotNull($lead->id);

        $opportunity = Opportunity::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'lead_id' => $lead->id,
            'name' => 'Media Management Suite',
            'expected_revenue' => '35000.00',
            'stage' => 'proposal',
        ]);

        $convertHandler = app(ConvertLeadToCustomerHandler::class);
        $customer = $convertHandler->handle(new ConvertLeadToCustomerCommand($lead->id));

        $this->assertNotNull($customer->id);
        $this->assertEquals('Daily Bugle', $customer->name);
        $this->assertEquals('peter@bugle.com', $customer->email);

        $lead->refresh();
        $this->assertEquals('converted', $lead->status);
        $this->assertEquals($customer->id, $lead->converted_customer_id);
        $this->assertNotNull($lead->converted_at);

        $opportunity->refresh();
        $this->assertEquals($customer->id, $opportunity->customer_id);

        // Verify primary contact person created
        $contactPerson = ContactPerson::where('contactable_type', Customer::class)
            ->where('contactable_id', $customer->id)
            ->first();
        $this->assertNotNull($contactPerson);
        $this->assertEquals('Peter', $contactPerson->first_name);
        $this->assertEquals('Parker', $contactPerson->last_name);
        $this->assertTrue($contactPerson->is_primary);

        // Re-converting should fail
        $this->expectException(DomainException::class);
        $convertHandler->handle(new ConvertLeadToCustomerCommand($lead->id));
    }

    public function test_contact_person_creation_and_primary_switch(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'code' => 'CUST-PRIMARY-TEST',
            'name' => 'Cyberdyne Systems',
            'is_active' => true,
        ]);

        $createHandler = app(CreateContactPersonHandler::class);

        $contact1 = $createHandler->handle(new CreateContactPersonCommand(new ContactPersonData(
            companyId: $this->company->id,
            firstName: 'Miles',
            lastName: 'Dyson',
            contactableType: Customer::class,
            contactableId: $customer->id,
            isPrimary: true
        )));

        $this->assertTrue($contact1->is_primary);

        // Add second contact with isPrimary = true
        $contact2 = $createHandler->handle(new CreateContactPersonCommand(new ContactPersonData(
            companyId: $this->company->id,
            firstName: 'John',
            lastName: 'Connor',
            contactableType: Customer::class,
            contactableId: $customer->id,
            isPrimary: true
        )));

        $this->assertTrue($contact2->is_primary);
        $contact1->refresh();
        $this->assertFalse($contact1->is_primary);

        // Update contact1 to become primary again
        $updateHandler = app(UpdateContactPersonHandler::class);
        $updateHandler->handle(new UpdateContactPersonCommand($contact1->id, new ContactPersonData(
            companyId: $this->company->id,
            firstName: 'Miles',
            lastName: 'Dyson',
            contactableType: Customer::class,
            contactableId: $customer->id,
            isPrimary: true
        )));

        $contact1->refresh();
        $contact2->refresh();
        $this->assertTrue($contact1->is_primary);
        $this->assertFalse($contact2->is_primary);
    }

    public function test_contact_group_and_membership_handlers(): void
    {
        $groupHandler = app(CreateContactGroupHandler::class);
        $group = $groupHandler->handle(new CreateContactGroupCommand(new ContactGroupData(
            companyId: $this->company->id,
            name: 'Government Contractors',
            code: 'GOV',
            type: 'customer'
        )));

        $this->assertEquals('GOV', $group->code);

        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'code' => 'C-GOV-01',
            'name' => 'Federal Bureau',
            'is_active' => true,
        ]);

        $assignHandler = app(AssignContactToGroupHandler::class);
        $membership = $assignHandler->handle(new AssignContactToGroupCommand(
            groupId: $group->id,
            memberType: Customer::class,
            memberId: $customer->id
        ));

        $this->assertNotNull($membership->id);
        $this->assertEquals($customer->id, $membership->member_id);
    }

    public function test_enable_customer_portal_access_handler(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'code' => 'C-PORTAL-TEST',
            'name' => 'Acme Portal Corp',
            'is_active' => true,
        ]);

        $handler = app(EnableCustomerPortalAccessHandler::class);
        $portalUser = $handler->handle(new EnableCustomerPortalAccessCommand(
            customerId: $customer->id,
            email: 'portal@acme.com',
            password: 'SecurePassword2026!',
            name: 'Acme Admin'
        ));

        $this->assertNotNull($portalUser->id);
        $this->assertEquals('active', $portalUser->status);
        $this->assertNotNull($portalUser->access_token);
        $this->assertEquals($customer->id, $portalUser->customer_id);

        // Idempotent update
        $updatedPortalUser = $handler->handle(new EnableCustomerPortalAccessCommand(
            customerId: $customer->id,
            email: 'portal@acme.com',
            password: 'NewSecurePassword2026!',
            name: 'Acme Super Admin'
        ));

        $this->assertEquals($portalUser->id, $updatedPortalUser->id);
        $this->assertEquals('Acme Super Admin', $updatedPortalUser->name);
    }

    public function test_customer_sales_stats_handler(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'code' => 'C-STATS-01',
            'name' => 'Stats Client',
            'currency' => 'CHF',
            'is_active' => true,
        ]);

        Quote::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'quote_number' => 'QUO-STATS-01',
            'quote_date' => now(),
            'total' => '2500.00',
            'status' => 'accepted',
        ]);

        SalesOrder::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'order_number' => 'SO-STATS-01',
            'order_date' => now(),
            'total' => '2500.00',
            'status' => 'confirmed',
        ]);

        Invoice::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-STATS-01',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'paid',
            'currency' => 'CHF',
            'total' => '1500.00',
            'outstanding_amount' => '0.00',
        ]);

        Invoice::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-STATS-02',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'issued',
            'currency' => 'CHF',
            'total' => '1000.00',
            'outstanding_amount' => '1000.00',
        ]);

        $statsHandler = app(GetCustomerSalesStatsHandler::class);
        $stats = $statsHandler->handle(new GetCustomerSalesStatsQuery(customerId: $customer->id));

        $this->assertEquals('2500.00', $stats->totalInvoiced);
        $this->assertEquals('1000.00', $stats->totalOutstanding);
        $this->assertEquals('1500.00', $stats->totalPaid);
        $this->assertEquals(2, $stats->invoicesCount);
        $this->assertEquals(1, $stats->quotesCount);
        $this->assertEquals(1, $stats->salesOrdersCount);
        $this->assertEquals('1250.00', $stats->averageOrderValue);
        $this->assertNotNull($stats->lastInvoiceDate);
        $this->assertNotNull($stats->lastOrderDate);

        // Stats with year filter matching current year
        $statsYear = $statsHandler->handle(new GetCustomerSalesStatsQuery(customerId: $customer->id, year: (int) date('Y')));
        $this->assertEquals('2500.00', $statsYear->totalInvoiced);

        // Stats for empty customer
        $emptyCustomer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Empty Stats Customer',
            'is_active' => true,
        ]);
        $emptyStats = $statsHandler->handle(new GetCustomerSalesStatsQuery($emptyCustomer->id));
        $this->assertEquals('0.00', $emptyStats->totalInvoiced);
        $this->assertEquals('0.00', $emptyStats->averageOrderValue);
    }

    public function test_contact_overview_and_portal_dashboard_handlers(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'code' => 'CUST-PORTAL-DASH',
            'name' => 'Portal Dash Corp',
            'currency' => 'CHF',
            'is_active' => true,
        ]);

        $overviewHandler = app(GetContactOverviewHandler::class);
        $overview = $overviewHandler->handle(new GetContactOverviewQuery);

        $this->assertGreaterThanOrEqual(1, $overview->totalCustomers);
        $this->assertIsArray($overview->recentContacts);
        $this->assertIsArray($overview->toArray());

        // Portal Dashboard Query

        $portalUser = app(EnableCustomerPortalAccessHandler::class)->handle(new EnableCustomerPortalAccessCommand(
            customerId: $customer->id,
            email: 'dash@portal.ch',
            password: 'secret',
            name: 'Dash User'
        ));

        $dashboardHandler = app(GetPortalCustomerDashboardHandler::class);
        $dashboardData = $dashboardHandler->handle(new GetPortalCustomerDashboardQuery($portalUser->id));

        $this->assertEquals($customer->id, $dashboardData->customer['id']);
        $this->assertEquals('CHF', $dashboardData->currency);
        $this->assertEquals('0.00', $dashboardData->totalInvoiced);
        $this->assertIsArray($dashboardData->toArray());
    }
}

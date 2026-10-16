<?php

namespace Tests\Unit;

use App\Models\User;
use App\Modules\Companies\Models\Company;
use App\Modules\CRM\Models\Lead;
use App\Modules\CrmContacts\Enums\ContactType;
use App\Modules\CrmContacts\Enums\GroupType;
use App\Modules\CrmContacts\Enums\LeadStatus;
use App\Modules\CrmContacts\Enums\PortalAccessStatus;
use App\Modules\CrmContacts\Models\ContactGroup;
use App\Modules\CrmContacts\Models\ContactPerson;
use App\Modules\CrmContacts\Models\CustomerPortalUser;
use App\Modules\Customers\Models\Customer;
use App\Modules\Suppliers\Models\Supplier;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmContactsModelsTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Company $company;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'CRM Tenant',
            'slug' => 'crm-tenant',
            'domain' => 'crm.test',
            'is_active' => true,
        ]);

        $this->company = Company::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'CRM Company AG',
            'currency' => 'CHF',
            'country' => 'CH',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'CRM Agent',
            'email' => 'agent@crm.test',
            'password' => bcrypt('password'),
            'current_tenant_id' => $this->tenant->id,
            'is_superadmin' => false,
        ]);

        app(TenantContext::class)->setTenant($this->tenant);
    }

    public function test_enums_definitions_and_labels(): void
    {
        $this->assertEquals('Customer', ContactType::Customer->label());
        $this->assertEquals('Supplier', ContactType::Supplier->label());
        $this->assertEquals('Lead', ContactType::Lead->label());
        $this->assertEquals('Other', ContactType::Other->label());

        $this->assertEquals('New', LeadStatus::New->label());
        $this->assertEquals('Contacted', LeadStatus::Contacted->label());
        $this->assertEquals('Qualified', LeadStatus::Qualified->label());
        $this->assertEquals('Unqualified', LeadStatus::Unqualified->label());
        $this->assertEquals('Converted to Customer', LeadStatus::Converted->label());

        $this->assertEquals('gray', LeadStatus::New->color());
        $this->assertEquals('info', LeadStatus::Contacted->color());
        $this->assertEquals('warning', LeadStatus::Qualified->color());
        $this->assertEquals('danger', LeadStatus::Unqualified->color());
        $this->assertEquals('success', LeadStatus::Converted->color());

        $this->assertEquals('General', GroupType::General->label());
        $this->assertEquals('Customer', GroupType::Customer->label());
        $this->assertEquals('Supplier', GroupType::Supplier->label());
        $this->assertEquals('Lead', GroupType::Lead->label());

        $this->assertEquals('Active', PortalAccessStatus::Active->label());
        $this->assertEquals('Suspended', PortalAccessStatus::Suspended->label());
        $this->assertEquals('Pending', PortalAccessStatus::Pending->label());
        $this->assertEquals('success', PortalAccessStatus::Active->color());
        $this->assertEquals('danger', PortalAccessStatus::Suspended->color());
        $this->assertEquals('warning', PortalAccessStatus::Pending->color());
    }

    public function test_contact_group_and_memberships_relations(): void
    {
        $group = ContactGroup::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'VIP Accounts',
            'code' => 'VIP',
            'type' => 'customer',
            'color' => '#10B981',
            'description' => 'Top tier clients',
        ]);

        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'code' => 'C-001',
            'name' => 'Alpha Client',
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'code' => 'S-001',
            'name' => 'Alpha Vendor',
            'is_active' => true,
        ]);

        $lead = Lead::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'title' => 'Big Deal',
            'contact_name' => 'John Doe',
            'assigned_to' => $this->user->id,
        ]);

        $contact = ContactPerson::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'contactable_type' => Customer::class,
            'contactable_id' => $customer->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@alpha.test',
            'is_primary' => true,
            'is_active' => true,
        ]);

        // Attach members to group
        $customer->groups()->attach($group->id, ['tenant_id' => $this->tenant->id]);
        $supplier->groups()->attach($group->id, ['tenant_id' => $this->tenant->id]);
        $lead->groups()->attach($group->id, ['tenant_id' => $this->tenant->id]);
        $contact->groups()->attach($group->id, ['tenant_id' => $this->tenant->id]);

        $this->assertEquals($this->tenant->id, $group->tenant->id);
        $this->assertEquals($this->company->id, $group->company->id);
        $this->assertCount(4, $group->memberships);
        $this->assertCount(1, $group->customers);
        $this->assertCount(1, $group->suppliers);
        $this->assertCount(1, $group->leads);
        $this->assertCount(1, $group->contactPersons);

        $this->assertCount(1, $customer->groups);
        $this->assertCount(1, $supplier->groups);
        $this->assertCount(1, $lead->groups);
        $this->assertCount(1, $contact->groups);

        $membership = $group->memberships()->first();
        $this->assertEquals($group->id, $membership->group->id);
        $this->assertNotNull($membership->member);
    }

    public function test_contact_person_attributes_and_relations(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'code' => 'C-002',
            'name' => 'Beta Corp',
            'is_active' => true,
        ]);

        $contact = ContactPerson::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'contactable_type' => Customer::class,
            'contactable_id' => $customer->id,
            'first_name' => 'Marco',
            'last_name' => 'Rossi',
            'job_title' => 'CFO',
            'department' => 'Finance',
            'email' => 'marco@beta.test',
            'phone' => '+41 22 123 4567',
            'mobile' => '+41 79 123 4567',
            'is_primary' => true,
            'is_active' => true,
            'notes' => 'Key decision maker',
        ]);

        $this->assertEquals('Marco Rossi', $contact->full_name);
        $this->assertEquals($customer->id, $contact->contactable->id);
        $this->assertEquals($contact->id, $customer->primaryContact->id);
        $this->assertCount(1, $customer->contactPersons);
    }

    public function test_customer_portal_user_model_and_authentication(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'code' => 'C-003',
            'name' => 'Gamma Ltd',
            'is_active' => true,
        ]);

        $contact = ContactPerson::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'contactable_type' => Customer::class,
            'contactable_id' => $customer->id,
            'first_name' => 'Sarah',
            'last_name' => 'Connor',
            'email' => 'sarah@gamma.test',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $portalUser = CustomerPortalUser::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'contact_person_id' => $contact->id,
            'name' => 'Sarah Connor',
            'email' => 'sarah@gamma.test',
            'password' => 'secret123',
            'access_token' => 'sample-test-token-12345',
            'status' => 'active',
            'last_login_at' => now(),
        ]);

        $this->assertTrue($portalUser->isActive());
        $this->assertTrue(Hash::check('secret123', $portalUser->password));
        $this->assertEquals($customer->id, $portalUser->customer->id);
        $this->assertEquals($contact->id, $portalUser->contactPerson->id);
        $this->assertEquals($portalUser->id, $contact->portalUser->id);
        $this->assertCount(1, $customer->portalUsers);

        $portalUser->update(['status' => 'suspended']);
        $this->assertFalse($portalUser->isActive());
    }

    public function test_lead_conversion_relationships(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'code' => 'C-004',
            'name' => 'Delta Systems',
            'is_active' => true,
        ]);

        $lead = Lead::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'title' => 'Software Contract',
            'contact_name' => 'Alex White',
            'status' => 'converted',
            'converted_customer_id' => $customer->id,
            'converted_at' => now(),
        ]);

        $this->assertEquals($customer->id, $lead->convertedCustomer->id);
        $this->assertNotNull($lead->converted_at);
    }
}

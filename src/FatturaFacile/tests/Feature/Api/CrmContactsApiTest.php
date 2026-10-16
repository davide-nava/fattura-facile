<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Modules\Companies\Models\Company;
use App\Modules\CRM\Models\Lead;
use App\Modules\Customers\Models\Customer;
use App\Modules\Identity\Models\TenantMembership;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmContactsApiTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Company $company;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'API Contacts Tenant',
            'slug' => 'api-contacts-tenant',
            'domain' => 'api-contacts.test',
            'is_active' => true,
        ]);

        $this->company = Company::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'API Contacts AG',
            'currency' => 'CHF',
            'country' => 'CH',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'API User',
            'email' => 'api@contacts.ch',
            'password' => bcrypt('password'),
            'current_tenant_id' => $this->tenant->id,
            'is_superadmin' => false,
        ]);

        TenantMembership::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'role' => 'tenant_admin',
            'is_active' => true,
            'permissions' => ['*'],
        ]);

        app(TenantContext::class)->setTenant($this->tenant);
    }

    public function test_contact_persons_api_crud(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Nestle SA',
            'is_active' => true,
        ]);

        // 1. Create Contact
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', (string) $this->tenant->id)
            ->postJson('/api/v1/contacts', [
                'company_id' => $this->company->id,
                'first_name' => 'Claude',
                'last_name' => 'Dufour',
                'contactable_type' => Customer::class,
                'contactable_id' => $customer->id,
                'email' => 'claude@nestle.com',
                'phone' => '+41 21 924 1111',
                'is_primary' => true,
            ]);

        $response->assertStatus(201);
        $contactId = $response->json('data.id');
        $this->assertNotNull($contactId);

        // 2. Index Contacts
        $listResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', (string) $this->tenant->id)
            ->getJson('/api/v1/contacts?search=Claude');

        $listResponse->assertStatus(200);
        $this->assertCount(1, $listResponse->json('data'));

        // 3. Show Contact
        $showResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', (string) $this->tenant->id)
            ->getJson("/api/v1/contacts/{$contactId}");

        $showResponse->assertStatus(200);
        $this->assertEquals('Claude', $showResponse->json('data.first_name'));

        // 4. Update Contact
        $updateResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', (string) $this->tenant->id)
            ->putJson("/api/v1/contacts/{$contactId}", [
                'company_id' => $this->company->id,
                'first_name' => 'Claude',
                'last_name' => 'Dufour-Meyer',
                'email' => 'claude.meyer@nestle.com',
            ]);

        $updateResponse->assertStatus(200);
        $this->assertEquals('Dufour-Meyer', $updateResponse->json('data.last_name'));
        $this->assertEquals('Claude', $updateResponse->json('data.first_name'));
    }

    public function test_contact_groups_api_crud_and_assign(): void
    {
        // 1. Create Group
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', (string) $this->tenant->id)
            ->postJson('/api/v1/contact-groups', [
                'company_id' => $this->company->id,
                'name' => 'Swiss Luxury Watchmakers',
                'code' => 'SLW',
                'type' => 'customer',
            ]);

        $response->assertStatus(201);
        $groupId = $response->json('data.id');

        // 2. Index Groups
        $listResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', (string) $this->tenant->id)
            ->getJson('/api/v1/contact-groups');

        $listResponse->assertStatus(200);
        $this->assertCount(1, $listResponse->json('data'));

        // 3. Assign Customer to Group
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Rolex SA',
            'is_active' => true,
        ]);

        $assignResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', (string) $this->tenant->id)
            ->postJson("/api/v1/contact-groups/{$groupId}/assign", [
                'member_type' => Customer::class,
                'member_id' => $customer->id,
            ]);

        $assignResponse->assertStatus(201);

        // 4. Show Group with Member
        $showResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', (string) $this->tenant->id)
            ->getJson("/api/v1/contact-groups/{$groupId}");

        $showResponse->assertStatus(200);
        $this->assertCount(1, $showResponse->json('data.memberships'));
    }

    public function test_leads_api_and_conversion(): void
    {
        // 1. Create Lead
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', (string) $this->tenant->id)
            ->postJson('/api/v1/leads', [
                'company_id' => $this->company->id,
                'title' => 'Banking Cloud Migration',
                'contact_name' => 'Marc Biver',
                'company_name' => 'UBS Geneva',
                'email' => 'marc.biver@ubs.ch',
                'status' => 'new',
            ]);

        $response->assertStatus(201);
        $leadId = $response->json('data.id');

        // 2. Index Leads
        $listResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', (string) $this->tenant->id)
            ->getJson('/api/v1/leads?search=UBS');

        $listResponse->assertStatus(200);
        $this->assertCount(1, $listResponse->json('data'));

        // 3. Show Lead
        $showResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', (string) $this->tenant->id)
            ->getJson("/api/v1/leads/{$leadId}");

        $showResponse->assertStatus(200);
        $this->assertEquals('UBS Geneva', $showResponse->json('data.company_name'));

        // 4. Convert Lead to Customer
        $convertResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', (string) $this->tenant->id)
            ->postJson("/api/v1/leads/{$leadId}/convert", [
                'customer_code' => 'CUST-UBS-01',
                'currency' => 'CHF',
                'payment_term_days' => 45,
            ]);

        $convertResponse->assertStatus(200);
        $this->assertEquals('UBS Geneva', $convertResponse->json('customer.name'));
        $this->assertEquals('CUST-UBS-01', $convertResponse->json('customer.code'));
    }

    public function test_customer_stats_and_overview_api(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Novartis Basel',
            'currency' => 'CHF',
            'is_active' => true,
        ]);

        Invoice::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-NOV-01',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'issued',
            'currency' => 'CHF',
            'total' => '8500.00',
            'outstanding_amount' => '8500.00',
        ]);

        $statsResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', (string) $this->tenant->id)
            ->getJson("/api/v1/customers/{$customer->id}/stats");

        $statsResponse->assertStatus(200);
        $this->assertEquals('8500.00', $statsResponse->json('data.total_invoiced'));
        $this->assertEquals('8500.00', $statsResponse->json('data.total_outstanding'));

        $overviewResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', (string) $this->tenant->id)
            ->getJson('/api/v1/contacts-overview');

        $overviewResponse->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $overviewResponse->json('data.total_customers'));
    }
}

<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Modules\Companies\Models\Company;
use App\Modules\Customers\Models\Customer;
use App\Modules\Identity\Models\TenantMembership;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Products\Models\Product;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiControllersTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Company $company;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'API Tenant',
            'slug' => 'api-tenant',
            'domain' => 'api.test',
            'is_active' => true,
        ]);

        $this->company = Company::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'API Company',
            'currency' => 'CHF',
            'country' => 'CH',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'API User',
            'email' => 'api@test.ch',
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

    public function test_api_health_endpoint(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'healthy',
                'platform' => 'FatturaFacile',
                'version' => '1.0.0',
            ]);
    }

    public function test_customer_api_crud_and_search(): void
    {
        $this->actingAs($this->user);

        // Create customer via API
        $createResponse = $this->postJson('/api/v1/customers', [
            'company_id' => $this->company->id,
            'name' => 'Alpha Customer',
            'code' => 'CUST-ALPHA',
            'email' => 'alpha@customer.test',
            'country' => 'CH',
            'currency' => 'CHF',
        ], ['X-Tenant-ID' => $this->tenant->id]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('data.name', 'Alpha Customer');

        $customerId = $createResponse->json('data.id');

        // Validation error on empty payload
        $this->postJson('/api/v1/customers', [], ['X-Tenant-ID' => $this->tenant->id])
            ->assertStatus(422);

        // Show customer
        $this->getJson("/api/v1/customers/{$customerId}", ['X-Tenant-ID' => $this->tenant->id])
            ->assertStatus(200)
            ->assertJsonPath('data.code', 'CUST-ALPHA');

        // Show non-existent customer
        $this->getJson('/api/v1/customers/999999', ['X-Tenant-ID' => $this->tenant->id])
            ->assertStatus(404);

        // Index and search
        $indexResponse = $this->getJson('/api/v1/customers?search=Alpha', ['X-Tenant-ID' => $this->tenant->id]);
        $indexResponse->assertStatus(200);
        $this->assertCount(1, $indexResponse->json('data'));

        $emptySearch = $this->getJson('/api/v1/customers?search=NonExistent', ['X-Tenant-ID' => $this->tenant->id]);
        $emptySearch->assertStatus(200);
        $this->assertCount(0, $emptySearch->json('data'));
    }

    public function test_product_api_index_and_show(): void
    {
        $this->actingAs($this->user);

        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Cloud Hosting Pro',
            'sku' => 'HOST-PRO',
            'type' => 'service',
            'sales_price' => '99.00',
            'is_stock_managed' => false,
            'is_active' => true,
        ]);

        // Index
        $response = $this->getJson('/api/v1/products', ['X-Tenant-ID' => $this->tenant->id]);
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));

        // Search match
        $searchMatch = $this->getJson('/api/v1/products?search=HOST', ['X-Tenant-ID' => $this->tenant->id]);
        $searchMatch->assertStatus(200);
        $this->assertCount(1, $searchMatch->json('data'));

        // Search no match
        $searchNoMatch = $this->getJson('/api/v1/products?search=Unmatched', ['X-Tenant-ID' => $this->tenant->id]);
        $searchNoMatch->assertStatus(200);
        $this->assertCount(0, $searchNoMatch->json('data'));

        // Show
        $this->getJson("/api/v1/products/{$product->id}", ['X-Tenant-ID' => $this->tenant->id])
            ->assertStatus(200)
            ->assertJsonPath('data.sku', 'HOST-PRO');

        $this->getJson('/api/v1/products/88888', ['X-Tenant-ID' => $this->tenant->id])
            ->assertStatus(404);
    }

    public function test_invoice_api_lifecycle(): void
    {
        $this->actingAs($this->user);

        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Invoice Customer',
            'is_active' => true,
        ]);

        // Validation failure
        $this->postJson('/api/v1/invoices', [], ['X-Tenant-ID' => $this->tenant->id])
            ->assertStatus(422);

        // Store invoice
        $storeResponse = $this->postJson('/api/v1/invoices', [
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'currency' => 'CHF',
            'items' => [
                [
                    'description' => 'Development Services',
                    'quantity' => 10,
                    'unit_price' => 150,
                    'tax_rate' => 8.1,
                ],
            ],
            'notes' => 'Net 30 days',
        ], ['X-Tenant-ID' => $this->tenant->id]);

        $storeResponse->assertStatus(201)
            ->assertJsonPath('data.status', 'draft');

        $invoiceId = $storeResponse->json('data.id');

        // Index with status filter
        $indexAll = $this->getJson('/api/v1/invoices', ['X-Tenant-ID' => $this->tenant->id]);
        $indexAll->assertStatus(200);
        $this->assertCount(1, $indexAll->json('data'));

        $indexDraft = $this->getJson('/api/v1/invoices?status=draft', ['X-Tenant-ID' => $this->tenant->id]);
        $indexDraft->assertStatus(200);
        $this->assertCount(1, $indexDraft->json('data'));

        $indexPaid = $this->getJson('/api/v1/invoices?status=paid', ['X-Tenant-ID' => $this->tenant->id]);
        $indexPaid->assertStatus(200);
        $this->assertCount(0, $indexPaid->json('data'));

        // Show
        $this->getJson("/api/v1/invoices/{$invoiceId}", ['X-Tenant-ID' => $this->tenant->id])
            ->assertStatus(200)
            ->assertJsonPath('data.id', $invoiceId);

        // Issue invoice
        $issueResponse = $this->postJson("/api/v1/invoices/{$invoiceId}/issue", [], ['X-Tenant-ID' => $this->tenant->id]);
        $issueResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'issued');

        // Download PDF
        $pdfResponse = $this->get("/api/v1/invoices/{$invoiceId}/pdf", ['X-Tenant-ID' => $this->tenant->id]);
        $pdfResponse->assertStatus(200)
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_tenant_isolation_in_api(): void
    {
        $otherTenant = Tenant::create([
            'name' => 'Foreign Tenant',
            'slug' => 'foreign-tenant',
            'domain' => 'foreign.test',
            'is_active' => true,
        ]);

        $otherCompany = Company::create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Foreign Company',
            'currency' => 'EUR',
            'country' => 'DE',
            'is_active' => true,
        ]);

        $otherCustomer = Customer::create([
            'tenant_id' => $otherTenant->id,
            'company_id' => $otherCompany->id,
            'name' => 'Secret Customer',
            'is_active' => true,
        ]);

        // User belongs to tenant 1, trying to access tenant 2's customer
        $this->actingAs($this->user);

        $this->getJson("/api/v1/customers/{$otherCustomer->id}", ['X-Tenant-ID' => $this->tenant->id])
            ->assertStatus(404);
    }
}

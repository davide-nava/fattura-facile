<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Accounting\Models\Account;
use App\Modules\Accounting\Models\JournalEntry;
use App\Modules\Companies\Models\Branch;
use App\Modules\Companies\Models\Company;
use App\Modules\Customers\Models\Customer;
use App\Modules\Documents\Services\DocumentService;
use App\Modules\Identity\Models\TenantMembership;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Invoicing\Services\InvoiceService;
use App\Modules\Products\Models\Product;
use App\Modules\Reporting\Services\ReportingService;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class ComprehensiveTenantIsolationGatesTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenantA;

    private Tenant $tenantB;

    private Company $companyA;

    private Company $companyB;

    private User $userA;

    private User $userB;

    private TenantContext $tenantContext;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenantContext = app(TenantContext::class);

        // Provision Tenant A
        $this->tenantA = Tenant::create(['name' => 'Tenant A Corp', 'slug' => 'tenant-a', 'is_active' => true]);
        $this->tenantContext->setTenant($this->tenantA);
        $this->companyA = Company::create(['name' => 'Company A', 'currency' => 'CHF']);
        $this->userA = User::factory()->create();
        TenantMembership::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->userA->id,
            'role' => 'tenant_admin',
            'is_active' => true,
        ]);

        // Provision Tenant B
        $this->tenantB = Tenant::create(['name' => 'Tenant B LLC', 'slug' => 'tenant-b', 'is_active' => true]);
        $this->tenantContext->setTenant($this->tenantB);
        $this->companyB = Company::create(['name' => 'Company B', 'currency' => 'EUR']);
        $this->userB = User::factory()->create();
        TenantMembership::create([
            'tenant_id' => $this->tenantB->id,
            'user_id' => $this->userB->id,
            'role' => 'tenant_admin',
            'is_active' => true,
        ]);

        // Reset to Tenant A as baseline
        $this->tenantContext->setTenant($this->tenantA);
    }

    /** Gate 1: Tenant A cannot read Tenant B's customers */
    public function test_tenant_a_cannot_read_tenant_b_customers(): void
    {
        $this->tenantContext->setTenant($this->tenantB);
        $custB = Customer::create(['company_id' => $this->companyB->id, 'name' => 'Customer of B']);

        $this->tenantContext->setTenant($this->tenantA);
        $this->assertNull(Customer::find($custB->id));
        $this->assertCount(0, Customer::where('id', $custB->id)->get());
    }

    /** Gate 2: Tenant A cannot modify or delete Tenant B's invoices */
    public function test_tenant_a_cannot_modify_or_delete_tenant_b_invoices(): void
    {
        $this->tenantContext->setTenant($this->tenantB);
        $custB = Customer::create(['company_id' => $this->companyB->id, 'name' => 'Customer B']);
        $invService = app(InvoiceService::class);
        $invB = $invService->createInvoice($this->companyB->id, $custB->id, [
            ['description' => 'Item B', 'quantity' => 1, 'unit_price' => 100, 'tax_rate' => 0],
        ]);

        $this->tenantContext->setTenant($this->tenantA);

        // Querying for invB under Tenant A returns null
        $this->assertNull(Invoice::find($invB->id));

        // Direct delete query scoped to Tenant A matches 0 rows
        $deleted = Invoice::where('id', $invB->id)->delete();
        $this->assertEquals(0, $deleted);

        // Verify record still exists intact under Tenant B
        $this->tenantContext->setTenant($this->tenantB);
        $this->assertNotNull(Invoice::find($invB->id));
    }

    /** Gate 3: Tenant A cannot access Tenant B's companies or branches */
    public function test_tenant_a_cannot_access_tenant_b_companies_or_branches(): void
    {
        $this->tenantContext->setTenant($this->tenantB);
        $branchB = Branch::create(['company_id' => $this->companyB->id, 'name' => 'Geneva Branch', 'code' => 'GE-01']);

        $this->tenantContext->setTenant($this->tenantA);
        $this->assertNull(Company::find($this->companyB->id));
        $this->assertNull(Branch::find($branchB->id));
    }

    /** Gate 4: Tenant A cannot read Tenant B's stock movements */
    public function test_tenant_a_cannot_read_tenant_b_stock_movements(): void
    {
        $this->tenantContext->setTenant($this->tenantB);
        $prodB = Product::create([
            'company_id' => $this->companyB->id,
            'name' => 'Product B',
            'sku' => 'PROD-B',
        ]);
        $whB = Warehouse::create(['company_id' => $this->companyB->id, 'name' => 'Warehouse B', 'code' => 'WH-B']);
        $movB = StockMovement::create([
            'company_id' => $this->companyB->id,
            'warehouse_id' => $whB->id,
            'product_id' => $prodB->id,
            'movement_type' => 'goods_receipt',
            'quantity' => '100.0000',
            'unit_cost' => '10.0000',
        ]);

        $this->tenantContext->setTenant($this->tenantA);
        $this->assertNull(StockMovement::find($movB->id));
        $this->assertEquals(0, StockMovement::count());
    }

    /** Gate 5: Tenant A cannot access Tenant B's accounting entries */
    public function test_tenant_a_cannot_access_tenant_b_accounting_entries(): void
    {
        $this->tenantContext->setTenant($this->tenantB);
        $accB = Account::create(['company_id' => $this->companyB->id, 'code' => '1000', 'name' => 'Cash', 'type' => 'asset']);
        $jeB = JournalEntry::create([
            'company_id' => $this->companyB->id,
            'entry_number' => 'JE-B-001',
            'entry_date' => '2026-03-01',
        ]);

        $this->tenantContext->setTenant($this->tenantA);
        $this->assertNull(Account::find($accB->id));
        $this->assertNull(JournalEntry::find($jeB->id));
    }

    /** Gate 6: Tenant A cannot download Tenant B's private documents */
    public function test_tenant_a_cannot_download_tenant_b_private_documents(): void
    {
        Storage::fake('local');
        $this->tenantContext->setTenant($this->tenantB);
        $file = UploadedFile::fake()->create('contract.pdf', 100);

        /** @var DocumentService $docService */
        $docService = app(DocumentService::class);
        $attachmentB = $docService->storeAttachment($this->companyB, $file, $this->companyB->id);

        // Switch to Tenant A
        $this->tenantContext->setTenant($this->tenantA);

        $this->expectException(AccessDeniedHttpException::class);
        $docService->downloadAttachment($attachmentB);
    }

    /** Gate 8: Tenant A cannot inspect Tenant B's dashboard metrics */
    public function test_tenant_a_cannot_inspect_tenant_b_dashboard_metrics(): void
    {
        $this->tenantContext->setTenant($this->tenantB);
        $custB = Customer::create(['company_id' => $this->companyB->id, 'name' => 'Big Spender']);
        $invService = app(InvoiceService::class);
        $invB = $invService->createInvoice($this->companyB->id, $custB->id, [
            ['description' => 'Big Contract', 'quantity' => 1, 'unit_price' => 50000.00, 'tax_rate' => 0],
        ]);
        $invService->issueInvoice($invB->id);

        // Switch to Tenant A and get dashboard metrics
        $this->tenantContext->setTenant($this->tenantA);
        /** @var ReportingService $reportService */
        $reportService = app(ReportingService::class);
        $metricsA = $reportService->getDashboardMetrics($this->companyA->id);

        // Tenant A must see zero receivables and zero revenue from Tenant B
        $this->assertEquals('0.00', $metricsA['total_receivables_raw']);
        $this->assertEquals('0.00', $metricsA['month_revenue_raw']);
    }

    /** Gate 9: Tenant A cannot access another tenant's data through API identifiers */
    public function test_api_rejects_cross_tenant_data_access(): void
    {
        $this->tenantContext->setTenant($this->tenantB);
        $custB = Customer::create(['company_id' => $this->companyB->id, 'name' => 'Secret Customer B']);

        // Request as user A with Tenant A header
        $this->actingAs($this->userA);

        $response = $this->withHeader('X-Tenant-ID', (string) $this->tenantA->id)
            ->getJson("/api/v1/customers/{$custB->id}");

        $response->assertStatus(404);
    }

    /** Gate 12: Changing request parameters cannot bypass tenant context */
    public function test_tampered_tenant_id_in_body_is_overwritten_by_validated_context(): void
    {
        $this->actingAs($this->userA);

        // User A attempts to create a customer belonging to Tenant B by injecting tenant_id in body
        $response = $this->withHeader('X-Tenant-ID', (string) $this->tenantA->id)
            ->postJson('/api/v1/customers', [
                'tenant_id' => $this->tenantB->id, // Malicious injection
                'company_id' => $this->companyA->id,
                'name' => 'Attempted Injection',
            ]);

        $response->assertStatus(201);
        $createdId = $response->json('data.id');

        // Verify in DB that tenant_id was stamped with Tenant A, NOT Tenant B
        $record = Customer::withoutGlobalScopes()->find($createdId);
        $this->assertEquals($this->tenantA->id, $record->tenant_id);
    }

    /** Gate 14: Suspended tenant users cannot continue accessing protected functionality */
    public function test_suspended_tenant_membership_denies_access(): void
    {
        // Deactivate user A's membership
        TenantMembership::where('tenant_id', $this->tenantA->id)
            ->where('user_id', $this->userA->id)
            ->update(['is_active' => false]);

        $this->assertFalse($this->userA->fresh()->hasAccessToTenant($this->tenantA->id));
    }

    public function test_document_download_success_and_missing_file(): void
    {
        Storage::fake('local');
        $this->tenantContext->setTenant($this->tenantA);

        $file = UploadedFile::fake()->create('valid.pdf', 50);
        /** @var DocumentService $docService */
        $docService = app(DocumentService::class);
        $attachment = $docService->storeAttachment($this->companyA, $file, $this->companyA->id);

        $response = $docService->downloadAttachment($attachment);
        $this->assertEquals(200, $response->getStatusCode());

        // Delete underlying file to test 404 abort branch
        Storage::disk('local')->delete($attachment->file_path);

        $this->expectException(NotFoundHttpException::class);
        $docService->downloadAttachment($attachment);
    }

    public function test_tenant_context_middleware_from_session_and_default_user_tenant(): void
    {
        $this->actingAs($this->userA);

        // 1. Using session active_tenant_id (line 29)
        $resSession = $this->withSession(['active_tenant_id' => $this->tenantA->id])
            ->getJson('/api/v1/customers');
        $resSession->assertStatus(200);

        // 2. Fallback to user->current_tenant_id (line 32 & 54)
        $this->userA->update(['current_tenant_id' => $this->tenantA->id]);
        $resUser = $this->withSession([])
            ->getJson('/api/v1/customers');
        $resUser->assertStatus(200);
        $resUser->assertSessionHas('active_tenant_id', $this->tenantA->id);
    }
}

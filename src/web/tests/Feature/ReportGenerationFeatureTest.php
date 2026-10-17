<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Companies\Models\Company;
use App\Modules\Customers\Models\Customer;
use App\Modules\Identity\Models\TenantMembership;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Invoicing\Models\InvoiceItem;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Configure PDF driver to use pure PHP dompdf for test speed & independence from Node/Chrome
    Config::set('pdf.default', 'dompdf');

    $this->tenant = Tenant::create(['name' => 'Feature Test Tenant', 'slug' => 'feat-t']);
    app(TenantContext::class)->setTenant($this->tenant);

    $this->user = User::factory()->create([
        'email_verified_at' => now(),
        'current_tenant_id' => $this->tenant->id,
    ]);

    TenantMembership::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'role' => 'tenant_admin',
        'is_active' => true,
        'permissions' => ['*'],
    ]);

    $this->company = Company::create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Swiss Alpine Solutions AG',
        'street' => 'Bahnhofstrasse 10',
        'city' => 'Zürich',
        'postal_code' => '8001',
        'country' => 'CH',
        'currency' => 'CHF',
        'tax_number' => 'CHE-123.456.789',
        'vat_number' => 'CHE-123.456.789 MWST',
        'settings' => [
            'branding' => [
                'primary_color' => '#0d9488',
                'secondary_color' => '#1e293b',
                'footer_text' => 'Official Swiss Alpine Solutions Document',
            ],
        ],
    ]);

    $this->customer = Customer::create([
        'tenant_id' => $this->tenant->id,
        'company_id' => $this->company->id,
        'name' => 'MegaCorp Gmbh',
        'street' => 'Industriestrasse 4',
        'city' => 'Basel',
        'postal_code' => '4000',
        'country' => 'CH',
    ]);
});

test('unauthenticated users are redirected from reports pages to login', function () {
    $this->get('/reports')->assertRedirect('/login');
    $this->get('/reports/financial-summary')->assertRedirect('/login');
    $this->get('/reports/financial-summary/download')->assertRedirect('/login');
    $this->get('/reports/financial-summary/stream')->assertRedirect('/login');
});

test('authenticated user can view reports index page with active branding', function () {
    $response = $this->actingAs($this->user)->get('/reports');

    $response->assertOk()
        ->assertSee('Reports &amp; Document Printing', false)
        ->assertSee('Financial Summary &amp; Health Report', false)
        ->assertSee('Sales &amp; Invoices Journal', false)
        ->assertSee('Swiss VAT &amp; Tax Summary Report', false)
        ->assertSee('Swiss Alpine Solutions AG');
});

test('preview endpoint renders financial summary report with kpis and print layout', function () {
    Invoice::create([
        'tenant_id' => $this->tenant->id,
        'company_id' => $this->company->id,
        'customer_id' => $this->customer->id,
        'invoice_number' => 'INV-2026-001',
        'invoice_date' => CarbonImmutable::today(),
        'due_date' => CarbonImmutable::today()->addDays(30),
        'status' => 'issued',
        'subtotal' => '2500.00',
        'tax_total' => '202.50',
        'total' => '2702.50',
        'outstanding_amount' => '2702.50',
        'currency' => 'CHF',
    ]);

    $response = $this->actingAs($this->user)->get('/reports/financial-summary');

    $response->assertOk()
        ->assertSee('Financial Summary &amp; Health Report', false)
        ->assertSee('Executive Breakdown')
        ->assertSee('Period Revenue')
        ->assertSee('2&#039;702.50 CHF', false)
        ->assertSee('Swiss Alpine Solutions AG')
        ->assertSee('Official Swiss Alpine Solutions Document');
});

test('preview endpoint supports download query flag', function () {
    $response = $this->actingAs($this->user)->get('/reports/financial-summary?download=1');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');

    $disposition = (string) $response->headers->get('Content-Disposition');
    expect($disposition)->toContain('attachment; filename="Report-financial-summary-');
});

test('download endpoint returns valid pdf attachment response', function () {
    $response = $this->actingAs($this->user)->get('/reports/invoice-journal/download');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');

    $disposition = (string) $response->headers->get('Content-Disposition');
    expect($disposition)->toContain('attachment; filename="Report-invoice-journal-');
});

test('stream endpoint returns valid inline pdf response', function () {
    $response = $this->actingAs($this->user)->get('/reports/vat-summary/stream');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');

    $disposition = (string) $response->headers->get('Content-Disposition');
    expect($disposition)->toContain('inline; filename="Report-vat-summary-');
});

test('preview of unknown report returns 404 not found', function () {
    $this->actingAs($this->user)
        ->get('/reports/unknown-xyz-report')
        ->assertNotFound();
});

test('tenant isolation prevents querying report for company of another tenant', function () {
    $otherTenant = Tenant::create(['name' => 'Foreign Tenant', 'slug' => 'foreign-t']);

    $foreignCompany = app(TenantContext::class)->executeAsSystem(function () use ($otherTenant) {
        return Company::create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Foreign Corp SA',
            'country' => 'CH',
            'currency' => 'CHF',
        ]);
    });

    // Attempting to query foreign company while authenticated as tenant user
    $this->actingAs($this->user)
        ->get('/reports/financial-summary?company_id='.$foreignCompany->id)
        ->assertNotFound();
});

test('invoice print controller preview, download, and stream endpoints function correctly', function () {
    $invoice = Invoice::create([
        'tenant_id' => $this->tenant->id,
        'company_id' => $this->company->id,
        'customer_id' => $this->customer->id,
        'invoice_number' => 'INV-PRINT-777',
        'invoice_date' => CarbonImmutable::today(),
        'due_date' => CarbonImmutable::today()->addDays(30),
        'status' => 'issued',
        'subtotal' => '1200.00',
        'tax_total' => '97.20',
        'total' => '1297.20',
        'outstanding_amount' => '1297.20',
        'currency' => 'CHF',
        'notes' => 'Thank you for your business.',
    ]);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'description' => 'Software License',
        'quantity' => '1',
        'unit_price' => '1200.00',
        'tax_rate' => '8.1',
        'tax_amount' => '97.20',
        'total' => '1297.20',
    ]);

    // Preview
    $previewResponse = $this->actingAs($this->user)->get("/invoices/{$invoice->id}/preview");
    $previewResponse->assertOk()
        ->assertSee('INV-PRINT-777')
        ->assertSee('Software License')
        ->assertSee('Swiss Alpine Solutions AG');

    // Download
    $downloadResponse = $this->actingAs($this->user)->get("/invoices/{$invoice->id}/download");
    $downloadResponse->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
    expect((string) $downloadResponse->headers->get('Content-Disposition'))
        ->toContain('attachment; filename="Invoice-INV-PRINT-777.pdf"');

    // Stream
    $streamResponse = $this->actingAs($this->user)->get("/invoices/{$invoice->id}/stream");
    $streamResponse->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
    expect((string) $streamResponse->headers->get('Content-Disposition'))
        ->toContain('inline; filename="Invoice-INV-PRINT-777.pdf"');
});

test('invoice print controller rejects access to cross-tenant invoice', function () {
    $otherTenant = Tenant::create(['name' => 'Competitor Tenant', 'slug' => 'comp-t']);

    $otherInvoice = app(TenantContext::class)->executeAsSystem(function () use ($otherTenant) {
        $otherCompany = Company::create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Comp SA',
            'currency' => 'CHF',
        ]);
        $otherCustomer = Customer::create([
            'tenant_id' => $otherTenant->id,
            'company_id' => $otherCompany->id,
            'name' => 'Comp Customer',
        ]);

        return Invoice::create([
            'tenant_id' => $otherTenant->id,
            'company_id' => $otherCompany->id,
            'customer_id' => $otherCustomer->id,
            'invoice_number' => 'INV-SECRET-999',
            'invoice_date' => CarbonImmutable::today(),
            'due_date' => CarbonImmutable::today()->addDays(30),
            'status' => 'issued',
            'subtotal' => '5000.00',
            'tax_total' => '405.00',
            'total' => '5405.00',
            'outstanding_amount' => '5405.00',
            'currency' => 'CHF',
        ]);
    });

    $this->actingAs($this->user)
        ->get("/invoices/{$otherInvoice->id}/preview")
        ->assertNotFound();
});

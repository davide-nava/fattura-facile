<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Customers\Pages\ManageCustomers;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Invoices\Pages\ManageInvoices;
use App\Filament\Resources\JournalEntries\JournalEntryResource;
use App\Filament\Resources\JournalEntries\Pages\ManageJournalEntries;
use App\Filament\Resources\Products\Pages\ManageProducts;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Warehouses\Pages\ManageWarehouses;
use App\Filament\Resources\Warehouses\WarehouseResource;
use App\Filament\Widgets\FinancialOverviewWidget;
use App\Models\User;
use App\Modules\Companies\Models\Company;
use App\Modules\Customers\Models\Customer;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantContext;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

class FilamentResourcesAndWidgetsTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Company $company;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Filament Tenant',
            'slug' => 'filament-tenant',
            'domain' => 'filament.test',
            'is_active' => true,
        ]);

        $this->company = Company::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Filament Company',
            'currency' => 'CHF',
            'country' => 'CH',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Filament Admin',
            'email' => 'admin@filament.test',
            'password' => bcrypt('password'),
            'current_tenant_id' => $this->tenant->id,
            'is_superadmin' => true,
        ]);

        app(TenantContext::class)->setTenant($this->tenant);
    }

    public function test_customer_resource_configuration(): void
    {
        $schema = Schema::make();
        $configuredSchema = CustomerResource::form($schema);
        $this->assertNotEmpty($configuredSchema->getComponents());

        $page = new ManageCustomers;
        $table = Table::make($page);
        $configuredTable = CustomerResource::table($table);
        $this->assertNotEmpty($configuredTable->getColumns());

        $pages = CustomerResource::getPages();
        $this->assertArrayHasKey('index', $pages);

        // Header actions on page
        $refMethod = new ReflectionMethod(ManageCustomers::class, 'getHeaderActions');
        $refMethod->setAccessible(true);
        $actions = $refMethod->invoke($page);
        $this->assertNotEmpty($actions);
    }

    public function test_invoice_resource_configuration_and_status_colors(): void
    {
        $schema = Schema::make();
        $configuredSchema = InvoiceResource::form($schema);
        $this->assertNotEmpty($configuredSchema->getComponents());

        $page = new ManageInvoices;
        $table = Table::make($page);
        $configuredTable = InvoiceResource::table($table);
        $this->assertNotEmpty($configuredTable->getColumns());

        $pages = InvoiceResource::getPages();
        $this->assertArrayHasKey('index', $pages);

        // Test status column color closure
        $statusColumn = $configuredTable->getColumn('status');
        $this->assertNotNull($statusColumn);

        // Inspect color closure
        $refColor = new ReflectionMethod($statusColumn, 'getColor');
        $refColor->setAccessible(true);

        $colorClosure = (new ReflectionClass($statusColumn))->getProperty('color');
        $colorClosure->setAccessible(true);
        $callback = $colorClosure->getValue($statusColumn);

        $this->assertEquals('gray', $callback('draft'));
        $this->assertEquals('info', $callback('issued'));
        $this->assertEquals('warning', $callback('partially_paid'));
        $this->assertEquals('success', $callback('paid'));
        $this->assertEquals('danger', $callback('cancelled'));
        $this->assertEquals('gray', $callback('unknown_status'));

        // Header actions on page
        $refMethod = new ReflectionMethod(ManageInvoices::class, 'getHeaderActions');
        $refMethod->setAccessible(true);
        $actions = $refMethod->invoke($page);
        $this->assertNotEmpty($actions);
    }

    public function test_journal_entry_resource_configuration(): void
    {
        $schema = Schema::make();
        $configuredSchema = JournalEntryResource::form($schema);
        $this->assertNotEmpty($configuredSchema->getComponents());

        $page = new ManageJournalEntries;
        $table = Table::make($page);
        $configuredTable = JournalEntryResource::table($table);
        $this->assertNotEmpty($configuredTable->getColumns());

        $pages = JournalEntryResource::getPages();
        $this->assertArrayHasKey('index', $pages);

        // Header actions on page
        $refMethod = new ReflectionMethod(ManageJournalEntries::class, 'getHeaderActions');
        $refMethod->setAccessible(true);
        $actions = $refMethod->invoke($page);
        $this->assertNotEmpty($actions);
    }

    public function test_product_resource_configuration(): void
    {
        $schema = Schema::make();
        $configuredSchema = ProductResource::form($schema);
        $this->assertNotEmpty($configuredSchema->getComponents());

        $page = new ManageProducts;
        $table = Table::make($page);
        $configuredTable = ProductResource::table($table);
        $this->assertNotEmpty($configuredTable->getColumns());

        $pages = ProductResource::getPages();
        $this->assertArrayHasKey('index', $pages);

        // Header actions on page
        $refMethod = new ReflectionMethod(ManageProducts::class, 'getHeaderActions');
        $refMethod->setAccessible(true);
        $actions = $refMethod->invoke($page);
        $this->assertNotEmpty($actions);
    }

    public function test_warehouse_resource_configuration(): void
    {
        $schema = Schema::make();
        $configuredSchema = WarehouseResource::form($schema);
        $this->assertNotEmpty($configuredSchema->getComponents());

        $page = new ManageWarehouses;
        $table = Table::make($page);
        $configuredTable = WarehouseResource::table($table);
        $this->assertNotEmpty($configuredTable->getColumns());

        $pages = WarehouseResource::getPages();
        $this->assertArrayHasKey('index', $pages);

        // Header actions on page
        $refMethod = new ReflectionMethod(ManageWarehouses::class, 'getHeaderActions');
        $refMethod->setAccessible(true);
        $actions = $refMethod->invoke($page);
        $this->assertNotEmpty($actions);
    }

    public function test_financial_overview_widget_branches(): void
    {
        $widget = new FinancialOverviewWidget;
        $refMethod = new ReflectionMethod(FinancialOverviewWidget::class, 'getStats');
        $refMethod->setAccessible(true);

        // 1. Without tenant context
        app(TenantContext::class)->reset();
        $statsNoTenant = $refMethod->invoke($widget);
        $this->assertCount(1, $statsNoTenant);
        $this->assertEquals('Tenant Context', $statsNoTenant[0]->getLabel());

        // 2. With tenant but company not found
        $emptyTenant = Tenant::create([
            'name' => 'Empty Tenant',
            'slug' => 'empty-tenant',
            'domain' => 'empty.test',
            'is_active' => true,
        ]);
        app(TenantContext::class)->setTenant($emptyTenant);
        $statsNoCompany = $refMethod->invoke($widget);
        $this->assertCount(1, $statsNoCompany);
        $this->assertEquals('Company', $statsNoCompany[0]->getLabel());

        // 3. With tenant and company configured
        app(TenantContext::class)->setTenant($this->tenant);
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Fin Customer',
            'is_active' => true,
        ]);

        Invoice::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-WIDGET-01',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'issued',
            'currency' => 'CHF',
            'total' => '1200.00',
            'outstanding_amount' => '1200.00',
            'issued_at' => now(),
        ]);

        $statsFull = $refMethod->invoke($widget);
        $this->assertCount(4, $statsFull);
        $this->assertEquals('Monthly Revenue', $statsFull[0]->getLabel());
        $this->assertEquals('Trade Receivables', $statsFull[1]->getLabel());
        $this->assertEquals('Trade Payables', $statsFull[2]->getLabel());
        $this->assertEquals('Stock Valuation', $statsFull[3]->getLabel());
    }
}

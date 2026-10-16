<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\ContactGroups\ContactGroupResource;
use App\Filament\Resources\ContactGroups\Pages\ManageContactGroups;
use App\Filament\Resources\ContactPersons\ContactPersonResource;
use App\Filament\Resources\ContactPersons\Pages\ManageContactPersons;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Customers\Pages\ManageCustomers;
use App\Filament\Resources\Leads\LeadResource;
use App\Filament\Resources\Leads\Pages\ManageLeads;
use App\Filament\Resources\Suppliers\Pages\ManageSuppliers;
use App\Filament\Resources\Suppliers\SupplierResource;
use App\Filament\Widgets\CrmStatsOverviewWidget;
use App\Models\User;
use App\Modules\Companies\Models\Company;
use App\Modules\CRM\Models\Lead;
use App\Modules\CRM\Models\Opportunity;
use App\Modules\Customers\Models\Customer;
use App\Modules\Suppliers\Models\Supplier;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantContext;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

class CrmFilamentResourcesTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Company $company;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Filament CRM Tenant',
            'slug' => 'filament-crm-tenant',
            'domain' => 'filament-crm.test',
            'is_active' => true,
        ]);

        $this->company = Company::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Filament CRM AG',
            'currency' => 'CHF',
            'country' => 'CH',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Filament CRM Admin',
            'email' => 'crm.admin@filament.test',
            'password' => bcrypt('password'),
            'current_tenant_id' => $this->tenant->id,
            'is_superadmin' => true,
        ]);

        app(TenantContext::class)->setTenant($this->tenant);
    }

    public function test_supplier_resource_configuration(): void
    {
        $schema = Schema::make();
        $configuredSchema = SupplierResource::form($schema);
        $this->assertNotEmpty($configuredSchema->getComponents());

        $page = new ManageSuppliers;
        $table = Table::make($page);
        $configuredTable = SupplierResource::table($table);
        $this->assertNotEmpty($configuredTable->getColumns());

        $pages = SupplierResource::getPages();
        $this->assertArrayHasKey('index', $pages);

        $refMethod = new ReflectionMethod(ManageSuppliers::class, 'getHeaderActions');
        $refMethod->setAccessible(true);
        $actions = $refMethod->invoke($page);
        $this->assertNotEmpty($actions);
    }

    public function test_lead_resource_configuration_and_status_colors(): void
    {
        $schema = Schema::make();
        $configuredSchema = LeadResource::form($schema);
        $this->assertNotEmpty($configuredSchema->getComponents());

        $page = new ManageLeads;
        $table = Table::make($page);
        $configuredTable = LeadResource::table($table);
        $this->assertNotEmpty($configuredTable->getColumns());

        $pages = LeadResource::getPages();
        $this->assertArrayHasKey('index', $pages);

        // Test status column colors
        $statusColumn = $configuredTable->getColumn('status');
        $this->assertNotNull($statusColumn);

        $colorClosure = (new ReflectionClass($statusColumn))->getProperty('color');
        $colorClosure->setAccessible(true);
        $callback = $colorClosure->getValue($statusColumn);

        $this->assertEquals('gray', $callback('new'));
        $this->assertEquals('info', $callback('contacted'));
        $this->assertEquals('warning', $callback('qualified'));
        $this->assertEquals('danger', $callback('unqualified'));
        $this->assertEquals('success', $callback('converted'));
        $this->assertEquals('gray', $callback('other'));

        $refMethod = new ReflectionMethod(ManageLeads::class, 'getHeaderActions');
        $refMethod->setAccessible(true);
        $actions = $refMethod->invoke($page);
        $this->assertNotEmpty($actions);
    }

    public function test_contact_person_resource_configuration(): void
    {
        $schema = Schema::make();
        $configuredSchema = ContactPersonResource::form($schema);
        $this->assertNotEmpty($configuredSchema->getComponents());

        $page = new ManageContactPersons;
        $table = Table::make($page);
        $configuredTable = ContactPersonResource::table($table);
        $this->assertNotEmpty($configuredTable->getColumns());

        $pages = ContactPersonResource::getPages();
        $this->assertArrayHasKey('index', $pages);

        $refMethod = new ReflectionMethod(ManageContactPersons::class, 'getHeaderActions');
        $refMethod->setAccessible(true);
        $actions = $refMethod->invoke($page);
        $this->assertNotEmpty($actions);
    }

    public function test_contact_group_resource_configuration(): void
    {
        $schema = Schema::make();
        $configuredSchema = ContactGroupResource::form($schema);
        $this->assertNotEmpty($configuredSchema->getComponents());

        $page = new ManageContactGroups;
        $table = Table::make($page);
        $configuredTable = ContactGroupResource::table($table);
        $this->assertNotEmpty($configuredTable->getColumns());

        $pages = ContactGroupResource::getPages();
        $this->assertArrayHasKey('index', $pages);

        $refMethod = new ReflectionMethod(ManageContactGroups::class, 'getHeaderActions');
        $refMethod->setAccessible(true);
        $actions = $refMethod->invoke($page);
        $this->assertNotEmpty($actions);
    }

    public function test_customer_resource_sales_stats_action(): void
    {
        $page = new ManageCustomers;
        $table = Table::make($page);
        $configuredTable = CustomerResource::table($table);

        $salesStatsAction = $configuredTable->getAction('salesStats');
        $this->assertNotNull($salesStatsAction);

        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Modal Client',
            'is_active' => true,
        ]);

        $headingClosure = (new ReflectionClass($salesStatsAction))->getProperty('modalHeading');
        $headingClosure->setAccessible(true);
        $heading = $headingClosure->getValue($salesStatsAction);
        if (is_callable($heading)) {
            $this->assertStringContainsString('Modal Client', $heading($customer));
        }

        $descClosure = (new ReflectionClass($salesStatsAction))->getProperty('modalDescription');
        $descClosure->setAccessible(true);
        $desc = $descClosure->getValue($salesStatsAction);
        if (is_callable($desc)) {
            $this->assertStringContainsString('Total Invoiced', $desc($customer));
        }
    }

    public function test_crm_stats_overview_widget_branches(): void
    {
        $widget = new CrmStatsOverviewWidget;
        $refMethod = new ReflectionMethod(CrmStatsOverviewWidget::class, 'getStats');
        $refMethod->setAccessible(true);

        // 1. Without tenant context
        app(TenantContext::class)->reset();
        $statsNoTenant = $refMethod->invoke($widget);
        $this->assertCount(1, $statsNoTenant);
        $this->assertEquals('Tenant Context', $statsNoTenant[0]->getLabel());

        // 2. With tenant but no company
        $emptyTenant = Tenant::create([
            'name' => 'Empty CRM Tenant',
            'slug' => 'empty-crm-tenant',
            'domain' => 'empty-crm.test',
            'is_active' => true,
        ]);
        app(TenantContext::class)->setTenant($emptyTenant);
        $statsNoCompany = $refMethod->invoke($widget);
        $this->assertCount(1, $statsNoCompany);
        $this->assertEquals('Company', $statsNoCompany[0]->getLabel());

        // 3. With tenant and company configured
        app(TenantContext::class)->setTenant($this->tenant);

        Customer::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Client Widget',
            'is_active' => true,
        ]);

        Supplier::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Vendor Widget',
            'is_active' => true,
        ]);

        Lead::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'title' => 'Widget Lead',
            'contact_name' => 'Sam',
            'status' => 'new',
        ]);

        Opportunity::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Widget Deal',
            'expected_revenue' => '15000.00',
            'stage' => 'proposal',
        ]);

        $statsFull = $refMethod->invoke($widget);
        $this->assertCount(4, $statsFull);
        $this->assertEquals('Active Customers', $statsFull[0]->getLabel());
        $this->assertEquals('Active Suppliers', $statsFull[1]->getLabel());
        $this->assertEquals('Open Leads', $statsFull[2]->getLabel());
        $this->assertEquals('Opportunity Pipeline', $statsFull[3]->getLabel());
    }
}

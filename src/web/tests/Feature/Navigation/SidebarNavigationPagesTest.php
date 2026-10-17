<?php

declare(strict_types=1);

namespace Tests\Feature\Navigation;

use App\Models\User;
use App\Modules\Companies\Models\Company;
use App\Modules\Identity\Models\TenantMembership;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantContext;
use App\Services\Navigation\NavigationManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SidebarNavigationPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Tenant $tenant;

    private TenantContext $tenantContext;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantContext = app(TenantContext::class);

        $this->tenant = Tenant::create([
            'name' => 'Helvetia Trade Ltd',
            'slug' => 'helvetia-trade-ltd',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create([
            'current_tenant_id' => $this->tenant->id,
        ]);

        TenantMembership::create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);

        $this->tenantContext->setTenant($this->tenant);

        Company::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Helvetia Trade Ltd',
            'legal_name' => 'Helvetia Trade AG',
            'tax_number' => 'CHE-109.876.543',
            'vat_number' => 'CHE-109.876.543 MWST',
            'currency' => 'CHF',
            'country' => 'CH',
            'is_active' => true,
        ]);
    }

    public function test_guest_is_redirected_on_all_sidebar_pages(): void
    {
        $navigationManager = app(NavigationManager::class);

        foreach ($navigationManager->getItems() as $item) {
            $routesToTest = [];

            if ($item->route) {
                $routesToTest[] = $item->route;
            }

            foreach ($item->children as $child) {
                if ($child->route) {
                    $routesToTest[] = $child->route;
                }
            }

            foreach ($routesToTest as $routeName) {
                $response = $this->get(route($routeName));
                $response->assertRedirect(route('login'));
            }
        }
    }

    public function test_all_sidebar_menu_routes_are_accessible_for_authenticated_tenant_user(): void
    {
        $this->actingAs($this->user);

        $routes = [
            'dashboard' => ['status' => 200, 'assertSee' => 'Fattura'],
            'filament.admin.resources.customers.index' => ['status' => 200, 'assertSee' => 'Customers'],
            'filament.admin.resources.quotes.index' => ['status' => 200, 'assertSee' => 'Quotes'],
            'filament.admin.resources.invoices.index' => ['status' => 200, 'assertSee' => 'Invoices'],
            'filament.admin.resources.suppliers.index' => ['status' => 200, 'assertSee' => 'Suppliers'],
            'filament.admin.resources.purchase-orders.index' => ['status' => 200, 'assertSee' => 'Purchase Orders'],
            'filament.admin.resources.vendor-bills.index' => ['status' => 200, 'assertSee' => 'Vendor Bills'],
            'filament.admin.resources.products.index' => ['status' => 200, 'assertSee' => 'Products'],
            'filament.admin.resources.warehouses.index' => ['status' => 200, 'assertSee' => 'Warehouses'],
            'filament.admin.resources.stock-movements.index' => ['status' => 200, 'assertSee' => 'Stock Movements'],
            'filament.admin.resources.chart-of-accounts.index' => ['status' => 200, 'assertSee' => 'Chart of Accounts'],
            'filament.admin.resources.journal-entries.index' => ['status' => 200, 'assertSee' => 'Journal Entries'],
            'filament.admin.pages.bank-reconciliation' => ['status' => 200, 'assertSee' => 'Bank Reconciliation'],
            'filament.admin.pages.hr.employees' => ['status' => 200, 'assertSee' => 'Employees'],
            'filament.admin.pages.hr.attendance' => ['status' => 200, 'assertSee' => 'Attendance'],
            'filament.admin.pages.hr.payroll' => ['status' => 200, 'assertSee' => 'Payroll'],
            'filament.admin.pages.reports.financial' => ['status' => 200, 'assertSee' => 'Financial Reports'],
            'filament.admin.pages.reports.sales' => ['status' => 200, 'assertSee' => 'Sales'],
            'company.edit' => ['status' => 200, 'assertSee' => 'Company Profile'],
            'profile.edit' => ['status' => 200, 'assertSee' => 'Profile'],
            'security.edit' => ['status' => 200, 'assertSee' => 'Security'],
            'appearance.edit' => ['status' => 200, 'assertSee' => 'Appearance'],
        ];

        foreach ($routes as $routeName => $expectation) {
            $response = $this->withSession(['auth.password_confirmed_at' => time()])
                ->get(route($routeName));

            $this->assertSame(
                $expectation['status'],
                $response->status(),
                "Route [{$routeName}] returned HTTP {$response->status()} redirecting to: ".$response->headers->get('Location')
            );

            if (isset($expectation['assertSee'])) {
                $response->assertSee(
                    $expectation['assertSee'],
                    false
                );
            }
        }
    }

    public function test_custom_filament_pages_display_sample_data(): void
    {
        $this->actingAs($this->user);

        // Bank Reconciliation
        $response = $this->get(route('filament.admin.pages.bank-reconciliation'));
        $response->assertOk();
        $response->assertSee('PostFinance AG');
        $response->assertSee('CAMT.054');
        $response->assertSee('142,580.40');

        // HR Employees
        $response = $this->get(route('filament.admin.pages.hr.employees'));
        $response->assertOk();
        $response->assertSee('Marcella Meier');
        $response->assertSee('Lead ERP Architect');
        $response->assertSee('Jean-Luc Favrod');

        // HR Attendance
        $response = $this->get(route('filament.admin.pages.hr.attendance'));
        $response->assertOk();
        $response->assertSee('08:15');
        $response->assertSee('ERP Core Architecture');
        $response->assertSee('Present');

        // HR Payroll
        $response = $this->get(route('filament.admin.pages.hr.payroll'));
        $response->assertOk();
        $response->assertSee('35,200.00');
        $response->assertSee('AHV / IV / EO / ALV');
        $response->assertSee('8,417.00');

        // Financial Reports
        $response = $this->get(route('filament.admin.pages.reports.financial'));
        $response->assertOk();
        $response->assertSee('240,780.40');
        $response->assertSee('1000');
        $response->assertSee('Cash on Hand (Kasse CHF)');

        // Sales Reports
        $response = $this->get(route('filament.admin.pages.reports.sales'));
        $response->assertOk();
        $response->assertSee('217,700.00');
        $response->assertSee('Alpine Solutions');
        $response->assertSee('October 2025');
    }

    public function test_company_settings_page_submits_and_updates_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('company.edit'));
        $response->assertOk();
        $response->assertSee('Helvetia Trade Ltd');
        $response->assertSee('CHE-109.876.543');

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Settings\Company::class)
            ->set('name', 'Helvetia Trade New Name AG')
            ->set('city', 'Lugano')
            ->set('state_canton', 'TI')
            ->call('updateCompanyInformation')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('companies', [
            'name' => 'Helvetia Trade New Name AG',
            'city' => 'Lugano',
            'state_canton' => 'TI',
        ]);
    }

    public function test_pages_render_localized_content_in_all_locales(): void
    {
        $this->actingAs($this->user);

        // Italian
        app()->setLocale('it');
        $responseIt = $this->get(route('filament.admin.pages.bank-reconciliation'));
        $responseIt->assertOk();
        $responseIt->assertSee('Riconciliazione Bancaria');

        // German
        app()->setLocale('de');
        $responseDe = $this->get(route('filament.admin.pages.bank-reconciliation'));
        $responseDe->assertOk();
        $responseDe->assertSee('Bankabstimmung');

        // French
        app()->setLocale('fr');
        $responseFr = $this->get(route('filament.admin.pages.bank-reconciliation'));
        $responseFr->assertOk();
        $responseFr->assertSee('Rapprochement bancaire');
    }
}

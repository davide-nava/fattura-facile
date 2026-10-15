<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Companies\Models\Branch;
use App\Modules\Companies\Models\Company;
use App\Modules\Companies\Models\FiscalYear;
use App\Modules\Customers\Models\Customer;
use App\Modules\Identity\Models\TenantMembership;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\StockService;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductCategory;
use App\Modules\Products\Models\TaxRate;
use App\Modules\Suppliers\Models\Supplier;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /** @var TenantContext $tenantContext */
        $tenantContext = app(TenantContext::class);

        // 1. Create Default Tenant
        $tenant = Tenant::create([
            'name' => 'Helvetia Trade AG',
            'slug' => 'helvetia-trade',
            'domain' => 'helvetia.fatturafacile.local',
            'is_active' => true,
        ]);

        $tenantContext->setTenant($tenant);

        // 2. Create Administrator User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@fatturafacile.eu',
            'password' => Hash::make('Password123!'),
            'current_tenant_id' => $tenant->id,
            'is_superadmin' => true,
        ]);

        TenantMembership::create([
            'tenant_id' => $tenant->id,
            'user_id' => $admin->id,
            'role' => 'tenant_admin',
            'is_active' => true,
            'permissions' => ['*'],
        ]);

        // 3. Create Primary Company (Legal Entity in Switzerland)
        $company = Company::create([
            'tenant_id' => $tenant->id,
            'name' => 'Helvetia Trade AG',
            'legal_name' => 'Helvetia Trade Aktiengesellschaft',
            'tax_number' => 'CHE-109.876.543',
            'vat_number' => 'CHE-109.876.543 MWST',
            'currency' => 'CHF',
            'country' => 'CH',
            'street' => 'Bahnhofstrasse 100',
            'city' => 'Zürich',
            'postal_code' => '8001',
            'state_canton' => 'ZH',
            'email' => 'contact@helvetia-trade.ch',
            'phone' => '+41 44 123 45 67',
            'website' => 'https://helvetia-trade.ch',
            'is_active' => true,
        ]);

        // 4. Create Branch
        $branch = Branch::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'Zürich HQ',
            'code' => 'HQ-ZH',
            'street' => 'Bahnhofstrasse 100',
            'city' => 'Zürich',
            'postal_code' => '8001',
            'country' => 'CH',
            'is_active' => true,
        ]);

        // 5. Create Fiscal Year & Accounting Periods
        $fiscalYear = FiscalYear::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'FY 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_closed' => false,
        ]);

        // 6. Seed Swiss VAT Rates & Chart of Accounts
        $this->callWith(SwissTaxRateSeeder::class, ['tenantId' => $tenant->id, 'companyId' => $company->id]);
        $this->callWith(SwissChartOfAccountsSeeder::class, ['tenantId' => $tenant->id, 'companyId' => $company->id]);

        $stdTax = TaxRate::where('company_id', $company->id)->where('code', 'CHE_STD_81')->first();

        // 7. Create Central Warehouse
        $warehouse = Warehouse::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Main Warehouse Zürich',
            'code' => 'WH-ZH-01',
            'street' => 'Industriestrasse 12',
            'city' => 'Dietikon',
            'postal_code' => '8953',
            'country' => 'CH',
            'is_active' => true,
        ]);

        // 8. Create Product Category & Products
        $catHardware = ProductCategory::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'IT Hardware',
            'code' => 'HW',
        ]);

        $catServices = ProductCategory::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'Consulting Services',
            'code' => 'SRV',
        ]);

        $laptop = Product::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'category_id' => $catHardware->id,
            'tax_rate_id' => $stdTax?->id,
            'name' => 'Enterprise Laptop X1',
            'sku' => 'LAP-X1-001',
            'type' => 'product',
            'sales_price' => '1499.00',
            'purchase_price' => '950.00',
            'unit' => 'pcs',
            'is_stock_managed' => true,
            'is_active' => true,
            'description' => 'High performance business laptop with Swiss keyboard',
        ]);

        $consulting = Product::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'category_id' => $catServices->id,
            'tax_rate_id' => $stdTax?->id,
            'name' => 'ERP Implementation Consulting',
            'sku' => 'SRV-ERP-01',
            'type' => 'service',
            'sales_price' => '190.00',
            'purchase_price' => '0.00',
            'unit' => 'hrs',
            'is_stock_managed' => false,
            'is_active' => true,
            'description' => 'Expert ERP architectural and integration consulting',
        ]);

        // 9. Initial Stock Receipt
        /** @var StockService $stockService */
        $stockService = app(StockService::class);
        $stockService->recordReceipt(
            $warehouse->id,
            $laptop->id,
            '50.0000',
            '950.0000',
            'manual',
            null,
            'Initial opening stock'
        );

        // 10. Create Sample Customer & Supplier
        Customer::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'code' => 'CUST-001',
            'name' => 'Alpine Solutions Sàrl',
            'legal_name' => 'Alpine Solutions Sàrl',
            'tax_number' => 'CHE-321.654.987',
            'email' => 'accounts@alpinesolutions.ch',
            'phone' => '+41 22 987 65 43',
            'street' => 'Rue du Rhône 42',
            'city' => 'Genève',
            'postal_code' => '1204',
            'state_canton' => 'GE',
            'country' => 'CH',
            'payment_term_days' => 30,
            'currency' => 'CHF',
            'credit_limit' => '25000.00',
            'is_active' => true,
        ]);

        Supplier::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'code' => 'SUPP-001',
            'name' => 'Swiss Components AG',
            'tax_number' => 'CHE-555.666.777',
            'vat_number' => 'CHE-555.666.777 MWST',
            'email' => 'sales@swisscomponents.ch',
            'phone' => '+41 31 555 44 33',
            'street' => 'Kramgasse 15',
            'city' => 'Bern',
            'postal_code' => '3011',
            'country' => 'CH',
            'payment_term_days' => 30,
            'currency' => 'CHF',
            'is_active' => true,
        ]);
    }
}

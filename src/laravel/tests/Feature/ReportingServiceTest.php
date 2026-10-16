<?php

namespace Tests\Feature;

use App\Modules\Companies\Models\Company;
use App\Modules\CRM\Models\Opportunity;
use App\Modules\Customers\Models\Customer;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Products\Models\Product;
use App\Modules\Purchasing\Models\SupplierInvoice;
use App\Modules\Reporting\Services\ReportingService;
use App\Modules\Suppliers\Models\Supplier;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantContext;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_reporting_service_metrics_aggregation(): void
    {
        $tenant = Tenant::create(['name' => 'Report Tenant', 'slug' => 'rep-t']);
        app(TenantContext::class)->setTenant($tenant);

        $company = Company::create(['name' => 'Report Co', 'currency' => 'CHF']);

        $service = app(ReportingService::class);

        // Empty state
        $emptyMetrics = $service->getDashboardMetrics($company->id);
        $this->assertEquals('0.00', $emptyMetrics['month_revenue_raw']);
        $this->assertEquals('0.00', $emptyMetrics['total_receivables_raw']);
        $this->assertEquals('0.00', $emptyMetrics['overdue_receivables_raw']);
        $this->assertEquals('0.00', $emptyMetrics['total_payables_raw']);
        $this->assertEquals('0.00', $emptyMetrics['stock_valuation_raw']);
        $this->assertEquals('0.00', $emptyMetrics['pipeline_value_raw']);

        $customer = Customer::create(['company_id' => $company->id, 'name' => 'C1']);
        $supplier = Supplier::create(['company_id' => $company->id, 'name' => 'S1']);
        $warehouse = Warehouse::create(['company_id' => $company->id, 'name' => 'W1']);
        $product = Product::create(['company_id' => $company->id, 'name' => 'P1', 'is_stock_managed' => true]);

        // Invoice 1: Issued today, due in 30 days -> Revenue: 1000, Receivable: 1000, Overdue: 0
        Invoice::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-REP-1',
            'invoice_date' => Carbon::today(),
            'due_date' => Carbon::today()->addDays(30),
            'status' => 'issued',
            'total' => '1000.00',
            'outstanding_amount' => '1000.00',
        ]);

        // Invoice 2: Issued this month, due 5 days ago -> Revenue: 500, Receivable: 500, Overdue: 500
        Invoice::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-REP-2',
            'invoice_date' => Carbon::today(),
            'due_date' => Carbon::today()->subDays(5),
            'status' => 'issued',
            'total' => '500.00',
            'outstanding_amount' => '500.00',
        ]);

        // Supplier Invoice: Total 800, Paid 300 -> Payable: 500
        SupplierInvoice::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'supplier_invoice_number' => 'SUP-REP-1',
            'invoice_date' => Carbon::today(),
            'due_date' => Carbon::today()->addDays(30),
            'status' => 'approved',
            'total' => '800.00',
            'paid_amount' => '300.00',
        ]);

        // Stock Balance: 10 units at 25.50 -> Valuation: 255.00
        StockBalance::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => '10.0000',
            'avg_unit_cost' => '25.5000',
        ]);

        // Opportunity: Open stage (proposal) = 15000, Closed won = 20000 (excluded)
        Opportunity::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'name' => 'Active Deal',
            'stage' => 'proposal',
            'expected_revenue' => '15000.00',
        ]);

        Opportunity::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'name' => 'Won Deal',
            'stage' => 'closed_won',
            'expected_revenue' => '20000.00',
        ]);

        $metrics = $service->getDashboardMetrics($company->id);

        $this->assertEquals('1500.00', $metrics['month_revenue_raw']);
        $this->assertEquals('1500.00', $metrics['total_receivables_raw']);
        $this->assertEquals('500.00', $metrics['overdue_receivables_raw']);
        $this->assertEquals('500.00', $metrics['total_payables_raw']);
        $this->assertEquals('255.00', $metrics['stock_valuation_raw']);
        $this->assertEquals('15000.00', $metrics['pipeline_value_raw']);
    }
}

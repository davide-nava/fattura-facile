<?php

namespace Tests\Feature;

use App\Modules\Companies\Models\Company;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\StockService;
use App\Modules\Products\Models\Product;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class InventoryStockTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private Company $company;

    private Warehouse $warehouse1;

    private Warehouse $warehouse2;

    private Product $product;

    private StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['name' => 'Swiss Logistics AG', 'slug' => 'swiss-logistics']);
        app(TenantContext::class)->setTenant($this->tenant);

        $this->company = Company::create([
            'name' => 'Central Operations',
            'currency' => 'CHF',
        ]);

        $this->warehouse1 = Warehouse::create([
            'company_id' => $this->company->id,
            'name' => 'Basel Depot',
            'code' => 'BS-01',
        ]);

        $this->warehouse2 = Warehouse::create([
            'company_id' => $this->company->id,
            'name' => 'Bern Depot',
            'code' => 'BE-01',
        ]);

        $this->product = Product::create([
            'company_id' => $this->company->id,
            'name' => 'Industrial Sensor S-100',
            'sku' => 'IND-S100',
            'unit' => 'pcs',
            'is_stock_managed' => true,
        ]);

        $this->stockService = app(StockService::class);
    }

    public function test_stock_receipt_updates_balance_and_calculates_wac(): void
    {
        // Batch 1: 10 units at 50.00 CHF each -> value 500.00
        $this->stockService->recordReceipt($this->warehouse1->id, $this->product->id, 10, '50.0000');

        $balance = StockBalance::where('warehouse_id', $this->warehouse1->id)
            ->where('product_id', $this->product->id)
            ->first();

        $this->assertNotNull($balance);
        $this->assertEquals('10.0000', $balance->quantity);
        $this->assertEquals('50.0000', $balance->avg_unit_cost);

        // Batch 2: 20 units at 80.00 CHF each -> value 1600.00
        // Total units = 30, Total value = 2100.00 -> New average cost = 2100 / 30 = 70.0000 CHF
        $this->stockService->recordReceipt($this->warehouse1->id, $this->product->id, 20, '80.0000');

        $balance->refresh();
        $this->assertEquals('30.0000', $balance->quantity);
        $this->assertEquals('70.0000', $balance->avg_unit_cost);

        // Assert 2 movements were recorded in the audit ledger
        $movements = StockMovement::where('product_id', $this->product->id)->get();
        $this->assertCount(2, $movements);
    }

    public function test_stock_issue_decreases_quantity(): void
    {
        $this->stockService->recordReceipt($this->warehouse1->id, $this->product->id, 25, '100.0000');
        $this->stockService->recordIssue($this->warehouse1->id, $this->product->id, 10);

        $balance = StockBalance::where('warehouse_id', $this->warehouse1->id)
            ->where('product_id', $this->product->id)
            ->first();

        $this->assertEquals('15.0000', $balance->quantity);
        $this->assertEquals('100.0000', $balance->avg_unit_cost);
    }

    public function test_overselling_throws_insufficient_stock_exception(): void
    {
        $this->stockService->recordReceipt($this->warehouse1->id, $this->product->id, 5, '100.0000');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Insufficient stock');

        // Attempting to issue 10 when only 5 exist
        $this->stockService->recordIssue($this->warehouse1->id, $this->product->id, 10);
    }

    public function test_inter_warehouse_transfer(): void
    {
        $this->stockService->recordReceipt($this->warehouse1->id, $this->product->id, 50, '40.0000');

        $this->stockService->recordTransfer(
            $this->warehouse1->id,
            $this->warehouse2->id,
            $this->product->id,
            20,
            'Relocating to Bern'
        );

        $balance1 = StockBalance::where('warehouse_id', $this->warehouse1->id)->where('product_id', $this->product->id)->first();
        $balance2 = StockBalance::where('warehouse_id', $this->warehouse2->id)->where('product_id', $this->product->id)->first();

        $this->assertEquals('30.0000', $balance1->quantity);
        $this->assertEquals('20.0000', $balance2->quantity);
        $this->assertEquals('40.0000', $balance2->avg_unit_cost);
    }

    public function test_warehouses_isolated_across_tenants(): void
    {
        $tenantB = Tenant::create(['name' => 'Foreign Corp', 'slug' => 'foreign-corp']);
        app(TenantContext::class)->setTenant($tenantB);

        $companyB = Company::create(['name' => 'Foreign HQ', 'currency' => 'EUR']);
        $warehouseB = Warehouse::create(['company_id' => $companyB->id, 'name' => 'Paris Hub', 'code' => 'PAR-01']);

        // Switch back to Tenant A
        app(TenantContext::class)->setTenant($this->tenant);

        $this->assertNull(Warehouse::find($warehouseB->id));
        $this->assertCount(2, Warehouse::all()); // only Basel and Bern
    }

    public function test_receipt_quantity_must_be_strictly_positive(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Receipt quantity must be strictly positive');

        $this->stockService->recordReceipt($this->warehouse1->id, $this->product->id, 0);
    }

    public function test_issue_quantity_must_be_strictly_positive(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Issue quantity must be strictly positive');

        $this->stockService->recordIssue($this->warehouse1->id, $this->product->id, -5);
    }

    public function test_non_stock_managed_product_throws_on_receipt(): void
    {
        $serviceProduct = Product::create([
            'company_id' => $this->company->id,
            'name' => 'Consulting',
            'sku' => 'CNS-01',
            'is_stock_managed' => false,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('not stock managed');

        $this->stockService->recordReceipt($this->warehouse1->id, $serviceProduct->id, 5);
    }

    public function test_non_stock_managed_product_throws_on_issue(): void
    {
        $serviceProduct = Product::create([
            'company_id' => $this->company->id,
            'name' => 'Consulting',
            'sku' => 'CNS-02',
            'is_stock_managed' => false,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('not stock managed');

        $this->stockService->recordIssue($this->warehouse1->id, $serviceProduct->id, 5);
    }

    public function test_same_source_and_destination_transfer_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Source and destination warehouses cannot be the same');

        $this->stockService->recordTransfer(
            $this->warehouse1->id,
            $this->warehouse1->id,
            $this->product->id,
            10
        );
    }

    public function test_allow_negative_stock_issue(): void
    {
        // Issue with no prior receipt and allowNegative = true
        $mov = $this->stockService->recordIssue(
            $this->warehouse1->id,
            $this->product->id,
            5,
            null,
            null,
            'Allowed negative',
            true
        );

        $this->assertEquals('-5.0000', $mov->quantity);

        $balance = StockBalance::where('warehouse_id', $this->warehouse1->id)
            ->where('product_id', $this->product->id)
            ->first();

        $this->assertEquals('-5.0000', $balance->quantity);

        // Issue again when balance already exists
        $this->stockService->recordIssue(
            $this->warehouse1->id,
            $this->product->id,
            3,
            null,
            null,
            'More negative',
            true
        );

        $this->assertEquals('-8.0000', $balance->fresh()->quantity);
    }

    public function test_receipt_when_balance_reaches_zero_uses_cost_str(): void
    {
        // Issue 5 with allowNegative
        $this->stockService->recordIssue($this->warehouse1->id, $this->product->id, 5, null, null, null, true);

        // Receive exactly 5 at cost 45.00 -> balance becomes 0.0000
        $this->stockService->recordReceipt($this->warehouse1->id, $this->product->id, 5, '45.0000');

        $balance = StockBalance::where('warehouse_id', $this->warehouse1->id)->where('product_id', $this->product->id)->first();
        $this->assertEquals('0.0000', $balance->quantity);
        $this->assertEquals('45.0000', $balance->avg_unit_cost);
    }
}

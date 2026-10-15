<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Products\Models\Product;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class StockService
{
    /**
     * Record receipt of physical stock (increases inventory and updates Weighted Average Cost).
     */
    public function recordReceipt(
        int $warehouseId,
        int $productId,
        string|float $quantity,
        string|float $unitCost = '0.0000',
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): StockMovement {
        $qtyStr = number_format((float) $quantity, 4, '.', '');
        $costStr = number_format((float) $unitCost, 4, '.', '');

        if (bccomp($qtyStr, '0.0000', 4) <= 0) {
            throw new InvalidArgumentException('Receipt quantity must be strictly positive.');
        }

        return DB::transaction(function () use ($warehouseId, $productId, $qtyStr, $costStr, $referenceType, $referenceId, $notes) {
            $product = Product::findOrFail($productId);
            if (! $product->is_stock_managed) {
                throw new RuntimeException("Product {$product->name} is not stock managed.");
            }

            $warehouse = Warehouse::findOrFail($warehouseId);
            $tenantId = app(TenantContext::class)->getTenantId() ?? $warehouse->tenant_id;
            $companyId = $warehouse->company_id;

            // Lock or create stock balance record
            $balance = StockBalance::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            if (! $balance) {
                $balance = StockBalance::create([
                    'tenant_id' => $tenantId,
                    'company_id' => $companyId,
                    'warehouse_id' => $warehouseId,
                    'product_id' => $productId,
                    'quantity' => $qtyStr,
                    'avg_unit_cost' => $costStr,
                ]);
            } else {
                $currentQty = $balance->quantity;
                $currentCost = $balance->avg_unit_cost;

                // Weighted Average Cost calculation:
                // (currentQty * currentCost + newQty * newCost) / (currentQty + newQty)
                $newTotalQty = bcadd($currentQty, $qtyStr, 4);

                if (bccomp($newTotalQty, '0.0000', 4) > 0) {
                    $currentTotalVal = bcmul($currentQty, $currentCost, 6);
                    $incomingVal = bcmul($qtyStr, $costStr, 6);
                    $newTotalVal = bcadd($currentTotalVal, $incomingVal, 6);
                    $newAvgCost = bcdiv($newTotalVal, $newTotalQty, 4);
                } else {
                    $newAvgCost = $costStr;
                }

                $balance->quantity = $newTotalQty;
                $balance->avg_unit_cost = $newAvgCost;
                $balance->save();
            }

            return StockMovement::create([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'movement_type' => 'goods_receipt',
                'quantity' => $qtyStr,
                'unit_cost' => $costStr,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Record issue of physical stock (decreases inventory).
     */
    public function recordIssue(
        int $warehouseId,
        int $productId,
        string|float $quantity,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null,
        bool $allowNegative = false
    ): StockMovement {
        $qtyStr = number_format((float) $quantity, 4, '.', '');

        if (bccomp($qtyStr, '0.0000', 4) <= 0) {
            throw new InvalidArgumentException('Issue quantity must be strictly positive.');
        }

        return DB::transaction(function () use ($warehouseId, $productId, $qtyStr, $referenceType, $referenceId, $notes, $allowNegative) {
            $product = Product::findOrFail($productId);
            if (! $product->is_stock_managed) {
                throw new RuntimeException("Product {$product->name} is not stock managed.");
            }

            $warehouse = Warehouse::findOrFail($warehouseId);
            $tenantId = app(TenantContext::class)->getTenantId() ?? $warehouse->tenant_id;
            $companyId = $warehouse->company_id;

            $balance = StockBalance::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            $currentQty = $balance ? $balance->quantity : '0.0000';
            $unitCost = $balance ? $balance->avg_unit_cost : '0.0000';

            if (! $allowNegative && bccomp($currentQty, $qtyStr, 4) < 0) {
                throw new RuntimeException("Insufficient stock in warehouse {$warehouse->name} for product {$product->name}. Available: {$currentQty}, Requested: {$qtyStr}");
            }

            $newQty = bcsub($currentQty, $qtyStr, 4);

            if ($balance) {
                $balance->quantity = $newQty;
                $balance->save();
            } else {
                $balance = StockBalance::create([
                    'tenant_id' => $tenantId,
                    'company_id' => $companyId,
                    'warehouse_id' => $warehouseId,
                    'product_id' => $productId,
                    'quantity' => $newQty,
                    'avg_unit_cost' => $unitCost,
                ]);
            }

            return StockMovement::create([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'movement_type' => 'goods_issue',
                'quantity' => '-'.$qtyStr,
                'unit_cost' => $unitCost,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Transfer stock between warehouses.
     */
    public function recordTransfer(
        int $fromWarehouseId,
        int $toWarehouseId,
        int $productId,
        string|float $quantity,
        ?string $notes = null
    ): void {
        if ($fromWarehouseId === $toWarehouseId) {
            throw new InvalidArgumentException('Source and destination warehouses cannot be the same.');
        }

        DB::transaction(function () use ($fromWarehouseId, $toWarehouseId, $productId, $quantity, $notes) {
            $issueMov = $this->recordIssue($fromWarehouseId, $productId, $quantity, 'transfer', null, "Transfer to warehouse #{$toWarehouseId}: {$notes}");
            $this->recordReceipt($toWarehouseId, $productId, $quantity, $issueMov->unit_cost, 'transfer', null, "Transfer from warehouse #{$fromWarehouseId}: {$notes}");
        });
    }
}

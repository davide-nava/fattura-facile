<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Companies\Models\Company;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Products\Models\Product;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        $movementType = fake()->randomElement([
            'goods_receipt',
            'goods_issue',
            'adjustment_in',
            'adjustment_out',
            'transfer_in',
            'transfer_out',
        ]);

        $isPositive = in_array($movementType, ['goods_receipt', 'adjustment_in', 'transfer_in'], true);
        $qty = fake()->numberBetween(1, 50);

        return [
            'tenant_id' => Tenant::query()->first()?->id ?? 1,
            'company_id' => Company::query()->first()?->id ?? 1,
            'warehouse_id' => Warehouse::query()->first()?->id ?? 1,
            'product_id' => Product::query()->first()?->id ?? 1,
            'movement_type' => $movementType,
            'quantity' => number_format($isPositive ? $qty : -$qty, 4, '.', ''),
            'unit_cost' => number_format(fake()->randomFloat(4, 15, 800), 4, '.', ''),
            'reference_type' => fake()->randomElement(['purchase_order', 'sales_order', 'manual', 'goods_receipt']),
            'reference_id' => fake()->numberBetween(1, 100),
            'notes' => fake()->optional()->sentence(),
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Companies\Models\Company;
use App\Modules\Purchasing\Models\PurchaseOrder;
use App\Modules\Suppliers\Models\Supplier;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 800, 25000);
        $tax = round($subtotal * 0.081, 2);
        $total = $subtotal + $tax;

        return [
            'tenant_id' => Tenant::query()->first()?->id ?? 1,
            'company_id' => Company::query()->first()?->id ?? 1,
            'supplier_id' => Supplier::query()->first()?->id ?? 1,
            'order_number' => 'PO-2026-'.fake()->unique()->numerify('####'),
            'order_date' => fake()->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            'expected_date' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'status' => fake()->randomElement(['draft', 'ordered', 'partially_received', 'received', 'cancelled']),
            'currency' => 'CHF',
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'tax_total' => number_format($tax, 2, '.', ''),
            'total' => number_format($total, 2, '.', ''),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}

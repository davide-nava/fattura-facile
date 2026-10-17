<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Companies\Models\Company;
use App\Modules\Customers\Models\Customer;
use App\Modules\Sales\Models\Quote;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quote>
 */
class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 500, 15000);
        $tax = round($subtotal * 0.081, 2);
        $total = $subtotal + $tax;

        return [
            'tenant_id' => Tenant::query()->first()?->id ?? 1,
            'company_id' => Company::query()->first()?->id ?? 1,
            'customer_id' => Customer::query()->first()?->id ?? 1,
            'quote_number' => 'QUO-2026-'.fake()->unique()->numerify('####'),
            'quote_date' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'valid_until' => fake()->dateTimeBetween('now', '+2 months')->format('Y-m-d'),
            'status' => fake()->randomElement(['draft', 'sent', 'accepted', 'rejected', 'converted']),
            'currency' => 'CHF',
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'tax_total' => number_format($tax, 2, '.', ''),
            'total' => number_format($total, 2, '.', ''),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}

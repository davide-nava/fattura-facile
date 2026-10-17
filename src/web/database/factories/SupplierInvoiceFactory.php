<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Companies\Models\Company;
use App\Modules\Purchasing\Models\SupplierInvoice;
use App\Modules\Suppliers\Models\Supplier;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierInvoice>
 */
class SupplierInvoiceFactory extends Factory
{
    protected $model = SupplierInvoice::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 400, 18000);
        $tax = round($subtotal * 0.081, 2);
        $total = $subtotal + $tax;
        $status = fake()->randomElement(['draft', 'received', 'approved', 'paid', 'cancelled']);
        $paid = $status === 'paid' ? $total : ($status === 'approved' ? round($total / 2, 2) : 0.00);

        return [
            'tenant_id' => Tenant::query()->first()?->id ?? 1,
            'company_id' => Company::query()->first()?->id ?? 1,
            'supplier_id' => Supplier::query()->first()?->id ?? 1,
            'purchase_order_id' => null,
            'supplier_invoice_number' => 'INV-SUPP-'.fake()->unique()->numerify('#####'),
            'invoice_date' => fake()->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            'due_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'status' => $status,
            'currency' => 'CHF',
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'tax_total' => number_format($tax, 2, '.', ''),
            'total' => number_format($total, 2, '.', ''),
            'paid_amount' => number_format($paid, 2, '.', ''),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}

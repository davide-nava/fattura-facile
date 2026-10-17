<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Queries;

use App\Modules\Companies\Models\Company;
use App\Modules\Invoicing\Models\Invoice;
use App\Shared\Money\MoneyValue;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

class InvoiceJournalQuery
{
    /**
     * Gather invoice journal dataset for the specified company and criteria.
     *
     * @param  array<string, mixed>  $parameters
     * @return array{
     *     invoices: Collection<int, Invoice>,
     *     total_subtotal: MoneyValue,
     *     total_tax: MoneyValue,
     *     total_gross: MoneyValue,
     *     total_outstanding: MoneyValue,
     *     period_start: CarbonImmutable,
     *     period_end: CarbonImmutable,
     *     status_counts: array<string, int>
     * }
     */
    public function execute(Company $company, array $parameters = []): array
    {
        $today = CarbonImmutable::today();
        $startDate = $parameters['start_date'] ?? $today->startOfMonth()->subMonths(1);
        $endDate = $parameters['end_date'] ?? $today->endOfMonth();

        if (is_string($startDate)) {
            $startDate = CarbonImmutable::parse($startDate);
        }
        if (is_string($endDate)) {
            $endDate = CarbonImmutable::parse($endDate);
        }

        $query = Invoice::where('company_id', $company->id)
            ->whereBetween('invoice_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->with(['customer'])
            ->orderBy('invoice_date', 'desc')
            ->orderBy('invoice_number', 'desc');

        if (! empty($parameters['status'])) {
            $query->where('status', (string) $parameters['status']);
        }

        /** @var Collection<int, Invoice> $invoices */
        $invoices = $query->get();

        $totalSubtotal = MoneyValue::zero();
        $totalTax = MoneyValue::zero();
        $totalGross = MoneyValue::zero();
        $totalOutstanding = MoneyValue::zero();
        $statusCounts = [];

        foreach ($invoices as $invoice) {
            $totalSubtotal = $totalSubtotal->add($invoice->subtotal);
            $totalTax = $totalTax->add($invoice->tax_total);
            $totalGross = $totalGross->add($invoice->total);
            $totalOutstanding = $totalOutstanding->add($invoice->outstanding_amount);

            $status = (string) $invoice->status;
            $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
        }

        return [
            'invoices' => $invoices,
            'total_subtotal' => $totalSubtotal,
            'total_tax' => $totalTax,
            'total_gross' => $totalGross,
            'total_outstanding' => $totalOutstanding,
            'period_start' => $startDate,
            'period_end' => $endDate,
            'status_counts' => $statusCounts,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Queries;

use App\Modules\Companies\Models\Company;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Invoicing\Models\InvoiceItem;
use App\Shared\Money\MoneyValue;
use Carbon\CarbonImmutable;

class TaxSummaryQuery
{
    /**
     * Gather tax / VAT breakdown for the specified company and period.
     *
     * @param  array<string, mixed>  $parameters
     * @return array{
     *     rate_breakdowns: array<string, array{rate: string, net_base: MoneyValue, tax_amount: MoneyValue, gross_total: MoneyValue, item_count: int}>,
     *     total_net: MoneyValue,
     *     total_tax: MoneyValue,
     *     total_gross: MoneyValue,
     *     period_start: CarbonImmutable,
     *     period_end: CarbonImmutable,
     *     invoice_count: int
     * }
     */
    public function execute(Company $company, array $parameters = []): array
    {
        $today = CarbonImmutable::today();
        $startDate = $parameters['start_date'] ?? $today->startOfMonth()->subMonths(2);
        $endDate = $parameters['end_date'] ?? $today->endOfMonth();

        if (is_string($startDate)) {
            $startDate = CarbonImmutable::parse($startDate);
        }
        if (is_string($endDate)) {
            $endDate = CarbonImmutable::parse($endDate);
        }

        // Issued invoices within the period
        $invoiceIds = Invoice::where('company_id', $company->id)
            ->whereIn('status', ['issued', 'partially_paid', 'paid'])
            ->whereBetween('invoice_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->pluck('id');

        $items = InvoiceItem::whereIn('invoice_id', $invoiceIds)->get();

        $rateBreakdowns = [];
        $totalNet = MoneyValue::zero();
        $totalTax = MoneyValue::zero();
        $totalGross = MoneyValue::zero();

        foreach ($items as $item) {
            $rateKey = number_format((float) $item->tax_rate, 1).'%';

            if (! isset($rateBreakdowns[$rateKey])) {
                $rateBreakdowns[$rateKey] = [
                    'rate' => $rateKey,
                    'net_base' => MoneyValue::zero(),
                    'tax_amount' => MoneyValue::zero(),
                    'gross_total' => MoneyValue::zero(),
                    'item_count' => 0,
                ];
            }

            $currency = $company->currency ?: 'CHF';
            $gross = MoneyValue::of((string) $item->total, $currency);
            $tax = MoneyValue::of((string) $item->tax_amount, $currency);
            $net = $gross->subtract($tax);

            $rateBreakdowns[$rateKey]['net_base'] = $rateBreakdowns[$rateKey]['net_base']->add($net);
            $rateBreakdowns[$rateKey]['tax_amount'] = $rateBreakdowns[$rateKey]['tax_amount']->add($tax);
            $rateBreakdowns[$rateKey]['gross_total'] = $rateBreakdowns[$rateKey]['gross_total']->add($gross);
            $rateBreakdowns[$rateKey]['item_count']++;

            $totalNet = $totalNet->add($net);
            $totalTax = $totalTax->add($tax);
            $totalGross = $totalGross->add($gross);
        }

        // Sort by tax rate descending
        krsort($rateBreakdowns);

        return [
            'rate_breakdowns' => $rateBreakdowns,
            'total_net' => $totalNet,
            'total_tax' => $totalTax,
            'total_gross' => $totalGross,
            'period_start' => $startDate,
            'period_end' => $endDate,
            'invoice_count' => $invoiceIds->count(),
        ];
    }
}

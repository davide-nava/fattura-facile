<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Queries;

use App\Modules\Companies\Models\Company;
use App\Modules\CRM\Models\Opportunity;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Purchasing\Models\SupplierInvoice;
use App\Shared\Money\MoneyValue;
use Carbon\CarbonImmutable;

class FinancialSummaryQuery
{
    /**
     * Gather raw financial aggregate metrics for the specified company.
     *
     * @param  array<string, mixed>  $parameters
     * @return array{
     *     month_revenue: MoneyValue,
     *     total_receivables: MoneyValue,
     *     overdue_receivables: MoneyValue,
     *     total_payables: MoneyValue,
     *     stock_valuation: MoneyValue,
     *     pipeline_value: MoneyValue,
     *     period_start: CarbonImmutable,
     *     period_end: CarbonImmutable,
     *     invoices_count: int,
     *     open_invoices_count: int
     * }
     */
    public function execute(Company $company, array $parameters = []): array
    {
        $today = CarbonImmutable::today();
        $startOfMonth = $parameters['start_date'] ?? $today->startOfMonth();
        $endOfMonth = $parameters['end_date'] ?? $today->endOfMonth();

        if (is_string($startOfMonth)) {
            $startOfMonth = CarbonImmutable::parse($startOfMonth);
        }
        if (is_string($endOfMonth)) {
            $endOfMonth = CarbonImmutable::parse($endOfMonth);
        }

        // Revenue this month (issued invoices within the period)
        $monthInvoices = Invoice::where('company_id', $company->id)
            ->whereIn('status', ['issued', 'partially_paid', 'paid'])
            ->whereBetween('invoice_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get();

        $monthRevenue = MoneyValue::zero();
        foreach ($monthInvoices as $inv) {
            $monthRevenue = $monthRevenue->add($inv->total);
        }

        // Outstanding Receivables
        $unpaidInvoices = Invoice::where('company_id', $company->id)
            ->whereIn('status', ['issued', 'partially_paid'])
            ->get();

        $totalReceivables = MoneyValue::zero();
        $overdueReceivables = MoneyValue::zero();

        foreach ($unpaidInvoices as $inv) {
            $totalReceivables = $totalReceivables->add($inv->outstanding_amount);
            if ($inv->due_date < $today) {
                $overdueReceivables = $overdueReceivables->add($inv->outstanding_amount);
            }
        }

        // Outstanding Payables
        $unpaidSupplierInvoices = SupplierInvoice::where('company_id', $company->id)
            ->whereIn('status', ['draft', 'received', 'approved'])
            ->get();

        $totalPayables = MoneyValue::zero();
        foreach ($unpaidSupplierInvoices as $supInv) {
            $due = bcsub((string) $supInv->total, (string) $supInv->paid_amount, 2);
            $totalPayables = $totalPayables->add($due);
        }

        // Stock Valuation
        $balances = StockBalance::where('company_id', $company->id)->get();
        $stockValuation = MoneyValue::zero();
        foreach ($balances as $b) {
            $val = bcmul((string) $b->quantity, (string) $b->avg_unit_cost, 2);
            $stockValuation = $stockValuation->add($val);
        }

        // CRM Pipeline
        $pipelineOpportunities = Opportunity::where('company_id', $company->id)
            ->whereNotIn('stage', ['closed_won', 'closed_lost'])
            ->get();

        $pipelineValue = MoneyValue::zero();
        foreach ($pipelineOpportunities as $opp) {
            $pipelineValue = $pipelineValue->add($opp->expected_revenue);
        }

        return [
            'month_revenue' => $monthRevenue,
            'total_receivables' => $totalReceivables,
            'overdue_receivables' => $overdueReceivables,
            'total_payables' => $totalPayables,
            'stock_valuation' => $stockValuation,
            'pipeline_value' => $pipelineValue,
            'period_start' => $startOfMonth,
            'period_end' => $endOfMonth,
            'invoices_count' => $monthInvoices->count(),
            'open_invoices_count' => $unpaidInvoices->count(),
        ];
    }
}

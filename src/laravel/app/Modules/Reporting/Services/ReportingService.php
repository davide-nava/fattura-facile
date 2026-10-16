<?php

namespace App\Modules\Reporting\Services;

use App\Modules\CRM\Models\Opportunity;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Purchasing\Models\SupplierInvoice;
use App\Shared\Money\MoneyValue;
use Carbon\Carbon;

class ReportingService
{
    /**
     * Get dashboard financial summary for a company.
     */
    public function getDashboardMetrics(int $companyId): array
    {
        $now = Carbon::today();
        $startOfMonth = $now->copy()->startOfMonth();

        // Revenue this month (issued invoices)
        $monthInvoices = Invoice::where('company_id', $companyId)
            ->whereIn('status', ['issued', 'partially_paid', 'paid'])
            ->where('invoice_date', '>=', $startOfMonth->toDateString())
            ->get();

        $monthRevenue = MoneyValue::zero();
        foreach ($monthInvoices as $inv) {
            $monthRevenue = $monthRevenue->add($inv->total);
        }

        // Outstanding Receivables
        $unpaidInvoices = Invoice::where('company_id', $companyId)
            ->whereIn('status', ['issued', 'partially_paid'])
            ->get();

        $totalReceivables = MoneyValue::zero();
        $overdueReceivables = MoneyValue::zero();

        foreach ($unpaidInvoices as $inv) {
            $totalReceivables = $totalReceivables->add($inv->outstanding_amount);
            if ($inv->due_date < $now) {
                $overdueReceivables = $overdueReceivables->add($inv->outstanding_amount);
            }
        }

        // Outstanding Payables
        $unpaidSupplierInvoices = SupplierInvoice::where('company_id', $companyId)
            ->whereIn('status', ['draft', 'received', 'approved'])
            ->get();

        $totalPayables = MoneyValue::zero();
        foreach ($unpaidSupplierInvoices as $supInv) {
            $due = bcsub($supInv->total, $supInv->paid_amount, 2);
            $totalPayables = $totalPayables->add($due);
        }

        // Stock Valuation
        $balances = StockBalance::where('company_id', $companyId)->get();
        $stockValuation = MoneyValue::zero();
        foreach ($balances as $b) {
            $val = bcmul($b->quantity, $b->avg_unit_cost, 2);
            $stockValuation = $stockValuation->add($val);
        }

        // CRM Pipeline
        $pipelineOpportunities = Opportunity::where('company_id', $companyId)
            ->whereNotIn('stage', ['closed_won', 'closed_lost'])
            ->get();

        $pipelineValue = MoneyValue::zero();
        foreach ($pipelineOpportunities as $opp) {
            $pipelineValue = $pipelineValue->add($opp->expected_revenue);
        }

        return [
            'month_revenue' => $monthRevenue->format(),
            'month_revenue_raw' => $monthRevenue->getAmount(),
            'total_receivables' => $totalReceivables->format(),
            'total_receivables_raw' => $totalReceivables->getAmount(),
            'overdue_receivables' => $overdueReceivables->format(),
            'overdue_receivables_raw' => $overdueReceivables->getAmount(),
            'total_payables' => $totalPayables->format(),
            'total_payables_raw' => $totalPayables->getAmount(),
            'stock_valuation' => $stockValuation->format(),
            'stock_valuation_raw' => $stockValuation->getAmount(),
            'pipeline_value' => $pipelineValue->format(),
            'pipeline_value_raw' => $pipelineValue->getAmount(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Reports;

use App\Modules\Companies\Models\Company;
use App\Modules\Reporting\Contracts\ReportInterface;
use App\Modules\Reporting\DTOs\ReportResult;
use App\Modules\Reporting\Queries\FinancialSummaryQuery;
use Carbon\CarbonImmutable;

class FinancialSummaryReport implements ReportInterface
{
    public function __construct(
        protected FinancialSummaryQuery $query
    ) {}

    public function key(): string
    {
        return 'financial-summary';
    }

    public function name(): string
    {
        return 'Financial Summary & Health Report';
    }

    public function description(): string
    {
        return 'Consolidated executive overview of revenue, receivables, payables, inventory valuation, and sales pipeline.';
    }

    public function category(): string
    {
        return 'Financial';
    }

    /**
     * @return view-string
     */
    public function view(): string
    {
        return 'reports.financial-summary';
    }

    public function paperOrientation(): string
    {
        return 'portrait';
    }

    public function paperSize(): string
    {
        return 'a4';
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public function generate(Company $company, array $parameters = []): ReportResult
    {
        $data = $this->query->execute($company, $parameters);
        $branding = $company->getBranding();
        $currency = $company->currency ?: 'CHF';

        $kpis = [
            [
                'label' => 'Period Revenue',
                'value' => $data['month_revenue']->format(),
                'subvalue' => "{$data['invoices_count']} invoices issued",
            ],
            [
                'label' => 'Total Receivables',
                'value' => $data['total_receivables']->format(),
                'subvalue' => "{$data['open_invoices_count']} pending invoices",
            ],
            [
                'label' => 'Overdue Receivables',
                'value' => $data['overdue_receivables']->format(),
                'subvalue' => $data['overdue_receivables']->isPositive() ? 'Action required' : 'All accounts current',
            ],
            [
                'label' => 'Total Payables',
                'value' => $data['total_payables']->format(),
                'subvalue' => 'Open supplier bills',
            ],
            [
                'label' => 'Inventory Valuation',
                'value' => $data['stock_valuation']->format(),
                'subvalue' => 'Materialized stock at WAC',
            ],
            [
                'label' => 'Sales Pipeline',
                'value' => $data['pipeline_value']->format(),
                'subvalue' => 'Unclosed CRM opportunities',
            ],
        ];

        $summaryTable = [
            'headers' => ['Category / Position', 'Amount', 'Status / Notes'],
            'rows' => [
                ['Invoiced Period Revenue', $data['month_revenue']->format(), 'Realized sales in period'],
                ['Accounts Receivable (Open)', $data['total_receivables']->format(), 'Pending customer settlement'],
                ['Overdue Receivables', $data['overdue_receivables']->format(), $data['overdue_receivables']->isPositive() ? 'Past payment term' : 'Compliant'],
                ['Accounts Payable', $data['total_payables']->format(), 'Due to suppliers'],
                ['Inventory Asset Value', $data['stock_valuation']->format(), 'Weighted average cost'],
                ['Expected Pipeline Revenue', $data['pipeline_value']->format(), 'Weighted commercial forecast'],
            ],
            'alignment' => ['left', 'right', 'left'],
        ];

        $summary = [
            'Net Working Position' => $data['month_revenue']->subtract($data['total_payables'])->format(),
            'Total Monitored Exposure' => $data['total_receivables']->add($data['overdue_receivables'])->format(),
        ];

        $startDateStr = $data['period_start']->format('d.m.Y');
        $endDateStr = $data['period_end']->format('d.m.Y');

        return new ReportResult(
            key: $this->key(),
            title: $this->name(),
            subtitle: "Reporting Period: {$startDateStr} - {$endDateStr}",
            company: $company,
            branding: $branding,
            generatedAt: CarbonImmutable::now(),
            parameters: $parameters,
            kpis: $kpis,
            tables: ['financial_summary' => $summaryTable],
            summary: $summary,
            notes: 'Generated under double-entry accounting standards with Swiss 5-cent rounding compliance.',
            orientation: $this->paperOrientation(),
            paperSize: $this->paperSize(),
        );
    }
}

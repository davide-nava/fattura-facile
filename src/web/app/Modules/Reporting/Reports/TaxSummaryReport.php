<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Reports;

use App\Modules\Companies\Models\Company;
use App\Modules\Reporting\Contracts\ReportInterface;
use App\Modules\Reporting\DTOs\ReportResult;
use App\Modules\Reporting\Queries\TaxSummaryQuery;
use Carbon\CarbonImmutable;

class TaxSummaryReport implements ReportInterface
{
    public function __construct(
        protected TaxSummaryQuery $query
    ) {}

    public function key(): string
    {
        return 'vat-summary';
    }

    public function name(): string
    {
        return 'Swiss VAT & Tax Summary Report';
    }

    public function description(): string
    {
        return 'Comprehensive tax report breaking down turnover, taxable base, and VAT liabilities classified by Swiss statutory rates.';
    }

    public function category(): string
    {
        return 'Tax';
    }

    /**
     * @return view-string
     */
    public function view(): string
    {
        return 'reports.vat-summary';
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
                'label' => 'Total Net Turnover',
                'value' => $data['total_net']->format(),
                'subvalue' => 'Taxable base',
            ],
            [
                'label' => 'Total VAT Liability',
                'value' => $data['total_tax']->format(),
                'subvalue' => 'Payable to ESTV/AFC',
            ],
            [
                'label' => 'Gross Invoiced Volume',
                'value' => $data['total_gross']->format(),
                'subvalue' => "From {$data['invoice_count']} invoices",
            ],
        ];

        $rows = [];
        foreach ($data['rate_breakdowns'] as $item) {
            $rows[] = [
                'rate' => $item['rate'],
                'items_count' => (string) $item['item_count'],
                'net_base' => $item['net_base']->format(),
                'tax_amount' => $item['tax_amount']->format(),
                'gross_total' => $item['gross_total']->format(),
            ];
        }

        $summary = [
            'Total Taxable Turnover (Net)' => $data['total_net']->format(),
            'Total Calculated VAT' => $data['total_tax']->format(),
            'Total Gross Value' => $data['total_gross']->format(),
        ];

        $startDateStr = $data['period_start']->format('d.m.Y');
        $endDateStr = $data['period_end']->format('d.m.Y');

        return new ReportResult(
            key: $this->key(),
            title: $this->name(),
            subtitle: "Tax Period: {$startDateStr} - {$endDateStr} | VAT UID: ".($company->vat_number ?: 'Not registered'),
            company: $company,
            branding: $branding,
            generatedAt: CarbonImmutable::now(),
            parameters: $parameters,
            kpis: $kpis,
            tables: [
                'vat_breakdown' => [
                    'headers' => ['VAT Rate', 'Position Count', 'Taxable Base (Net)', 'Tax Amount', 'Gross Total'],
                    'rows' => $rows,
                    'alignment' => ['left', 'center', 'right', 'right', 'right'],
                ],
            ],
            summary: $summary,
            notes: 'Prepared in accordance with Swiss Federal Tax Administration (ESTV) Value Added Tax Act.',
            orientation: $this->paperOrientation(),
            paperSize: $this->paperSize(),
        );
    }
}

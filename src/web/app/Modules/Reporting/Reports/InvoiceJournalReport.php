<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Reports;

use App\Modules\Companies\Models\Company;
use App\Modules\Customers\Models\Customer;
use App\Modules\Reporting\Contracts\ReportInterface;
use App\Modules\Reporting\DTOs\ReportResult;
use App\Modules\Reporting\Queries\InvoiceJournalQuery;
use App\Shared\Money\MoneyValue;
use Carbon\CarbonImmutable;

class InvoiceJournalReport implements ReportInterface
{
    public function __construct(
        protected InvoiceJournalQuery $query
    ) {}

    public function key(): string
    {
        return 'invoice-journal';
    }

    public function name(): string
    {
        return 'Sales & Invoices Journal';
    }

    public function description(): string
    {
        return 'Detailed chronologically sorted register of outgoing invoices with tax breakdown, settlement, and customer balances.';
    }

    public function category(): string
    {
        return 'Sales';
    }

    /**
     * @return view-string
     */
    public function view(): string
    {
        return 'reports.invoice-journal';
    }

    public function paperOrientation(): string
    {
        return 'landscape';
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
                'label' => 'Total Invoices',
                'value' => (string) $data['invoices']->count(),
                'subvalue' => 'Documents in period',
            ],
            [
                'label' => 'Total Net Volume',
                'value' => $data['total_subtotal']->format(),
                'subvalue' => 'Excluding VAT',
            ],
            [
                'label' => 'Total VAT Invoiced',
                'value' => $data['total_tax']->format(),
                'subvalue' => 'Calculated tax liability',
            ],
            [
                'label' => 'Total Gross Turnover',
                'value' => $data['total_gross']->format(),
                'subvalue' => 'Billed volume',
            ],
            [
                'label' => 'Outstanding Balance',
                'value' => $data['total_outstanding']->format(),
                'subvalue' => $data['total_outstanding']->isPositive() ? 'Unsettled claims' : 'Fully settled',
            ],
        ];

        $rows = [];
        foreach ($data['invoices'] as $inv) {
            /** @var Customer|null $customer */
            $customer = $inv->customer;
            $customerName = $customer !== null ? $customer->name : '-';
            $dateFormatted = $inv->invoice_date->format('d.m.Y');
            $dueFormatted = $inv->due_date->format('d.m.Y');

            $rows[] = [
                'number' => $inv->invoice_number,
                'date' => $dateFormatted,
                'due_date' => $dueFormatted,
                'customer' => $customerName,
                'status' => strtoupper((string) $inv->status),
                'subtotal' => MoneyValue::of((string) $inv->subtotal, $currency)->format(),
                'tax' => MoneyValue::of((string) $inv->tax_total, $currency)->format(),
                'total' => MoneyValue::of((string) $inv->total, $currency)->format(),
                'outstanding' => MoneyValue::of((string) $inv->outstanding_amount, $currency)->format(),
            ];
        }

        $summary = [
            'Total Invoices Count' => (string) $data['invoices']->count(),
            'Total Subtotal (Net)' => $data['total_subtotal']->format(),
            'Total VAT' => $data['total_tax']->format(),
            'Total Gross' => $data['total_gross']->format(),
            'Total Outstanding' => $data['total_outstanding']->format(),
        ];

        $startDateStr = $data['period_start']->format('d.m.Y');
        $endDateStr = $data['period_end']->format('d.m.Y');

        return new ReportResult(
            key: $this->key(),
            title: $this->name(),
            subtitle: "Period: {$startDateStr} - {$endDateStr}",
            company: $company,
            branding: $branding,
            generatedAt: CarbonImmutable::now(),
            parameters: $parameters,
            kpis: $kpis,
            tables: [
                'invoices' => [
                    'headers' => ['Invoice #', 'Date', 'Due Date', 'Customer', 'Status', 'Net Base', 'VAT', 'Total', 'Outstanding'],
                    'rows' => $rows,
                    'alignment' => ['left', 'left', 'left', 'left', 'center', 'right', 'right', 'right', 'right'],
                ],
            ],
            summary: $summary,
            notes: 'Official invoice register report according to Swiss commercial bookkeeping principles.',
            orientation: $this->paperOrientation(),
            paperSize: $this->paperSize(),
        );
    }
}

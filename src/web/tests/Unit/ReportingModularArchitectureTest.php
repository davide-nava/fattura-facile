<?php

declare(strict_types=1);

use App\Modules\Companies\Models\Company;
use App\Modules\Customers\Models\Customer;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Invoicing\Models\InvoiceItem;
use App\Modules\Reporting\Contracts\ReportInterface;
use App\Modules\Reporting\DTOs\ReportResult;
use App\Modules\Reporting\Reports\FinancialSummaryReport;
use App\Modules\Reporting\Reports\InvoiceJournalReport;
use App\Modules\Reporting\Reports\TaxSummaryReport;
use App\Modules\Reporting\Services\ReportManager;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $tenant = Tenant::create(['name' => 'Reporting Modular Tenant', 'slug' => 'rep-mod-t']);
    app(TenantContext::class)->setTenant($tenant);

    $this->company = Company::create([
        'name' => 'Fattura Swiss SA',
        'street' => 'Via Cantonale 5',
        'city' => 'Lugano',
        'postal_code' => '6900',
        'country' => 'CH',
        'currency' => 'CHF',
        'tax_number' => 'CHE-999.888.777',
        'vat_number' => 'CHE-999.888.777 MWST',
    ]);

    $this->customer = Customer::create([
        'company_id' => $this->company->id,
        'name' => 'Client SA',
    ]);
});

test('report manager contains built-in reports', function () {
    /** @var ReportManager $manager */
    $manager = app(ReportManager::class);

    $reports = $manager->all();

    expect($reports)->toHaveKeys(['financial-summary', 'invoice-journal', 'vat-summary'])
        ->and($manager->get('financial-summary'))->toBeInstanceOf(FinancialSummaryReport::class)
        ->and($manager->get('invoice-journal'))->toBeInstanceOf(InvoiceJournalReport::class)
        ->and($manager->get('vat-summary'))->toBeInstanceOf(TaxSummaryReport::class);
});

test('report manager throws exception for non-existent report', function () {
    /** @var ReportManager $manager */
    $manager = app(ReportManager::class);

    $manager->findOrFail('non-existent-report');
})->throws(InvalidArgumentException::class, 'Report [non-existent-report] not found.');

test('report manager supports custom report registration', function () {
    /** @var ReportManager $manager */
    $manager = app(ReportManager::class);

    $customReport = new class implements ReportInterface
    {
        public function key(): string
        {
            return 'custom-kpi';
        }

        public function name(): string
        {
            return 'Custom KPI Report';
        }

        public function description(): string
        {
            return 'Test custom report.';
        }

        public function category(): string
        {
            return 'Custom';
        }

        public function view(): string
        {
            return 'reports.custom';
        }

        public function paperOrientation(): string
        {
            return 'portrait';
        }

        public function paperSize(): string
        {
            return 'a4';
        }

        public function generate(Company $company, array $parameters = []): ReportResult
        {
            return new ReportResult(
                key: $this->key(),
                title: $this->name(),
                subtitle: 'Custom Subtitle',
                company: $company,
                branding: $company->getBranding(),
                generatedAt: CarbonImmutable::now(),
            );
        }
    };

    $manager->register($customReport);

    expect($manager->get('custom-kpi'))->toBe($customReport)
        ->and($manager->findOrFail('custom-kpi')->name())->toBe('Custom KPI Report');
});

test('financial summary report use-case generates expected DTO structure', function () {
    $report = app(FinancialSummaryReport::class);

    expect($report->key())->toBe('financial-summary')
        ->and($report->category())->toBe('Financial')
        ->and($report->paperOrientation())->toBe('portrait')
        ->and($report->paperSize())->toBe('a4');

    $result = $report->generate($this->company);

    expect($result)->toBeInstanceOf(ReportResult::class)
        ->and($result->title)->toBe('Financial Summary & Health Report')
        ->and($result->company->id)->toBe($this->company->id)
        ->and($result->kpis)->toHaveCount(6)
        ->and($result->tables)->toHaveKey('financial_summary')
        ->and($result->summary)->toHaveKey('Net Working Position');
});

test('invoice journal report use-case generates formatted rows and totals', function () {
    $report = app(InvoiceJournalReport::class);

    expect($report->key())->toBe('invoice-journal')
        ->and($report->category())->toBe('Sales')
        ->and($report->paperOrientation())->toBe('landscape');

    Invoice::create([
        'company_id' => $this->company->id,
        'customer_id' => $this->customer->id,
        'invoice_number' => 'INV-TEST-001',
        'invoice_date' => CarbonImmutable::today(),
        'due_date' => CarbonImmutable::today()->addDays(30),
        'status' => 'issued',
        'subtotal' => '1000.00',
        'tax_total' => '81.00',
        'total' => '1081.00',
        'outstanding_amount' => '1081.00',
        'currency' => 'CHF',
    ]);

    $result = $report->generate($this->company);

    expect($result->tables['invoices']['rows'])->toHaveCount(1)
        ->and($result->tables['invoices']['rows'][0]['number'])->toBe('INV-TEST-001')
        ->and($result->summary['Total Invoices Count'])->toBe('1')
        ->and($result->summary['Total Gross'])->toBe('1\'081.00 CHF');
});

test('vat summary report use-case aggregates swiss tax rates correctly', function () {
    $report = app(TaxSummaryReport::class);

    expect($report->key())->toBe('vat-summary')
        ->and($report->category())->toBe('Tax');

    $invoice = Invoice::create([
        'company_id' => $this->company->id,
        'customer_id' => $this->customer->id,
        'invoice_number' => 'INV-VAT-001',
        'invoice_date' => CarbonImmutable::today(),
        'due_date' => CarbonImmutable::today()->addDays(14),
        'status' => 'issued',
        'subtotal' => '1000.00',
        'tax_total' => '81.00',
        'total' => '1081.00',
        'currency' => 'CHF',
    ]);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'description' => 'Consulting Service',
        'quantity' => '1',
        'unit_price' => '1000.00',
        'tax_rate' => '8.1',
        'tax_amount' => '81.00',
        'total' => '1081.00',
    ]);

    $result = $report->generate($this->company);

    expect($result->tables['vat_breakdown']['rows'])->toHaveCount(1)
        ->and($result->tables['vat_breakdown']['rows'][0]['rate'])->toBe('8.1%')
        ->and($result->summary['Total Calculated VAT'])->toBe('81.00 CHF');
});

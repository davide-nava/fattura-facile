<?php

namespace Tests\Feature;

use App\Modules\Companies\Models\Company;
use App\Modules\Customers\Models\Customer;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Invoicing\Services\InvoicePdfService;
use App\Modules\Invoicing\Services\InvoiceService;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class InvoiceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private Company $company;

    private Customer $customer;

    private InvoiceService $invoiceService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['name' => 'Swiss Watchmakers SA', 'slug' => 'swiss-watchmakers']);
        app(TenantContext::class)->setTenant($this->tenant);

        $this->company = Company::create([
            'name' => 'Geneva Atelier',
            'tax_number' => 'CHE-999.888.777',
            'vat_number' => 'CHE-999.888.777 MWST',
            'currency' => 'CHF',
            'country' => 'CH',
        ]);

        $this->customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Boutique du Rhône',
            'currency' => 'CHF',
            'payment_term_days' => 30,
        ]);

        $this->invoiceService = app(InvoiceService::class);
    }

    public function test_invoice_creation_calculates_swiss_vat_and_rounding(): void
    {
        $items = [
            [
                'description' => 'Luxury Timepiece Ref 101',
                'quantity' => 2,
                'unit_price' => 1250.00, // 2500.00
                'discount_rate' => 10.00, // 10% discount -> 2250.00
                'tax_rate' => 8.10,      // 8.1% VAT -> 182.25
            ],
            [
                'description' => 'Leather Strap',
                'quantity' => 1,
                'unit_price' => 120.00,  // 120.00
                'discount_rate' => 0.00,
                'tax_rate' => 8.10,      // 8.1% VAT -> 9.72
            ],
        ];

        // Subtotal = 2250.00 + 120.00 = 2370.00
        // VAT = 182.25 + 9.72 = 191.97
        // Total = 2561.97 -> rounded to 5 cents: 2561.95 CHF
        $invoice = $this->invoiceService->createInvoice(
            $this->company->id,
            $this->customer->id,
            $items,
            '2026-03-01'
        );

        $this->assertEquals('draft', $invoice->status);
        $this->assertEquals('2370.00', $invoice->subtotal);
        $this->assertEquals('191.97', $invoice->tax_total);
        $this->assertEquals('2561.95', $invoice->total);
        $this->assertEquals('2561.95', $invoice->outstanding_amount);
        $this->assertStringStartsWith('INV-2026-', $invoice->invoice_number);
    }

    public function test_invoice_issue_workflow(): void
    {
        $invoice = $this->invoiceService->createInvoice(
            $this->company->id,
            $this->customer->id,
            [['description' => 'Test Item', 'quantity' => 1, 'unit_price' => 100.00, 'tax_rate' => 8.10]]
        );

        $this->assertEquals('draft', $invoice->status);

        $issued = $this->invoiceService->issueInvoice($invoice->id);

        $this->assertEquals('issued', $issued->status);
        $this->assertNotNull($issued->issued_at);

        // Cannot issue an already issued invoice
        $this->expectException(RuntimeException::class);
        $this->invoiceService->issueInvoice($invoice->id);
    }

    public function test_credit_note_workflow_reduces_outstanding_amount(): void
    {
        $invoice = $this->invoiceService->createInvoice(
            $this->company->id,
            $this->customer->id,
            [['description' => 'Goods Delivered', 'quantity' => 2, 'unit_price' => 500.00, 'tax_rate' => 0.00]]
        );
        $this->invoiceService->issueInvoice($invoice->id);

        $this->assertEquals('1000.00', $invoice->fresh()->outstanding_amount);

        // Issue Credit Note for 1 returned item (500.00)
        $creditNote = $this->invoiceService->createCreditNote(
            $invoice->id,
            'Customer returned 1 damaged unit',
            [['description' => 'Returned Item', 'quantity' => 1, 'unit_price' => 500.00, 'tax_rate' => 0.00]]
        );

        $this->assertEquals('500.00', $creditNote->total);
        $this->assertEquals('500.00', $invoice->fresh()->outstanding_amount);
        $this->assertEquals('issued', $invoice->fresh()->status);

        // Issue second Credit Note for remaining 500.00 -> invoice should be fully settled
        $this->invoiceService->createCreditNote(
            $invoice->id,
            'Discount compensation',
            [['description' => 'Discount', 'quantity' => 1, 'unit_price' => 500.00, 'tax_rate' => 0.00]]
        );

        $this->assertEquals('0.00', $invoice->fresh()->outstanding_amount);
        $this->assertEquals('paid', $invoice->fresh()->status);
    }

    public function test_invoice_pdf_generation_produces_valid_stream(): void
    {
        $invoice = $this->invoiceService->createInvoice(
            $this->company->id,
            $this->customer->id,
            [['description' => 'Consulting Service', 'quantity' => 5, 'unit_price' => 200.00, 'tax_rate' => 8.10]]
        );

        /** @var InvoicePdfService $pdfService */
        $pdfService = app(InvoicePdfService::class);
        $pdfContent = $pdfService->generatePdf($invoice);

        $this->assertNotEmpty($pdfContent);
        // Verify standard PDF header magic bytes %PDF-
        $this->assertStringStartsWith('%PDF-', $pdfContent);

        // Also test downloadPdf response
        $response = $pdfService->downloadPdf($invoice);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_create_invoice_requires_at_least_one_item(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('at least one line item');

        $this->invoiceService->createInvoice(
            $this->company->id,
            $this->customer->id,
            []
        );
    }

    public function test_create_credit_note_against_draft_throws(): void
    {
        $invoice = $this->invoiceService->createInvoice(
            $this->company->id,
            $this->customer->id,
            [['description' => 'Item', 'quantity' => 1, 'unit_price' => 100.00]]
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot create credit note against invoice in status: draft');

        $this->invoiceService->createCreditNote($invoice->id, 'Credit test');
    }

    public function test_create_credit_note_without_items_copies_invoice_items_and_handles_overflow(): void
    {
        $invoice = $this->invoiceService->createInvoice(
            $this->company->id,
            $this->customer->id,
            [['description' => 'Original Item', 'quantity' => 2, 'unit_price' => 100.00, 'tax_rate' => 0.00]]
        );

        $this->invoiceService->issueInvoice($invoice->id);

        // Manually adjust invoice outstanding_amount lower to test overflow branch (line 241)
        $invoice->update(['outstanding_amount' => '50.00']);

        // Call createCreditNote with null items -> copies invoice items (total = 200.00, exceeding 50.00)
        $cn = $this->invoiceService->createCreditNote($invoice->id, 'Full credit', null);

        $this->assertEquals('200.00', $cn->total);
        $this->assertEquals('0.00', $invoice->fresh()->outstanding_amount);
        $this->assertEquals('paid', $invoice->fresh()->status);
    }
}

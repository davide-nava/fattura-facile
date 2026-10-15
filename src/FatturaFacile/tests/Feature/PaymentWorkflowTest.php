<?php

namespace Tests\Feature;

use App\Modules\Companies\Models\Company;
use App\Modules\Customers\Models\Customer;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Invoicing\Services\InvoiceService;
use App\Modules\Payments\Models\Payment;
use App\Modules\Payments\Services\PaymentService;
use App\Modules\Purchasing\Models\SupplierInvoice;
use App\Modules\Suppliers\Models\Supplier;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class PaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private Company $company;

    private Customer $customer;

    private Supplier $supplier;

    private InvoiceService $invoiceService;

    private PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['name' => 'Rhone Trading SA', 'slug' => 'rhone-trading']);
        app(TenantContext::class)->setTenant($this->tenant);

        $this->company = Company::create(['name' => 'Rhone Trading Geneva', 'currency' => 'CHF']);
        $this->customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Leman Stores Sàrl',
            'currency' => 'CHF',
        ]);
        $this->supplier = Supplier::create([
            'company_id' => $this->company->id,
            'name' => 'Swiss Packaging AG',
            'currency' => 'CHF',
        ]);

        $this->invoiceService = app(InvoiceService::class);
        $this->paymentService = app(PaymentService::class);
    }

    public function test_partial_payment_updates_outstanding_and_sets_partially_paid(): void
    {
        $invoice = $this->invoiceService->createInvoice(
            $this->company->id,
            $this->customer->id,
            [['description' => 'Goods Delivery', 'quantity' => 1, 'unit_price' => 1000.00, 'tax_rate' => 0.00]]
        );
        $this->invoiceService->issueInvoice($invoice->id);

        $this->assertEquals('1000.00', $invoice->fresh()->outstanding_amount);

        // Make partial payment of 400.00 CHF
        $payment = $this->paymentService->recordCustomerPayment(
            $this->company->id,
            400.00,
            'bank_transfer',
            null,
            'CHF',
            'REF-BANK-001',
            'Partial transfer',
            [['invoice_id' => $invoice->id, 'amount' => 400.00]]
        );

        $this->assertEquals('partially_paid', $invoice->fresh()->status);
        $this->assertEquals('400.00', $invoice->fresh()->paid_amount);
        $this->assertEquals('600.00', $invoice->fresh()->outstanding_amount);
        $this->assertCount(1, $payment->allocations);
    }

    public function test_full_payment_clears_invoice_and_sets_paid(): void
    {
        $invoice = $this->invoiceService->createInvoice(
            $this->company->id,
            $this->customer->id,
            [['description' => 'Goods Delivery', 'quantity' => 1, 'unit_price' => 500.00, 'tax_rate' => 0.00]]
        );
        $this->invoiceService->issueInvoice($invoice->id);

        $this->paymentService->recordCustomerPayment(
            $this->company->id,
            500.00,
            'bank_transfer',
            null,
            'CHF',
            'REF-FULL',
            'Full payment',
            [['invoice_id' => $invoice->id, 'amount' => 500.00]]
        );

        $this->assertEquals('paid', $invoice->fresh()->status);
        $this->assertEquals('500.00', $invoice->fresh()->paid_amount);
        $this->assertEquals('0.00', $invoice->fresh()->outstanding_amount);
    }

    public function test_overpayment_exceeding_invoice_total_is_rejected(): void
    {
        $invoice = $this->invoiceService->createInvoice(
            $this->company->id,
            $this->customer->id,
            [['description' => 'Small Service', 'quantity' => 1, 'unit_price' => 100.00, 'tax_rate' => 0.00]]
        );
        $this->invoiceService->issueInvoice($invoice->id);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('exceeds invoice total');

        // Attempting to allocate 150.00 to a 100.00 invoice
        $this->paymentService->recordCustomerPayment(
            $this->company->id,
            150.00,
            'bank_transfer',
            null,
            'CHF',
            'REF-ERR',
            'Overpayment',
            [['invoice_id' => $invoice->id, 'amount' => 150.00]]
        );
    }

    public function test_supplier_payment_workflow(): void
    {
        $supInv = SupplierInvoice::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'supplier_id' => $this->supplier->id,
            'supplier_invoice_number' => 'SUPP-INV-999',
            'invoice_date' => '2026-02-01',
            'due_date' => '2026-03-01',
            'currency' => 'CHF',
            'subtotal' => '800.00',
            'tax_total' => '0.00',
            'total' => '800.00',
            'paid_amount' => '0.00',
            'status' => 'approved',
        ]);

        $this->paymentService->recordSupplierPayment(
            $this->company->id,
            800.00,
            'bank_transfer',
            null,
            'CHF',
            'REF-SUPP-PAY',
            'Supplier settled',
            [['supplier_invoice_id' => $supInv->id, 'amount' => 800.00]]
        );

        $this->assertEquals('paid', $supInv->fresh()->status);
        $this->assertEquals('800.00', $supInv->fresh()->paid_amount);
    }

    public function test_customer_payment_amount_must_be_positive(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Payment amount must be greater than zero');

        $this->paymentService->recordCustomerPayment(
            $this->company->id,
            0.00
        );
    }

    public function test_supplier_payment_amount_must_be_positive(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Payment amount must be greater than zero');

        $this->paymentService->recordSupplierPayment(
            $this->company->id,
            -10.00
        );
    }

    public function test_allocation_exceeding_payment_amount_throws(): void
    {
        $invoice = $this->invoiceService->createInvoice(
            $this->company->id,
            $this->customer->id,
            [['description' => 'Service', 'quantity' => 1, 'unit_price' => 500.00, 'tax_rate' => 0.00]]
        );
        $this->invoiceService->issueInvoice($invoice->id);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('exceeds payment total');

        // Payment is 100, but allocated 200
        $this->paymentService->recordCustomerPayment(
            $this->company->id,
            100.00,
            'bank_transfer',
            null,
            'CHF',
            null,
            null,
            [['invoice_id' => $invoice->id, 'amount' => 200.00]]
        );
    }

    public function test_zero_or_negative_allocation_amount_is_skipped(): void
    {
        $invoice = $this->invoiceService->createInvoice(
            $this->company->id,
            $this->customer->id,
            [['description' => 'Service', 'quantity' => 1, 'unit_price' => 500.00, 'tax_rate' => 0.00]]
        );
        $this->invoiceService->issueInvoice($invoice->id);

        $payment = $this->paymentService->recordCustomerPayment(
            $this->company->id,
            100.00,
            'bank_transfer',
            null,
            'CHF',
            null,
            null,
            [['invoice_id' => $invoice->id, 'amount' => 0.00]]
        );

        $this->assertCount(0, $payment->allocations);
    }
}

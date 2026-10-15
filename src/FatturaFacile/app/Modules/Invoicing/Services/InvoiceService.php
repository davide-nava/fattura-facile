<?php

namespace App\Modules\Invoicing\Services;

use App\Modules\Audit\Services\AuditService;
use App\Modules\Companies\Models\Company;
use App\Modules\Companies\Models\DocumentSequence;
use App\Modules\Customers\Models\Customer;
use App\Modules\Invoicing\Models\CreditNote;
use App\Modules\Invoicing\Models\CreditNoteItem;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Invoicing\Models\InvoiceItem;
use App\Modules\Tenancy\Services\TenantContext;
use App\Shared\Money\MoneyValue;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class InvoiceService
{
    public function __construct(
        private AuditService $auditService,
        private TenantContext $tenantContext
    ) {}

    /**
     * Create a draft invoice with server-side recalculated totals.
     */
    public function createInvoice(
        int $companyId,
        int $customerId,
        array $items,
        ?string $invoiceDate = null,
        ?string $dueDate = null,
        string $currency = 'CHF',
        ?string $notes = null,
        ?int $salesOrderId = null
    ): Invoice {
        if (empty($items)) {
            throw new InvalidArgumentException('Invoice must contain at least one line item.');
        }

        return DB::transaction(function () use ($companyId, $customerId, $items, $invoiceDate, $dueDate, $currency, $notes, $salesOrderId) {
            $company = Company::findOrFail($companyId);
            $customer = Customer::findOrFail($customerId);
            $tenantId = $this->tenantContext->getTenantId() ?? $company->tenant_id;

            $invDate = $invoiceDate ? Carbon::parse($invoiceDate) : Carbon::today();
            $dDate = $dueDate ? Carbon::parse($dueDate) : $invDate->copy()->addDays($customer->payment_term_days ?: 30);

            $invoiceNumber = DocumentSequence::getNextNumber($tenantId, $companyId, 'invoice', 'INV-'.$invDate->format('Y').'-');

            $invoice = Invoice::create([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'customer_id' => $customerId,
                'sales_order_id' => $salesOrderId,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $invDate->toDateString(),
                'due_date' => $dDate->toDateString(),
                'status' => 'draft',
                'currency' => $currency,
                'subtotal' => '0.00',
                'tax_total' => '0.00',
                'total' => '0.00',
                'paid_amount' => '0.00',
                'outstanding_amount' => '0.00',
                'notes' => $notes,
            ]);

            $subtotalMoney = MoneyValue::zero($currency);
            $taxTotalMoney = MoneyValue::zero($currency);

            foreach ($items as $item) {
                $qty = number_format((float) ($item['quantity'] ?? 1), 4, '.', '');
                $price = number_format((float) ($item['unit_price'] ?? 0), 2, '.', '');
                $discountRate = number_format((float) ($item['discount_rate'] ?? 0), 2, '.', '');
                $taxRate = number_format((float) ($item['tax_rate'] ?? 8.10), 4, '.', '');

                // lineBase = qty * price
                $lineBase = bcmul($qty, $price, 4);

                // discountAmount = lineBase * (discountRate / 100)
                $discountFactor = bcdiv($discountRate, '100', 4);
                $discountAmount = bcmul($lineBase, $discountFactor, 4);
                $lineSubtotalStr = bcsub($lineBase, $discountAmount, 2);
                $lineSubtotal = MoneyValue::of($lineSubtotalStr, $currency);

                // taxAmount = lineSubtotal * (taxRate / 100)
                $taxFactor = bcdiv($taxRate, '100', 6);
                $taxAmountStr = bcmul($lineSubtotal->getAmount(), $taxFactor, 2);
                $lineTax = MoneyValue::of($taxAmountStr, $currency);

                $lineTotal = $lineSubtotal->add($lineTax);

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'] ?? 'Item',
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'discount_rate' => $discountRate,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $lineTax->getAmount(),
                    'total' => $lineTotal->getAmount(),
                ]);

                $subtotalMoney = $subtotalMoney->add($lineSubtotal);
                $taxTotalMoney = $taxTotalMoney->add($lineTax);
            }

            $totalMoney = $subtotalMoney->add($taxTotalMoney);
            if ($currency === 'CHF') {
                $totalMoney = $totalMoney->roundSwissFiveCents();
            }

            $invoice->subtotal = $subtotalMoney->getAmount();
            $invoice->tax_total = $taxTotalMoney->getAmount();
            $invoice->total = $totalMoney->getAmount();
            $invoice->outstanding_amount = $totalMoney->getAmount();
            $invoice->save();

            $this->auditService->log('create', $invoice, null, $invoice->toArray(), $companyId);

            return $invoice;
        });
    }

    /**
     * Issue invoice, finalizing it and preparing accounting integration.
     */
    public function issueInvoice(int $invoiceId): Invoice
    {
        return DB::transaction(function () use ($invoiceId) {
            $invoice = Invoice::findOrFail($invoiceId);

            if ($invoice->status !== 'draft') {
                throw new RuntimeException("Only draft invoices can be issued. Current status: {$invoice->status}");
            }

            $old = $invoice->toArray();
            $invoice->status = 'issued';
            $invoice->issued_at = now();
            $invoice->save();

            $this->auditService->log('issue', $invoice, $old, $invoice->toArray(), $invoice->company_id);

            return $invoice;
        });
    }

    /**
     * Create Credit Note against an issued invoice.
     */
    public function createCreditNote(
        int $invoiceId,
        string $reason,
        ?array $items = null
    ): CreditNote {
        return DB::transaction(function () use ($invoiceId, $reason, $items) {
            $invoice = Invoice::with('items')->findOrFail($invoiceId);

            if ($invoice->status === 'draft' || $invoice->status === 'cancelled') {
                throw new RuntimeException("Cannot create credit note against invoice in status: {$invoice->status}");
            }

            $tenantId = $this->tenantContext->getTenantId() ?? $invoice->tenant_id;
            $companyId = $invoice->company_id;
            $creditNoteNumber = DocumentSequence::getNextNumber($tenantId, $companyId, 'credit_note', 'CRN-'.date('Y').'-');

            $creditNote = CreditNote::create([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'customer_id' => $invoice->customer_id,
                'invoice_id' => $invoice->id,
                'credit_note_number' => $creditNoteNumber,
                'credit_note_date' => Carbon::today()->toDateString(),
                'status' => 'issued',
                'currency' => $invoice->currency,
                'subtotal' => '0.00',
                'tax_total' => '0.00',
                'total' => '0.00',
                'reason' => $reason,
                'issued_at' => now(),
            ]);

            $subtotalMoney = MoneyValue::zero($invoice->currency);
            $taxTotalMoney = MoneyValue::zero($invoice->currency);

            $sourceItems = $items ?: $invoice->items->map(fn ($i) => [
                'product_id' => $i->product_id,
                'description' => $i->description,
                'quantity' => $i->quantity,
                'unit_price' => $i->unit_price,
                'tax_rate' => $i->tax_rate,
            ])->toArray();

            foreach ($sourceItems as $item) {
                $qty = number_format((float) ($item['quantity'] ?? 1), 4, '.', '');
                $price = number_format((float) ($item['unit_price'] ?? 0), 2, '.', '');
                $taxRate = number_format((float) ($item['tax_rate'] ?? 8.10), 4, '.', '');

                $lineSubtotalStr = bcmul($qty, $price, 2);
                $lineSubtotal = MoneyValue::of($lineSubtotalStr, $invoice->currency);

                $taxFactor = bcdiv($taxRate, '100', 6);
                $taxAmountStr = bcmul($lineSubtotal->getAmount(), $taxFactor, 2);
                $lineTax = MoneyValue::of($taxAmountStr, $invoice->currency);
                $lineTotal = $lineSubtotal->add($lineTax);

                CreditNoteItem::create([
                    'credit_note_id' => $creditNote->id,
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'] ?? 'Credit Line',
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $lineTax->getAmount(),
                    'total' => $lineTotal->getAmount(),
                ]);

                $subtotalMoney = $subtotalMoney->add($lineSubtotal);
                $taxTotalMoney = $taxTotalMoney->add($lineTax);
            }

            $totalMoney = $subtotalMoney->add($taxTotalMoney);
            if ($invoice->currency === 'CHF') {
                $totalMoney = $totalMoney->roundSwissFiveCents();
            }

            $creditNote->subtotal = $subtotalMoney->getAmount();
            $creditNote->tax_total = $taxTotalMoney->getAmount();
            $creditNote->total = $totalMoney->getAmount();
            $creditNote->save();

            // Adjust invoice outstanding amount
            $currentOutstanding = MoneyValue::of($invoice->outstanding_amount, $invoice->currency);
            $newOutstanding = $currentOutstanding->subtract($totalMoney);
            if ($newOutstanding->isNegative()) {
                $newOutstanding = MoneyValue::zero($invoice->currency);
            }
            $invoice->outstanding_amount = $newOutstanding->getAmount();
            if ($newOutstanding->isZero()) {
                $invoice->status = 'paid';
            }
            $invoice->save();

            $this->auditService->log('issue_credit_note', $creditNote, null, $creditNote->toArray(), $companyId);

            return $creditNote;
        });
    }
}

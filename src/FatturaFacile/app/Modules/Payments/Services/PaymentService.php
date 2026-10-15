<?php

namespace App\Modules\Payments\Services;

use App\Modules\Audit\Services\AuditService;
use App\Modules\Companies\Models\Company;
use App\Modules\Companies\Models\DocumentSequence;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Payments\Models\Payment;
use App\Modules\Payments\Models\PaymentAllocation;
use App\Modules\Purchasing\Models\SupplierInvoice;
use App\Modules\Tenancy\Services\TenantContext;
use App\Shared\Money\MoneyValue;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class PaymentService
{
    public function __construct(
        private AuditService $auditService,
        private TenantContext $tenantContext
    ) {}

    /**
     * Record customer payment and allocate to invoice(s).
     *
     * @param  array<array{invoice_id: int, amount: string|float}>  $allocations
     */
    public function recordCustomerPayment(
        int $companyId,
        string|float $amount,
        string $paymentMethod = 'bank_transfer',
        ?string $paymentDate = null,
        string $currency = 'CHF',
        ?string $reference = null,
        ?string $notes = null,
        array $allocations = []
    ): Payment {
        $paymentAmount = MoneyValue::of($amount, $currency);
        if ($paymentAmount->isNegative() || $paymentAmount->isZero()) {
            throw new InvalidArgumentException('Payment amount must be greater than zero.');
        }

        return DB::transaction(function () use ($companyId, $paymentAmount, $paymentMethod, $paymentDate, $currency, $reference, $notes, $allocations) {
            $company = Company::findOrFail($companyId);
            $tenantId = $this->tenantContext->getTenantId() ?? $company->tenant_id;
            $payDate = $paymentDate ? Carbon::parse($paymentDate) : Carbon::today();

            $paymentNumber = DocumentSequence::getNextNumber($tenantId, $companyId, 'payment', 'PAY-'.$payDate->format('Y').'-');

            $payment = Payment::create([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'payment_number' => $paymentNumber,
                'payment_date' => $payDate->toDateString(),
                'payment_method' => $paymentMethod,
                'amount' => $paymentAmount->getAmount(),
                'currency' => $currency,
                'reference' => $reference,
                'notes' => $notes,
            ]);

            $totalAllocated = MoneyValue::zero($currency);

            foreach ($allocations as $alloc) {
                $invoice = Invoice::where('tenant_id', $tenantId)->findOrFail($alloc['invoice_id']);
                $allocatedMoney = MoneyValue::of($alloc['amount'], $currency);

                if ($allocatedMoney->isNegative() || $allocatedMoney->isZero()) {
                    continue;
                }

                $currentPaid = MoneyValue::of($invoice->paid_amount, $currency);
                $newPaid = $currentPaid->add($allocatedMoney);

                $invoiceTotal = MoneyValue::of($invoice->total, $currency);
                if ($newPaid->greaterThan($invoiceTotal)) {
                    throw new RuntimeException("Allocated payment exceeds invoice total for invoice {$invoice->invoice_number}.");
                }

                $newOutstanding = $invoiceTotal->subtract($newPaid);

                $invoice->paid_amount = $newPaid->getAmount();
                $invoice->outstanding_amount = $newOutstanding->getAmount();
                $invoice->status = $newOutstanding->isZero() ? 'paid' : 'partially_paid';
                $invoice->save();

                PaymentAllocation::create([
                    'tenant_id' => $tenantId,
                    'company_id' => $companyId,
                    'payment_id' => $payment->id,
                    'allocatable_type' => Invoice::class,
                    'allocatable_id' => $invoice->id,
                    'allocated_amount' => $allocatedMoney->getAmount(),
                ]);

                $totalAllocated = $totalAllocated->add($allocatedMoney);
            }

            if ($totalAllocated->greaterThan($paymentAmount)) {
                throw new InvalidArgumentException("Total allocated ({$totalAllocated->getAmount()}) exceeds payment total ({$paymentAmount->getAmount()}).");
            }

            $this->auditService->log('create', $payment, null, $payment->toArray(), $companyId);

            return $payment;
        });
    }

    /**
     * Record supplier payment and allocate to supplier invoice(s).
     */
    public function recordSupplierPayment(
        int $companyId,
        string|float $amount,
        string $paymentMethod = 'bank_transfer',
        ?string $paymentDate = null,
        string $currency = 'CHF',
        ?string $reference = null,
        ?string $notes = null,
        array $allocations = []
    ): Payment {
        $paymentAmount = MoneyValue::of($amount, $currency);
        if ($paymentAmount->isNegative() || $paymentAmount->isZero()) {
            throw new InvalidArgumentException('Payment amount must be greater than zero.');
        }

        return DB::transaction(function () use ($companyId, $paymentAmount, $paymentMethod, $paymentDate, $currency, $reference, $notes, $allocations) {
            $company = Company::findOrFail($companyId);
            $tenantId = $this->tenantContext->getTenantId() ?? $company->tenant_id;
            $payDate = $paymentDate ? Carbon::parse($paymentDate) : Carbon::today();

            $paymentNumber = DocumentSequence::getNextNumber($tenantId, $companyId, 'supplier_payment', 'SPAY-'.$payDate->format('Y').'-');

            $payment = Payment::create([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'payment_number' => $paymentNumber,
                'payment_date' => $payDate->toDateString(),
                'payment_method' => $paymentMethod,
                'amount' => $paymentAmount->getAmount(),
                'currency' => $currency,
                'reference' => $reference,
                'notes' => $notes,
            ]);

            foreach ($allocations as $alloc) {
                $supInv = SupplierInvoice::where('tenant_id', $tenantId)->findOrFail($alloc['supplier_invoice_id']);
                $allocatedMoney = MoneyValue::of($alloc['amount'], $currency);

                $currentPaid = MoneyValue::of($supInv->paid_amount, $currency);
                $newPaid = $currentPaid->add($allocatedMoney);

                $supInvTotal = MoneyValue::of($supInv->total, $currency);
                $supInv->paid_amount = $newPaid->getAmount();
                if ($newPaid->greaterThan($supInvTotal) || $newPaid->equals($supInvTotal)) {
                    $supInv->status = 'paid';
                }
                $supInv->save();

                PaymentAllocation::create([
                    'tenant_id' => $tenantId,
                    'company_id' => $companyId,
                    'payment_id' => $payment->id,
                    'allocatable_type' => SupplierInvoice::class,
                    'allocatable_id' => $supInv->id,
                    'allocated_amount' => $allocatedMoney->getAmount(),
                ]);
            }

            $this->auditService->log('create_supplier_payment', $payment, null, $payment->toArray(), $companyId);

            return $payment;
        });
    }
}

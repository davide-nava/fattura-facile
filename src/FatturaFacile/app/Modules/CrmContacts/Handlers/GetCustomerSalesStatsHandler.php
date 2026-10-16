<?php

namespace App\Modules\CrmContacts\Handlers;

use App\Modules\CrmContacts\DTOs\CustomerSalesStats;
use App\Modules\CrmContacts\Queries\GetCustomerSalesStatsQuery;
use App\Modules\Customers\Models\Customer;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Sales\Models\SalesOrder;
use App\Shared\Money\MoneyValue;

class GetCustomerSalesStatsHandler
{
    public function handle(GetCustomerSalesStatsQuery $query): CustomerSalesStats
    {
        $customer = Customer::with(['quotes', 'salesOrders'])->findOrFail($query->customerId);
        $currency = $customer->currency ?: 'CHF';

        $invoicesQuery = Invoice::where('customer_id', $customer->id);
        if ($query->year !== null) {
            $invoicesQuery->whereYear('invoice_date', $query->year);
        }

        $invoices = $invoicesQuery->get();

        $totalInvoiced = MoneyValue::zero($currency);
        $totalOutstanding = MoneyValue::zero($currency);

        foreach ($invoices as $invoice) {
            if ($invoice->status !== 'cancelled') {
                $totalInvoiced = $totalInvoiced->add(MoneyValue::of($invoice->total ?? '0.00', $currency));
                $totalOutstanding = $totalOutstanding->add(MoneyValue::of($invoice->outstanding_amount ?? '0.00', $currency));
            }
        }

        $totalPaid = $totalInvoiced->subtract($totalOutstanding);
        if (bccomp($totalPaid->getAmount(), '0.00', 2) < 0) {
            $totalPaid = MoneyValue::zero($currency);
        }

        $invoicesCount = $invoices->count();
        $quotesCount = $customer->quotes->count();
        $salesOrdersCount = $customer->salesOrders->count();

        $averageOrderValue = '0.00';
        if ($invoicesCount > 0) {
            $averageOrderValue = $totalInvoiced->divide((string) $invoicesCount)->getAmount();
        }

        $lastInvoiceDate = $invoices->max('invoice_date');
        $lastOrderDate = SalesOrder::where('customer_id', $customer->id)->max('order_date');

        return new CustomerSalesStats(
            customerId: $customer->id,
            customerName: $customer->name,
            currency: $currency,
            totalInvoiced: $totalInvoiced->getAmount(),
            totalPaid: $totalPaid->getAmount(),
            totalOutstanding: $totalOutstanding->getAmount(),
            quotesCount: $quotesCount,
            salesOrdersCount: $salesOrdersCount,
            invoicesCount: $invoicesCount,
            averageOrderValue: $averageOrderValue,
            lastOrderDate: $lastOrderDate ? (string) $lastOrderDate : null,
            lastInvoiceDate: $lastInvoiceDate ? (string) $lastInvoiceDate : null,
        );
    }
}

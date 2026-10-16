<?php

namespace App\Modules\CrmContacts\DTOs;

readonly class CustomerSalesStats
{
    public function __construct(
        public int $customerId,
        public string $customerName,
        public string $currency,
        public string $totalInvoiced,
        public string $totalPaid,
        public string $totalOutstanding,
        public int $quotesCount,
        public int $salesOrdersCount,
        public int $invoicesCount,
        public string $averageOrderValue,
        public ?string $lastOrderDate = null,
        public ?string $lastInvoiceDate = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'customer_id' => $this->customerId,
            'customer_name' => $this->customerName,
            'currency' => $this->currency,
            'total_invoiced' => $this->totalInvoiced,
            'total_paid' => $this->totalPaid,
            'total_outstanding' => $this->totalOutstanding,
            'quotes_count' => $this->quotesCount,
            'sales_orders_count' => $this->salesOrdersCount,
            'invoices_count' => $this->invoicesCount,
            'average_order_value' => $this->averageOrderValue,
            'last_order_date' => $this->lastOrderDate,
            'last_invoice_date' => $this->lastInvoiceDate,
        ];
    }
}

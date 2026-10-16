<?php

namespace App\Modules\CrmContacts\DTOs;

readonly class PortalDashboardData
{
    /**
     * @param  array<string, mixed>  $customer
     * @param  array<string, mixed>|null  $primaryContact
     * @param  array<int, mixed>  $recentInvoices
     * @param  array<int, mixed>  $recentQuotes
     * @param  array<int, mixed>  $recentOrders
     */
    public function __construct(
        public array $customer,
        public ?array $primaryContact,
        public string $totalInvoiced,
        public string $totalOutstanding,
        public string $currency,
        public array $recentInvoices = [],
        public array $recentQuotes = [],
        public array $recentOrders = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'customer' => $this->customer,
            'primary_contact' => $this->primaryContact,
            'total_invoiced' => $this->totalInvoiced,
            'total_outstanding' => $this->totalOutstanding,
            'currency' => $this->currency,
            'recent_invoices' => $this->recentInvoices,
            'recent_quotes' => $this->recentQuotes,
            'recent_orders' => $this->recentOrders,
        ];
    }
}

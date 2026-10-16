<?php

namespace App\Modules\CrmContacts\Handlers;

use App\Modules\CrmContacts\DTOs\PortalDashboardData;
use App\Modules\CrmContacts\Models\CustomerPortalUser;
use App\Modules\CrmContacts\Queries\GetPortalCustomerDashboardQuery;
use App\Shared\Money\MoneyValue;

class GetPortalCustomerDashboardHandler
{
    public function handle(GetPortalCustomerDashboardQuery $query): PortalDashboardData
    {
        $portalUser = CustomerPortalUser::with(['customer.invoices', 'customer.quotes', 'customer.salesOrders', 'customer.primaryContact'])
            ->findOrFail($query->portalUserId);

        $customer = $portalUser->customer;
        $currency = $customer->currency ?: 'CHF';

        $totalInvoiced = MoneyValue::zero($currency);
        $totalOutstanding = MoneyValue::zero($currency);

        foreach ($customer->invoices as $invoice) {
            if ($invoice->status !== 'cancelled') {
                $totalInvoiced = $totalInvoiced->add(MoneyValue::of($invoice->total ?? '0.00', $currency));
                $totalOutstanding = $totalOutstanding->add(MoneyValue::of($invoice->outstanding_amount ?? '0.00', $currency));
            }
        }

        $recentInvoices = $customer->invoices()
            ->latest('invoice_date')
            ->take(10)
            ->get()
            ->toArray();

        $recentQuotes = $customer->quotes()
            ->latest()
            ->take(5)
            ->get()
            ->toArray();

        $recentOrders = $customer->salesOrders()
            ->latest()
            ->take(5)
            ->get()
            ->toArray();

        $primaryContact = $customer->primaryContact ? $customer->primaryContact->toArray() : null;

        return new PortalDashboardData(
            customer: $customer->toArray(),
            primaryContact: $primaryContact,
            totalInvoiced: $totalInvoiced->getAmount(),
            totalOutstanding: $totalOutstanding->getAmount(),
            currency: $currency,
            recentInvoices: $recentInvoices,
            recentQuotes: $recentQuotes,
            recentOrders: $recentOrders,
        );
    }
}

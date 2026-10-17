@php
    $theme = request()->cookie('theme', 'light');
@endphp

<!DOCTYPE html>
<html lang="it" class="{{ $theme === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Invoice') }} {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #333; line-height: 1.4; }
        .header { margin-bottom: 30px; }
        .company-info { float: left; width: 50%; }
        .customer-info { float: right; width: 45%; }
        .clear { clear: both; }
        .title { font-size: 24px; font-weight: bold; margin-top: 20px; color: #10b981; }
        .meta-table { width: 100%; margin-top: 15px; margin-bottom: 25px; border-collapse: collapse; }
        .meta-table td { padding: 4px; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .items-table th { background-color: #f3f4f6; border-bottom: 2px solid #e5e7eb; padding: 8px; text-align: left; }
        .items-table td { padding: 8px; border-bottom: 1px solid #f3f4f6; }
        .text-right { text-align: right; }
        .totals-table { width: 40%; float: right; margin-top: 20px; border-collapse: collapse; }
        .totals-table td { padding: 6px; }
        .grand-total { font-size: 16px; font-weight: bold; border-top: 2px solid #10b981; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }
        .swiss-qr-note { margin-top: 30px; padding: 10px; background-color: #f9fafb; border-left: 4px solid #10b981; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <h2>{{ $company->name }}</h2>
            <p>
                {{ $company->street }}<br>
                {{ $company->postal_code }} {{ $company->city }}<br>
                {{ $company->country }}<br>
                @if($company->tax_number) UID: {{ $company->tax_number }}<br> @endif
                @if($company->vat_number) VAT: {{ $company->vat_number }}<br> @endif
                @if($company->email) Email: {{ $company->email }} @endif
            </p>
        </div>
        <div class="customer-info">
            <strong>{{ __('Billed To:') }}</strong>
            <p>
                <strong>{{ $customer->name }}</strong><br>
                @if($customer->legal_name && $customer->legal_name !== $customer->name) {{ $customer->legal_name }}<br> @endif
                {{ $customer->street }}<br>
                {{ $customer->postal_code }} {{ $customer->city }}<br>
                {{ $customer->country }}<br>
                @if($customer->vat_number) VAT: {{ $customer->vat_number }} @endif
            </p>
        </div>
        <div class="clear"></div>
    </div>

    <div class="title">{{ __('INVOICE') }} {{ $invoice->invoice_number }}</div>

    <table class="meta-table">
        <tr>
            <td><strong>{{ __('Invoice Date:') }}</strong> {{ $invoice->invoice_date->format('d.m.Y') }}</td>
            <td><strong>{{ __('Due Date:') }}</strong> {{ $invoice->due_date->format('d.m.Y') }}</td>
            <td><strong>{{ __('Currency:') }}</strong> {{ $invoice->currency }}</td>
            <td><strong>{{ __('Status:') }}</strong> {{ strtoupper($invoice->status) }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>{{ __('Description') }}</th>
                <th class="text-right">{{ __('Qty') }}</th>
                <th class="text-right">{{ __('Unit Price') }}</th>
                <th class="text-right">{{ __('VAT %') }}</th>
                <th class="text-right">{{ __('Total') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="text-right">{{ number_format((float) $item->quantity, 2) }}</td>
                <td class="text-right">{{ number_format((float) $item->unit_price, 2) }}</td>
                <td class="text-right">{{ number_format((float) $item->tax_rate, 1) }}%</td>
                <td class="text-right">{{ number_format((float) $item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td>{{ __('Subtotal:') }}</td>
            <td class="text-right">{{ number_format((float) $invoice->subtotal, 2) }} {{ $invoice->currency }}</td>
        </tr>
        <tr>
            <td>{{ __('VAT Total:') }}</td>
            <td class="text-right">{{ number_format((float) $invoice->tax_total, 2) }} {{ $invoice->currency }}</td>
        </tr>
        <tr class="grand-total">
            <td><strong>{{ __('Total:') }}</strong></td>
            <td class="text-right"><strong>{{ number_format((float) $invoice->total, 2) }} {{ $invoice->currency }}</strong></td>
        </tr>
    </table>

    <div class="clear"></div>

    @if($invoice->notes)
        <div style="margin-top: 20px;">
            <strong>{{ __('Notes:') }}</strong>
            <p>{{ $invoice->notes }}</p>
        </div>
    @endif

    <div class="swiss-qr-note">
        <strong>{{ __('Payment Information:') }}</strong><br>
        {{ __('Please transfer the total amount of :total :currency within :days days mentioning reference:', ['total' => number_format((float) $invoice->total, 2), 'currency' => $invoice->currency, 'days' => $customer->payment_term_days ?: 30]) }} <strong>{{ $invoice->invoice_number }}</strong>.
    </div>

    <div class="footer">
        {{ $company->name }} &bull; {{ $company->city }}, {{ $company->country }} &bull; Fattura Facile ERP (https://fatturafacile.eu)
    </div>
</body>
</html>

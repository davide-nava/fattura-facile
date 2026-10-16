@extends('portal.layout')

@section('title', 'Client Portal Dashboard')

@section('content')
<div class="space-y-8">
    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Welcome, {{ $portalUser->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">Customer Account: <span class="font-semibold text-gray-700">{{ $data->customer['name'] }}</span> ({{ $data->customer['code'] ?? 'N/A' }})</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <a href="{{ route('portal.invoices') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition">View Invoices</a>
            <a href="{{ route('portal.quotes') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition">View Quotes</a>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Invoiced</div>
            <div class="text-2xl font-bold text-gray-900 mt-2">{{ $data->currency }} {{ number_format((float) $data->totalInvoiced, 2) }}</div>
            <div class="text-xs text-gray-500 mt-1">All historical invoices</div>
        </div>
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Outstanding Balance</div>
            <div class="text-2xl font-bold {{ bccomp($data->totalOutstanding, '0.00', 2) > 0 ? 'text-red-600' : 'text-emerald-600' }} mt-2">
                {{ $data->currency }} {{ number_format((float) $data->totalOutstanding, 2) }}
            </div>
            <div class="text-xs text-gray-500 mt-1">Due for settlement</div>
        </div>
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Payment Terms</div>
            <div class="text-2xl font-bold text-gray-900 mt-2">{{ $data->customer['payment_term_days'] ?? 30 }} Days</div>
            <div class="text-xs text-gray-500 mt-1">Standard invoice payment term</div>
        </div>
    </div>

    <!-- Recent Invoices Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-900">Recent Invoices</h2>
            <a href="{{ route('portal.invoices') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-800">View All &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Number</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Outstanding</th>
                        <th class="px-6 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($data->recentInvoices as $invoice)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $invoice['invoice_number'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $invoice['invoice_date'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium 
                                @if($invoice['status'] === 'paid') bg-emerald-100 text-emerald-800
                                @elseif($invoice['status'] === 'issued') bg-blue-100 text-blue-800
                                @elseif($invoice['status'] === 'partially_paid') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $invoice['status'])) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-gray-900">{{ $invoice['currency'] }} {{ number_format((float) $invoice['total'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-gray-700">{{ $invoice['currency'] }} {{ number_format((float) ($invoice['outstanding_amount'] ?? '0.00'), 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <a href="{{ route('portal.invoices.download', $invoice['id']) }}" class="text-emerald-600 hover:text-emerald-800 font-medium">Download PDF</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">No invoices found for your account.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@extends('portal.layout')

@section('title', __('Client Portal Dashboard'))

@section('content')
<div class="space-y-8">
    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center transition-colors duration-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ __('Welcome, :name', ['name' => $portalUser->name]) }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('Customer Account:') }} <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $data->customer['name'] }}</span> ({{ $data->customer['code'] ?? 'N/A' }})</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <a href="{{ route('portal.invoices') }}" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-sm font-semibold shadow-sm transition-colors">{{ __('View Invoices') }}</a>
            <a href="{{ route('portal.quotes') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold transition-colors">{{ __('View Quotes') }}</a>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm transition-colors duration-200">
            <div class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">{{ __('Total Invoiced') }}</div>
            <div class="text-2xl font-black text-slate-900 dark:text-white mt-2">{{ $data->currency }} {{ number_format((float) $data->totalInvoiced, 2) }}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('All historical invoices') }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm transition-colors duration-200">
            <div class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">{{ __('Outstanding Balance') }}</div>
            <div class="text-2xl font-black {{ bccomp($data->totalOutstanding, '0.00', 2) > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }} mt-2">
                {{ $data->currency }} {{ number_format((float) $data->totalOutstanding, 2) }}
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Due for settlement') }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm transition-colors duration-200">
            <div class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">{{ __('Payment Terms') }}</div>
            <div class="text-2xl font-black text-slate-900 dark:text-white mt-2">{{ $data->customer['payment_term_days'] ?? 30 }} {{ __('Days') }}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Standard invoice payment term') }}</div>
        </div>
    </div>

    <!-- Recent Invoices Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden transition-colors duration-200">
        <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('Recent Invoices') }}</h2>
            <a href="{{ route('portal.invoices') }}" class="text-sm font-semibold text-teal-600 dark:text-teal-400 hover:text-teal-700 dark:hover:text-teal-300 transition-colors">{{ __('View All') }} &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-xs">{{ __('Number') }}</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-xs">{{ __('Date') }}</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-xs">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-right font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-xs">{{ __('Total') }}</th>
                        <th class="px-6 py-3 text-right font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-xs">{{ __('Outstanding') }}</th>
                        <th class="px-6 py-3 text-right font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-xs">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                    @forelse($data->recentInvoices as $invoice)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap font-semibold text-slate-900 dark:text-white">{{ $invoice['invoice_number'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-500 dark:text-slate-400">{{ $invoice['invoice_date'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                @if($invoice['status'] === 'paid') bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60
                                @elseif($invoice['status'] === 'issued') bg-blue-50 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60
                                @elseif($invoice['status'] === 'partially_paid') bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60
                                @else bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 @endif">
                                {{ ucfirst(str_replace('_', ' ', $invoice['status'])) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-semibold text-slate-900 dark:text-white">{{ $invoice['currency'] }} {{ number_format((float) $invoice['total'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-semibold text-slate-700 dark:text-slate-300">{{ $invoice['currency'] }} {{ number_format((float) ($invoice['outstanding_amount'] ?? '0.00'), 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <a href="{{ route('portal.invoices.download', $invoice['id']) }}" class="text-teal-600 dark:text-teal-400 hover:text-teal-700 dark:hover:text-teal-300 font-semibold transition-colors">{{ __('Download PDF') }}</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-400 dark:text-slate-500">{{ __('No invoices found for your account.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

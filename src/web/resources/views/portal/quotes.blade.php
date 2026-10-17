@extends('portal.layout')

@section('title', __('Your Quotes'))

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ __('Quotes & Proposals') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('Review your commercial offers and quotes') }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden transition-colors duration-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-xs">{{ __('Quote #') }}</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-xs">{{ __('Date') }}</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-xs">{{ __('Valid Until') }}</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-xs">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-right font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-xs">{{ __('Total Amount') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                    @forelse($quotes as $quote)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap font-semibold text-slate-900 dark:text-white">{{ $quote->quote_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-500 dark:text-slate-400">{{ $quote->quote_date }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-500 dark:text-slate-400">{{ $quote->valid_until ?? __('N/A') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                @if($quote->status === 'accepted') bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60
                                @elseif($quote->status === 'sent') bg-blue-50 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60
                                @elseif($quote->status === 'rejected') bg-red-50 dark:bg-red-950/60 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-800/60
                                @else bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 @endif">
                                {{ __(ucfirst($quote->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-semibold text-slate-900 dark:text-white">{{ $quote->currency }} {{ number_format((float) $quote->total, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-400 dark:text-slate-500">{{ __('No quotes available.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($quotes->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $quotes->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

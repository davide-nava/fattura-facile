@extends('portal.layout')

@section('title', __('Your Quotes'))

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('Quotes & Proposals') }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ __('Review your commercial offers and quotes') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('Quote #') }}</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('Date') }}</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('Valid Until') }}</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">{{ __('Total Amount') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($quotes as $quote)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-900">{{ $quote->quote_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $quote->quote_date }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $quote->valid_until ?? __('N/A') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium 
                                @if($quote->status === 'accepted') bg-emerald-100 text-emerald-800
                                @elseif($quote->status === 'sent') bg-blue-100 text-blue-800
                                @elseif($quote->status === 'rejected') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ __(ucfirst($quote->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-gray-900">{{ $quote->currency }} {{ number_format((float) $quote->total, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">{{ __('No quotes available.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($quotes->hasPages())
        <div class="p-4 border-t border-gray-200">
            {{ $quotes->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

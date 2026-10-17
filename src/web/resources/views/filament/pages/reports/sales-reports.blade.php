<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Sales Performance KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Total Billed Revenue') }}</span>
                    <span class="p-2 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400">
                        <i class="fa-solid fa-file-invoice-dollar text-sm"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white">CHF 217,700.00</div>
                <div class="text-xs font-medium text-emerald-600 dark:text-emerald-400 mt-2">
                    +18.4% {{ __('vs previous 6-month period') }}
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Average Deal Size') }}</span>
                    <span class="p-2 rounded-xl bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400">
                        <i class="fa-solid fa-chart-pie text-sm"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white">CHF 2,366.30</div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">
                    {{ __('92 total orders & invoices') }}
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Collection Rate') }}</span>
                    <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">
                        <i class="fa-solid fa-circle-check text-sm"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">96.8%</div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">
                    {{ __('Swiss QR-Bill direct settlement') }}
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Quote Conversion') }}</span>
                    <span class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                        <i class="fa-solid fa-bullseye text-sm"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white">74.2%</div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">
                    {{ __('From accepted quote to paid invoice') }}
                </div>
            </div>
        </div>

        <!-- Monthly Trends Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Revenue by Period (CHF)') }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Monthly performance and invoice count breakdown') }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-start text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-start">{{ __('Period') }}</th>
                            <th class="px-6 py-3 text-end">{{ __('Turnover (CHF)') }}</th>
                            <th class="px-6 py-3 text-center">{{ __('Invoices Issued') }}</th>
                            <th class="px-6 py-3 text-end">{{ __('Growth vs Prior') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                        @foreach ($this->getMonthlyRevenue() as $rev)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4 text-slate-900 dark:text-white font-bold">
                                    {{ $rev['month'] }}
                                </td>
                                <td class="px-6 py-4 text-end font-mono font-bold text-slate-900 dark:text-white">
                                    CHF {{ $rev['revenue'] }}
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-slate-700 dark:text-slate-300">
                                    {{ $rev['invoices'] }}
                                </td>
                                <td class="px-6 py-4 text-end font-mono font-semibold {{ str_starts_with($rev['growth'], '+') ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500' }}">
                                    {{ $rev['growth'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Clients Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Top Clients by Volume') }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Largest sales accounts and payment reliability metric') }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-start text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-start">{{ __('Customer Account') }}</th>
                            <th class="px-6 py-3 text-center">{{ __('Canton') }}</th>
                            <th class="px-6 py-3 text-center">{{ __('Invoices') }}</th>
                            <th class="px-6 py-3 text-end">{{ __('Total Volume') }}</th>
                            <th class="px-6 py-3 text-center">{{ __('Payment Track Record') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                        @foreach ($this->getTopClients() as $client)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                                    {{ $client['customer'] }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ $client['canton'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center font-mono">
                                    {{ $client['invoices_count'] }}
                                </td>
                                <td class="px-6 py-4 text-end font-mono font-black text-slate-900 dark:text-white">
                                    {{ $client['currency'] }} {{ $client['total_spend'] }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i>
                                        {{ $client['payment_performance'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>

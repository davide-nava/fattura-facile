<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Financial Summary KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Total Assets (Aktiven)') }}</span>
                    <span class="p-2 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400">
                        <i class="fa-solid fa-vault text-sm"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white">CHF 240,780.40</div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">
                    {{ __('Liquidity, receivables & inventory') }}
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Total Liabilities (Fremdkapital)') }}</span>
                    <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400">
                        <i class="fa-solid fa-file-invoice-dollar text-sm"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white">CHF 36,058.25</div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">
                    {{ __('Trade payables & VAT obligations') }}
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Net Revenue YTD') }}</span>
                    <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">
                        <i class="fa-solid fa-arrow-trend-up text-sm"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">CHF 134,800.00</div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">
                    {{ __('Operating turnover (CHF)') }}
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Operating Profit (EBIT)') }}</span>
                    <span class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                        <i class="fa-solid fa-chart-line text-sm"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">CHF 31,000.00</div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">
                    23.0% {{ __('profit margin') }}
                </div>
            </div>
        </div>

        <!-- Trial Balance (Bilancio di Verifica) Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Swiss KMU Trial Balance (Bilancio di Verifica)') }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Compliant with Swiss Code of Obligations (Art. 957-963 CO)') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors">
                        <i class="fa-solid fa-file-pdf text-xs text-rose-500"></i>
                        <span>{{ __('Export PDF') }}</span>
                    </button>
                    <button type="button" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors">
                        <i class="fa-solid fa-file-excel text-xs text-emerald-500"></i>
                        <span>{{ __('Export Excel') }}</span>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-start text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-start">{{ __('Code') }}</th>
                            <th class="px-6 py-3 text-start">{{ __('Account Name') }}</th>
                            <th class="px-6 py-3 text-center">{{ __('Classification') }}</th>
                            <th class="px-6 py-3 text-end">{{ __('Debit Total (CHF)') }}</th>
                            <th class="px-6 py-3 text-end">{{ __('Credit Total (CHF)') }}</th>
                            <th class="px-6 py-3 text-end">{{ __('Net Balance (CHF)') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                        @foreach ($this->getTrialBalance() as $acc)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4 font-mono font-bold text-slate-900 dark:text-white">
                                    {{ $acc['code'] }}
                                </td>
                                <td class="px-6 py-4 text-slate-800 dark:text-slate-200">
                                    {{ $acc['name'] }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider
                                        {{ $acc['type'] === 'asset' ? 'bg-cyan-50 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-300' : '' }}
                                        {{ $acc['type'] === 'liability' ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300' : '' }}
                                        {{ $acc['type'] === 'equity' ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300' : '' }}
                                        {{ $acc['type'] === 'revenue' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300' : '' }}
                                        {{ $acc['type'] === 'expense' ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300' : '' }}
                                    ">
                                        {{ $acc['type'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-end font-mono text-slate-700 dark:text-slate-300">
                                    {{ $acc['debit'] !== '0.00' ? $acc['debit'] : '-' }}
                                </td>
                                <td class="px-6 py-4 text-end font-mono text-slate-700 dark:text-slate-300">
                                    {{ $acc['credit'] !== '0.00' ? $acc['credit'] : '-' }}
                                </td>
                                <td class="px-6 py-4 text-end font-mono font-bold text-slate-900 dark:text-white">
                                    {{ $acc['balance'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 dark:bg-slate-800/80 font-bold text-xs uppercase tracking-wider text-slate-900 dark:text-white border-t-2 border-slate-200 dark:border-slate-700">
                        <tr>
                            <td colspan="3" class="px-6 py-3">{{ __('Equilibrium Total') }}</td>
                            <td class="px-6 py-3 text-end font-mono">CHF 296,080.40</td>
                            <td class="px-6 py-3 text-end font-mono">CHF 296,080.40</td>
                            <td class="px-6 py-3 text-end font-mono text-emerald-600 dark:text-emerald-400">&Delta; 0.00 ({{ __('Balanced') }})</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>

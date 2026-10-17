<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Top Summary Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Total Bank Balances') }}</span>
                    <span class="p-2 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400">
                        <i class="fa-solid fa-building-columns text-sm"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white">CHF 228,000.40</div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">
                    {{ __('Across 3 active banking accounts') }}
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Reconciliation Status') }}</span>
                    <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">
                        <i class="fa-solid fa-check-double text-sm"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">99.4%</div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">
                    {{ __('1 transaction requiring review') }}
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Unreconciled Variance') }}</span>
                    <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400">
                        <i class="fa-solid fa-scale-unbalanced text-sm"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-amber-600 dark:text-amber-400">CHF 1,300.00</div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">
                    {{ __('CAMT.054 customer statement match pending') }}
                </div>
            </div>
        </div>

        <!-- Bank Accounts Section -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Configured Financial Accounts') }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Connected via EBICS & ISO 20022 Swiss Banking Standard') }}</p>
                </div>
                <button type="button" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-white bg-teal-600 hover:bg-teal-700 rounded-xl transition-colors">
                    <i class="fa-solid fa-file-arrow-up text-xs"></i>
                    <span>{{ __('Import CAMT.053') }}</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-start text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-start">{{ __('Bank / Entity') }}</th>
                            <th class="px-6 py-3 text-start">{{ __('IBAN') }}</th>
                            <th class="px-6 py-3 text-end">{{ __('Bank Balance') }}</th>
                            <th class="px-6 py-3 text-end">{{ __('Ledger Balance') }}</th>
                            <th class="px-6 py-3 text-end">{{ __('Difference') }}</th>
                            <th class="px-6 py-3 text-center">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                        @foreach ($this->getBankAccounts() as $account)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4 text-slate-900 dark:text-white font-bold">
                                    {{ $account['name'] }}
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-600 dark:text-slate-300">
                                    {{ $account['iban'] }}
                                </td>
                                <td class="px-6 py-4 text-end text-slate-900 dark:text-white font-semibold">
                                    {{ $account['currency'] }} {{ $account['bank_balance'] }}
                                </td>
                                <td class="px-6 py-4 text-end text-slate-600 dark:text-slate-400">
                                    {{ $account['currency'] }} {{ $account['ledger_balance'] }}
                                </td>
                                <td class="px-6 py-4 text-end {{ $account['difference'] !== '0.00' ? 'text-amber-600 dark:text-amber-400 font-bold' : 'text-slate-500' }}">
                                    {{ $account['currency'] }} {{ $account['difference'] }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($account['status'] === 'balanced')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            <i class="fa-solid fa-check text-[10px]"></i>
                                            {{ __('Reconciled') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                            <i class="fa-solid fa-clock text-[10px]"></i>
                                            {{ __('Pending Review') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Transactions Log -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Bank Feed Entries') }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Automated Matching against General Ledger Journal Entries') }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-start text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-start">{{ __('Date') }}</th>
                            <th class="px-6 py-3 text-start">{{ __('Description & Notes') }}</th>
                            <th class="px-6 py-3 text-start">{{ __('Bank Reference') }}</th>
                            <th class="px-6 py-3 text-end">{{ __('Amount (CHF)') }}</th>
                            <th class="px-6 py-3 text-center">{{ __('Matched Ledger Entry') }}</th>
                            <th class="px-6 py-3 text-center">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                        @foreach ($this->getRecentTransactions() as $tx)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4 text-xs text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                    {{ $tx['date'] }}
                                </td>
                                <td class="px-6 py-4 text-slate-900 dark:text-white max-w-sm truncate font-semibold">
                                    {{ $tx['description'] }}
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-500 dark:text-slate-400">
                                    {{ $tx['reference'] }}
                                </td>
                                <td class="px-6 py-4 text-end font-mono font-bold whitespace-nowrap {{ str_starts_with($tx['amount'], '+') ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-900 dark:text-white' }}">
                                    {{ $tx['amount'] }}
                                </td>
                                <td class="px-6 py-4 text-center text-xs font-mono">
                                    @if ($tx['matched_entry'])
                                        <span class="text-teal-600 dark:text-teal-400 font-bold hover:underline cursor-pointer">
                                            {{ $tx['matched_entry'] }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic">{{ __('None') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($tx['status'] === 'matched')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">
                                            {{ __('Matched') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300">
                                            {{ __('Unmatched') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>

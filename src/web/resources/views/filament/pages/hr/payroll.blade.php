<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Payroll Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Total Gross Salaries') }}</span>
                    <span class="p-2 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400">
                        <i class="fa-solid fa-coins text-sm"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white">CHF 35,200.00</div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">
                    {{ __('Period 2026-03 (Current Month)') }}
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Social Deductions (1st Pillar)') }}</span>
                    <span class="p-2 rounded-xl bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400">
                        <i class="fa-solid fa-landmark text-sm"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white">CHF 2,252.80</div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">
                    AHV / IV / EO / ALV (6.4%)
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Occupational Pension (2nd Pillar)') }}</span>
                    <span class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                        <i class="fa-solid fa-piggy-bank text-sm"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white">CHF 1,760.00</div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">
                    BVG / LPP {{ __('Insured contributions') }}
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Total Net Payout') }}</span>
                    <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">
                        <i class="fa-solid fa-money-bill-transfer text-sm"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">CHF 31,007.20</div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">
                    {{ __('Disbursement scheduled via ISO 20022') }}
                </div>
            </div>
        </div>

        <!-- Payroll Runs Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Monthly Salary Slips') }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Detailed wage calculations according to Swiss Federal Regulations') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-white bg-teal-600 hover:bg-teal-700 rounded-xl transition-colors">
                        <i class="fa-solid fa-calculator text-xs"></i>
                        <span>{{ __('Run Payroll') }}</span>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-start text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-start">{{ __('Employee') }}</th>
                            <th class="px-6 py-3 text-center">{{ __('Period') }}</th>
                            <th class="px-6 py-3 text-end">{{ __('Gross (CHF)') }}</th>
                            <th class="px-6 py-3 text-end">{{ __('AHV/ALV (CHF)') }}</th>
                            <th class="px-6 py-3 text-end">{{ __('BVG 2nd Pillar') }}</th>
                            <th class="px-6 py-3 text-end">{{ __('Net Pay (CHF)') }}</th>
                            <th class="px-6 py-3 text-center">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                        @foreach ($this->getPayrollRuns() as $run)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4 text-slate-900 dark:text-white font-bold">
                                    {{ $run['employee'] }}
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-xs text-slate-500">
                                    {{ $run['period'] }}
                                </td>
                                <td class="px-6 py-4 text-end font-mono font-semibold text-slate-900 dark:text-white">
                                    {{ $run['gross'] }}
                                </td>
                                <td class="px-6 py-4 text-end font-mono text-xs text-slate-500">
                                    -{{ $run['ahv_alv'] }}
                                </td>
                                <td class="px-6 py-4 text-end font-mono text-xs text-slate-500">
                                    -{{ $run['bvg'] }}
                                </td>
                                <td class="px-6 py-4 text-end font-mono font-black text-emerald-600 dark:text-emerald-400">
                                    {{ $run['net'] }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($run['status'] === 'approved')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">
                                            <i class="fa-solid fa-check text-[10px]"></i>
                                            {{ __('Approved') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300">
                                            <i class="fa-solid fa-pen-ruler text-[10px]"></i>
                                            {{ __('Draft') }}
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

<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Top HR KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Total Headcount') }}</span>
                    <span class="p-2 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400">
                        <i class="fa-solid fa-users text-sm"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white">5 {{ __('Employees') }}</div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">
                    {{ __('4.3 FTE total capacity') }}
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Active Cantons') }}</span>
                    <span class="p-2 rounded-xl bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400">
                        <i class="fa-solid fa-map-location-dot text-sm"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white">5 {{ __('Cantons') }}</div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">
                    ZH, GE, TI, BE, VD ({{ __('Swiss Coverage') }})
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Social Compliance') }}</span>
                    <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">
                        <i class="fa-solid fa-shield-check text-sm"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">100%</div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">
                    {{ __('AHV/IV/ALV/BVG registered') }}
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Monthly Payroll') }}</span>
                    <span class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                        <i class="fa-solid fa-money-check-dollar text-sm"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white">CHF 38,400.00</div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">
                    {{ __('Gross base compensation') }}
                </div>
            </div>
        </div>

        <!-- Employees Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Employee Directory') }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Swiss Employment Contracts & Canton Tax Registrations') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-white bg-teal-600 hover:bg-teal-700 rounded-xl transition-colors">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>{{ __('Add Employee') }}</span>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-start text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-start">{{ __('Employee') }}</th>
                            <th class="px-6 py-3 text-start">{{ __('Role & Department') }}</th>
                            <th class="px-6 py-3 text-center">{{ __('Canton') }}</th>
                            <th class="px-6 py-3 text-start">{{ __('Contract Type') }}</th>
                            <th class="px-6 py-3 text-start">{{ __('Start Date') }}</th>
                            <th class="px-6 py-3 text-center">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                        @foreach ($this->getEmployees() as $emp)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $emp['name'] }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ $emp['email'] }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-slate-800 dark:text-slate-200 font-semibold">{{ $emp['role'] }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $emp['department'] }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ $emp['canton'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600 dark:text-slate-300">
                                    {{ $emp['contract'] }}
                                </td>
                                <td class="px-6 py-4 text-xs font-mono text-slate-500">
                                    {{ $emp['start_date'] }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">
                                        <i class="fa-solid fa-circle text-[6px]"></i>
                                        {{ __('Active') }}
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

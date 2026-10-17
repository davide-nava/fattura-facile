<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Attendance Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Present Today') }}</span>
                    <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">
                        <i class="fa-solid fa-user-check text-sm"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">4 / 5</div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">
                    80% {{ __('on duty') }}
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('On Vacation') }}</span>
                    <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400">
                        <i class="fa-solid fa-plane-departure text-sm"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-amber-600 dark:text-amber-400">1 {{ __('Staff') }}</div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">
                    Stefan Künzler ({{ __('Approved Leave') }})
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Logged Hours Today') }}</span>
                    <span class="p-2 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400">
                        <i class="fa-solid fa-stopwatch text-sm"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white">28.75 {{ __('hrs') }}</div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">
                    {{ __('Compliant with Swiss Labour Law (ArG)') }}
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Overtime Balance') }}</span>
                    <span class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                        <i class="fa-solid fa-clock-rotate-left text-sm"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white">+4.50 {{ __('hrs') }}</div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">
                    {{ __('Cumulative department credit') }}
                </div>
            </div>
        </div>

        <!-- Daily Timesheet Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Daily Attendance Records') }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Time logging and project activity audit trail') }}</p>
                </div>
                <button type="button" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-white bg-teal-600 hover:bg-teal-700 rounded-xl transition-colors">
                    <i class="fa-solid fa-fingerprint text-xs"></i>
                    <span>{{ __('Clock In / Out') }}</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-start text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-start">{{ __('Employee') }}</th>
                            <th class="px-6 py-3 text-start">{{ __('Date') }}</th>
                            <th class="px-6 py-3 text-center">{{ __('Check In') }}</th>
                            <th class="px-6 py-3 text-center">{{ __('Check Out') }}</th>
                            <th class="px-6 py-3 text-end">{{ __('Total Hours') }}</th>
                            <th class="px-6 py-3 text-start">{{ __('Project / Cost Center') }}</th>
                            <th class="px-6 py-3 text-center">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                        @foreach ($this->getAttendanceRecords() as $rec)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4 text-slate-900 dark:text-white font-bold">
                                    {{ $rec['employee'] }}
                                </td>
                                <td class="px-6 py-4 text-xs font-mono text-slate-500">
                                    {{ $rec['date'] }}
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-xs text-slate-700 dark:text-slate-300">
                                    {{ $rec['check_in'] }}
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-xs text-slate-700 dark:text-slate-300">
                                    {{ $rec['check_out'] }}
                                </td>
                                <td class="px-6 py-4 text-end font-mono font-bold text-slate-900 dark:text-white">
                                    {{ $rec['hours'] }}
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600 dark:text-slate-300 font-medium">
                                    {{ $rec['project'] }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($rec['type'] === 'present')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">
                                            <i class="fa-solid fa-check text-[10px]"></i>
                                            {{ __('Present') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300">
                                            <i class="fa-solid fa-umbrella-beach text-[10px]"></i>
                                            {{ __('Vacation') }}
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

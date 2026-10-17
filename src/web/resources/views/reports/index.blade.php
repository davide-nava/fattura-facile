<x-layouts::app :title="__('Reports & Printing')">
    <div class="flex flex-col gap-8">
        <!-- Header Banner -->
        <div class="p-6 sm:p-8 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6 transition-colors duration-200">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-teal-50 dark:bg-teal-950/60 text-teal-800 dark:text-teal-300 border border-teal-200 dark:border-teal-800/60">
                        <span class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></span>
                        {{ __('Reporting Infrastructure Active') }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700">
                        Driver: {{ strtoupper(config('pdf.default', 'browsershot')) }}
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    {{ __('Reports & Document Printing') }}
                </h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm max-w-2xl">
                    {{ __('Generate, preview, stream and download pixel-perfect business intelligence reports and statutory documents tailored with your tenant dynamic branding.') }}
                </p>
            </div>

            @if($company)
            <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs space-y-1">
                <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full" style="background-color: {{ $branding->primaryColor }};"></span>
                    <span>{{ $company->name }}</span>
                </div>
                <div class="text-slate-500 dark:text-slate-400">Currency: <strong>{{ $company->currency ?: 'CHF' }}</strong></div>
                <div class="text-slate-500 dark:text-slate-400">UID: {{ $company->tax_number ?: 'Not configured' }}</div>
            </div>
            @endif
        </div>

        <!-- Available Reports Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($reports as $report)
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 flex flex-col justify-between shadow-xs hover:border-teal-500/50 hover:shadow-md transition-all">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800/60">
                                {{ $report->category() }}
                            </span>
                            <span class="text-[11px] text-slate-400 dark:text-slate-500 font-mono">
                                {{ strtoupper($report->paperSize()) }} &bull; {{ ucfirst($report->paperOrientation()) }}
                            </span>
                        </div>

                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">
                            {{ $report->name() }}
                        </h3>

                        <p class="text-slate-500 dark:text-slate-400 text-xs leading-relaxed mb-6">
                            {{ $report->description() }}
                        </p>
                    </div>

                    <div class="border-t border-slate-100 dark:border-slate-800 pt-4 flex items-center justify-between gap-2">
                        <a href="{{ route('reports.preview', ['report' => $report->key()]) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>{{ __('Preview') }}</span>
                        </a>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('reports.stream', ['report' => $report->key()]) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-teal-700 dark:text-teal-300 bg-teal-50 dark:bg-teal-950/60 hover:bg-teal-100 dark:hover:bg-teal-900/60 border border-teal-200 dark:border-teal-800/60 transition"
                               title="{{ __('Stream PDF in new tab') }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                <span>{{ __('Stream') }}</span>
                            </a>

                            <a href="{{ route('reports.download', ['report' => $report->key()]) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-white bg-slate-900 dark:bg-teal-600 hover:bg-slate-800 dark:hover:bg-teal-500 shadow-xs transition"
                               title="{{ __('Download PDF file') }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>{{ __('PDF') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts::app>

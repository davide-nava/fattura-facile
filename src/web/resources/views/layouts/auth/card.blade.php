@php
    $theme = request()->cookie('theme', 'light');
@endphp

    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $theme === 'dark' ? 'dark' : '' }}">
<head>
    @include('partials.head')
</head>
<body
    class="min-h-screen bg-gradient-to-b from-slate-100/70 via-white to-slate-50 dark:from-slate-900 dark:via-slate-950 dark:to-slate-900 text-slate-800 dark:text-slate-100 antialiased font-sans selection:bg-teal-500 selection:text-white flex flex-col justify-between transition-colors duration-200">
<!-- Top Branding Header -->
<header
    class="w-full py-6 px-4 sm:px-6 lg:px-8 border-b border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md transition-colors duration-200">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        <a href="{{ url('/') }}" class="flex items-center gap-3 group" wire:navigate>
            <x-app-logo-icon class="h-10 w-10 shrink-0 transition-transform duration-300 group-hover:scale-105"/>
            <div class="flex flex-col">
                        <span
                            class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-none">
                            Fattura<span class="text-teal-600 dark:text-teal-400">Facile</span>
                        </span>
                <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 mt-1"
                      data-slogan="Fatture semplici, offline e cloud">{{ __('Simple invoices, offline and cloud') }}</span>
            </div>
        </a>

        <div class="flex items-center gap-2 sm:gap-3">
            <x-language-switcher />
            <x-theme-toggle/>
        </div>
    </div>
</header>

<!-- Centered Card Container -->
<main class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8">
    <div class="w-full max-w-md">
        <div
            class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-xl overflow-hidden transition-all duration-200">
            <!-- Brand Gradient Highlight Bar -->
            <div class="h-1.5 w-full bg-gradient-to-r from-teal-500 via-teal-600 to-emerald-500"></div>

            <!-- Inner Form Content -->
            <div class="px-7 py-8 sm:px-10 sm:py-9">
                {{ $slot }}
            </div>
        </div>

        <!-- Assistance / Privacy note below card -->
        <p class="text-center text-xs text-slate-500 dark:text-slate-400 mt-6">
            {{ __('Need help? Contact') }} <a href="mailto:info@fatturafacile.eu"
                                              class="font-semibold text-teal-600 dark:text-teal-400 hover:text-teal-700 dark:hover:text-teal-300 underline">info@fatturafacile.eu</a>
        </p>
        <p class="text-center text-xs text-slate-500 dark:text-slate-400 mt-4">
            <span>&copy; {{ date('Y') }} Fattura Facile.</span>
        </p>
    </div>
</main>

{{--        <!-- Mini Footer -->--}}
{{--        <footer class="w-full py-5 px-4 text-center text-xs text-slate-500 border-t border-slate-200/80 bg-white/70">--}}
{{--            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">--}}
{{--                <span>&copy; {{ date('Y') }} Fattura Facile.</span>--}}
{{--                <div class="flex items-center gap-3">--}}
{{--                    <a href="https://fatturafacile.eu" target="_blank" rel="noopener noreferrer" class="hover:text-teal-600 transition-colors">fatturafacile.eu</a>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </footer>--}}

@persist('toast')
<flux:toast.group>
    <flux:toast/>
</flux:toast.group>
@endpersist

@fluxScripts
</body>
</html>

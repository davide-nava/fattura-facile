<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-gradient-to-b from-slate-100/70 via-white to-slate-50 text-slate-800 antialiased font-sans selection:bg-teal-500 selection:text-white flex flex-col justify-between">
{{--        <!-- Top Branding Header -->--}}
{{--        <header class="w-full py-6 px-4 sm:px-6 lg:px-8 border-b border-slate-200/70 bg-white/80 backdrop-blur-md">--}}
{{--            <div class="max-w-7xl mx-auto flex items-center justify-between">--}}
{{--                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group" wire:navigate>--}}
{{--                    <x-app-logo-icon class="h-10 w-10 shrink-0 transition-transform duration-300 group-hover:scale-105" />--}}
{{--                    <div class="flex flex-col">--}}
{{--                        <span class="text-2xl font-extrabold tracking-tight text-slate-900 leading-none">--}}
{{--                            Fattura<span class="text-teal-600">Facile</span>--}}
{{--                        </span>--}}
{{--                        <span class="text-[11px] font-semibold text-slate-500 mt-1">Fatture semplici, offline e cloud</span>--}}
{{--                    </div>--}}
{{--                </a>--}}

{{--                <div class="hidden sm:flex items-center gap-2">--}}
{{--                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">--}}
{{--                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>--}}
{{--                        IT 🇮🇹 (SDI) & CH 🇨🇭 (QR-Bill)--}}
{{--                    </span>--}}
{{--                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-teal-50 text-teal-800 border border-teal-200">--}}
{{--                        GDPR & nLPD--}}
{{--                    </span>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </header>--}}

        <!-- Centered Card Container -->
        <main class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8">
            <div class="w-full max-w-md">
                <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xl overflow-hidden transition-all duration-200">
                    <!-- Brand Gradient Highlight Bar -->
                    <div class="h-1.5 w-full bg-gradient-to-r from-teal-500 via-teal-600 to-emerald-500"></div>

                    <!-- Inner Form Content -->
                    <div class="px-7 py-8 sm:px-10 sm:py-9">
                        {{ $slot }}
                    </div>
                </div>

                <!-- Assistance / Privacy note below card -->
                <p class="text-center text-xs text-slate-500 mt-6">
                    Hai bisogno di supporto? Contatta <a href="mailto:info@fatturafacile.eu" class="font-semibold text-teal-600 hover:text-teal-700 underline">info@fatturafacile.eu</a>
                </p>
                <p class="text-center text-xs text-slate-500 mt-6">
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
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>

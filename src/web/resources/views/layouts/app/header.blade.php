<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        @filamentStyles
    </head>
    <body class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased font-sans selection:bg-teal-500 selection:text-white flex flex-col justify-between transition-colors duration-200">
        <!-- Navigation Header matching https://fatturafacile.eu/ -->
        <header class="sticky top-0 z-40 w-full backdrop-blur-md bg-white/90 dark:bg-slate-900/90 border-b border-slate-200/80 dark:border-slate-800 transition-colors duration-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
                <!-- Brand Logo -->
                <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

                <!-- Navigation Links -->
                <nav class="hidden md:flex items-center space-x-6 text-sm font-medium text-slate-600 dark:text-slate-300">
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'text-teal-600 dark:text-teal-400 font-bold border-b-2 border-teal-600 dark:border-teal-400 pb-1' : 'hover:text-teal-600 dark:hover:text-teal-400 transition-colors' }}" wire:navigate>
                        {{ __('Dashboard') }}
                    </a>
                    <a href="{{ url('/admin') }}" class="hover:text-teal-600 dark:hover:text-teal-400 transition-colors flex items-center gap-1.5">
                        <span data-panel="Pannello Gestionale">{{ __('Management Panel') }}</span>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-teal-50 dark:bg-teal-950/60 text-teal-800 dark:text-teal-300 border border-teal-200 dark:border-teal-800/60">ERP</span>
                    </a>
                    <a href="{{ url('/admin/customers') }}" class="hover:text-teal-600 dark:hover:text-teal-400 transition-colors">
                        {{ __('CRM & Customers') }}
                    </a>
                    <a href="{{ url('/portal/dashboard') }}" class="hover:text-teal-600 dark:hover:text-teal-400 transition-colors">
                        {{ __('Self-Service Portal') }}
                    </a>
                    <a href="{{ url('/') }}" class="hover:text-teal-600 dark:hover:text-teal-400 transition-colors">
                        {{ __('Website') }}
                    </a>
                </nav>

                <!-- User Profile & Action -->
                <div class="flex items-center gap-2.5 sm:gap-3">
                    <x-language-switcher />

                    <x-theme-toggle />

                    <span class="hidden lg:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        IT 🇮🇹 & CH 🇨🇭
                    </span>

                    @auth
                        <x-desktop-user-menu />
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-white bg-slate-900 dark:bg-teal-600 hover:bg-slate-800 dark:hover:bg-teal-500 rounded-xl shadow-sm transition-all duration-200">
                            {{ __('Log in') }}
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Main Workspace -->
        <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {{ $slot }}
        </main>

        <!-- Dynamic Official Footer -->
        @include('partials.footer')

        @livewire('notifications')

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
        @filamentScripts
    </body>
</html>

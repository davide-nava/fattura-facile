<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        @filamentStyles
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-800 antialiased font-sans selection:bg-teal-500 selection:text-white flex flex-col justify-between">
        <!-- Navigation Header matching https://fatturafacile.eu/ -->
        <header class="sticky top-0 z-40 w-full backdrop-blur-md bg-white/90 border-b border-slate-200/80 transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
                <!-- Brand Logo -->
                <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

                <!-- Navigation Links -->
                <nav class="hidden md:flex items-center space-x-6 text-sm font-medium text-slate-600">
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'text-teal-600 font-bold border-b-2 border-teal-600 pb-1' : 'hover:text-teal-600 transition-colors' }}" wire:navigate>
                        Dashboard
                    </a>
                    <a href="{{ url('/admin') }}" class="hover:text-teal-600 transition-colors flex items-center gap-1.5">
                        <span>Pannello Gestionale</span>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-teal-50 text-teal-800 border border-teal-200">ERP</span>
                    </a>
                    <a href="{{ url('/admin/customers') }}" class="hover:text-teal-600 transition-colors">
                        CRM & Clienti
                    </a>
                    <a href="{{ url('/portal/dashboard') }}" class="hover:text-teal-600 transition-colors">
                        Portale Self-Service
                    </a>
                    <a href="{{ url('/') }}" class="hover:text-teal-600 transition-colors">
                        Sito Web
                    </a>
                </nav>

                <!-- User Profile & Action -->
                <div class="flex items-center gap-3">
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        IT 🇮🇹 & CH 🇨🇭
                    </span>

                    @auth
                        <x-desktop-user-menu />
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-white bg-slate-900 hover:bg-teal-700 rounded-xl shadow-sm transition-all duration-200">
                            Accedi
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

@php
    $theme = request()->cookie('theme', 'light');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $theme === 'dark' ? 'dark' : '' }}">
<head>
    @include('partials.head')
    @filamentStyles
</head>
<body
    class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased font-sans selection:bg-teal-500 selection:text-white flex flex-col justify-between transition-colors duration-200">

<div
    x-data="{
        sidebarCollapsed: localStorage.getItem('ff_erp_sidebar_collapsed') === 'true',
        init() {
            window.addEventListener('sidebar-toggle', (e) => {
                this.sidebarCollapsed = e.detail.collapsed;
            });
        }
    }"
    class="min-h-screen flex flex-col flex-1"
>
    <!-- Collapsible ERP Sidebar -->
    <x-sidebar />

    <!-- Main Application Container -->
    <div
        :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-68'"
        class="flex-1 flex flex-col min-w-0 transition-all duration-300 ease-in-out"
    >
        <!-- Top Navigation Header -->
        <header
            class="sticky top-0 z-30 w-full backdrop-blur-md bg-white/90 dark:bg-slate-900/90 border-b border-slate-200/80 dark:border-slate-800 transition-colors duration-200"
        >
            <div class="px-4 sm:px-6 h-18 flex items-center justify-between gap-4">
                <!-- Left: Mobile Toggle & Mobile Logo / Desktop Breadcrumb -->
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="window.dispatchEvent(new CustomEvent('toggle-mobile-sidebar'))"
                        class="lg:hidden p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                        aria-label="{{ __('menu.toggle_sidebar') }}"
                    >
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>

                    <div class="lg:hidden">
                        <x-app-logo href="{{ route('dashboard') }}" wire:navigate />
                    </div>

                    <!-- Breadcrumb / Active Page Indicator (Desktop) -->
                    <div class="hidden lg:flex items-center gap-2 text-xs font-medium text-slate-400 dark:text-slate-500">
                        <span>Fattura Facile</span>
                        <span class="sr-only">FatturaFacile</span>
                        <span>/</span>
                        <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ $title ?? __('menu.dashboard') }}</span>
                    </div>
                </div>

                <!-- Right: Language Switcher, Theme & User Menu -->
                <div class="flex items-center gap-2.5 sm:gap-3">
                    @auth
                        <x-desktop-user-menu />
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
    </div>
</div>

@livewire('notifications')

@persist('toast')
<flux:toast.group>
    <flux:toast/>
</flux:toast.group>
@endpersist

@fluxScripts
@filamentScripts
</body>
</html>

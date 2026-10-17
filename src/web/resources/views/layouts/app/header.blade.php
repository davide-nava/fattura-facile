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
<!-- Navigation Header matching https://fatturafacile.eu/ -->
<header
    class="sticky top-0 z-40 w-full backdrop-blur-md bg-white/90 dark:bg-slate-900/90 border-b border-slate-200/80 dark:border-slate-800 transition-colors duration-200">
    <div class="px-4 h-20 flex items-center justify-between">
        <!-- Brand Logo -->
        <x-app-logo href="{{ route('dashboard') }}" wire:navigate/>

        <!-- User Profile & Action -->
        <div class="flex items-center gap-2.5 sm:gap-3">
            @auth
                <x-desktop-user-menu/>
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
    <flux:toast/>
</flux:toast.group>
@endpersist

@fluxScripts
@filamentScripts
</body>
</html>

@php
    $theme = request()->cookie('theme', 'light');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth {{ $theme === 'dark' ? 'dark' : '' }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased font-sans selection:bg-teal-500 selection:text-white flex flex-col justify-between transition-colors duration-200">
    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-40 w-full backdrop-blur-md bg-white/90 dark:bg-slate-900/90 border-b border-slate-200/80 dark:border-slate-800 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <x-app-logo href="{{ route('dashboard') }}" wire:navigate />
            <div class="flex items-center gap-2.5 sm:gap-3">
                <x-language-switcher />
                <x-theme-toggle />
                @auth
                    <x-desktop-user-menu />
                @else
                    <a href="{{ route('login') }}" data-login="Accedi" class="px-4 py-2 text-sm font-semibold text-white bg-slate-900 dark:bg-teal-600 hover:bg-slate-800 dark:hover:bg-teal-500 rounded-xl shadow-sm transition-all duration-200">
                        {{ __('Log in') }}
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <main class="flex-1">
    </main>

    <!-- Official Footer Component -->
    @include('partials.footer')

    @fluxScripts
</body>
</html>

@php
    $theme = request()->cookie('theme', 'light');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth {{ $theme === 'dark' ? 'dark' : '' }}">
<head>
    @include('partials.head')
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen flex flex-col font-sans selection:bg-teal-500 selection:text-white transition-colors duration-200">
    <nav class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800 sticky top-0 z-40 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <div class="shrink-0 flex items-center">
                        <a href="{{ route('portal.dashboard') }}" class="flex items-center gap-2.5">
                            <x-app-logo-icon class="h-8 w-8 shrink-0" />
                            <span class="text-slate-900 dark:text-white font-black text-xl tracking-tight">Fattura<span class="text-teal-600 dark:text-teal-400">Facile</span></span>
                            <span class="text-[10px] bg-teal-50 dark:bg-teal-950/60 text-teal-800 dark:text-teal-300 border border-teal-200 dark:border-teal-800/60 px-2 py-0.5 rounded-full font-bold uppercase">B2B Portal</span>
                        </a>
                    </div>
                    @auth('customer_portal')
                    <div class="hidden sm:-my-px sm:ml-8 sm:flex sm:space-x-8 h-16">
                        <a href="{{ route('portal.dashboard') }}" class="{{ request()->routeIs('portal.dashboard') ? 'border-teal-600 dark:border-teal-400 text-teal-600 dark:text-teal-400 font-bold' : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors">
                            {{ __('Dashboard') }}
                        </a>
                        <a href="{{ route('portal.invoices') }}" class="{{ request()->routeIs('portal.invoices*') ? 'border-teal-600 dark:border-teal-400 text-teal-600 dark:text-teal-400 font-bold' : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors">
                            {{ __('Invoices') }}
                        </a>
                        <a href="{{ route('portal.quotes') }}" class="{{ request()->routeIs('portal.quotes*') ? 'border-teal-600 dark:border-teal-400 text-teal-600 dark:text-teal-400 font-bold' : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors">
                            {{ __('Quotes') }}
                        </a>
                        <a href="{{ route('portal.profile') }}" class="{{ request()->routeIs('portal.profile*') ? 'border-teal-600 dark:border-teal-400 text-teal-600 dark:text-teal-400 font-bold' : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors">
                            {{ __('Profile') }}
                        </a>
                    </div>
                    @endauth
                </div>
                <div class="flex items-center space-x-3">
                    <x-language-switcher />

                    <x-theme-toggle />

                    @auth('customer_portal')
                    <div class="text-sm text-slate-700 dark:text-slate-300 hidden md:block">
                        <span class="font-semibold">{{ auth('customer_portal')->user()->name }}</span>
                        <span class="text-xs text-slate-400 dark:text-slate-500">({{ auth('customer_portal')->user()->customer->name }})</span>
                    </div>
                    <form method="POST" action="{{ route('portal.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 cursor-pointer transition-colors">
                            {{ __('Log out') }}
                        </button>
                    </form>
                    @else
                    <a href="{{ route('portal.login') }}" class="text-sm font-medium text-teal-600 dark:text-teal-400 hover:text-teal-700 dark:hover:text-teal-300 transition-colors">
                        {{ __('Sign In') }}
                    </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        @if (session('status'))
            <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/60 border-l-4 border-emerald-500 text-emerald-800 dark:text-emerald-300 text-sm rounded-xl">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-950/60 border-l-4 border-red-500 text-red-800 dark:text-red-300 text-sm rounded-xl">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 py-4 mt-auto transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs text-slate-500 dark:text-slate-400">
            &copy; {{ date('Y') }} Fattura Facile Portal. {{ __('All rights reserved. Secure B2B/B2C Self-Service.') }}
        </div>
    </footer>
</body>
</html>

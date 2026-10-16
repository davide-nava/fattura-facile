<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('Client Portal')) - FatturaFacile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col font-sans">
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('portal.dashboard') }}" class="flex items-center space-x-2">
                            <span class="text-emerald-600 font-bold text-xl">FatturaFacile</span>
                            <span class="text-xs bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded font-medium">B2B Portal</span>
                        </a>
                    </div>
                    @auth('customer_portal')
                    <div class="hidden sm:-my-px sm:ml-8 sm:flex sm:space-x-8">
                        <a href="{{ route('portal.dashboard') }}" class="{{ request()->routeIs('portal.dashboard') ? 'border-emerald-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            {{ __('Dashboard') }}
                        </a>
                        <a href="{{ route('portal.invoices') }}" class="{{ request()->routeIs('portal.invoices*') ? 'border-emerald-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            {{ __('Invoices') }}
                        </a>
                        <a href="{{ route('portal.quotes') }}" class="{{ request()->routeIs('portal.quotes*') ? 'border-emerald-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            {{ __('Quotes') }}
                        </a>
                        <a href="{{ route('portal.profile') }}" class="{{ request()->routeIs('portal.profile*') ? 'border-emerald-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            {{ __('Profile') }}
                        </a>
                    </div>
                    @endauth
                </div>
                <div class="flex items-center space-x-4">
                    <x-language-switcher />

                    @auth('customer_portal')
                    <div class="text-sm text-gray-600 hidden md:block">
                        <span class="font-semibold">{{ auth('customer_portal')->user()->name }}</span>
                        <span class="text-xs text-gray-400">({{ auth('customer_portal')->user()->customer->name }})</span>
                    </div>
                    <form method="POST" action="{{ route('portal.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">
                            {{ __('Log out') }}
                        </button>
                    </form>
                    @else
                    <a href="{{ route('portal.login') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-800">
                        {{ __('Sign In') }}
                    </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        @if (session('status'))
            <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 text-sm rounded">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-white border-t border-gray-200 py-4 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs text-gray-500">
            &copy; {{ date('Y') }} FatturaFacile Portal. All rights reserved. Secure B2B/B2C Self-Service.
        </div>
    </footer>
</body>
</html>

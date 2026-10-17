@extends('portal.layout')

@section('title', __('Client Portal Login'))

@section('content')
<div class="max-w-md mx-auto my-12 bg-white dark:bg-slate-900 p-8 border border-slate-200/90 dark:border-slate-800 rounded-3xl shadow-xl transition-colors duration-200 overflow-hidden relative">
    <div class="h-1.5 w-full absolute top-0 left-0 bg-gradient-to-r from-teal-500 via-teal-600 to-emerald-500"></div>

    <div class="text-center mt-2 mb-8">
        <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Sign in to Client Portal') }}</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('Access your invoices, quotes, and account statements') }}</p>
    </div>

    <form method="POST" action="{{ route('portal.login.post') }}" class="space-y-6">
        @csrf

        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Email Address') }}</label>
            <input type="email" name="email" id="email" required autofocus
                value="{{ old('email') }}"
                class="mt-1 block w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl shadow-xs focus:outline-none focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 sm:text-sm transition-colors">
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Password') }}</label>
            <input type="password" name="password" id="password" required
                class="mt-1 block w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl shadow-xs focus:outline-none focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 sm:text-sm transition-colors">
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input type="checkbox" name="remember" id="remember"
                    class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-slate-300 dark:border-slate-700 dark:bg-slate-800 rounded">
                <label for="remember" class="ml-2 block text-sm text-slate-600 dark:text-slate-400">{{ __('Remember me') }}</label>
            </div>
        </div>

        <div>
            <button type="submit"
                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-slate-900 dark:bg-teal-600 hover:bg-slate-800 dark:hover:bg-teal-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-all duration-200">
                {{ __('Sign In') }}
            </button>
        </div>
    </form>
</div>
@endsection

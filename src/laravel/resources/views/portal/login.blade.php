@extends('portal.layout')

@section('title', __('Client Portal Login'))

@section('content')
<div class="max-w-md mx-auto my-12 bg-white p-8 border border-gray-200 rounded-xl shadow-sm">
    <div class="flex justify-center mb-6">
        <x-language-switcher />
    </div>

    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-900">{{ __('Sign in to Client Portal') }}</h2>
        <p class="text-sm text-gray-500 mt-1">{{ __('Access your invoices, quotes, and account statements') }}</p>
    </div>

    <form method="POST" action="{{ route('portal.login.post') }}" class="space-y-6">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">{{ __('Email Address') }}</label>
            <input type="email" name="email" id="email" required autofocus
                value="{{ old('email') }}"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">{{ __('Password') }}</label>
            <input type="password" name="password" id="password" required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input type="checkbox" name="remember" id="remember"
                    class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                <label for="remember" class="ml-2 block text-sm text-gray-700">{{ __('Remember me') }}</label>
            </div>
        </div>

        <div>
            <button type="submit"
                class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
                {{ __('Sign In') }}
            </button>
        </div>
    </form>
</div>
@endsection

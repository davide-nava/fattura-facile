@extends('portal.layout')

@section('title', __('Your Account Profile'))

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ __('Account Profile') }}</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('Manage your contact information and billing address') }}</p>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 sm:p-8 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm transition-colors duration-200">
        <form method="POST" action="{{ route('portal.profile.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Contact Person Name') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name', $portalUser->name) }}" required
                    class="mt-1 block w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl shadow-xs focus:outline-none focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 sm:text-sm transition-colors">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Email Address (Read-only)') }}</label>
                <input type="email" value="{{ $portalUser->email }}" disabled
                    class="mt-1 block w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 rounded-xl sm:text-sm cursor-not-allowed">
            </div>

            <div>
                <label for="phone" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Phone') }}</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $customer->phone) }}"
                    class="mt-1 block w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl shadow-xs focus:outline-none focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 sm:text-sm transition-colors">
            </div>

            <div class="border-t border-slate-100 dark:border-slate-800 pt-5">
                <h3 class="text-md font-bold text-slate-900 dark:text-white mb-4">{{ __('Billing Address') }}</h3>
                <div class="space-y-4">
                    <div>
                        <label for="street" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Street & Number') }}</label>
                        <input type="text" name="street" id="street" value="{{ old('street', $customer->street) }}"
                            class="mt-1 block w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl shadow-xs focus:outline-none focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 sm:text-sm transition-colors">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="postal_code" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Postal Code') }}</label>
                            <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', $customer->postal_code) }}"
                                class="mt-1 block w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl shadow-xs focus:outline-none focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 sm:text-sm transition-colors">
                        </div>
                        <div>
                            <label for="city" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('City') }}</label>
                            <input type="text" name="city" id="city" value="{{ old('city', $customer->city) }}"
                                class="mt-1 block w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl shadow-xs focus:outline-none focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 sm:text-sm transition-colors">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit"
                    class="px-6 py-2.5 bg-slate-900 dark:bg-teal-600 hover:bg-slate-800 dark:hover:bg-teal-500 text-white rounded-xl text-sm font-bold shadow-sm transition-all duration-200">
                    {{ __('Save Changes') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

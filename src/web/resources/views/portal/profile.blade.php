@extends('portal.layout')

@section('title', __('Your Account Profile'))

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Account Profile') }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ __('Manage your contact information and billing address') }}</p>
    </div>

    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
        <form method="POST" action="{{ route('portal.profile.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Contact Person Name') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name', $portalUser->name) }}" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('Email Address (Read-only)') }}</label>
                <input type="email" value="{{ $portalUser->email }}" disabled
                    class="mt-1 block w-full px-3 py-2 border border-gray-200 bg-gray-50 text-gray-500 rounded-md sm:text-sm cursor-not-allowed">
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">{{ __('Phone') }}</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $customer->phone) }}"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
            </div>

            <div class="border-t border-gray-100 pt-4">
                <h3 class="text-md font-semibold text-gray-800 mb-3">{{ __('Billing Address') }}</h3>
                <div class="space-y-4">
                    <div>
                        <label for="street" class="block text-sm font-medium text-gray-700">{{ __('Street & Number') }}</label>
                        <input type="text" name="street" id="street" value="{{ old('street', $customer->street) }}"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="postal_code" class="block text-sm font-medium text-gray-700">{{ __('Postal Code') }}</label>
                            <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', $customer->postal_code) }}"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700">{{ __('City') }}</label>
                            <input type="text" name="city" id="city" value="{{ old('city', $customer->city) }}"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit"
                    class="px-5 py-2.5 bg-emerald-600 text-white rounded-md text-sm font-medium hover:bg-emerald-700 shadow-sm transition">
                    {{ __('Save Changes') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

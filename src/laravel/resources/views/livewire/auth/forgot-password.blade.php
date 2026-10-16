<x-layouts::auth :title="__('Forgot password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Forgot password')" :description="__('Enter your email to receive a password reset link for FatturaFacile')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-5">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                type="email"
                required
                autofocus
                placeholder="email@example.com"
            />

            <div class="pt-2">
                <flux:button variant="primary" type="submit" class="w-full bg-slate-900 hover:bg-teal-700 text-white font-bold py-3 rounded-xl shadow-sm transition-all duration-200" data-test="email-password-reset-link-button">
                    {{ __('Email password reset link') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-slate-600 dark:text-slate-400 pt-2 border-t border-slate-100">
            <span>{{ __('Or, return to') }}</span>
            <flux:link :href="route('login')" class="text-teal-600 hover:text-teal-700 font-semibold" wire:navigate>{{ __('log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>

<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <div class="flex justify-center -mt-1">
            <x-language-switcher />
        </div>

        <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to access FatturaFacile')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-passkey-verify />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0 text-teal-600 hover:text-teal-700 font-medium" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

            <div class="pt-2">
                <flux:button variant="primary" type="submit" class="w-full bg-slate-900 hover:bg-teal-700 text-white font-bold py-3 rounded-xl shadow-sm transition-all duration-200" data-test="login-button">
                    {{ __('Log in') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-slate-600 dark:text-slate-400 pt-2 border-t border-slate-100">
            <span>{{ __('Don\'t have an account?') }}</span>
            <flux:link :href="route('register')" class="text-teal-600 hover:text-teal-700 font-semibold" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>

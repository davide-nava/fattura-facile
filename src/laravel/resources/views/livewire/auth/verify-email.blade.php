<x-layouts::auth :title="__('Email verification')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Email verification')"
            :description="__('Please verify your email address to continue using FatturaFacile.')"
        />

        <flux:text class="text-center text-sm text-slate-600 dark:text-slate-400">
            {{ __('Please verify your email address by clicking on the link we just emailed to you.') }}
        </flux:text>

        @if (session('status') == 'verification-link-sent')
            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-center text-sm font-medium text-emerald-800">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif

        <div class="flex flex-col items-center justify-between space-y-3 pt-2">
            <form method="POST" action="{{ route('verification.send') }}" class="w-full">
                @csrf
                <flux:button type="submit" variant="primary" class="w-full bg-slate-900 hover:bg-teal-700 text-white font-bold py-3 rounded-xl shadow-sm transition-all duration-200">
                    {{ __('Resend verification email') }}
                </flux:button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:button variant="ghost" type="submit" class="w-full text-sm font-semibold text-slate-600 hover:text-slate-900 cursor-pointer" data-test="logout-button">
                    {{ __('Log out') }}
                </flux:button>
            </form>
        </div>
    </div>
</x-layouts::auth>

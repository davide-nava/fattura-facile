<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('Company settings') }}</flux:heading>

    <x-settings.layout :heading="__('Company Profile')" :subheading="__('Update your registered legal entity, Swiss UID and business address')">
        <form wire:submit="updateCompanyInformation" class="my-6 w-full space-y-6">
            <div class="space-y-4">
                <flux:input wire:model="name" :label="__('Display Trade Name')" type="text" required />

                <flux:input wire:model="legal_name" :label="__('Full Legal Name (as per Commercial Register)')" type="text" />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:input wire:model="tax_number" :label="__('Swiss UID (CHE-xxx.xxx.xxx)')" type="text" placeholder="CHE-109.876.543" />
                    <flux:input wire:model="vat_number" :label="__('VAT Registration (MWST / IVA)')" type="text" placeholder="CHE-109.876.543 MWST" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:input wire:model="currency" :label="__('Base Currency')" type="text" required />
                    <flux:input wire:model="country" :label="__('Country Code (ISO 2)')" type="text" required />
                </div>

                <flux:input wire:model="street" :label="__('Street & Building Number')" type="text" />

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <flux:input wire:model="postal_code" :label="__('Postal Code')" type="text" />
                    <flux:input wire:model="city" :label="__('City')" type="text" />
                    <flux:input wire:model="state_canton" :label="__('Canton / Region')" type="text" placeholder="ZH, TI, GE..." />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:input wire:model="email" :label="__('Official Contact Email')" type="email" />
                    <flux:input wire:model="phone" :label="__('Phone Number')" type="tel" />
                </div>

                <flux:input wire:model="website" :label="__('Corporate Website')" type="url" />
            </div>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('Save Changes') }}</flux:button>
            </div>
        </form>
    </x-settings.layout>
</section>

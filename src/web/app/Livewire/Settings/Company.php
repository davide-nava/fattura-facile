<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Modules\Companies\Models\Company as CompanyModel;
use App\Modules\Tenancy\Services\TenantContext;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Company settings')]
class Company extends Component
{
    public string $name = '';

    public string $legal_name = '';

    public string $tax_number = '';

    public string $vat_number = '';

    public string $currency = 'CHF';

    public string $country = 'CH';

    public string $street = '';

    public string $city = '';

    public string $postal_code = '';

    public string $state_canton = '';

    public string $email = '';

    public string $phone = '';

    public string $website = '';

    public ?int $companyId = null;

    public function mount(): void
    {
        $tenantContext = app(TenantContext::class);
        $tenant = $tenantContext->getTenant() ?? Auth::user()?->currentTenant;

        $company = null;
        if ($tenant) {
            $company = CompanyModel::where('tenant_id', $tenant->id)->first();
        }

        if (! $company) {
            $company = CompanyModel::first();
        }

        if ($company) {
            $this->companyId = $company->id;
            $this->name = (string) $company->name;
            $this->legal_name = (string) ($company->legal_name ?? '');
            $this->tax_number = (string) ($company->tax_number ?? '');
            $this->vat_number = (string) ($company->vat_number ?? '');
            $this->currency = (string) ($company->currency ?? 'CHF');
            $this->country = (string) ($company->country ?? 'CH');
            $this->street = (string) ($company->street ?? '');
            $this->city = (string) ($company->city ?? '');
            $this->postal_code = (string) ($company->postal_code ?? '');
            $this->state_canton = (string) ($company->state_canton ?? '');
            $this->email = (string) ($company->email ?? '');
            $this->phone = (string) ($company->phone ?? '');
            $this->website = (string) ($company->website ?? '');
        }
    }

    public function updateCompanyInformation(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'vat_number' => ['nullable', 'string', 'max:50'],
            'currency' => ['required', 'string', 'size:3'],
            'country' => ['required', 'string', 'size:2'],
            'street' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'state_canton' => ['nullable', 'string', 'max:10'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'string', 'max:255'],
        ]);

        if ($this->companyId) {
            $company = CompanyModel::find($this->companyId);
            if ($company) {
                $company->update($validated);
            }
        }

        Flux::toast(variant: 'success', text: __('Company profile updated successfully.'));
    }
}

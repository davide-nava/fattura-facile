<?php

declare(strict_types=1);

use App\Modules\Companies\Models\Company;
use App\Modules\Companies\Services\CompanyBranding;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $tenant = Tenant::create(['name' => 'Branding Tenant', 'slug' => 'brand-t']);
    app(TenantContext::class)->setTenant($tenant);
});

test('company branding resolves defaults when settings are empty', function () {
    $company = Company::create([
        'name' => 'Acme AG',
        'street' => 'Bahnhofstrasse 1',
        'city' => 'Zürich',
        'postal_code' => '8001',
        'country' => 'CH',
        'currency' => 'CHF',
    ]);

    $branding = CompanyBranding::fromCompany($company);

    expect($branding->primaryColor)->toBe('#0d9488')
        ->and($branding->secondaryColor)->toBe('#1e293b')
        ->and($branding->accentColor)->toBe('#f59e0b')
        ->and($branding->hasLogo())->toBeFalse()
        ->and($branding->footerText)->toContain('Acme AG')
        ->and($branding->footerText)->toContain('Bahnhofstrasse 1, 8001 Zürich')
        ->and($branding->toCssVariables())->toContain('--primary-color: #0d9488');
});

test('company branding parses customized settings from database', function () {
    $company = Company::create([
        'name' => 'Alpine Tech SA',
        'street' => 'Via Nassa 10',
        'city' => 'Lugano',
        'postal_code' => '6900',
        'country' => 'CH',
        'currency' => 'CHF',
        'tax_number' => 'CHE-111.222.333',
        'vat_number' => 'CHE-111.222.333 MWST',
        'settings' => [
            'branding' => [
                'primary_color' => '#2563eb',
                'secondary_color' => '#0f172a',
                'accent_color' => '#ec4899',
                'logo_url' => 'https://example.com/logo.png',
                'header_text' => 'Confidential Executive Document',
                'footer_text' => 'Custom Legal Footer Notice',
                'font_family' => 'Arial, sans-serif',
                'bank_details' => [
                    'iban' => 'CH9300000000000000000',
                    'bic' => 'UBSWCHZH',
                    'bank_name' => 'UBS Switzerland',
                ],
            ],
        ],
    ]);

    $branding = $company->getBranding();

    expect($branding->primaryColor)->toBe('#2563eb')
        ->and($branding->secondaryColor)->toBe('#0f172a')
        ->and($branding->accentColor)->toBe('#ec4899')
        ->and($branding->logoUrl)->toBe('https://example.com/logo.png')
        ->and($branding->hasLogo())->toBeTrue()
        ->and($branding->headerText)->toBe('Confidential Executive Document')
        ->and($branding->footerText)->toBe('Custom Legal Footer Notice')
        ->and($branding->fontFamily)->toBe('Arial, sans-serif')
        ->and($branding->bankDetails['iban'])->toBe('CH9300000000000000000')
        ->and($branding->bankDetails['bic'])->toBe('UBSWCHZH');
});

test('company branding sanitizes invalid hex color codes safely', function () {
    $company = Company::create([
        'name' => 'Sanitize Test AG',
        'currency' => 'CHF',
        'settings' => [
            'branding' => [
                'primary_color' => 'invalid-red; background: black;',
            ],
        ],
    ]);

    $branding = $company->getBranding();

    expect($branding->primaryColor)->toBe('#0d9488');
});

test('company branding handles null company gracefully', function () {
    $branding = CompanyBranding::fromCompany(null);

    expect($branding->primaryColor)->toBe('#0d9488')
        ->and($branding->hasLogo())->toBeFalse();
});

<?php

declare(strict_types=1);

namespace App\Modules\Companies\Services;

use App\Modules\Companies\Models\Company;

class CompanyBranding
{
    /**
     * @param  array<string, mixed>  $bankDetails
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public readonly string $primaryColor = '#0d9488',
        public readonly string $secondaryColor = '#1e293b',
        public readonly string $accentColor = '#f59e0b',
        public readonly ?string $logoUrl = null,
        public readonly ?string $headerText = null,
        public readonly ?string $footerText = null,
        public readonly string $fontFamily = "'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
        public readonly array $bankDetails = [],
        public readonly array $extra = [],
    ) {}

    /**
     * Build branding configuration from a Company model instance.
     */
    public static function fromCompany(?Company $company): self
    {
        if ($company === null) {
            return new self;
        }

        /** @var array<string, mixed> $settings */
        $settings = $company->settings ?? [];
        /** @var array<string, mixed> $brandingSettings */
        $brandingSettings = $settings['branding'] ?? [];

        $primaryColor = self::sanitizeHexColor((string) ($brandingSettings['primary_color'] ?? '#0d9488'), '#0d9488');
        $secondaryColor = self::sanitizeHexColor((string) ($brandingSettings['secondary_color'] ?? '#1e293b'), '#1e293b');
        $accentColor = self::sanitizeHexColor((string) ($brandingSettings['accent_color'] ?? '#f59e0b'), '#f59e0b');

        $logoUrl = ! empty($brandingSettings['logo_url']) ? (string) $brandingSettings['logo_url'] : null;
        $headerText = ! empty($brandingSettings['header_text']) ? (string) $brandingSettings['header_text'] : null;

        $defaultFooter = self::generateDefaultFooter($company);
        $footerText = ! empty($brandingSettings['footer_text']) ? (string) $brandingSettings['footer_text'] : $defaultFooter;

        $fontFamily = ! empty($brandingSettings['font_family'])
            ? (string) $brandingSettings['font_family']
            : "'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";

        $bankDetails = (array) ($brandingSettings['bank_details'] ?? [
            'iban' => $company->settings['banking']['iban'] ?? null,
            'bic' => $company->settings['banking']['bic'] ?? null,
            'bank_name' => $company->settings['banking']['bank_name'] ?? null,
        ]);

        return new self(
            primaryColor: $primaryColor,
            secondaryColor: $secondaryColor,
            accentColor: $accentColor,
            logoUrl: $logoUrl,
            headerText: $headerText,
            footerText: $footerText,
            fontFamily: $fontFamily,
            bankDetails: array_filter($bankDetails),
            extra: (array) ($brandingSettings['extra'] ?? []),
        );
    }

    /**
     * Ensure a valid hex color string is returned.
     */
    protected static function sanitizeHexColor(string $color, string $default): string
    {
        $color = trim($color);
        if (preg_match('/^#([a-fA-F0-9]{3}){1,2}$/', $color)) {
            return $color;
        }

        return $default;
    }

    /**
     * Generate standard Swiss ERP legal footer from company attributes.
     */
    protected static function generateDefaultFooter(Company $company): string
    {
        $parts = [
            $company->name,
            trim("{$company->street}, {$company->postal_code} {$company->city}"),
        ];

        if ($company->tax_number) {
            $parts[] = "UID: {$company->tax_number}";
        }

        if ($company->vat_number) {
            $parts[] = "VAT: {$company->vat_number}";
        }

        if ($company->email) {
            $parts[] = $company->email;
        }

        return implode(' • ', array_filter($parts));
    }

    public function hasLogo(): bool
    {
        return ! empty($this->logoUrl);
    }

    /**
     * Export as CSS Custom Properties for print styles.
     */
    public function toCssVariables(): string
    {
        return implode('; ', [
            "--primary-color: {$this->primaryColor}",
            "--secondary-color: {$this->secondaryColor}",
            "--accent-color: {$this->accentColor}",
            "--font-family: {$this->fontFamily}",
        ]);
    }
}

# ADR 0005: Swiss Localization Foundations

## Status
Accepted

## Context
Fattura Facile targets Swiss SMEs alongside international applicability. Swiss commercial standards require specific tax rates, rounding behaviors (Swiss 5-cent rounding for cash or invoice totals), enterprise UID formatting, and language handling (EN, DE, FR, IT).

## Decision
1. **Configurable Tax Rates**: Swiss VAT rates (Standard 8.1%, Reduced 2.6%, Accommodation 3.8%, Exempt 0%) are seeded with effective dates and code classifications.
2. **5-Cent Rounding**: A Swiss rounding strategy rounds invoice amounts to the nearest 0.05 CHF when configured for Swiss legal entities (`round(amount * 20) / 20`).
3. **UID Validation**: Swiss enterprise identification numbers are validated against the `CHE-xxx.xxx.xxx` format with optional VAT suffix (`CHE-123.456.789 MWST/TVA/IVA`).
4. **Decoupled Architecture**: All localization logic resides in `App\Modules\Companies\` and `App\Shared\Localization\` so it is easily configured per legal entity rather than hardcoded globally.

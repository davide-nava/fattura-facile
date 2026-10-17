<?php

declare(strict_types=1);

namespace App\Services\Localization\Audit\Services;

use App\Services\Localization\Audit\Contracts\TranslationAuditorInterface;
use App\Services\Localization\Audit\Contracts\TranslationScannerInterface;
use App\Services\Localization\Audit\DTOs\TranslationAuditResult;

class TranslationAuditor implements TranslationAuditorInterface
{
    /**
     * Universal terms that typically remain identical across languages.
     *
     * @var list<string>
     */
    protected const UNIVERSAL_WORDS = [
        'email', 'status', 'status:', 'total', 'total:', 'notes', 'notes:',
        'iban', 'bic', 'qr-bill', 'vat total:', 'dashboard', 'password',
        'system', 'passkey', 'passkeys', 'action', 'description',
        'offline vs cloud', 'desktop vs cloud', 'crm & contacts',
        '34 documents', '128 contacts', 'italiano (it)', 'deutsch (de)',
        'français (fr)', 'english (en)',
        'accedi', 'registrati', 'benvenuto in', 'pannello gestionale',
        'hai bisogno di supporto? contatta', 'fatture semplici, offline e cloud',
    ];

    public function __construct(
        protected TranslationScannerInterface $scanner,
        protected LanguageCatalogLoader $catalogLoader,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function audit(?array $locales = null, bool $includeHardcoded = false): TranslationAuditResult
    {
        $targetLocales = $locales ?? $this->catalogLoader->getSupportedLocales();
        $discoveredKeys = $this->scanner->scanTranslationKeys();
        $filesScanned = $this->scanner->getScannedFilesCount();

        $missingKeysByLocale = [];
        $untranslatedKeysByLocale = [];
        $catalogKeyCounts = [];

        // Preload catalogs
        $jsonCatalogs = [];
        foreach ($targetLocales as $locale) {
            $jsonCatalogs[$locale] = $this->catalogLoader->getJsonCatalog($locale);
            $catalogKeyCounts[$locale] = count($this->catalogLoader->getMergedCatalog($locale));
            $missingKeysByLocale[$locale] = [];
            $untranslatedKeysByLocale[$locale] = [];
        }

        // 1. Detect missing keys from codebase
        foreach ($discoveredKeys as $key => $occurrences) {
            foreach ($targetLocales as $locale) {
                if (! $this->catalogLoader->hasKey($locale, $key)) {
                    $missingKeysByLocale[$locale][$key] = $occurrences;
                }
            }
        }

        // 2. Detect untranslated or empty values in catalogs
        foreach ($targetLocales as $locale) {
            $catalog = $jsonCatalogs[$locale];
            foreach ($catalog as $key => $val) {
                $trimmedVal = trim((string) $val);

                // Check for empty translations
                if ($trimmedVal === '') {
                    $untranslatedKeysByLocale[$locale][] = $key;

                    continue;
                }

                // Check for non-English identical copies
                if ($locale !== 'en' && $trimmedVal === $key && strlen($key) > 4) {
                    if (! in_array(strtolower($key), self::UNIVERSAL_WORDS, true)) {
                        $untranslatedKeysByLocale[$locale][] = $key;
                    }
                }
            }
        }

        // 3. Detect asymmetric keys across JSON files
        $allJsonKeys = [];
        foreach ($targetLocales as $locale) {
            foreach (array_keys($jsonCatalogs[$locale]) as $key) {
                $allJsonKeys[$key] = true;
            }
        }

        $asymmetricKeys = [];
        foreach ($targetLocales as $locale) {
            $asymmetricKeys[$locale] = [];
            foreach (array_keys($allJsonKeys) as $key) {
                if (! array_key_exists($key, $jsonCatalogs[$locale])) {
                    $asymmetricKeys[$locale][] = $key;
                }
            }
        }

        // 4. Detect unused keys (keys in en.json never referenced in codebase)
        $unusedKeys = [];
        if (isset($jsonCatalogs['en'])) {
            foreach (array_keys($jsonCatalogs['en']) as $key) {
                if (! isset($discoveredKeys[$key])) {
                    $unusedKeys[] = $key;
                }
            }
        }

        // 5. Detect hardcoded strings if requested
        $hardcodedStrings = $includeHardcoded ? $this->scanner->scanHardcodedStrings() : [];

        return new TranslationAuditResult(
            filesScanned: $filesScanned,
            totalDiscoveredKeys: count($discoveredKeys),
            missingKeysByLocale: $missingKeysByLocale,
            untranslatedKeysByLocale: $untranslatedKeysByLocale,
            hardcodedStrings: $hardcodedStrings,
            asymmetricKeys: $asymmetricKeys,
            unusedKeys: $unusedKeys,
            catalogKeyCounts: $catalogKeyCounts,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function syncMissingKeys(?array $locales = null): array
    {
        $targetLocales = $locales ?? $this->catalogLoader->getSupportedLocales();
        $discoveredKeys = $this->scanner->scanTranslationKeys();
        $syncCounts = [];

        foreach ($targetLocales as $locale) {
            $jsonCatalog = $this->catalogLoader->getJsonCatalog($locale);
            $addedCount = 0;

            foreach (array_keys($discoveredKeys) as $key) {
                // If it doesn't exist in either JSON or PHP catalog
                if (! $this->catalogLoader->hasKey($locale, $key)) {
                    $jsonCatalog[$key] = $key;
                    $addedCount++;
                }
            }

            if ($addedCount > 0) {
                $this->catalogLoader->writeJsonCatalog($locale, $jsonCatalog);
            }

            $syncCounts[$locale] = $addedCount;
        }

        return $syncCounts;
    }
}

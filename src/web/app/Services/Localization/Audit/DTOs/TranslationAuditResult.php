<?php

declare(strict_types=1);

namespace App\Services\Localization\Audit\DTOs;

final class TranslationAuditResult
{
    /**
     * @param  int  $filesScanned  Total source files analyzed
     * @param  int  $totalDiscoveredKeys  Total distinct translation keys discovered in code
     * @param  array<string, array<string, list<TranslationOccurrence>>>  $missingKeysByLocale  [locale => [key => list<TranslationOccurrence>]]
     * @param  array<string, list<string>>  $untranslatedKeysByLocale  [locale => list<key>]
     * @param  list<HardcodedStringOccurrence>  $hardcodedStrings  Potential hardcoded texts in templates
     * @param  array<string, list<string>>  $asymmetricKeys  [locale => list<keys present in other locales but missing here>]
     * @param  list<string>  $unusedKeys  Keys in catalogs not referenced in source code
     * @param  array<string, int>  $catalogKeyCounts  [locale => total catalog keys]
     */
    public function __construct(
        public int $filesScanned,
        public int $totalDiscoveredKeys,
        public array $missingKeysByLocale = [],
        public array $untranslatedKeysByLocale = [],
        public array $hardcodedStrings = [],
        public array $asymmetricKeys = [],
        public array $unusedKeys = [],
        public array $catalogKeyCounts = [],
    ) {}

    /**
     * Determine whether any locale (or a specific locale) has missing translation keys.
     */
    public function hasMissingKeys(?string $locale = null): bool
    {
        if ($locale !== null) {
            return ! empty($this->missingKeysByLocale[$locale]);
        }

        foreach ($this->missingKeysByLocale as $missing) {
            if (! empty($missing)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether any locale (or a specific locale) has untranslated entries.
     */
    public function hasUntranslatedKeys(?string $locale = null): bool
    {
        if ($locale !== null) {
            return ! empty($this->untranslatedKeysByLocale[$locale]);
        }

        foreach ($this->untranslatedKeysByLocale as $untranslated) {
            if (! empty($untranslated)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether potential hardcoded strings were found.
     */
    public function hasHardcodedStrings(): bool
    {
        return ! empty($this->hardcodedStrings);
    }

    /**
     * Get the total count of missing keys across all audited locales.
     */
    public function getTotalMissingKeysCount(?string $locale = null): int
    {
        if ($locale !== null) {
            return count($this->missingKeysByLocale[$locale] ?? []);
        }

        $allMissing = [];
        foreach ($this->missingKeysByLocale as $keys) {
            foreach (array_keys($keys) as $key) {
                $allMissing[$key] = true;
            }
        }

        return count($allMissing);
    }

    /**
     * Check if the audit passes cleanly.
     */
    public function isClean(?string $locale = null, bool $strict = false): bool
    {
        if ($this->hasMissingKeys($locale)) {
            return false;
        }

        if ($strict) {
            if ($this->hasUntranslatedKeys($locale)) {
                return false;
            }
            if ($this->hasHardcodedStrings()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Export result as an associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'files_scanned' => $this->filesScanned,
            'total_discovered_keys' => $this->totalDiscoveredKeys,
            'catalog_key_counts' => $this->catalogKeyCounts,
            'missing_keys_count' => $this->getTotalMissingKeysCount(),
            'missing_keys_by_locale' => array_map(function (array $keys) {
                return array_map(function (array $occurrences) {
                    return array_map(fn (TranslationOccurrence $occ) => [
                        'file' => $occ->file,
                        'line' => $occ->line,
                        'helper' => $occ->helper,
                    ], $occurrences);
                }, $keys);
            }, $this->missingKeysByLocale),
            'untranslated_keys_by_locale' => $this->untranslatedKeysByLocale,
            'hardcoded_strings' => array_map(fn (HardcodedStringOccurrence $h) => [
                'text' => $h->text,
                'file' => $h->file,
                'line' => $h->line,
                'element' => $h->element,
            ], $this->hardcodedStrings),
            'asymmetric_keys' => $this->asymmetricKeys,
            'unused_keys_count' => count($this->unusedKeys),
            'is_clean' => $this->isClean(),
        ];
    }
}

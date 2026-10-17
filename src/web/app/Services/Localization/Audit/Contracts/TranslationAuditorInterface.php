<?php

declare(strict_types=1);

namespace App\Services\Localization\Audit\Contracts;

use App\Services\Localization\Audit\DTOs\TranslationAuditResult;

interface TranslationAuditorInterface
{
    /**
     * Run a comprehensive audit across configured or specified locales.
     *
     * @param  list<string>|null  $locales
     */
    public function audit(?array $locales = null, bool $includeHardcoded = false): TranslationAuditResult;

    /**
     * Synchronize / populate missing keys into language files.
     *
     * @param  list<string>|null  $locales
     * @return array<string, int> Map of locale to count of newly added keys
     */
    public function syncMissingKeys(?array $locales = null): array;
}

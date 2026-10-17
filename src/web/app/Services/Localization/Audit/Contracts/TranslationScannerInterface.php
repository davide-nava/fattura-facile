<?php

declare(strict_types=1);

namespace App\Services\Localization\Audit\Contracts;

use App\Services\Localization\Audit\DTOs\HardcodedStringOccurrence;
use App\Services\Localization\Audit\DTOs\TranslationOccurrence;

interface TranslationScannerInterface
{
    /**
     * Scan source directories for translation keys.
     *
     * @param  list<string>|null  $directories
     * @return array<string, list<TranslationOccurrence>>
     */
    public function scanTranslationKeys(?array $directories = null): array;

    /**
     * Scan Blade views for potential hardcoded user-facing strings.
     *
     * @param  list<string>|null  $directories
     * @return list<HardcodedStringOccurrence>
     */
    public function scanHardcodedStrings(?array $directories = null): array;

    /**
     * Return the count of files scanned during the last scan.
     */
    public function getScannedFilesCount(): int;
}

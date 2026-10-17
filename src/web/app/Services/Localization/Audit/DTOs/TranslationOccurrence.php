<?php

declare(strict_types=1);

namespace App\Services\Localization\Audit\DTOs;

final readonly class TranslationOccurrence
{
    /**
     * @param  string  $key  The translation key discovered in code
     * @param  string  $file  Relative path to the file containing the occurrence
     * @param  int  $line  Line number of the occurrence
     * @param  string  $helper  Helper function or directive used (e.g. '__', '@lang', 'trans')
     */
    public function __construct(
        public string $key,
        public string $file,
        public int $line,
        public string $helper = '__',
    ) {}
}

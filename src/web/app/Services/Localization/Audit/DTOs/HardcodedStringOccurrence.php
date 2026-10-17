<?php

declare(strict_types=1);

namespace App\Services\Localization\Audit\DTOs;

final readonly class HardcodedStringOccurrence
{
    /**
     * @param  string  $text  The potential hardcoded user-facing text
     * @param  string  $file  Relative path to the template file
     * @param  int  $line  Line number of the element
     * @param  string  $element  Tag or element name where string was detected
     */
    public function __construct(
        public string $text,
        public string $file,
        public int $line,
        public string $element,
    ) {}
}

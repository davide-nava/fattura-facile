<?php

declare(strict_types=1);

namespace App\Services\Localization\Audit\Services;

use App\Services\Localization\Audit\Contracts\TranslationScannerInterface;
use App\Services\Localization\Audit\DTOs\HardcodedStringOccurrence;
use App\Services\Localization\Audit\DTOs\TranslationOccurrence;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class CodebaseTranslationScanner implements TranslationScannerInterface
{
    protected int $scannedFilesCount = 0;

    /**
     * Common acronyms, technical symbols, and words to ignore in hardcoded detection.
     *
     * @var list<string>
     */
    protected const IGNORED_HARDCODED_TERMS = [
        'vat', 'uid', 'chf', 'eur', 'usd', 'iban', 'bic', 'qr-bill', 'pdf', 'erp',
        'id', 'ok', 'no', 'yes', 'post', 'get', 'api', 'url', 'csv', 'xml', 'json',
        'it', 'en', 'de', 'fr', 'b2b', 'b2c', 'qr', 'svg', 'crm',
    ];

    /**
     * @param  string|null  $basePath  Application base path
     */
    public function __construct(
        protected ?string $basePath = null,
    ) {
        $this->basePath = $basePath ?? base_path();
    }

    /**
     * {@inheritdoc}
     */
    public function scanTranslationKeys(?array $directories = null): array
    {
        $directories = $directories ?? [
            $this->basePath.'/resources/views',
            $this->basePath.'/app',
            $this->basePath.'/routes',
        ];

        $this->scannedFilesCount = 0;
        $keys = [];

        $patterns = [
            '__' => '/__\(\s*([\'"])(.*?)\1\s*(?:,[^)]*)?\)/s',
            '@lang' => '/@lang\(\s*([\'"])(.*?)\1\s*(?:,[^)]*)?\)/s',
            'trans' => '/trans\(\s*([\'"])(.*?)\1\s*(?:,[^)]*)?\)/s',
            'trans_choice' => '/trans_choice\(\s*([\'"])(.*?)\1\s*(?:,[^)]*)?\)/s',
            'Lang::get' => '/Lang::get\(\s*([\'"])(.*?)\1\s*(?:,[^)]*)?\)/s',
            'Lang::choice' => '/Lang::choice\(\s*([\'"])(.*?)\1\s*(?:,[^)]*)?\)/s',
        ];

        foreach ($directories as $directory) {
            if (! is_dir($directory)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if (! $file->isFile()) {
                    continue;
                }

                $filename = $file->getFilename();
                $extension = $file->getExtension();

                if (! in_array($extension, ['php', 'js', 'vue']) && ! str_ends_with($filename, '.blade.php')) {
                    continue;
                }

                // Skip vendor published views unless specifically requested
                $filePath = $file->getPathname();
                $normalizedPath = str_replace('\\', '/', $filePath);

                $this->scannedFilesCount++;
                $content = file_get_contents($filePath);
                if ($content === false) {
                    continue;
                }

                $relativeFile = $this->getRelativePath($normalizedPath);

                foreach ($patterns as $helper => $pattern) {
                    if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
                        foreach ($matches as $match) {
                            $rawKey = $match[2][0];
                            $offset = $match[0][1];

                            // Skip dynamic PHP expressions or empty keys
                            if (str_contains($rawKey, '$') || trim($rawKey) === '') {
                                continue;
                            }

                            // Unescape quotes if needed
                            $quote = $match[1][0];
                            $key = ($quote === "'") ? str_replace("\'", "'", $rawKey) : stripcslashes($rawKey);

                            $lineNumber = substr_count(substr($content, 0, $offset), "\n") + 1;

                            $keys[$key][] = new TranslationOccurrence(
                                key: $key,
                                file: $relativeFile,
                                line: $lineNumber,
                                helper: $helper,
                            );
                        }
                    }
                }
            }
        }

        return $keys;
    }

    /**
     * {@inheritdoc}
     */
    public function scanHardcodedStrings(?array $directories = null): array
    {
        $directories = $directories ?? [
            $this->basePath.'/resources/views',
        ];

        $hardcoded = [];

        // Match common HTML and Blade tags containing non-localized text nodes
        $tagPattern = '/<(h[1-6]|p|span|button|flux:heading|flux:subheading|flux:button|flux:label|label|th|td|a)[^>]*>([^<>{}\@\$\n\r]+)<\/\1>/i';

        // Match attributes like placeholder="Static text" or title="Static text"
        $attrPattern = '/\s(placeholder|title|aria-label)="([^"{}\@\$]+)"/i';

        foreach ($directories as $directory) {
            if (! is_dir($directory)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                    continue;
                }

                $filePath = $file->getPathname();
                $normalizedPath = str_replace('\\', '/', $filePath);

                // Skip vendor directories
                if (str_contains($normalizedPath, '/vendor/')) {
                    continue;
                }

                $content = file_get_contents($filePath);
                if ($content === false) {
                    continue;
                }

                $relativeFile = $this->getRelativePath($normalizedPath);

                // 1. Scan tags
                if (preg_match_all($tagPattern, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
                    foreach ($matches as $match) {
                        $element = $match[1][0];
                        $text = trim($match[2][0]);
                        $offset = $match[0][1];

                        if ($this->isSignificantHardcodedText($text)) {
                            $lineNumber = substr_count(substr($content, 0, $offset), "\n") + 1;
                            $hardcoded[] = new HardcodedStringOccurrence(
                                text: $text,
                                file: $relativeFile,
                                line: $lineNumber,
                                element: "<{$element}>",
                            );
                        }
                    }
                }

                // 2. Scan attributes
                if (preg_match_all($attrPattern, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
                    foreach ($matches as $match) {
                        $attrName = $match[1][0];
                        $attrVal = trim($match[2][0]);
                        $offset = $match[0][1];

                        if ($this->isSignificantHardcodedText($attrVal)) {
                            $lineNumber = substr_count(substr($content, 0, $offset), "\n") + 1;
                            $hardcoded[] = new HardcodedStringOccurrence(
                                text: $attrVal,
                                file: $relativeFile,
                                line: $lineNumber,
                                element: "attr:{$attrName}",
                            );
                        }
                    }
                }
            }
        }

        return $hardcoded;
    }

    /**
     * {@inheritdoc}
     */
    public function getScannedFilesCount(): int
    {
        return $this->scannedFilesCount;
    }

    /**
     * Check if a string looks like human-readable text requiring localization.
     */
    protected function isSignificantHardcodedText(string $text): bool
    {
        if (strlen($text) < 3) {
            return false;
        }

        // Must contain at least one word character
        if (! preg_match('/[a-zA-Z]{3,}/', $text)) {
            return false;
        }

        // Exclude common numbers, symbols, and dates
        if (preg_match('/^[\d\s\-\.\:\,\/\#]+$/', $text)) {
            return false;
        }

        $cleanText = strtolower(trim($text));
        if (in_array($cleanText, self::IGNORED_HARDCODED_TERMS, true)) {
            return false;
        }

        // Exclude application branding tokens
        if (in_array($cleanText, ['fattura', 'facile', 'fattura facile'], true)) {
            return false;
        }

        // Exclude web domains
        if (preg_match('/^[a-z0-9\-]+\.(eu|com|ch|org|net|io)$/i', $text)) {
            return false;
        }

        return true;
    }

    /**
     * Convert absolute path to relative path from base path.
     */
    protected function getRelativePath(string $path): string
    {
        $normalizedBase = str_replace('\\', '/', $this->basePath);
        if (str_starts_with($path, $normalizedBase.'/')) {
            return substr($path, strlen($normalizedBase) + 1);
        }

        return $path;
    }
}

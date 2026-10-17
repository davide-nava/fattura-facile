<?php

declare(strict_types=1);

namespace App\Services\Localization\Audit\Services;

use App\Services\Localization\Contracts\LocaleManagerInterface;

class LanguageCatalogLoader
{
    /**
     * @var array<string, array<string, string>>
     */
    protected array $jsonCatalogs = [];

    /**
     * @var array<string, array<string, string>>
     */
    protected array $phpCatalogs = [];

    public function __construct(
        protected LocaleManagerInterface $localeManager,
        protected ?string $baseLangPath = null,
    ) {
        $this->baseLangPath = $baseLangPath ?? lang_path();
    }

    /**
     * Get the list of all supported locales.
     *
     * @return list<string>
     */
    public function getSupportedLocales(): array
    {
        return $this->localeManager->getSupportedLocales();
    }

    /**
     * Load JSON translations dictionary for a given locale.
     *
     * @return array<string, string>
     */
    public function getJsonCatalog(string $locale): array
    {
        if (isset($this->jsonCatalogs[$locale])) {
            return $this->jsonCatalogs[$locale];
        }

        $filePath = "{$this->baseLangPath}/{$locale}.json";

        if (! file_exists($filePath)) {
            return $this->jsonCatalogs[$locale] = [];
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return $this->jsonCatalogs[$locale] = [];
        }

        $decoded = json_decode($content, true);

        return $this->jsonCatalogs[$locale] = is_array($decoded) ? $decoded : [];
    }

    /**
     * Load and flatten PHP translation files (e.g. auth.php, ui.php) for a given locale.
     *
     * @return array<string, string>
     */
    public function getPhpCatalog(string $locale): array
    {
        if (isset($this->phpCatalogs[$locale])) {
            return $this->phpCatalogs[$locale];
        }

        $dir = "{$this->baseLangPath}/{$locale}";
        $flattened = [];

        if (is_dir($dir)) {
            $files = glob("{$dir}/*.php") ?: [];
            foreach ($files as $file) {
                $group = basename($file, '.php');
                $data = include $file;
                if (is_array($data)) {
                    $this->flattenArray($data, $group, $flattened);
                }
            }
        }

        return $this->phpCatalogs[$locale] = $flattened;
    }

    /**
     * Load combined catalog (JSON entries + flattened PHP keys).
     *
     * @return array<string, string>
     */
    public function getMergedCatalog(string $locale): array
    {
        return array_merge($this->getPhpCatalog($locale), $this->getJsonCatalog($locale));
    }

    /**
     * Determine whether a translation key exists in either JSON or PHP catalog for the locale.
     */
    public function hasKey(string $locale, string $key): bool
    {
        $json = $this->getJsonCatalog($locale);
        if (array_key_exists($key, $json)) {
            return true;
        }

        $php = $this->getPhpCatalog($locale);

        return array_key_exists($key, $php);
    }

    /**
     * Write updated JSON catalog to disk, maintaining alphabetical sorting and clean JSON formatting.
     *
     * @param  array<string, string>  $catalog
     */
    public function writeJsonCatalog(string $locale, array $catalog): void
    {
        ksort($catalog, SORT_NATURAL | SORT_FLAG_CASE);

        $filePath = "{$this->baseLangPath}/{$locale}.json";
        $encoded = json_encode($catalog, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($encoded !== false) {
            file_put_contents($filePath, $encoded.PHP_EOL);
            $this->jsonCatalogs[$locale] = $catalog;
        }
    }

    /**
     * Recursively flatten a nested translation array into dot-notation keys.
     *
     * @param  array<string, mixed>  $array
     * @param  array<string, string>  $result
     */
    protected function flattenArray(array $array, string $prefix, array &$result): void
    {
        foreach ($array as $key => $value) {
            $fullKey = "{$prefix}.{$key}";
            if (is_array($value)) {
                $this->flattenArray($value, $fullKey, $result);
            } else {
                $result[$fullKey] = (string) $value;
            }
        }
    }
}

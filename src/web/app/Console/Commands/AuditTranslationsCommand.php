<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Localization\Audit\Contracts\TranslationAuditorInterface;
use App\Services\Localization\Audit\DTOs\HardcodedStringOccurrence;
use App\Services\Localization\Audit\DTOs\TranslationAuditResult;
use App\Services\Localization\Audit\DTOs\TranslationOccurrence;
use Illuminate\Console\Command;

class AuditTranslationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lang:audit
                            {--locale= : Specific locale to audit (e.g. it, en, de, fr)}
                            {--show-hardcoded : Scan and report potential hardcoded UI strings in Blade templates}
                            {--sync : Automatically populate missing keys into language JSON files}
                            {--strict : Fail with error code if untranslated strings or warnings are found}
                            {--json : Output audit results as machine-readable JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit localization coverage, missing keys, untranslated entries, and hardcoded strings across all configured languages';

    public function __construct(
        protected TranslationAuditorInterface $auditor,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $localeOption = $this->option('locale');
        $targetLocales = $localeOption !== null && $localeOption !== '' ? [(string) $localeOption] : null;
        $includeHardcoded = (bool) $this->option('show-hardcoded');
        $isJson = (bool) $this->option('json');
        $isStrict = (bool) $this->option('strict');
        $shouldSync = (bool) $this->option('sync');

        // Handle auto-sync if requested
        if ($shouldSync) {
            $syncResults = $this->auditor->syncMissingKeys($targetLocales);
            if (! $isJson) {
                $this->info('Synchronizing missing translation keys into JSON language catalogs:');
                foreach ($syncResults as $locale => $addedCount) {
                    $this->line("  - [{$locale}]: added {$addedCount} missing keys.");
                }
                $this->newLine();
            }
        }

        $result = $this->auditor->audit($targetLocales, $includeHardcoded);

        if ($isJson) {
            $output = json_encode($result->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $this->output->writeln((string) $output);

            return $result->isClean($localeOption, $isStrict) ? self::SUCCESS : self::FAILURE;
        }

        $this->displayConsoleReport($result, $localeOption, $includeHardcoded, $isStrict);

        return $result->isClean($localeOption, $isStrict) ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Format and output the human-readable console audit report.
     */
    protected function displayConsoleReport(
        TranslationAuditResult $result,
        ?string $localeFilter,
        bool $includeHardcoded,
        bool $isStrict,
    ): void {
        $this->newLine();
        $this->line('<fg=cyan;options=bold>==========================================================</>');
        $this->line('<fg=cyan;options=bold>   Fattura Facile — Localization & i18n Audit Report     </>');
        $this->line('<fg=cyan;options=bold>==========================================================</>');
        $this->newLine();

        $this->line("Files scanned: <options=bold>{$result->filesScanned}</>");
        $this->line("Distinct translation keys used in code: <options=bold>{$result->totalDiscoveredKeys}</>");
        $this->newLine();

        // 1. Locales Overview Table
        $overviewRows = [];
        foreach ($result->catalogKeyCounts as $locale => $totalKeys) {
            $missingCount = count($result->missingKeysByLocale[$locale] ?? []);
            $untranslatedCount = count($result->untranslatedKeysByLocale[$locale] ?? []);

            $status = ($missingCount === 0)
                ? '<fg=green;options=bold>✓ Fully Translated</>'
                : "<fg=red;options=bold>✗ {$missingCount} Missing</>";

            if ($missingCount === 0 && $untranslatedCount > 0) {
                $status .= " <fg=yellow>({$untranslatedCount} untranslated)</>";
            }

            $overviewRows[] = [
                $locale,
                $totalKeys,
                $missingCount === 0 ? '<fg=green>0</>' : "<fg=red>{$missingCount}</>",
                $untranslatedCount === 0 ? '<fg=green>0</>' : "<fg=yellow>{$untranslatedCount}</>",
                $status,
            ];
        }

        $this->table(
            ['Locale', 'Catalog Keys', 'Missing Keys', 'Untranslated / Copies', 'Status'],
            $overviewRows
        );

        $this->newLine();

        // 2. Missing Keys Details
        $hasMissing = false;
        $missingRows = [];

        foreach ($result->missingKeysByLocale as $locale => $keys) {
            if (empty($keys)) {
                continue;
            }
            $hasMissing = true;

            foreach ($keys as $key => $occurrences) {
                $locations = implode("\n", array_map(
                    fn (TranslationOccurrence $occ) => "{$occ->file}:{$occ->line}",
                    array_slice($occurrences, 0, 3)
                ));

                if (count($occurrences) > 3) {
                    $locations .= "\n(+".(count($occurrences) - 3).' more)';
                }

                $missingRows[] = [
                    $locale,
                    $key,
                    $locations,
                ];
            }
        }

        if ($hasMissing) {
            $this->error('Missing Localization Keys Detected:');
            $this->table(
                ['Locale', 'Missing Key', 'Occurrence in Code (File:Line)'],
                $missingRows
            );
            $this->newLine();
            $this->line('<fg=yellow>Tip: Run `php artisan lang:audit --sync` to automatically insert missing keys into lang/*.json catalogs.</>');
            $this->newLine();
        }

        // 3. Potential Hardcoded Strings
        if ($includeHardcoded && $result->hasHardcodedStrings()) {
            $this->warn('Potential Hardcoded UI Strings in Blade Templates:');
            $hardcodedRows = array_map(function (HardcodedStringOccurrence $h) {
                return [
                    $h->element,
                    $h->text,
                    "{$h->file}:{$h->line}",
                ];
            }, array_slice($result->hardcodedStrings, 0, 50));

            $this->table(
                ['Tag / Attribute', 'String Text', 'Location'],
                $hardcodedRows
            );

            if (count($result->hardcodedStrings) > 50) {
                $this->line('... and '.(count($result->hardcodedStrings) - 50).' more.');
            }
            $this->newLine();
        }

        // 4. Untranslated copies
        if ($isStrict && $result->hasUntranslatedKeys($localeFilter)) {
            $this->warn('Potentially Untranslated Keys (English text duplicated in non-English file):');
            foreach ($result->untranslatedKeysByLocale as $locale => $keys) {
                if (! empty($keys)) {
                    $this->line("<fg=yellow;options=bold>[{$locale}]</> ".implode(', ', array_slice($keys, 0, 10)));
                }
            }
            $this->newLine();
        }

        // Final Outcome Banner
        if ($result->isClean($localeFilter, $isStrict)) {
            $this->info('✓ AUDIT PASSED: All translation keys are fully covered and valid across target languages.');
        } else {
            $this->error('✗ AUDIT FAILED: Missing localization keys found. Please translate or sync missing keys.');
        }
    }
}

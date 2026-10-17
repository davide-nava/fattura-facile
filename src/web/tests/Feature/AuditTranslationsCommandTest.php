<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Localization\Audit\Contracts\TranslationAuditorInterface;
use App\Services\Localization\Audit\DTOs\HardcodedStringOccurrence;
use App\Services\Localization\Audit\DTOs\TranslationAuditResult;
use App\Services\Localization\Audit\DTOs\TranslationOccurrence;
use Tests\TestCase;

class AuditTranslationsCommandTest extends TestCase
{
    public function test_lang_audit_command_runs_successfully_and_returns_zero_exit_code(): void
    {
        $this->artisan('lang:audit')
            ->expectsOutputToContain('Fattura Facile — Localization & i18n Audit Report')
            ->expectsOutputToContain('✓ AUDIT PASSED: All translation keys are fully covered and valid across target languages.')
            ->assertExitCode(0);
    }

    public function test_lang_audit_command_with_json_flag_outputs_valid_json_report(): void
    {
        $this->artisan('lang:audit', ['--json' => true])
            ->assertExitCode(0);
    }

    public function test_lang_audit_command_with_specific_locale_filter(): void
    {
        $this->artisan('lang:audit', ['--locale' => 'it'])
            ->expectsOutputToContain('it')
            ->assertExitCode(0);
    }

    public function test_lang_audit_command_with_show_hardcoded_flag(): void
    {
        $this->artisan('lang:audit', ['--show-hardcoded' => true])
            ->assertExitCode(0);
    }

    public function test_lang_audit_command_with_strict_flag(): void
    {
        $this->artisan('lang:audit', ['--strict' => true])
            ->assertExitCode(0);
    }

    public function test_lang_audit_command_fails_when_missing_keys_exist(): void
    {
        $mockAuditor = $this->createMock(TranslationAuditorInterface::class);

        $fakeResult = new TranslationAuditResult(
            filesScanned: 10,
            totalDiscoveredKeys: 5,
            missingKeysByLocale: [
                'it' => [
                    'Missing String Example' => [
                        new TranslationOccurrence('Missing String Example', 'resources/views/test.blade.php', 12),
                    ],
                ],
                'en' => [],
                'de' => [],
                'fr' => [],
            ],
            untranslatedKeysByLocale: ['it' => [], 'en' => [], 'de' => [], 'fr' => []],
            hardcodedStrings: [],
            asymmetricKeys: ['it' => [], 'en' => [], 'de' => [], 'fr' => []],
            unusedKeys: [],
            catalogKeyCounts: ['it' => 10, 'en' => 10, 'de' => 10, 'fr' => 10],
        );

        $mockAuditor->expects($this->once())
            ->method('audit')
            ->willReturn($fakeResult);

        $this->app->instance(TranslationAuditorInterface::class, $mockAuditor);

        $this->artisan('lang:audit')
            ->expectsOutputToContain('Missing Localization Keys Detected:')
            ->expectsOutputToContain('Missing String Example')
            ->expectsOutputToContain('✗ AUDIT FAILED: Missing localization keys found. Please translate or sync missing keys.')
            ->assertExitCode(1);
    }

    public function test_lang_audit_command_json_mode_fails_with_code_one_when_not_clean(): void
    {
        $mockAuditor = $this->createMock(TranslationAuditorInterface::class);

        $fakeResult = new TranslationAuditResult(
            filesScanned: 5,
            totalDiscoveredKeys: 2,
            missingKeysByLocale: [
                'it' => [
                    'Missing Key' => [new TranslationOccurrence('Missing Key', 'app/Test.php', 20)],
                ],
            ],
            untranslatedKeysByLocale: ['it' => []],
            hardcodedStrings: [],
            asymmetricKeys: ['it' => []],
            unusedKeys: [],
            catalogKeyCounts: ['it' => 5],
        );

        $mockAuditor->expects($this->once())
            ->method('audit')
            ->willReturn($fakeResult);

        $this->app->instance(TranslationAuditorInterface::class, $mockAuditor);

        $this->artisan('lang:audit', ['--json' => true])
            ->assertExitCode(1);
    }

    public function test_lang_audit_command_sync_option_triggers_synchronization(): void
    {
        $mockAuditor = $this->createMock(TranslationAuditorInterface::class);

        $mockAuditor->expects($this->once())
            ->method('syncMissingKeys')
            ->willReturn(['it' => 2, 'en' => 0, 'de' => 2, 'fr' => 2]);

        $mockAuditor->expects($this->once())
            ->method('audit')
            ->willReturn(new TranslationAuditResult(
                filesScanned: 1,
                totalDiscoveredKeys: 1,
                missingKeysByLocale: ['it' => [], 'en' => [], 'de' => [], 'fr' => []],
                catalogKeyCounts: ['it' => 10, 'en' => 10, 'de' => 10, 'fr' => 10],
            ));

        $this->app->instance(TranslationAuditorInterface::class, $mockAuditor);

        $this->artisan('lang:audit', ['--sync' => true])
            ->expectsOutputToContain('Synchronizing missing translation keys into JSON language catalogs:')
            ->expectsOutputToContain('[it]: added 2 missing keys.')
            ->assertExitCode(0);
    }

    public function test_lang_audit_command_displays_hardcoded_strings_when_flag_provided(): void
    {
        $mockAuditor = $this->createMock(TranslationAuditorInterface::class);

        $mockAuditor->expects($this->once())
            ->method('audit')
            ->willReturn(new TranslationAuditResult(
                filesScanned: 5,
                totalDiscoveredKeys: 3,
                missingKeysByLocale: ['it' => [], 'en' => []],
                untranslatedKeysByLocale: ['it' => [], 'en' => []],
                hardcodedStrings: [
                    new HardcodedStringOccurrence('Hardcoded Text Here', 'resources/views/test.blade.php', 15, '<button>'),
                ],
                catalogKeyCounts: ['it' => 10, 'en' => 10],
            ));

        $this->app->instance(TranslationAuditorInterface::class, $mockAuditor);

        $this->artisan('lang:audit', ['--show-hardcoded' => true])
            ->expectsOutputToContain('Potential Hardcoded UI Strings in Blade Templates:')
            ->expectsOutputToContain('Hardcoded Text Here')
            ->assertExitCode(0);
    }
}

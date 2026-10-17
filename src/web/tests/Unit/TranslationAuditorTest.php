<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Localization\Audit\Contracts\TranslationScannerInterface;
use App\Services\Localization\Audit\DTOs\HardcodedStringOccurrence;
use App\Services\Localization\Audit\DTOs\TranslationOccurrence;
use App\Services\Localization\Audit\Services\CodebaseTranslationScanner;
use App\Services\Localization\Audit\Services\LanguageCatalogLoader;
use App\Services\Localization\Audit\Services\TranslationAuditor;
use App\Services\Localization\Contracts\LocaleManagerInterface;
use Tests\TestCase;

class TranslationAuditorTest extends TestCase
{
    public function test_catalog_loader_returns_supported_locales(): void
    {
        $localeManager = $this->createMock(LocaleManagerInterface::class);
        $localeManager->method('getSupportedLocales')->willReturn(['it', 'en', 'de', 'fr']);

        $loader = new LanguageCatalogLoader($localeManager);

        $this->assertSame(['it', 'en', 'de', 'fr'], $loader->getSupportedLocales());
    }

    public function test_catalog_loader_reads_json_catalog_and_detects_keys(): void
    {
        $tempDir = sys_get_temp_dir().'/lang_test_'.uniqid();
        mkdir($tempDir, 0777, true);

        file_put_contents("{$tempDir}/en.json", json_encode(['Hello' => 'Hello', 'Save' => 'Save']));
        file_put_contents("{$tempDir}/it.json", json_encode(['Hello' => 'Ciao', 'Save' => 'Salva']));

        $localeManager = $this->createMock(LocaleManagerInterface::class);
        $localeManager->method('getSupportedLocales')->willReturn(['en', 'it']);

        $loader = new LanguageCatalogLoader($localeManager, $tempDir);

        $this->assertTrue($loader->hasKey('en', 'Hello'));
        $this->assertTrue($loader->hasKey('it', 'Save'));
        $this->assertFalse($loader->hasKey('en', 'NonExistentKey'));

        // Cleanup
        unlink("{$tempDir}/en.json");
        unlink("{$tempDir}/it.json");
        rmdir($tempDir);
    }

    public function test_catalog_loader_reads_php_catalog_files(): void
    {
        $tempDir = sys_get_temp_dir().'/lang_php_test_'.uniqid();
        mkdir("{$tempDir}/en", 0777, true);

        file_put_contents("{$tempDir}/en/ui.php", "<?php return ['buttons' => ['submit' => 'Submit', 'cancel' => 'Cancel']];");

        $localeManager = $this->createMock(LocaleManagerInterface::class);
        $localeManager->method('getSupportedLocales')->willReturn(['en']);

        $loader = new LanguageCatalogLoader($localeManager, $tempDir);

        $this->assertTrue($loader->hasKey('en', 'ui.buttons.submit'));
        $this->assertTrue($loader->hasKey('en', 'ui.buttons.cancel'));
        $this->assertFalse($loader->hasKey('en', 'ui.buttons.delete'));

        // Cleanup
        unlink("{$tempDir}/en/ui.php");
        rmdir("{$tempDir}/en");
        rmdir($tempDir);
    }

    public function test_catalog_loader_writes_sorted_json_catalog(): void
    {
        $tempDir = sys_get_temp_dir().'/lang_write_test_'.uniqid();
        mkdir($tempDir, 0777, true);

        $localeManager = $this->createMock(LocaleManagerInterface::class);
        $localeManager->method('getSupportedLocales')->willReturn(['en']);

        $loader = new LanguageCatalogLoader($localeManager, $tempDir);

        $loader->writeJsonCatalog('en', [
            'Zoo' => 'Zoo',
            'Apple' => 'Apple',
            'Mango' => 'Mango',
        ]);

        $written = json_decode((string) file_get_contents("{$tempDir}/en.json"), true);
        $keys = array_keys($written);

        $this->assertSame(['Apple', 'Mango', 'Zoo'], $keys);

        // Cleanup
        unlink("{$tempDir}/en.json");
        rmdir($tempDir);
    }

    public function test_codebase_scanner_detects_translation_helpers_in_files(): void
    {
        $tempDir = sys_get_temp_dir().'/scanner_test_'.uniqid();
        mkdir("{$tempDir}/views", 0777, true);

        $bladeContent = <<<'BLADE'
        <div>
            <h1>{{ __('Welcome to our site') }}</h1>
            <p>@lang('Please verify your email address')</p>
            <span>{{ trans('ui.login') }}</span>
            <button>{{ Lang::get('Save') }}</button>
        </div>
        BLADE;

        file_put_contents("{$tempDir}/views/test.blade.php", $bladeContent);

        $scanner = new CodebaseTranslationScanner($tempDir);
        $keys = $scanner->scanTranslationKeys(["{$tempDir}/views"]);

        $this->assertArrayHasKey('Welcome to our site', $keys);
        $this->assertArrayHasKey('Please verify your email address', $keys);
        $this->assertArrayHasKey('ui.login', $keys);
        $this->assertArrayHasKey('Save', $keys);

        $this->assertSame('__', $keys['Welcome to our site'][0]->helper);
        $this->assertSame(2, $keys['Welcome to our site'][0]->line);

        // Cleanup
        unlink("{$tempDir}/views/test.blade.php");
        rmdir("{$tempDir}/views");
        rmdir($tempDir);
    }

    public function test_codebase_scanner_detects_hardcoded_strings(): void
    {
        $tempDir = sys_get_temp_dir().'/scanner_hardcoded_test_'.uniqid();
        mkdir("{$tempDir}/views", 0777, true);

        $bladeContent = <<<'BLADE'
        <div>
            <flux:heading>Unlocalized Title</flux:heading>
            <p>This is a hardcoded paragraph that should be translated.</p>
            <button placeholder="Enter code">Click Me</button>
        </div>
        BLADE;

        file_put_contents("{$tempDir}/views/sample.blade.php", $bladeContent);

        $scanner = new CodebaseTranslationScanner($tempDir);
        $hardcoded = $scanner->scanHardcodedStrings(["{$tempDir}/views"]);

        $texts = array_map(fn (HardcodedStringOccurrence $h) => $h->text, $hardcoded);

        $this->assertContains('Unlocalized Title', $texts);
        $this->assertContains('This is a hardcoded paragraph that should be translated.', $texts);
        $this->assertContains('Click Me', $texts);

        // Cleanup
        unlink("{$tempDir}/views/sample.blade.php");
        rmdir("{$tempDir}/views");
        rmdir($tempDir);
    }

    public function test_auditor_identifies_missing_and_untranslated_keys(): void
    {
        $scanner = $this->createMock(TranslationScannerInterface::class);
        $scanner->method('scanTranslationKeys')->willReturn([
            'Common Key' => [new TranslationOccurrence('Common Key', 'views/a.blade.php', 1)],
            'Missing Everywhere' => [new TranslationOccurrence('Missing Everywhere', 'views/b.blade.php', 2)],
        ]);
        $scanner->method('getScannedFilesCount')->willReturn(2);

        $loader = $this->createMock(LanguageCatalogLoader::class);
        $loader->method('getSupportedLocales')->willReturn(['en', 'it']);
        $loader->method('getMergedCatalog')->willReturn(['Common Key' => 'Val']);
        $loader->method('getJsonCatalog')->willReturnMap([
            ['en', ['Common Key' => 'Common Key']],
            ['it', ['Common Key' => 'Chiave Comune']],
        ]);
        $loader->method('hasKey')->willReturnCallback(function (string $locale, string $key) {
            return $key === 'Common Key';
        });

        $auditor = new TranslationAuditor($scanner, $loader);
        $result = $auditor->audit(['en', 'it']);

        $this->assertFalse($result->isClean());
        $this->assertTrue($result->hasMissingKeys('en'));
        $this->assertTrue($result->hasMissingKeys('it'));
        $this->assertArrayHasKey('Missing Everywhere', $result->missingKeysByLocale['en']);
        $this->assertArrayHasKey('Missing Everywhere', $result->missingKeysByLocale['it']);
    }

    public function test_auditor_sync_missing_keys_populates_missing_entries(): void
    {
        $scanner = $this->createMock(TranslationScannerInterface::class);
        $scanner->method('scanTranslationKeys')->willReturn([
            'Key One' => [new TranslationOccurrence('Key One', 'views/a.blade.php', 1)],
            'Key Two' => [new TranslationOccurrence('Key Two', 'views/b.blade.php', 2)],
        ]);

        $loader = $this->createMock(LanguageCatalogLoader::class);
        $loader->method('getSupportedLocales')->willReturn(['en', 'it']);
        $loader->method('getJsonCatalog')->willReturnMap([
            ['en', ['Key One' => 'Key One']],
            ['it', []],
        ]);
        $loader->method('hasKey')->willReturnCallback(function (string $locale, string $key) {
            return $locale === 'en' && $key === 'Key One';
        });

        $loader->expects($this->exactly(2))
            ->method('writeJsonCatalog');

        $auditor = new TranslationAuditor($scanner, $loader);
        $counts = $auditor->syncMissingKeys(['en', 'it']);

        $this->assertSame(1, $counts['en']);
        $this->assertSame(2, $counts['it']);
    }
}

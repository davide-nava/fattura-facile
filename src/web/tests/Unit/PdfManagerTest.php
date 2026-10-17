<?php

declare(strict_types=1);

use App\Services\Pdf\Contracts\PdfGeneratorInterface;
use App\Services\Pdf\Drivers\BrowsershotGenerator;
use App\Services\Pdf\Drivers\DompdfGenerator;
use App\Services\Pdf\PdfManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
use Tests\TestCase;

uses(TestCase::class);

test('pdf manager resolves default driver from config', function () {
    Config::set('pdf.default', 'browsershot');

    /** @var PdfManager $manager */
    $manager = app(PdfManager::class);

    expect($manager->getDefaultDriver())->toBe('browsershot')
        ->and($manager->driver())->toBeInstanceOf(BrowsershotGenerator::class);
});

test('pdf manager resolves dompdf driver explicitly', function () {
    /** @var PdfManager $manager */
    $manager = app(PdfManager::class);

    $driver = $manager->driver('dompdf');

    expect($driver)->toBeInstanceOf(DompdfGenerator::class);
});

test('pdf manager throws exception for unsupported driver', function () {
    /** @var PdfManager $manager */
    $manager = app(PdfManager::class);

    $manager->driver('unsupported_driver');
})->throws(InvalidArgumentException::class, 'PDF Driver [unsupported_driver] not supported.');

test('pdf manager supports custom driver extension', function () {
    /** @var PdfManager $manager */
    $manager = app(PdfManager::class);

    $customMock = Mockery::mock(PdfGeneratorInterface::class);
    $manager->extend('custom_engine', fn () => $customMock);

    expect($manager->driver('custom_engine'))->toBe($customMock);
});

test('pdf manager supports setting explicit driver instance', function () {
    /** @var PdfManager $manager */
    $manager = app(PdfManager::class);

    $mockDriver = Mockery::mock(PdfGeneratorInterface::class);
    $manager->setDriver('mocked', $mockDriver);

    expect($manager->driver('mocked'))->toBe($mockDriver);
});

test('pdf manager executes fallback driver when primary driver throws exception', function () {
    Config::set('pdf.default', 'primary_failing');
    Config::set('pdf.fallback', 'fallback_driver');

    /** @var PdfManager $manager */
    $manager = app(PdfManager::class);

    $failingPrimary = Mockery::mock(PdfGeneratorInterface::class);
    $failingPrimary->shouldReceive('generateFromHtml')
        ->once()
        ->andThrow(new RuntimeException('Puppeteer process failed'));

    $workingFallback = Mockery::mock(PdfGeneratorInterface::class);
    $workingFallback->shouldReceive('generateFromHtml')
        ->once()
        ->with('<h1>Test</h1>', [])
        ->andReturn('%PDF-1.4 Fallback Content');

    $manager->setDriver('primary_failing', $failingPrimary);
    $manager->setDriver('fallback_driver', $workingFallback);

    Log::shouldReceive('warning')
        ->once()
        ->with(Mockery::pattern('/Primary PDF driver \[primary_failing\] failed/'));

    $pdf = $manager->generateFromHtml('<h1>Test</h1>');

    expect($pdf)->toBe('%PDF-1.4 Fallback Content');
});

test('pdf manager generates download and stream responses with appropriate headers', function () {
    /** @var PdfManager $manager */
    $manager = app(PdfManager::class);

    $mockDriver = Mockery::mock(PdfGeneratorInterface::class);
    $mockDriver->shouldReceive('download')
        ->once()
        ->with('dummy.view', [], 'invoice.pdf', [])
        ->andReturn(response('PDF_BINARY', 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="invoice.pdf"',
        ]));

    $mockDriver->shouldReceive('stream')
        ->once()
        ->with('dummy.view', [], 'invoice.pdf', [])
        ->andReturn(response('PDF_BINARY', 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="invoice.pdf"',
        ]));

    $manager->setDriver($manager->getDefaultDriver(), $mockDriver);

    $downloadResponse = $manager->download('dummy.view', [], 'invoice.pdf');
    expect($downloadResponse->getStatusCode())->toBe(200)
        ->and($downloadResponse->headers->get('Content-Type'))->toBe('application/pdf')
        ->and($downloadResponse->headers->get('Content-Disposition'))->toBe('attachment; filename="invoice.pdf"');

    $streamResponse = $manager->stream('dummy.view', [], 'invoice.pdf');
    expect($streamResponse->getStatusCode())->toBe(200)
        ->and($streamResponse->headers->get('Content-Type'))->toBe('application/pdf')
        ->and($streamResponse->headers->get('Content-Disposition'))->toBe('inline; filename="invoice.pdf"');
});

test('browsershot generator configures margins and orientation options', function () {
    $generator = new BrowsershotGenerator([
        'no_sandbox' => true,
        'timeout' => 45,
    ]);

    $browsershot = $generator->createBrowsershot('<div>Sample HTML</div>', [
        'format' => 'A4',
        'orientation' => 'landscape',
        'margins' => ['top' => 15, 'right' => 20, 'bottom' => 15, 'left' => 20],
    ]);

    expect($browsershot)->toBeInstanceOf(Browsershot::class);
});

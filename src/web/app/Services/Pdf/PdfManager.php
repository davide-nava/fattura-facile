<?php

declare(strict_types=1);

namespace App\Services\Pdf;

use App\Services\Pdf\Contracts\PdfGeneratorInterface;
use App\Services\Pdf\Drivers\BrowsershotGenerator;
use App\Services\Pdf\Drivers\DompdfGenerator;
use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PdfManager implements PdfGeneratorInterface
{
    /**
     * The array of resolved drivers.
     *
     * @var array<string, PdfGeneratorInterface>
     */
    protected array $drivers = [];

    /**
     * The registered custom driver creators.
     *
     * @var array<string, Closure>
     */
    protected array $customCreators = [];

    public function __construct(
        protected Application $app
    ) {}

    /**
     * Get a driver instance by name.
     */
    public function driver(?string $driver = null): PdfGeneratorInterface
    {
        $driver = $driver ?: $this->getDefaultDriver();

        if (! isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->resolve($driver);
        }

        return $this->drivers[$driver];
    }

    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return (string) config('pdf.default', 'browsershot');
    }

    /**
     * Get the fallback driver name.
     */
    public function getFallbackDriver(): ?string
    {
        $fallback = config('pdf.fallback', 'dompdf');

        return $fallback ? (string) $fallback : null;
    }

    /**
     * Resolve the given driver.
     *
     * @throws InvalidArgumentException
     */
    protected function resolve(string $driver): PdfGeneratorInterface
    {
        if (isset($this->customCreators[$driver])) {
            return $this->customCreators[$driver]($this->app);
        }

        $method = 'create'.ucfirst($driver).'Driver';
        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        throw new InvalidArgumentException("PDF Driver [{$driver}] not supported.");
    }

    /**
     * Create an instance of the Browsershot PDF driver.
     */
    protected function createBrowsershotDriver(): BrowsershotGenerator
    {
        $config = (array) config('pdf.drivers.browsershot', []);

        return new BrowsershotGenerator($config);
    }

    /**
     * Create an instance of the Dompdf PDF driver.
     */
    protected function createDompdfDriver(): DompdfGenerator
    {
        $config = (array) config('pdf.drivers.dompdf', []);

        return new DompdfGenerator($config);
    }

    /**
     * Register a custom driver creator Closure.
     */
    public function extend(string $driver, Closure $callback): self
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Explicitly set an instance of a driver (useful for tests/mocking).
     */
    public function setDriver(string $name, PdfGeneratorInterface $driver): self
    {
        $this->drivers[$name] = $driver;

        return $this;
    }

    /**
     * Execute a callback on the primary driver, falling back to the fallback driver if execution fails.
     *
     * @template T
     *
     * @param  callable(PdfGeneratorInterface): T  $callback
     * @return T
     */
    protected function executeWithFallback(callable $callback): mixed
    {
        $primaryDriverName = $this->getDefaultDriver();
        $primary = $this->driver($primaryDriverName);
        $fallbackName = $this->getFallbackDriver();

        try {
            return $callback($primary);
        } catch (Throwable $e) {
            if ($fallbackName && $fallbackName !== $primaryDriverName) {
                Log::warning("Primary PDF driver [{$primaryDriverName}] failed. Attempting fallback [{$fallbackName}]. Error: ".$e->getMessage());

                return $callback($this->driver($fallbackName));
            }

            throw $e;
        }
    }

    /**
     * Generate raw PDF binary data from HTML string.
     *
     * @param  array<string, mixed>  $options
     */
    public function generateFromHtml(string $html, array $options = []): string
    {
        return $this->executeWithFallback(fn (PdfGeneratorInterface $driver): string => $driver->generateFromHtml($html, $options));
    }

    /**
     * Generate raw PDF binary data from a Blade view.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $options
     */
    public function generateFromView(string|View $view, array $data = [], array $options = []): string
    {
        return $this->executeWithFallback(fn (PdfGeneratorInterface $driver): string => $driver->generateFromView($view, $data, $options));
    }

    /**
     * Create an HTTP response to download the generated PDF.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $options
     */
    public function download(string|View $view, array $data = [], string $filename = 'document.pdf', array $options = []): Response
    {
        return $this->executeWithFallback(fn (PdfGeneratorInterface $driver): Response => $driver->download($view, $data, $filename, $options));
    }

    /**
     * Create an HTTP response to stream the generated PDF inline.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $options
     */
    public function stream(string|View $view, array $data = [], string $filename = 'document.pdf', array $options = []): Response
    {
        return $this->executeWithFallback(fn (PdfGeneratorInterface $driver): Response => $driver->stream($view, $data, $filename, $options));
    }
}

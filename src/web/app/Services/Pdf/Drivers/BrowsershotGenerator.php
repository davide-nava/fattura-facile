<?php

declare(strict_types=1);

namespace App\Services\Pdf\Drivers;

use App\Services\Pdf\Contracts\PdfGeneratorInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\Response;

class BrowsershotGenerator implements PdfGeneratorInterface
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        protected array $config = []
    ) {}

    /**
     * Generate raw PDF binary data from HTML string.
     *
     * @param  array<string, mixed>  $options
     */
    public function generateFromHtml(string $html, array $options = []): string
    {
        $browsershot = $this->createBrowsershot($html, $options);

        return $browsershot->pdf();
    }

    /**
     * Generate raw PDF binary data from a Blade view.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $options
     */
    public function generateFromView(string|View $view, array $data = [], array $options = []): string
    {
        $data['isPdf'] = true;

        if ($view instanceof View) {
            $html = $view->with($data)->render();
        } else {
            /** @var view-string $viewName */
            $viewName = $view;
            $html = ViewFacade::make($viewName, $data)->render();
        }

        return $this->generateFromHtml($html, $options);
    }

    /**
     * Create an HTTP response to download the generated PDF.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $options
     */
    public function download(string|View $view, array $data = [], string $filename = 'document.pdf', array $options = []): Response
    {
        $pdfContent = $this->generateFromView($view, $data, $options);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Create an HTTP response to stream the generated PDF inline.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $options
     */
    public function stream(string|View $view, array $data = [], string $filename = 'document.pdf', array $options = []): Response
    {
        $pdfContent = $this->generateFromView($view, $data, $options);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Build and configure the underlying Browsershot instance.
     *
     * @param  array<string, mixed>  $options
     */
    public function createBrowsershot(string $html, array $options = []): Browsershot
    {
        $browsershot = Browsershot::html($html);

        if (! empty($this->config['node_binary'])) {
            $browsershot->setNodeBinary((string) $this->config['node_binary']);
        }

        if (! empty($this->config['npm_binary'])) {
            $browsershot->setNpmBinary((string) $this->config['npm_binary']);
        }

        if (! empty($this->config['node_modules_path'])) {
            $browsershot->setNodeModulePath((string) $this->config['node_modules_path']);
        }

        if (! empty($this->config['chrome_path'])) {
            $browsershot->setChromePath((string) $this->config['chrome_path']);
        }

        if ($this->config['no_sandbox'] ?? true) {
            $browsershot->noSandbox();
        }

        if (! empty($this->config['temp_path'])) {
            if (! is_dir($this->config['temp_path'])) {
                @mkdir($this->config['temp_path'], 0755, true);
            }
            $browsershot->setCustomTempPath((string) $this->config['temp_path']);
        }

        if (! empty($this->config['timeout'])) {
            $browsershot->timeout((int) $this->config['timeout']);
        }

        $defaultOptions = $this->config['default_options'] ?? [];
        $mergedOptions = array_merge($defaultOptions, $options);

        $format = $mergedOptions['format'] ?? 'A4';
        $browsershot->format((string) $format);

        $orientation = strtolower((string) ($mergedOptions['orientation'] ?? 'portrait'));
        if ($orientation === 'landscape') {
            $browsershot->landscape();
        }

        if ($mergedOptions['print_background'] ?? true) {
            $browsershot->showBackground();
        }

        $margins = $mergedOptions['margins'] ?? ['top' => 10, 'right' => 10, 'bottom' => 10, 'left' => 10];
        $browsershot->margins(
            (float) ($margins['top'] ?? 10),
            (float) ($margins['right'] ?? 10),
            (float) ($margins['bottom'] ?? 10),
            (float) ($margins['left'] ?? 10)
        );

        return $browsershot;
    }
}

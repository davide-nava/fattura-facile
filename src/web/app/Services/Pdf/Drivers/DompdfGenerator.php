<?php

declare(strict_types=1);

namespace App\Services\Pdf\Drivers;

use App\Services\Pdf\Contracts\PdfGeneratorInterface;
use Barryvdh\DomPDF\Facade\Pdf as DomPdfFacade;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;

class DompdfGenerator implements PdfGeneratorInterface
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
        $mergedOptions = array_merge($this->config['default_options'] ?? [], $options);
        $paper = strtolower((string) ($mergedOptions['format'] ?? 'a4'));
        $orientation = strtolower((string) ($mergedOptions['orientation'] ?? 'portrait'));

        $pdf = DomPdfFacade::loadHTML($html)->setPaper($paper, $orientation);

        return $pdf->output();
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

        $mergedOptions = array_merge($this->config['default_options'] ?? [], $options);
        $paper = strtolower((string) ($mergedOptions['format'] ?? 'a4'));
        $orientation = strtolower((string) ($mergedOptions['orientation'] ?? 'portrait'));

        if ($view instanceof View) {
            $html = $view->with($data)->render();
            $pdf = DomPdfFacade::loadHTML($html)->setPaper($paper, $orientation);
        } else {
            /** @var view-string $viewName */
            $viewName = $view;
            $pdf = DomPdfFacade::loadView($viewName, $data)->setPaper($paper, $orientation);
        }

        return $pdf->output();
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
}

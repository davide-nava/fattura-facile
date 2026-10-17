<?php

declare(strict_types=1);

namespace App\Services\Pdf\Contracts;

use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;

interface PdfGeneratorInterface
{
    /**
     * Generate raw PDF binary data from HTML string.
     *
     * @param  array<string, mixed>  $options
     */
    public function generateFromHtml(string $html, array $options = []): string;

    /**
     * Generate raw PDF binary data from a Blade view.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $options
     */
    public function generateFromView(string|View $view, array $data = [], array $options = []): string;

    /**
     * Create an HTTP response to download the generated PDF.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $options
     */
    public function download(string|View $view, array $data = [], string $filename = 'document.pdf', array $options = []): Response;

    /**
     * Create an HTTP response to stream the generated PDF inline.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $options
     */
    public function stream(string|View $view, array $data = [], string $filename = 'document.pdf', array $options = []): Response;
}

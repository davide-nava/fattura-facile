<?php

declare(strict_types=1);

namespace App\Services\Pdf\Facades;

use App\Services\Pdf\Contracts\PdfGeneratorInterface;
use App\Services\Pdf\PdfManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string generateFromHtml(string $html, array<string, mixed> $options = [])
 * @method static string generateFromView(\Illuminate\Contracts\View\View|string $view, array<string, mixed> $data = [], array<string, mixed> $options = [])
 * @method static \Symfony\Component\HttpFoundation\Response download(\Illuminate\Contracts\View\View|string $view, array<string, mixed> $data = [], string $filename = 'document.pdf', array<string, mixed> $options = [])
 * @method static \Symfony\Component\HttpFoundation\Response stream(\Illuminate\Contracts\View\View|string $view, array<string, mixed> $data = [], string $filename = 'document.pdf', array<string, mixed> $options = [])
 * @method static \App\Services\Pdf\Contracts\PdfGeneratorInterface driver(?string $driver = null)
 *
 * @see PdfManager
 */
class Pdf extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PdfGeneratorInterface::class;
    }
}

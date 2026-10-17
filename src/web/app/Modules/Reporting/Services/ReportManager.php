<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Services;

use App\Modules\Companies\Models\Company;
use App\Modules\Reporting\Contracts\ReportInterface;
use App\Modules\Reporting\DTOs\ReportResult;
use App\Modules\Reporting\Reports\FinancialSummaryReport;
use App\Modules\Reporting\Reports\InvoiceJournalReport;
use App\Modules\Reporting\Reports\TaxSummaryReport;
use App\Services\Pdf\Contracts\PdfGeneratorInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class ReportManager
{
    /**
     * @var array<string, ReportInterface>
     */
    protected array $reports = [];

    public function __construct(
        protected Application $app,
        protected PdfGeneratorInterface $pdfGenerator,
    ) {
        $this->registerDefaultReports();
    }

    /**
     * Register core built-in domain reports.
     */
    protected function registerDefaultReports(): void
    {
        $this->register($this->app->make(FinancialSummaryReport::class));
        $this->register($this->app->make(InvoiceJournalReport::class));
        $this->register($this->app->make(TaxSummaryReport::class));
    }

    /**
     * Register a new or custom report.
     */
    public function register(ReportInterface $report): self
    {
        $this->reports[$report->key()] = $report;

        return $this;
    }

    /**
     * Get all registered reports.
     *
     * @return Collection<string, ReportInterface>
     */
    public function all(): Collection
    {
        return collect($this->reports);
    }

    /**
     * Get a report instance by its unique key.
     */
    public function get(string $key): ?ReportInterface
    {
        return $this->reports[$key] ?? null;
    }

    /**
     * Find a report or throw an InvalidArgumentException.
     *
     * @throws InvalidArgumentException
     */
    public function findOrFail(string $key): ReportInterface
    {
        $report = $this->get($key);

        if ($report === null) {
            throw new InvalidArgumentException("Report [{$key}] not found.");
        }

        return $report;
    }

    /**
     * Build report dataset using the report use-case.
     *
     * @param  array<string, mixed>  $parameters
     */
    public function generate(string $key, Company $company, array $parameters = []): ReportResult
    {
        return $this->findOrFail($key)->generate($company, $parameters);
    }

    /**
     * Render the report as HTML string.
     *
     * @param  array<string, mixed>  $parameters
     */
    public function renderHtml(string $key, Company $company, array $parameters = []): string
    {
        $report = $this->findOrFail($key);
        $result = $report->generate($company, $parameters);
        /** @var view-string $viewName */
        $viewName = $report->view();

        return View::make($viewName, $result->toViewData())->render();
    }

    /**
     * Generate raw PDF binary data for the specified report.
     *
     * @param  array<string, mixed>  $parameters
     */
    public function renderPdf(string $key, Company $company, array $parameters = []): string
    {
        $report = $this->findOrFail($key);
        $result = $report->generate($company, $parameters);

        return $this->pdfGenerator->generateFromView(
            $report->view(),
            $result->toViewData(),
            [
                'format' => $report->paperSize(),
                'orientation' => $report->paperOrientation(),
            ]
        );
    }

    /**
     * Return an HTTP response to download the generated report as a PDF attachment.
     *
     * @param  array<string, mixed>  $parameters
     */
    public function downloadPdf(string $key, Company $company, array $parameters = [], ?string $filename = null): Response
    {
        $report = $this->findOrFail($key);
        $result = $report->generate($company, $parameters);
        $filename = $filename ?: "Report-{$key}-{$result->generatedAt->format('Ymd-His')}.pdf";

        return $this->pdfGenerator->download(
            $report->view(),
            $result->toViewData(),
            $filename,
            [
                'format' => $report->paperSize(),
                'orientation' => $report->paperOrientation(),
            ]
        );
    }

    /**
     * Return an HTTP response to stream the report as PDF inline in the browser.
     *
     * @param  array<string, mixed>  $parameters
     */
    public function streamPdf(string $key, Company $company, array $parameters = [], ?string $filename = null): Response
    {
        $report = $this->findOrFail($key);
        $result = $report->generate($company, $parameters);
        $filename = $filename ?: "Report-{$key}-{$result->generatedAt->format('Ymd-His')}.pdf";

        return $this->pdfGenerator->stream(
            $report->view(),
            $result->toViewData(),
            $filename,
            [
                'format' => $report->paperSize(),
                'orientation' => $report->paperOrientation(),
            ]
        );
    }
}

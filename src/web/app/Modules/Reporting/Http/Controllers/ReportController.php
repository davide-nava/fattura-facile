<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Companies\Models\Company;
use App\Modules\Reporting\Services\ReportManager;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function __construct(
        protected ReportManager $reportManager,
        protected TenantContext $tenantContext,
    ) {}

    /**
     * Resolve company from request or active tenant context.
     */
    protected function resolveCompany(Request $request): Company
    {
        $query = Company::query();

        if ($request->filled('company_id')) {
            /** @var Company $company */
            $company = $query->findOrFail((int) $request->input('company_id'));

            return $company;
        }

        /** @var Company $company */
        $company = $query->where('is_active', true)->first() ?? $query->firstOrFail();

        return $company;
    }

    /**
     * Display the list of available reports.
     */
    public function index(Request $request): View
    {
        $company = Company::where('is_active', true)->first() ?? Company::first();
        $branding = $company?->getBranding();
        $reports = $this->reportManager->all();

        return view('reports.index', [
            'reports' => $reports,
            'company' => $company,
            'branding' => $branding,
        ]);
    }

    /**
     * Preview report in responsive HTML print mode.
     */
    public function preview(Request $request, string $report): Response|View
    {
        try {
            $reportInstance = $this->reportManager->findOrFail($report);
        } catch (InvalidArgumentException) {
            abort(404, "Report [{$report}] not found.");
        }

        if ($request->boolean('download')) {
            return $this->download($request, $report);
        }

        $company = $this->resolveCompany($request);
        $parameters = $request->all();

        $result = $reportInstance->generate($company, $parameters);

        /** @var view-string $viewName */
        $viewName = $reportInstance->view();

        return view($viewName, $result->toViewData());
    }

    /**
     * Download report as a PDF attachment.
     */
    public function download(Request $request, string $report): Response
    {
        try {
            $company = $this->resolveCompany($request);
            $parameters = $request->all();

            return $this->reportManager->downloadPdf($report, $company, $parameters);
        } catch (InvalidArgumentException) {
            abort(404, "Report [{$report}] not found.");
        }
    }

    /**
     * Stream report inline in the browser as a PDF document.
     */
    public function stream(Request $request, string $report): Response
    {
        try {
            $company = $this->resolveCompany($request);
            $parameters = $request->all();

            return $this->reportManager->streamPdf($report, $company, $parameters);
        } catch (InvalidArgumentException) {
            abort(404, "Report [{$report}] not found.");
        }
    }
}

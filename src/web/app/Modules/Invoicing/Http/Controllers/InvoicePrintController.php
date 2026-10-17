<?php

declare(strict_types=1);

namespace App\Modules\Invoicing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Companies\Models\Company;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Invoicing\Services\InvoicePdfService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class InvoicePrintController extends Controller
{
    public function __construct(
        protected InvoicePdfService $invoicePdfService
    ) {}

    /**
     * Preview invoice in HTML print mode.
     */
    public function preview(Request $request, Invoice $invoice): Response|View
    {
        $invoice->loadMissing(['customer', 'company', 'items']);

        if ($request->boolean('download')) {
            return $this->download($request, $invoice);
        }

        /** @var Company|null $company */
        $company = $invoice->company;
        $branding = $company?->getBranding();

        return view('invoices.pdf', [
            'invoice' => $invoice,
            'company' => $company,
            'customer' => $invoice->customer,
            'branding' => $branding,
        ]);
    }

    /**
     * Download invoice as PDF attachment.
     */
    public function download(Request $request, Invoice $invoice): Response
    {
        return $this->invoicePdfService->downloadPdf($invoice);
    }

    /**
     * Stream invoice inline as PDF in the browser.
     */
    public function stream(Request $request, Invoice $invoice): Response
    {
        return $this->invoicePdfService->streamPdf($invoice);
    }
}

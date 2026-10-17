<?php

declare(strict_types=1);

namespace App\Modules\Invoicing\Services;

use App\Modules\Companies\Models\Company;
use App\Modules\Invoicing\Models\Invoice;
use App\Services\Pdf\Contracts\PdfGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;

class InvoicePdfService
{
    public function __construct(
        protected PdfGeneratorInterface $pdfGenerator
    ) {}

    /**
     * Render the invoice PDF binary string.
     */
    public function generatePdf(Invoice $invoice): string
    {
        $invoice->loadMissing(['customer', 'company', 'items']);
        /** @var Company|null $company */
        $company = $invoice->company;
        $branding = $company?->getBranding();

        return $this->pdfGenerator->generateFromView('invoices.pdf', [
            'invoice' => $invoice,
            'company' => $company,
            'customer' => $invoice->customer,
            'branding' => $branding,
        ], [
            'format' => 'A4',
            'orientation' => 'portrait',
        ]);
    }

    /**
     * Return an HTTP response to download the PDF.
     */
    public function downloadPdf(Invoice $invoice): Response
    {
        $invoice->loadMissing(['customer', 'company', 'items']);
        /** @var Company|null $company */
        $company = $invoice->company;
        $branding = $company?->getBranding();
        $filename = "Invoice-{$invoice->invoice_number}.pdf";

        return $this->pdfGenerator->download('invoices.pdf', [
            'invoice' => $invoice,
            'company' => $company,
            'customer' => $invoice->customer,
            'branding' => $branding,
        ], $filename, [
            'format' => 'A4',
            'orientation' => 'portrait',
        ]);
    }

    /**
     * Return an HTTP response to stream the PDF inline in the browser.
     */
    public function streamPdf(Invoice $invoice): Response
    {
        $invoice->loadMissing(['customer', 'company', 'items']);
        /** @var Company|null $company */
        $company = $invoice->company;
        $branding = $company?->getBranding();
        $filename = "Invoice-{$invoice->invoice_number}.pdf";

        return $this->pdfGenerator->stream('invoices.pdf', [
            'invoice' => $invoice,
            'company' => $company,
            'customer' => $invoice->customer,
            'branding' => $branding,
        ], $filename, [
            'format' => 'A4',
            'orientation' => 'portrait',
        ]);
    }
}

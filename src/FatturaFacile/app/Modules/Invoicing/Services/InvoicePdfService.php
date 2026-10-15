<?php

namespace App\Modules\Invoicing\Services;

use App\Modules\Invoicing\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class InvoicePdfService
{
    /**
     * Render the invoice PDF binary string.
     */
    public function generatePdf(Invoice $invoice): string
    {
        $invoice->loadMissing(['customer', 'company', 'items']);

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'company' => $invoice->company,
            'customer' => $invoice->customer,
        ])->setPaper('a4', 'portrait');

        return $pdf->output();
    }

    /**
     * Return an HTTP response to stream or download the PDF.
     */
    public function downloadPdf(Invoice $invoice): Response
    {
        $pdfContent = $this->generatePdf($invoice);
        $filename = "Invoice-{$invoice->invoice_number}.pdf";

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}

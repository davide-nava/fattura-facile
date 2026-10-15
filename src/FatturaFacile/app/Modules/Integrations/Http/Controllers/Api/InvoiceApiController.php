<?php

namespace App\Modules\Integrations\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Invoicing\Services\InvoicePdfService;
use App\Modules\Invoicing\Services\InvoiceService;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InvoiceApiController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService,
        private InvoicePdfService $pdfService
    ) {}

    public function index(Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->requireTenantId();

        $query = Invoice::where('tenant_id', $tenantId)
            ->with(['customer:id,name,code', 'items']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json($query->paginate(25));
    }

    public function show(int $id, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->requireTenantId();
        $invoice = Invoice::where('tenant_id', $tenantId)
            ->with(['customer', 'company', 'items'])
            ->findOrFail($id);

        return response()->json(['data' => $invoice]);
    }

    public function store(Request $request, TenantContext $tenantContext): JsonResponse
    {
        $request->validate([
            'company_id' => 'required|integer',
            'customer_id' => 'required|integer',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric',
            'currency' => 'nullable|string|size:3',
            'invoice_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $invoice = $this->invoiceService->createInvoice(
            $request->input('company_id'),
            $request->input('customer_id'),
            $request->input('items'),
            $request->input('invoice_date'),
            $request->input('due_date'),
            $request->input('currency', 'CHF'),
            $request->input('notes')
        );

        return response()->json(['data' => $invoice->load('items')], 201);
    }

    public function issue(int $id, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->requireTenantId();
        Invoice::where('tenant_id', $tenantId)->findOrFail($id);

        $issued = $this->invoiceService->issueInvoice($id);

        return response()->json(['data' => $issued]);
    }

    public function downloadPdf(int $id, TenantContext $tenantContext): Response
    {
        $tenantId = $tenantContext->requireTenantId();
        $invoice = Invoice::where('tenant_id', $tenantId)->findOrFail($id);

        return $this->pdfService->downloadPdf($invoice);
    }
}

<?php

namespace App\Modules\CrmContacts\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Modules\CrmContacts\Handlers\GetPortalCustomerDashboardHandler;
use App\Modules\CrmContacts\Models\CustomerPortalUser;
use App\Modules\CrmContacts\Queries\GetPortalCustomerDashboardQuery;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Invoicing\Services\InvoicePdfService;
use App\Modules\Sales\Models\Quote;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PortalDashboardController extends Controller
{
    private function getAuthenticatedPortalUser(): CustomerPortalUser
    {
        /** @var CustomerPortalUser $user */
        $user = Auth::guard('customer_portal')->user();

        return $user;
    }

    public function dashboard(
        Request $request,
        GetPortalCustomerDashboardHandler $handler
    ): View|JsonResponse {
        $portalUser = $this->getAuthenticatedPortalUser();
        $query = new GetPortalCustomerDashboardQuery(portalUserId: $portalUser->id);
        $dashboardData = $handler->handle($query);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($dashboardData->toArray());
        }

        return view('portal.dashboard', [
            'portalUser' => $portalUser,
            'data' => $dashboardData,
        ]);
    }

    public function invoices(Request $request): View|JsonResponse
    {
        $portalUser = $this->getAuthenticatedPortalUser();
        $invoices = Invoice::where('customer_id', $portalUser->customer_id)
            ->latest('invoice_date')
            ->paginate(15);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($invoices);
        }

        return view('portal.invoices', [
            'portalUser' => $portalUser,
            'invoices' => $invoices,
        ]);
    }

    public function quotes(Request $request): View|JsonResponse
    {
        $portalUser = $this->getAuthenticatedPortalUser();
        $quotes = Quote::where('customer_id', $portalUser->customer_id)
            ->latest()
            ->paginate(15);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($quotes);
        }

        return view('portal.quotes', [
            'portalUser' => $portalUser,
            'quotes' => $quotes,
        ]);
    }

    public function downloadInvoice(
        int $id,
        InvoicePdfService $pdfService
    ): Response|JsonResponse {
        $portalUser = $this->getAuthenticatedPortalUser();
        /** @var Invoice $invoice */
        $invoice = Invoice::where('customer_id', $portalUser->customer_id)
            ->findOrFail($id);

        return $pdfService->downloadPdf($invoice);
    }

    public function showProfile(): View|JsonResponse
    {
        $portalUser = $this->getAuthenticatedPortalUser();
        $customer = $portalUser->customer;

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'portal_user' => $portalUser,
                'customer' => $customer,
            ]);
        }

        return view('portal.profile', [
            'portalUser' => $portalUser,
            'customer' => $customer,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse|JsonResponse
    {
        $portalUser = $this->getAuthenticatedPortalUser();
        $customer = $portalUser->customer;

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'street' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
        ]);

        if (! empty($validated['name'])) {
            $portalUser->update(['name' => $validated['name']]);
        }

        $customer->update([
            'phone' => $validated['phone'] ?? $customer->phone,
            'street' => $validated['street'] ?? $customer->street,
            'city' => $validated['city'] ?? $customer->city,
            'postal_code' => $validated['postal_code'] ?? $customer->postal_code,
        ]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Profile updated successfully.',
                'customer' => $customer->fresh(),
            ]);
        }

        return redirect()->route('portal.profile')->with('status', 'Profile updated successfully.');
    }
}

<?php

namespace App\Modules\CrmContacts\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\CRM\Models\Lead;
use App\Modules\CrmContacts\Commands\ConvertLeadToCustomerCommand;
use App\Modules\CrmContacts\Commands\CreateLeadCommand;
use App\Modules\CrmContacts\DTOs\LeadData;
use App\Modules\CrmContacts\Handlers\ConvertLeadToCustomerHandler;
use App\Modules\CrmContacts\Handlers\CreateLeadHandler;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadApiController extends Controller
{
    public function index(Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->requireTenantId();
        $query = Lead::where('tenant_id', $tenantId)->with(['assignedUser', 'convertedCustomer']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($search, $operator) {
                $q->where('title', $operator, "%{$search}%")
                    ->orWhere('contact_name', $operator, "%{$search}%")
                    ->orWhere('company_name', $operator, "%{$search}%")
                    ->orWhere('email', $operator, "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json($query->paginate(25));
    }

    public function show(int $id, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->requireTenantId();
        $lead = Lead::where('tenant_id', $tenantId)
            ->with(['assignedUser', 'opportunities', 'activities', 'contactPersons', 'convertedCustomer'])
            ->findOrFail($id);

        return response()->json(['data' => $lead]);
    }

    public function store(
        Request $request,
        TenantContext $tenantContext,
        CreateLeadHandler $handler
    ): JsonResponse {
        $validated = $request->validate([
            'company_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:new,contacted,qualified,unqualified,converted',
            'source' => 'nullable|string|max:100',
            'assigned_to' => 'nullable|integer',
            'notes' => 'nullable|string',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'integer',
        ]);

        $data = LeadData::fromArray($validated);
        $lead = $handler->handle(new CreateLeadCommand($data));

        return response()->json(['data' => $lead], 201);
    }

    public function convert(
        int $id,
        Request $request,
        TenantContext $tenantContext,
        ConvertLeadToCustomerHandler $handler
    ): JsonResponse {
        $validated = $request->validate([
            'customer_code' => 'nullable|string|max:50',
            'currency' => 'nullable|string|size:3',
            'payment_term_days' => 'nullable|integer|min:0',
        ]);

        $customer = $handler->handle(new ConvertLeadToCustomerCommand(
            leadId: $id,
            customerCode: $validated['customer_code'] ?? null,
            currency: $validated['currency'] ?? 'CHF',
            paymentTermDays: (int) ($validated['payment_term_days'] ?? 30)
        ));

        return response()->json([
            'message' => 'Lead successfully converted to customer.',
            'customer' => $customer,
        ]);
    }
}

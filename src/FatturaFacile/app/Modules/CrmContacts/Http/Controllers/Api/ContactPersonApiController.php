<?php

namespace App\Modules\CrmContacts\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\CrmContacts\Commands\CreateContactPersonCommand;
use App\Modules\CrmContacts\Commands\UpdateContactPersonCommand;
use App\Modules\CrmContacts\DTOs\ContactPersonData;
use App\Modules\CrmContacts\Handlers\CreateContactPersonHandler;
use App\Modules\CrmContacts\Handlers\UpdateContactPersonHandler;
use App\Modules\CrmContacts\Models\ContactPerson;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactPersonApiController extends Controller
{
    public function index(Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->requireTenantId();
        $query = ContactPerson::where('tenant_id', $tenantId);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($search, $operator) {
                $q->where('first_name', $operator, "%{$search}%")
                    ->orWhere('last_name', $operator, "%{$search}%")
                    ->orWhere('email', $operator, "%{$search}%");
            });
        }

        if ($request->filled('contactable_type') && $request->filled('contactable_id')) {
            $query->where('contactable_type', $request->input('contactable_type'))
                ->where('contactable_id', $request->input('contactable_id'));
        }

        return response()->json($query->paginate(25));
    }

    public function show(int $id, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->requireTenantId();
        $contact = ContactPerson::where('tenant_id', $tenantId)->findOrFail($id);

        return response()->json(['data' => $contact]);
    }

    public function store(
        Request $request,
        TenantContext $tenantContext,
        CreateContactPersonHandler $handler
    ): JsonResponse {
        $validated = $request->validate([
            'company_id' => 'required|integer',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'contactable_type' => 'nullable|string',
            'contactable_id' => 'nullable|integer',
            'job_title' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'is_primary' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'integer',
        ]);

        $data = ContactPersonData::fromArray($validated);
        $contact = $handler->handle(new CreateContactPersonCommand($data));

        return response()->json(['data' => $contact], 201);
    }

    public function update(
        int $id,
        Request $request,
        TenantContext $tenantContext,
        UpdateContactPersonHandler $handler
    ): JsonResponse {
        $validated = $request->validate([
            'company_id' => 'required|integer',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'contactable_type' => 'nullable|string',
            'contactable_id' => 'nullable|integer',
            'job_title' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'is_primary' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'integer',
        ]);

        $data = ContactPersonData::fromArray($validated);
        $contact = $handler->handle(new UpdateContactPersonCommand($id, $data));

        return response()->json(['data' => $contact]);
    }
}

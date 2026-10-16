<?php

namespace App\Modules\CrmContacts\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\CrmContacts\Commands\AssignContactToGroupCommand;
use App\Modules\CrmContacts\Commands\CreateContactGroupCommand;
use App\Modules\CrmContacts\DTOs\ContactGroupData;
use App\Modules\CrmContacts\Handlers\AssignContactToGroupHandler;
use App\Modules\CrmContacts\Handlers\CreateContactGroupHandler;
use App\Modules\CrmContacts\Models\ContactGroup;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactGroupApiController extends Controller
{
    public function index(Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->requireTenantId();
        $query = ContactGroup::where('tenant_id', $tenantId)->withCount('memberships');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        return response()->json($query->paginate(25));
    }

    public function show(int $id, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->requireTenantId();
        $group = ContactGroup::where('tenant_id', $tenantId)
            ->with(['memberships.member'])
            ->findOrFail($id);

        return response()->json(['data' => $group]);
    }

    public function store(
        Request $request,
        TenantContext $tenantContext,
        CreateContactGroupHandler $handler
    ): JsonResponse {
        $validated = $request->validate([
            'company_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'type' => 'nullable|string|in:general,customer,supplier,lead',
            'color' => 'nullable|string|max:30',
            'description' => 'nullable|string',
        ]);

        $data = ContactGroupData::fromArray($validated);
        $group = $handler->handle(new CreateContactGroupCommand($data));

        return response()->json(['data' => $group], 201);
    }

    public function assign(
        int $id,
        Request $request,
        TenantContext $tenantContext,
        AssignContactToGroupHandler $handler
    ): JsonResponse {
        $validated = $request->validate([
            'member_type' => 'required|string',
            'member_id' => 'required|integer',
        ]);

        $membership = $handler->handle(new AssignContactToGroupCommand(
            groupId: $id,
            memberType: $validated['member_type'],
            memberId: (int) $validated['member_id']
        ));

        return response()->json(['data' => $membership], 201);
    }
}

<?php

namespace App\Modules\CrmContacts\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\CrmContacts\Handlers\GetContactOverviewHandler;
use App\Modules\CrmContacts\Handlers\GetCustomerSalesStatsHandler;
use App\Modules\CrmContacts\Queries\GetContactOverviewQuery;
use App\Modules\CrmContacts\Queries\GetCustomerSalesStatsQuery;
use App\Modules\Customers\Models\Customer;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerStatsApiController extends Controller
{
    public function stats(
        int $id,
        Request $request,
        TenantContext $tenantContext,
        GetCustomerSalesStatsHandler $handler
    ): JsonResponse {
        $tenantId = $tenantContext->requireTenantId();
        Customer::where('tenant_id', $tenantId)->findOrFail($id);

        $year = $request->filled('year') ? (int) $request->input('year') : null;
        $stats = $handler->handle(new GetCustomerSalesStatsQuery(customerId: $id, year: $year));

        return response()->json(['data' => $stats->toArray()]);
    }

    public function overview(
        Request $request,
        TenantContext $tenantContext,
        GetContactOverviewHandler $handler
    ): JsonResponse {
        $tenantContext->requireTenantId();

        $overview = $handler->handle(new GetContactOverviewQuery(
            search: $request->input('search'),
            type: $request->input('type'),
            groupId: $request->filled('group_id') ? (int) $request->input('group_id') : null
        ));

        return response()->json(['data' => $overview->toArray()]);
    }
}

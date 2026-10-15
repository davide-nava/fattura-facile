<?php

namespace App\Modules\Integrations\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Products\Models\Product;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductApiController extends Controller
{
    public function index(Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->requireTenantId();

        $query = Product::where('tenant_id', $tenantId);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($search, $operator) {
                $q->where('name', $operator, "%{$search}%")
                    ->orWhere('sku', $operator, "%{$search}%");
            });
        }

        return response()->json($query->paginate(25));
    }

    public function show(int $id, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->requireTenantId();
        $product = Product::where('tenant_id', $tenantId)->findOrFail($id);

        return response()->json(['data' => $product]);
    }
}

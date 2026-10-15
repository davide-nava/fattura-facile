<?php

namespace App\Modules\Tenancy\Http\Middleware;

use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var TenantContext $tenantContext */
        $tenantContext = app(TenantContext::class);

        $user = Auth::user();

        if ($user) {
            $requestedTenantId = null;

            // 1. Explicit API header
            if ($request->hasHeader('X-Tenant-ID')) {
                $requestedTenantId = (int) $request->header('X-Tenant-ID');
            } elseif ($request->hasSession() && $request->session()->has('active_tenant_id')) {
                // 2. Active session
                $requestedTenantId = (int) $request->session()->get('active_tenant_id');
            } elseif ($user->current_tenant_id) {
                // 3. User default
                $requestedTenantId = (int) $user->current_tenant_id;
            }

            $tenant = null;

            if ($requestedTenantId) {
                if ($user->hasAccessToTenant($requestedTenantId)) {
                    $tenant = Tenant::withoutGlobalScopes()
                        ->where('id', $requestedTenantId)
                        ->where('is_active', true)
                        ->first();
                }
            }

            // Fallback to first available active tenant for this user
            if (! $tenant) {
                $tenant = $user->tenants()->where('tenants.is_active', true)->wherePivot('is_active', true)->first();
            }

            if ($tenant) {
                $tenantContext->setTenant($tenant);
                if ($request->hasSession()) {
                    $request->session()->put('active_tenant_id', $tenant->id);
                }
            } else {
                $tenantContext->reset();
            }
        }

        return $next($request);
    }
}

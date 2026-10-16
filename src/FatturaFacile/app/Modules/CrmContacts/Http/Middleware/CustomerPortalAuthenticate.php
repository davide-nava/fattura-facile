<?php

namespace App\Modules\CrmContacts\Http\Middleware;

use App\Modules\CrmContacts\Models\CustomerPortalUser;
use App\Modules\Tenancy\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomerPortalAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var TenantContext $tenantContext */
        $tenantContext = app(TenantContext::class);

        // 1. Check API Bearer Token
        $token = $request->bearerToken();
        if ($token) {
            $portalUser = CustomerPortalUser::where('access_token', $token)
                ->where('status', 'active')
                ->first();

            if ($portalUser) {
                Auth::guard('customer_portal')->setUser($portalUser);
                $tenantContext->setTenant($portalUser->tenant);

                return $next($request);
            }
        }

        // 2. Check Session Guard
        if (Auth::guard('customer_portal')->check()) {
            /** @var CustomerPortalUser $portalUser */
            $portalUser = Auth::guard('customer_portal')->user();
            if ($portalUser && $portalUser->isActive()) {
                $tenantContext->setTenant($portalUser->tenant);

                return $next($request);
            }
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Unauthenticated from customer portal.',
            ], 401);
        }

        return redirect()->route('portal.login');
    }
}

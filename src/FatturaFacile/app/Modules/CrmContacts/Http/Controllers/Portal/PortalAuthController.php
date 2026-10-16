<?php

namespace App\Modules\CrmContacts\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Modules\CrmContacts\Models\CustomerPortalUser;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PortalAuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('portal.login');
    }

    public function login(Request $request): RedirectResponse|JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        /** @var CustomerPortalUser|null $user */
        $user = CustomerPortalUser::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'The provided credentials do not match our records.',
                ], 422);
            }

            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        if (! $user->isActive()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Your portal account is suspended or pending activation.',
                ], 403);
            }

            throw ValidationException::withMessages([
                'email' => ['Your portal account is suspended or pending activation.'],
            ]);
        }

        Auth::guard('customer_portal')->login($user, (bool) $request->boolean('remember'));
        $user->update(['last_login_at' => now()]);

        app(TenantContext::class)->setTenant($user->tenant);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Login successful.',
                'token' => $user->access_token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'customer_id' => $user->customer_id,
                ],
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('portal.dashboard'));
    }

    public function logout(Request $request): RedirectResponse|JsonResponse
    {
        Auth::guard('customer_portal')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Logged out successfully.']);
        }

        return redirect()->route('portal.login');
    }
}

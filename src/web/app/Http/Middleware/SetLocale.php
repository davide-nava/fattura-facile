<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Supported application locales.
     *
     * @var array<int, string>
     */
    public const SUPPORTED_LOCALES = ['it', 'en', 'de', 'fr'];

    /**
     * Handle an incoming request and set the active locale.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->determineLocale($request);

        App::setLocale($locale);

        return $next($request);
    }

    /**
     * Determine the user's preferred locale.
     */
    private function determineLocale(Request $request): string
    {
        // 1. Check explicit request parameter (?lang=xx or ?locale=xx)
        $param = $request->query('lang') ?? $request->query('locale');
        if (is_string($param) && in_array($param, self::SUPPORTED_LOCALES, true)) {
            if ($request->hasSession()) {
                $request->session()->put('locale', $param);
            }

            return $param;
        }

        // 2. Check session-stored locale
        if ($request->hasSession() && $request->session()->has('locale')) {
            $sessionLocale = $request->session()->get('locale');
            if (is_string($sessionLocale) && in_array($sessionLocale, self::SUPPORTED_LOCALES, true)) {
                return $sessionLocale;
            }
        }

        // 3. Check authenticated user's saved preference
        $user = $request->user();
        if ($user && ! empty($user->locale) && in_array($user->locale, self::SUPPORTED_LOCALES, true)) {
            if ($request->hasSession()) {
                $request->session()->put('locale', $user->locale);
            }

            return $user->locale;
        }

        // 4. Fallback to application default locale
        $default = config('app.locale', 'en');

        return in_array($default, self::SUPPORTED_LOCALES, true) ? $default : 'en';
    }
}

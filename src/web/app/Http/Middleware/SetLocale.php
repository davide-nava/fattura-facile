<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Localization\Contracts\LocaleManagerInterface;
use App\Services\Localization\LocaleManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Supported application locales for backward compatibility.
     *
     * @var array<int, string>
     */
    public const SUPPORTED_LOCALES = LocaleManager::SUPPORTED_LOCALES;

    public function __construct(
        private readonly LocaleManagerInterface $localeManager,
    ) {}

    /**
     * Handle an incoming request and set the active locale.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->determineLocale($request);

        App::setLocale($locale);

        // Keep active session in sync if session middleware is present
        if ($request->hasSession() && $request->session()->get('locale') !== $locale) {
            $request->session()->put('locale', $locale);
        }

        $response = $next($request);

        $activeLocale = App::getLocale();
        if (! $this->localeManager->isSupported($activeLocale)) {
            $activeLocale = $locale;
        }

        // Keep active session in sync if changed downstream by a controller or action
        if ($request->hasSession() && $request->session()->get('locale') !== $activeLocale) {
            $request->session()->put('locale', $activeLocale);
        }

        // Synchronize and persist cookie on the client response whenever missing or updated
        if ($request->cookie(LocaleManager::COOKIE_NAME) !== $activeLocale) {
            $response->headers->setCookie(
                $this->localeManager->createLocaleCookie($activeLocale, $request)
            );
        }

        return $response;
    }

    /**
     * Determine the user's preferred locale via LocaleManager.
     */
    public function determineLocale(Request $request): string
    {
        return $this->localeManager->resolveLocale($request);
    }
}

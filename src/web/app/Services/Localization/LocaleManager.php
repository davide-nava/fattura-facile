<?php

declare(strict_types=1);

namespace App\Services\Localization;

use App\Services\Localization\Contracts\LocaleManagerInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class LocaleManager implements LocaleManagerInterface
{
    /**
     * Supported application locales.
     *
     * @var array<int, string>
     */
    public const SUPPORTED_LOCALES = ['it', 'en', 'de', 'fr'];

    /**
     * Name of the client/server synchronization cookie.
     */
    public const COOKIE_NAME = 'locale';

    /**
     * Cookie retention period in minutes (1 year = 525,600 minutes).
     */
    public const COOKIE_LIFETIME_MINUTES = 525600;

    /**
     * {@inheritdoc}
     */
    public function getSupportedLocales(): array
    {
        return self::SUPPORTED_LOCALES;
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported(?string $locale): bool
    {
        if ($locale === null || $locale === '') {
            return false;
        }

        return in_array($locale, self::SUPPORTED_LOCALES, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultLocale(): string
    {
        $default = config('app.locale', 'en');

        return $this->isSupported((string) $default) ? (string) $default : 'en';
    }

    /**
     * {@inheritdoc}
     */
    public function resolveLocale(Request $request): string
    {
        // 1. Explicit request query parameter (?lang=xx or ?locale=xx)
        $param = $request->query('lang') ?? $request->query('locale');
        if (is_string($param) && $this->isSupported($param)) {
            return $param;
        }

        // 2. Active Session preference
        if ($request->hasSession() && $request->session()->has('locale')) {
            $sessionLocale = $request->session()->get('locale');
            if (is_string($sessionLocale) && $this->isSupported($sessionLocale)) {
                return $sessionLocale;
            }
        }

        // 3. Authenticated User saved preference
        $user = $request->user();
        if ($user && ! empty($user->locale) && $this->isSupported((string) $user->locale)) {
            return (string) $user->locale;
        }

        // 4. HTTP Cookie (written by JavaScript localStorage sync or previous server response)
        $cookieLocale = $request->cookie(self::COOKIE_NAME) ?? $request->cookie('app_locale');
        if (is_string($cookieLocale) && $this->isSupported($cookieLocale)) {
            return $cookieLocale;
        }

        // 5. Browser Accept-Language header matching supported locales
        $browserLocale = $request->getPreferredLanguage(self::SUPPORTED_LOCALES);
        if (is_string($browserLocale) && $this->isSupported($browserLocale)) {
            return $browserLocale;
        }

        // 6. Application default fallback
        return $this->getDefaultLocale();
    }

    /**
     * {@inheritdoc}
     */
    public function createLocaleCookie(string $locale, ?Request $request = null): Cookie
    {
        $resolvedLocale = $this->isSupported($locale) ? $locale : $this->getDefaultLocale();
        $isSecure = $request?->isSecure() ?? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');

        return cookie(
            name: self::COOKIE_NAME,
            value: $resolvedLocale,
            minutes: self::COOKIE_LIFETIME_MINUTES,
            path: '/',
            domain: null,
            secure: $isSecure,
            httpOnly: false, // Must be accessible to JavaScript/Alpine for bi-directional localStorage synchronization
            raw: false,
            sameSite: 'lax',
        );
    }
}

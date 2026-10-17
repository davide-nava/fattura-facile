<?php

declare(strict_types=1);

namespace App\Services\Localization\Contracts;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

interface LocaleManagerInterface
{
    /**
     * Get the list of supported application locales.
     *
     * @return array<int, string>
     */
    public function getSupportedLocales(): array;

    /**
     * Determine whether the given locale is supported.
     */
    public function isSupported(?string $locale): bool;

    /**
     * Get the fallback default locale.
     */
    public function getDefaultLocale(): string;

    /**
     * Resolve the active locale based on request parameters, session, user preference, cookie, or header.
     */
    public function resolveLocale(Request $request): string;

    /**
     * Create an unencrypted HTTP cookie for persisting the user's selected locale across client and server.
     */
    public function createLocaleCookie(string $locale, ?Request $request = null): Cookie;
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Localization\Commands\SetLocaleCommand;
use App\Services\Localization\Commands\SetLocaleCommandHandler;
use App\Services\Localization\Contracts\LocaleManagerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    public function __construct(
        private readonly SetLocaleCommandHandler $handler,
        private readonly LocaleManagerInterface $localeManager,
    ) {}

    /**
     * Switch application language and persist preference to session, user record, and client cookie.
     */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        $cookie = null;

        if ($this->localeManager->isSupported($locale)) {
            $cookie = $this->handler->handle(new SetLocaleCommand(
                locale: $locale,
                user: $request->user(),
                request: $request,
            ));
        }

        $response = redirect()->back(fallback: route('home'));

        if ($cookie !== null) {
            $response->withCookie($cookie);
        }

        return $response;
    }

    /**
     * API/AJAX endpoint to update the application locale and return JSON confirmation with persistent cookie.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in($this->localeManager->getSupportedLocales())],
        ]);

        $locale = (string) $validated['locale'];

        $cookie = $this->handler->handle(new SetLocaleCommand(
            locale: $locale,
            user: $request->user(),
            request: $request,
        ));

        return response()
            ->json([
                'success' => true,
                'locale' => $locale,
                'message' => __('Language updated successfully.'),
            ])
            ->withCookie($cookie);
    }
}

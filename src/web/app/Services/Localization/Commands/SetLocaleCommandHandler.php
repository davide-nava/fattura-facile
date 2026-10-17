<?php

declare(strict_types=1);

namespace App\Services\Localization\Commands;

use App\Services\Localization\Contracts\LocaleManagerInterface;
use App\Services\Localization\Events\LocaleChangedEvent;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Cookie;

final readonly class SetLocaleCommandHandler
{
    public function __construct(
        private LocaleManagerInterface $localeManager,
    ) {}

    /**
     * Handle updating active application locale, session, user record, and return persistence cookie.
     */
    public function handle(SetLocaleCommand $command): Cookie
    {
        $locale = $command->locale;

        if (! $this->localeManager->isSupported($locale)) {
            $locale = $this->localeManager->getDefaultLocale();
        }

        App::setLocale($locale);

        if ($command->request?->hasSession()) {
            $command->request->session()->put('locale', $locale);
        }

        if ($command->user !== null && method_exists($command->user, 'update')) {
            $command->user->update(['locale' => $locale]);
        }

        event(new LocaleChangedEvent($locale, $command->user));

        return $this->localeManager->createLocaleCookie($locale, $command->request);
    }
}

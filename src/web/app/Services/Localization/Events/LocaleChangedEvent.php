<?php

declare(strict_types=1);

namespace App\Services\Localization\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final readonly class LocaleChangedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $locale,
        public ?Authenticatable $user = null,
    ) {}
}

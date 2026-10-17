<?php

declare(strict_types=1);

namespace App\Services\Localization\Commands;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

final readonly class SetLocaleCommand
{
    public function __construct(
        public string $locale,
        public ?Authenticatable $user = null,
        public ?Request $request = null,
    ) {}
}

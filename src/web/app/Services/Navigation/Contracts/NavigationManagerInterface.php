<?php

declare(strict_types=1);

namespace App\Services\Navigation\Contracts;

use App\Services\Navigation\MenuItem;

interface NavigationManagerInterface
{
    /**
     * Get the full list of top-level navigation items.
     *
     * @return array<int, MenuItem>
     */
    public function getItems(): array;
}

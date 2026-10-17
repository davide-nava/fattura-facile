<?php

declare(strict_types=1);

namespace App\Services\Navigation;

use Illuminate\Support\Facades\Route;

class MenuItem
{
    /**
     * @param  string  $key  Translation key, e.g. 'menu.dashboard'
     * @param  string  $icon  FontAwesome icon class, e.g. 'fa-solid fa-chart-pie'
     * @param  string|null  $route  Named route name, e.g. 'dashboard'
     * @param  string|null  $url  Fallback URL path if route is not defined
     * @param  array<int, string>  $activePatterns  Route/URL pattern matches for active state
     * @param  array<int, MenuItem>  $children  Submenu items
     * @param  string|null  $badge  Optional badge text (e.g. 'New' or count)
     * @param  string|null  $badgeColor  Badge color style (e.g. 'teal', 'emerald', 'amber')
     */
    public function __construct(
        public readonly string $key,
        public readonly string $icon,
        public readonly ?string $route = null,
        public readonly ?string $url = null,
        public readonly array $activePatterns = [],
        public readonly array $children = [],
        public readonly ?string $badge = null,
        public readonly ?string $badgeColor = null,
    ) {}

    /**
     * Get the translated display label.
     */
    public function getLabel(): string
    {
        return __($this->key);
    }

    /**
     * Resolve the target URL safely.
     */
    public function getUrl(): string
    {
        if ($this->route !== null && Route::has($this->route)) {
            return route($this->route);
        }

        if ($this->url !== null) {
            return url($this->url);
        }

        return '#';
    }

    /**
     * Determine if this menu item has child items.
     */
    public function hasChildren(): bool
    {
        return ! empty($this->children);
    }

    /**
     * Check if this item is currently active based on current request.
     */
    public function isActive(): bool
    {
        if ($this->route !== null && request()->routeIs($this->route)) {
            return true;
        }

        foreach ($this->activePatterns as $pattern) {
            if (request()->routeIs($pattern) || request()->is($pattern)) {
                return true;
            }
        }

        if ($this->url !== null) {
            $trimmed = ltrim($this->url, '/');
            if ($trimmed !== '' && (request()->is($trimmed) || request()->is($trimmed.'/*'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if any child item is active.
     */
    public function hasActiveChild(): bool
    {
        foreach ($this->children as $child) {
            if ($child->isActive() || $child->hasActiveChild()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if this item or any of its children is active.
     */
    public function isSelfOrChildActive(): bool
    {
        return $this->isActive() || $this->hasActiveChild();
    }
}

<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Services\Navigation\Contracts\NavigationManagerInterface;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Sidebar extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        protected NavigationManagerInterface $navigation
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.sidebar.index', [
            'items' => $this->navigation->getItems(),
        ]);
    }
}

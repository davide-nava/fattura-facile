<?php

namespace App\Modules\CrmContacts\Providers;

use Illuminate\Support\ServiceProvider;

class CrmContactsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Service bindings for CQRS
    }

    public function boot(): void
    {
        $this->loadViewsFrom(resource_path('views/portal'), 'portal');

        if (file_exists(base_path('routes/portal.php'))) {
            $this->loadRoutesFrom(base_path('routes/portal.php'));
        }
    }
}

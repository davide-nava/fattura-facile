<?php

use App\Modules\CrmContacts\Providers\CrmContactsServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\FolioServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\HorizonServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    FolioServiceProvider::class,
    FortifyServiceProvider::class,
    HorizonServiceProvider::class,
    CrmContactsServiceProvider::class,
];

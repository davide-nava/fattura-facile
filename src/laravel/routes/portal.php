<?php

use App\Modules\CrmContacts\Http\Controllers\Portal\PortalAuthController;
use App\Modules\CrmContacts\Http\Controllers\Portal\PortalDashboardController;
use App\Modules\CrmContacts\Http\Middleware\CustomerPortalAuthenticate;
use Illuminate\Support\Facades\Route;

// Web routes for B2B/B2C Customer Self-Service Portal
Route::middleware(['web'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/login', [PortalAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [PortalAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [PortalAuthController::class, 'logout'])->name('logout');

    // Authenticated Portal Routes
    Route::middleware([CustomerPortalAuthenticate::class])->group(function () {
        Route::get('/', [PortalDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/invoices', [PortalDashboardController::class, 'invoices'])->name('invoices');
        Route::get('/invoices/{id}/download', [PortalDashboardController::class, 'downloadInvoice'])->name('invoices.download');
        Route::get('/quotes', [PortalDashboardController::class, 'quotes'])->name('quotes');
        Route::get('/profile', [PortalDashboardController::class, 'showProfile'])->name('profile');
        Route::put('/profile', [PortalDashboardController::class, 'updateProfile'])->name('profile.update');
    });
});

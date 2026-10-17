<?php

use App\Http\Controllers\LocaleController;
use App\Modules\CrmContacts\Http\Controllers\Api\ContactGroupApiController;
use App\Modules\CrmContacts\Http\Controllers\Api\ContactPersonApiController;
use App\Modules\CrmContacts\Http\Controllers\Api\CustomerStatsApiController;
use App\Modules\CrmContacts\Http\Controllers\Api\LeadApiController;
use App\Modules\CrmContacts\Http\Controllers\Portal\PortalAuthController;
use App\Modules\CrmContacts\Http\Controllers\Portal\PortalDashboardController;
use App\Modules\CrmContacts\Http\Middleware\CustomerPortalAuthenticate;
use App\Modules\Integrations\Http\Controllers\Api\CustomerApiController;
use App\Modules\Integrations\Http\Controllers\Api\InvoiceApiController;
use App\Modules\Integrations\Http\Controllers\Api\ProductApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth');

Route::get('/v1/health', function () {
    return response()->json([
        'status' => 'healthy',
        'platform' => 'Fattura Facile',
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::post('/v1/locale', [LocaleController::class, 'update'])->name('api.locale.update');

Route::prefix('v1')->middleware(['tenant.context'])->group(function () {
    // Customers API
    Route::get('/customers', [CustomerApiController::class, 'index']);
    Route::post('/customers', [CustomerApiController::class, 'store']);
    Route::get('/customers/{id}', [CustomerApiController::class, 'show']);
    Route::get('/customers/{id}/stats', [CustomerStatsApiController::class, 'stats']);

    // CRM Contact Persons API
    Route::get('/contacts', [ContactPersonApiController::class, 'index']);
    Route::post('/contacts', [ContactPersonApiController::class, 'store']);
    Route::get('/contacts/{id}', [ContactPersonApiController::class, 'show']);
    Route::put('/contacts/{id}', [ContactPersonApiController::class, 'update']);
    Route::get('/contacts-overview', [CustomerStatsApiController::class, 'overview']);

    // CRM Contact Groups API
    Route::get('/contact-groups', [ContactGroupApiController::class, 'index']);
    Route::post('/contact-groups', [ContactGroupApiController::class, 'store']);
    Route::get('/contact-groups/{id}', [ContactGroupApiController::class, 'show']);
    Route::post('/contact-groups/{id}/assign', [ContactGroupApiController::class, 'assign']);

    // CRM Leads API
    Route::get('/leads', [LeadApiController::class, 'index']);
    Route::post('/leads', [LeadApiController::class, 'store']);
    Route::get('/leads/{id}', [LeadApiController::class, 'show']);
    Route::post('/leads/{id}/convert', [LeadApiController::class, 'convert']);

    // Products API
    Route::get('/products', [ProductApiController::class, 'index']);
    Route::get('/products/{id}', [ProductApiController::class, 'show']);

    // Invoices API
    Route::get('/invoices', [InvoiceApiController::class, 'index']);
    Route::post('/invoices', [InvoiceApiController::class, 'store']);
    Route::get('/invoices/{id}', [InvoiceApiController::class, 'show']);
    Route::post('/invoices/{id}/issue', [InvoiceApiController::class, 'issue']);
    Route::get('/invoices/{id}/pdf', [InvoiceApiController::class, 'downloadPdf']);
});

// B2B/B2C Customer Self-Service Portal API
Route::prefix('v1/portal')->group(function () {
    Route::post('/auth/login', [PortalAuthController::class, 'login']);
    Route::post('/auth/logout', [PortalAuthController::class, 'logout']);

    Route::middleware([CustomerPortalAuthenticate::class])->group(function () {
        Route::get('/dashboard', [PortalDashboardController::class, 'dashboard']);
        Route::get('/invoices', [PortalDashboardController::class, 'invoices']);
        Route::get('/quotes', [PortalDashboardController::class, 'quotes']);
        Route::get('/profile', [PortalDashboardController::class, 'showProfile']);
        Route::put('/profile', [PortalDashboardController::class, 'updateProfile']);
    });
});

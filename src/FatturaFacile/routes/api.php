<?php

use App\Modules\Integrations\Http\Controllers\Api\CustomerApiController;
use App\Modules\Integrations\Http\Controllers\Api\InvoiceApiController;
use App\Modules\Integrations\Http\Controllers\Api\ProductApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/v1/health', function () {
    return response()->json([
        'status' => 'healthy',
        'platform' => 'FatturaFacile',
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::prefix('v1')->middleware(['tenant.context'])->group(function () {
    // Customers API
    Route::get('/customers', [CustomerApiController::class, 'index']);
    Route::post('/customers', [CustomerApiController::class, 'store']);
    Route::get('/customers/{id}', [CustomerApiController::class, 'show']);

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

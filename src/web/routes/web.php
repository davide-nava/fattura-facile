<?php

use App\Http\Controllers\LocaleController;
use App\Modules\Invoicing\Http\Controllers\InvoicePrintController;
use App\Modules\Reporting\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('language/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');
Route::post('language', [LocaleController::class, 'update'])->name('locale.update');

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/{report}', [ReportController::class, 'preview'])->name('preview');
        Route::get('/{report}/download', [ReportController::class, 'download'])->name('download');
        Route::get('/{report}/stream', [ReportController::class, 'stream'])->name('stream');
    });

    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/{invoice}/preview', [InvoicePrintController::class, 'preview'])->name('preview');
        Route::get('/{invoice}/download', [InvoicePrintController::class, 'download'])->name('download');
        Route::get('/{invoice}/stream', [InvoicePrintController::class, 'stream'])->name('stream');
    });
});

require __DIR__.'/settings.php';

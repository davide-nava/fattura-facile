<?php

use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::get('language/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/', 'welcome')->name('home');

    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';

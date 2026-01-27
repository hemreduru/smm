<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\SettingsController; // Import

Route::get('/', function () {
    return redirect()->route('login');
});

// Global Settings Routes
Route::get('lang/{locale}', [SettingsController::class, 'setLocale'])->name('settings.locale');
Route::get('theme/{theme}', [SettingsController::class, 'setTheme'])->name('settings.theme');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PlatformAccountController;
use App\Http\Controllers\AccountGroupController;
use App\Http\Controllers\ContentController;

Route::get('/', fn() => redirect()->route('login'));

// Global Settings Routes
Route::get('lang/{locale}', [SettingsController::class, 'setLocale'])->name('settings.locale');
Route::get('theme/{theme}', [SettingsController::class, 'setTheme'])->name('settings.theme');

// Guest Routes (Unauthenticated)
Route::middleware('guest')->group(function () {
    // Login
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);

    // Register
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register']);

    // Password Reset
    Route::controller(PasswordResetController::class)->group(function () {
        Route::get('forgot-password', 'showForgotForm')->name('password.request');
        Route::post('forgot-password', 'sendResetLink')->name('password.email');
        Route::get('reset-password/{token}', 'showResetForm')->name('password.reset');
        Route::post('reset-password', 'reset')->name('password.update');
    });
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Email Verification
    Route::controller(EmailVerificationController::class)->prefix('email')->group(function () {
        Route::get('verify', 'notice')->name('verification.notice');
        Route::get('verify/{id}/{hash}', 'verify')->middleware('signed')->name('verification.verify');
        Route::post('verification-notification', 'resend')->middleware('throttle:6,1')->name('verification.send');
    });

    // Dashboard
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

    // Workspaces - Resource routes
    Route::resource('workspaces', WorkspaceController::class);

    // Workspace extra actions (not covered by resource)
    Route::prefix('workspaces/{workspace}')->name('workspaces.')->group(function () {
        Route::post('switch', [WorkspaceController::class, 'switch'])->name('switch');
        Route::post('invite', [WorkspaceController::class, 'inviteUser'])->name('invite');
        Route::delete('users/{user}', [WorkspaceController::class, 'removeUser'])->name('users.remove');
        Route::put('users/{user}/role', [WorkspaceController::class, 'updateUserRole'])->name('users.role');
    });

    // User Management (requires workspace)
    Route::middleware('workspace.selected')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/data', [UserController::class, 'data'])->name('users.data');

        // Platform Accounts
        Route::resource('accounts', PlatformAccountController::class);
        Route::get('accounts-data', [PlatformAccountController::class, 'data'])->name('accounts.data');

        // Account Groups
        Route::resource('groups', AccountGroupController::class);
        Route::get('groups-data', [AccountGroupController::class, 'data'])->name('groups.data');

        // Contents
        Route::resource('contents', ContentController::class);
        Route::get('contents-data', [ContentController::class, 'data'])->name('contents.data');
        Route::get('contents-kanban', [ContentController::class, 'kanban'])->name('contents.kanban');
        Route::post('contents/{content}/approve', [ContentController::class, 'approve'])->name('contents.approve');
        Route::post('contents/{content}/schedule', [ContentController::class, 'schedule'])->name('contents.schedule');
    });

    // User Profile
    Route::get('profile', [UserController::class, 'profile'])->name('profile');
    Route::put('profile', [UserController::class, 'updateProfile'])->name('profile.update');
});

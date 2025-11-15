<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\TwoFactorAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| This file contains all authentication-related routes:
| - Login / Logout
| - Registration
| - Password Reset
| - Email Verification
| - Two-Factor Authentication (2FA)
|
*/

// ===================================
// Guest Routes (Unauthenticated Users)
// ===================================

Route::middleware('guest')->group(function () {

    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Registration
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Password Reset
    Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])
        ->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])
        ->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])
        ->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->name('password.update');

    // Two-Factor Authentication Verification
    // Note: This is a special case - user is partially authenticated but not fully logged in
    Route::get('/2fa/verify', [TwoFactorAuthController::class, 'showVerifyForm'])
        ->name('2fa.verify');
    Route::post('/2fa/verify', [TwoFactorAuthController::class, 'verify']);
});

// ===================================
// Authenticated Routes
// ===================================

Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Email Verification
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/email/resend', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.resend');

    // Two-Factor Authentication Setup (requires verified email)
    Route::middleware('verified')->group(function () {
        Route::get('/2fa/setup', [TwoFactorAuthController::class, 'showSetupForm'])
            ->name('2fa.setup');
        Route::post('/2fa/enable', [TwoFactorAuthController::class, 'enable'])
            ->name('2fa.enable');
        Route::post('/2fa/disable', [TwoFactorAuthController::class, 'disable'])
            ->name('2fa.disable');
    });
});

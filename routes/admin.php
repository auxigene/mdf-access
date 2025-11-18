<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| This file contains all administrative routes.
| All routes require authentication, email verification, and system admin privileges.
|
*/

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Admin Dashboard
    // Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // User Management
    // Route::resource('users', AdminUserController::class);
    // Route::post('users/{user}/toggle-admin', [AdminUserController::class, 'toggleAdmin'])->name('users.toggle-admin');

    // Organization Management
    // Route::resource('organizations', AdminOrganizationController::class);

    // Role Management
    // Route::resource('roles', AdminRoleController::class);

    // Permission Management
    // Route::resource('permissions', AdminPermissionController::class);

    // API Key Management
    // Route::resource('api-keys', AdminApiKeyController::class);
    // Route::post('api-keys/{apiKey}/regenerate', [AdminApiKeyController::class, 'regenerate'])->name('api-keys.regenerate');

    // System Settings
    // Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    // Route::put('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');

    // Activity Logs
    // Route::get('/logs', [AdminLogController::class, 'index'])->name('logs.index');

    // System Health
    // Route::get('/health', [AdminHealthController::class, 'index'])->name('health.index');
});

<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| This file contains all dashboard and authenticated user routes.
| All routes require authentication and email verification.
|
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // Main Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // User Profile
    // Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    // Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // User Settings
    // Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    // Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Projects (example - uncomment when controllers are created)
    // Route::resource('projects', ProjectController::class);

    // Portfolios (example - uncomment when controllers are created)
    // Route::resource('portfolios', PortfolioController::class);

    // Programs (example - uncomment when controllers are created)
    // Route::resource('programs', ProgramController::class);

    // Tasks (example - uncomment when controllers are created)
    // Route::resource('tasks', TaskController::class);

    // Resources (example - uncomment when controllers are created)
    // Route::resource('resources', ResourceController::class);

    // Budgets (example - uncomment when controllers are created)
    // Route::resource('budgets', BudgetController::class);
});

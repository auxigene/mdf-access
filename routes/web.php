<?php

use App\Http\Controllers\Api\ExcelUpdateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
| Route Organization:
| - routes/web.php      : Public routes and route file loader
| - routes/auth.php     : Authentication routes (login, register, password reset, 2FA)
| - routes/dashboard.php: Dashboard and user-authenticated routes
| - routes/admin.php    : Admin panel routes (system admin only)
| - routes/api.php      : API routes (API key authentication)
|
*/

// ===================================
// Public Routes
// ===================================

// Homepage
Route::get('/', function () {
    return view('welcome-samsic');
})->name('home');

// Homepage Mockups (for preview/testing)
Route::get('/mockup', function () {
    return view('homepage-mockup-samsic');
})->name('mockup.complete');

Route::get('/mockup/minimal', function () {
    return view('homepage-mockup-samsic');
})->name('mockup.minimal');

// Download page
Route::get('/download', function () {
    return view('download');
})->name('download.page');

// Public Excel download (no authentication required for easy access)
Route::get('/excel/download/{fileName?}', [ExcelUpdateController::class, 'download'])
    ->name('excel.download');

// ===================================
// Load Modular Route Files
// ===================================

// Authentication routes (login, register, password reset, email verification, 2FA)
require __DIR__.'/auth.php';

// Dashboard and user routes (requires auth + verified)
require __DIR__.'/dashboard.php';

// Admin routes (requires auth + verified + system admin)
require __DIR__.'/admin.php';

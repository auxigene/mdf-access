<?php

use App\Http\Controllers\Api\ExcelUpdateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
| Authentication: All API routes use API Key authentication via the
| 'api.key' middleware. API keys are managed in the database and can
| have different types and access levels (read, write, admin).
|
| API Key Middleware Format: api.key:{type},{level}
| Example: api.key:excel_update,write
|
*/

// ===================================
// Excel Update API (Kizeo Integration)
// ===================================

Route::prefix('excel')->name('api.excel.')->group(function () {

    // Update Excel file with data
    // Requires: API key of type 'excel_update' with 'write' access
    Route::post('/update', [ExcelUpdateController::class, 'update'])
        ->middleware('api.key:excel_update,write')
        ->name('update');

    // Download updated Excel file
    // Requires: API key of type 'excel_update' with 'read' access
    Route::get('/download/{fileName?}', [ExcelUpdateController::class, 'download'])
        ->middleware('api.key:excel_update,read')
        ->name('download');
});

// ===================================
// Future API Endpoints
// ===================================

// Projects API (example - uncomment when controllers are created)
// Route::prefix('projects')->middleware('api.key:projects,read')->group(function () {
//     Route::get('/', [ProjectApiController::class, 'index']);
//     Route::get('/{project}', [ProjectApiController::class, 'show']);
//     Route::post('/', [ProjectApiController::class, 'store'])->middleware('api.key:projects,write');
//     Route::put('/{project}', [ProjectApiController::class, 'update'])->middleware('api.key:projects,write');
//     Route::delete('/{project}', [ProjectApiController::class, 'destroy'])->middleware('api.key:projects,admin');
// });

// Tasks API (example - uncomment when controllers are created)
// Route::prefix('tasks')->middleware('api.key:tasks,read')->group(function () {
//     Route::get('/', [TaskApiController::class, 'index']);
//     Route::get('/{task}', [TaskApiController::class, 'show']);
//     Route::post('/', [TaskApiController::class, 'store'])->middleware('api.key:tasks,write');
//     Route::put('/{task}', [TaskApiController::class, 'update'])->middleware('api.key:tasks,write');
//     Route::delete('/{task}', [TaskApiController::class, 'destroy'])->middleware('api.key:tasks,admin');
// });

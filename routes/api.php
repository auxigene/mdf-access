<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ExcelUpdateController;
use App\Http\Controllers\Api\ExcelDownloadController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Protected routes with API key authentication
Route::middleware('api.key')->group(function () {
    // Route pour mettre à jour un fichier Excel avec les données Kizeo
    Route::post('/excel/update', [ExcelUpdateController::class, 'update']);

    // Route pour télécharger un fichier Excel
    Route::get('/excel/download/{filename?}', [ExcelDownloadController::class, 'download']);

    // Route pour lister les fichiers Excel disponibles
    Route::get('/excel/list', [ExcelDownloadController::class, 'list']);
});

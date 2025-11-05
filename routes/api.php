<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ExcelUpdateController;

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

// Route pour mettre à jour un fichier Excel avec les données Kizeo
// Requiert une clé API de type 'excel_update' avec le niveau d'accès 'write'
Route::post('/excel/update', [ExcelUpdateController::class, 'update'])
    ->middleware('api.key:excel_update,write');

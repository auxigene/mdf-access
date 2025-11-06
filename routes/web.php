<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ExcelUpdateController;

Route::get('/', function () {
    return view('welcome');
});

// Route pour afficher la page de téléchargement
Route::get('/download', function () {
    return view('download');
})->name('download.page');

// Route web pour télécharger le fichier Excel (sans authentification pour faciliter l'accès)
Route::get('/excel/download/{fileName?}', [ExcelUpdateController::class, 'download'])
    ->name('excel.download');

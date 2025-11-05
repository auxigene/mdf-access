<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateExcelRequest;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use Exception;

class ExcelUpdateController extends Controller
{
    /**
     * Met à jour un fichier Excel avec les données fournies
     */
    public function update(UpdateExcelRequest $request)
    {
        try {
            $validated = $request->validated();
            $data = $validated['data'];

            // Nom du fichier Excel (peut être passé dans la requête ou configuré par défaut)
            $fileName = $validated['fichier_excel'] ?? 'template.xlsx';

            // Chemin complet du fichier dans storage
            $filePath = storage_path('app/excel/' . $fileName);

            // Vérifier si le fichier existe
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => "Le fichier Excel '$fileName' n'existe pas dans storage/app/excel/",
                    'path' => $filePath
                ], 404);
            }

            // Charger le fichier Excel
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();

            // Parcourir les données et mettre à jour les cellules
            $updatedCells = [];
            foreach ($data as $item) {
                $colonne = $item['colonne_excel'];
                $rang = $item['rang'];
                $valeur = $item['valeur'];
                $cellReference = $colonne . $rang;

                // Mettre à jour la cellule
                $sheet->setCellValue($cellReference, $valeur);

                $updatedCells[] = [
                    'cellule' => $cellReference,
                    'valeur' => $valeur,
                    'champ_kizeo' => $item['champ_kizeo']
                ];
            }

            // Sauvegarder le fichier
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($filePath);

            return response()->json([
                'success' => true,
                'message' => 'Fichier Excel mis à jour avec succès',
                'fichier' => $fileName,
                'cellules_modifiees' => count($updatedCells),
                'details' => $updatedCells
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du fichier Excel',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

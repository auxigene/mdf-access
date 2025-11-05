<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelDownloadController extends Controller
{
    /**
     * Download an Excel file
     *
     * @param Request $request
     * @param string|null $filename
     * @return StreamedResponse|\Illuminate\Http\JsonResponse
     */
    public function download(Request $request, ?string $filename = null)
    {
        // Default to template.xlsx if no filename provided
        $filename = $filename ?? 'template.xlsx';

        // Validate filename (security: prevent directory traversal)
        if (str_contains($filename, '..') || str_contains($filename, '/')) {
            return response()->json([
                'error' => 'Invalid filename',
                'message' => 'Filename cannot contain directory separators'
            ], 400);
        }

        // Build the file path
        $filePath = storage_path('app/excel/' . $filename);

        // Check if file exists
        if (!file_exists($filePath)) {
            return response()->json([
                'error' => 'File not found',
                'message' => "The requested Excel file '{$filename}' does not exist"
            ], 404);
        }

        // Check if file is readable
        if (!is_readable($filePath)) {
            return response()->json([
                'error' => 'File not readable',
                'message' => "The requested Excel file '{$filename}' cannot be read"
            ], 500);
        }

        // Return the file as a download
        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * List available Excel files
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        $excelPath = storage_path('app/excel/');

        if (!is_dir($excelPath)) {
            return response()->json([
                'files' => []
            ]);
        }

        $files = [];
        $directory = new \DirectoryIterator($excelPath);

        foreach ($directory as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ['xlsx', 'xls'])) {
                $files[] = [
                    'name' => $file->getFilename(),
                    'size' => $file->getSize(),
                    'modified_at' => date('Y-m-d H:i:s', $file->getMTime()),
                ];
            }
        }

        return response()->json([
            'files' => $files,
            'count' => count($files)
        ]);
    }
}

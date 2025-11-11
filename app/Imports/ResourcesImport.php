<?php

namespace App\Imports;

use App\Models\Resource;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ResourcesImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row)
    {
        $user = User::where('email', $row['user_email'])->first();
        if (!$user) {
            throw new \Exception("Utilisateur non trouvé: {$row['user_email']}");
        }

        // Convertir les compétences séparées par ";" en tableau
        $skills = null;
        if (!empty($row['skills'])) {
            $skills = array_map('trim', explode(';', $row['skills']));
        }

        return new Resource([
            'user_id' => $user->id,
            'role' => $row['role'] ?? null,
            'department' => $row['department'] ?? null,
            'cost_per_hour' => !empty($row['cost_per_hour']) ? $row['cost_per_hour'] : null,
            'availability_percentage' => $row['availability_percentage'],
            'skills' => $skills,
            'status' => $row['status'] ?? 'available',
        ]);
    }
}

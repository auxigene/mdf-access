<?php

namespace App\Imports;

use App\Models\Phase;
use App\Models\Project;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class PhasesImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row)
    {
        $project = Project::where('code', $row['project_code'])->first();
        if (!$project) {
            throw new \Exception("Projet non trouvé: {$row['project_code']}");
        }

        return new Phase([
            'project_id' => $project->id,
            'name' => $row['name'],
            'description' => $row['description'] ?? null,
            'sequence' => $row['sequence'],
            'start_date' => !empty($row['start_date']) ? $row['start_date'] : null,
            'end_date' => !empty($row['end_date']) ? $row['end_date'] : null,
            'status' => $row['status'] ?? 'not_started',
            'completion_percentage' => $row['completion_percentage'] ?? 0,
        ]);
    }
}

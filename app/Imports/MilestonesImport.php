<?php

namespace App\Imports;

use App\Models\Milestone;
use App\Models\Project;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class MilestonesImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row)
    {
        $project = Project::where('code', $row['project_code'])->first();
        if (!$project) {
            throw new \Exception("Projet non trouvé: {$row['project_code']}");
        }

        $critical = $this->parseBoolean($row['critical'] ?? 'non');

        return new Milestone([
            'project_id' => $project->id,
            'name' => $row['name'],
            'description' => $row['description'] ?? null,
            'due_date' => $row['due_date'],
            'status' => $row['status'] ?? 'pending',
            'critical' => $critical,
            'achieved_date' => !empty($row['achieved_date']) ? $row['achieved_date'] : null,
        ]);
    }

    private function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        $value = strtolower(trim($value));
        return in_array($value, ['oui', 'yes', '1', 'true', 'vrai']);
    }
}

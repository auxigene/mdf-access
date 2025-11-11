<?php

namespace App\Imports;

use App\Models\Risk;
use App\Models\Project;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class RisksImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row)
    {
        $project = Project::where('code', $row['project_code'])->first();
        if (!$project) {
            throw new \Exception("Projet non trouvé: {$row['project_code']}");
        }

        $ownerId = null;
        if (!empty($row['owner_email'])) {
            $owner = User::where('email', $row['owner_email'])->first();
            $ownerId = $owner?->id;
        }

        return new Risk([
            'project_id' => $project->id,
            'category' => $row['category'] ?? null,
            'description' => $row['description'],
            'probability' => $row['probability'],
            'impact' => $row['impact'],
            'mitigation_strategy' => $row['mitigation_strategy'] ?? null,
            'owner_id' => $ownerId,
            'status' => $row['status'] ?? 'identified',
            'identified_date' => $row['identified_date'],
            'review_date' => !empty($row['review_date']) ? $row['review_date'] : null,
        ]);
    }
}

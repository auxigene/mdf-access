<?php

namespace App\Imports;

use App\Models\WbsElement;
use App\Models\Project;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class WbsElementsImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row)
    {
        $project = Project::where('code', $row['project_code'])->first();
        if (!$project) {
            throw new \Exception("Projet non trouvé: {$row['project_code']}");
        }

        $parentId = null;
        if (!empty($row['parent_code'])) {
            $parent = WbsElement::where('project_id', $project->id)
                                ->where('code', $row['parent_code'])
                                ->first();
            if (!$parent) {
                throw new \Exception("Élément WBS parent non trouvé: {$row['parent_code']} pour le projet {$row['project_code']}");
            }
            $parentId = $parent->id;
        }

        $assignedOrgId = !empty($row['assigned_organization_id']) ? $row['assigned_organization_id'] : null;

        return new WbsElement([
            'project_id' => $project->id,
            'parent_id' => $parentId,
            'assigned_organization_id' => $assignedOrgId,
            'code' => $row['code'],
            'name' => $row['name'],
            'description' => $row['description'] ?? null,
            'level' => $row['level'],
            'start_date' => !empty($row['start_date']) ? $row['start_date'] : null,
            'end_date' => !empty($row['end_date']) ? $row['end_date'] : null,
            'completion_percentage' => $row['completion_percentage'] ?? 0,
        ]);
    }
}

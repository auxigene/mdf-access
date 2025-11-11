<?php

namespace App\Imports;

use App\Models\Task;
use App\Models\Project;
use App\Models\WbsElement;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class TasksImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row)
    {
        $project = Project::where('code', $row['project_code'])->first();
        if (!$project) {
            throw new \Exception("Projet non trouvé: {$row['project_code']}");
        }

        $wbsElementId = null;
        if (!empty($row['wbs_code'])) {
            $wbs = WbsElement::where('project_id', $project->id)
                             ->where('code', $row['wbs_code'])
                             ->first();
            $wbsElementId = $wbs?->id;
        }

        $assignedTo = null;
        if (!empty($row['assigned_to_email'])) {
            $user = User::where('email', $row['assigned_to_email'])->first();
            $assignedTo = $user?->id;
        }

        $assignedOrgId = !empty($row['assigned_organization_id']) ? $row['assigned_organization_id'] : null;

        return new Task([
            'project_id' => $project->id,
            'wbs_element_id' => $wbsElementId,
            'assigned_organization_id' => $assignedOrgId,
            'name' => $row['name'],
            'description' => $row['description'] ?? null,
            'assigned_to' => $assignedTo,
            'priority' => $row['priority'] ?? 'medium',
            'status' => $row['status'] ?? 'not_started',
            'estimated_hours' => !empty($row['estimated_hours']) ? $row['estimated_hours'] : null,
            'actual_hours' => !empty($row['actual_hours']) ? $row['actual_hours'] : 0,
            'start_date' => !empty($row['start_date']) ? $row['start_date'] : null,
            'end_date' => !empty($row['end_date']) ? $row['end_date'] : null,
            'completion_percentage' => $row['completion_percentage'] ?? 0,
        ]);
    }
}

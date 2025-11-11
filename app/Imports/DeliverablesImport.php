<?php

namespace App\Imports;

use App\Models\Deliverable;
use App\Models\Project;
use App\Models\WbsElement;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class DeliverablesImport implements ToModel, WithHeadingRow, SkipsEmptyRows
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

        $assignedOrgId = !empty($row['assigned_organization_id']) ? $row['assigned_organization_id'] : null;

        $approvedBy = null;
        if (!empty($row['approved_by_email'])) {
            $approver = User::where('email', $row['approved_by_email'])->first();
            $approvedBy = $approver?->id;
        }

        $approvedAt = null;
        if (!empty($row['approved_at'])) {
            $approvedAt = $row['approved_at'];
        }

        return new Deliverable([
            'project_id' => $project->id,
            'wbs_element_id' => $wbsElementId,
            'assigned_organization_id' => $assignedOrgId,
            'name' => $row['name'],
            'description' => $row['description'] ?? null,
            'type' => $row['type'] ?? null,
            'due_date' => !empty($row['due_date']) ? $row['due_date'] : null,
            'delivery_date' => !empty($row['delivery_date']) ? $row['delivery_date'] : null,
            'status' => $row['status'] ?? 'not_started',
            'approved_by' => $approvedBy,
            'approved_at' => $approvedAt,
        ]);
    }
}

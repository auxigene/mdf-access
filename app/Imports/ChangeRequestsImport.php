<?php

namespace App\Imports;

use App\Models\ChangeRequest;
use App\Models\Project;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ChangeRequestsImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row)
    {
        $project = Project::where('code', $row['project_code'])->first();
        if (!$project) {
            throw new \Exception("Projet non trouvé: {$row['project_code']}");
        }

        $requestedBy = null;
        if (!empty($row['requested_by_email'])) {
            $requester = User::where('email', $row['requested_by_email'])->first();
            $requestedBy = $requester?->id;
        }

        $approvedBy = null;
        if (!empty($row['approved_by_email'])) {
            $approver = User::where('email', $row['approved_by_email'])->first();
            $approvedBy = $approver?->id;
        }

        return new ChangeRequest([
            'project_id' => $project->id,
            'title' => $row['title'],
            'description' => $row['description'],
            'justification' => $row['justification'] ?? null,
            'impact_analysis' => $row['impact_analysis'] ?? null,
            'cost_impact' => !empty($row['cost_impact']) ? $row['cost_impact'] : null,
            'schedule_impact' => !empty($row['schedule_impact']) ? $row['schedule_impact'] : null,
            'status' => $row['status'] ?? 'submitted',
            'requested_by' => $requestedBy,
            'approved_by' => $approvedBy,
            'approval_date' => !empty($row['approval_date']) ? $row['approval_date'] : null,
        ]);
    }
}

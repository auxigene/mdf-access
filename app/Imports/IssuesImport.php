<?php

namespace App\Imports;

use App\Models\Issue;
use App\Models\Project;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class IssuesImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row)
    {
        $project = Project::where('code', $row['project_code'])->first();
        if (!$project) {
            throw new \Exception("Projet non trouvé: {$row['project_code']}");
        }

        $reportedBy = null;
        if (!empty($row['reported_by_email'])) {
            $reporter = User::where('email', $row['reported_by_email'])->first();
            $reportedBy = $reporter?->id;
        }

        $assignedTo = null;
        if (!empty($row['assigned_to_email'])) {
            $assignee = User::where('email', $row['assigned_to_email'])->first();
            $assignedTo = $assignee?->id;
        }

        return new Issue([
            'project_id' => $project->id,
            'title' => $row['title'],
            'description' => $row['description'] ?? null,
            'severity' => $row['severity'],
            'priority' => $row['priority'],
            'status' => $row['status'] ?? 'open',
            'reported_by' => $reportedBy,
            'assigned_to' => $assignedTo,
            'reported_date' => $row['reported_date'],
            'resolved_date' => !empty($row['resolved_date']) ? $row['resolved_date'] : null,
            'resolution' => $row['resolution'] ?? null,
        ]);
    }
}

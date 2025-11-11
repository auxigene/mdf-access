<?php

namespace App\Imports;

use App\Models\Project;
use App\Models\Program;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ProjectsImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    public function model(array $row)
    {
        $programId = null;
        if (!empty($row['program_name'])) {
            $program = Program::where('name', $row['program_name'])->first();
            $programId = $program?->id;
        }

        $projectManagerId = null;
        if (!empty($row['project_manager_email'])) {
            $manager = User::where('email', $row['project_manager_email'])->first();
            $projectManagerId = $manager?->id;
        }

        return new Project([
            'program_id' => $programId,
            'client_organization_id' => $row['client_organization_id'],
            'client_reference' => $row['client_reference'] ?? null,
            'code' => $row['code'],
            'name' => $row['name'],
            'description' => $row['description'] ?? null,
            'project_manager_id' => $projectManagerId,
            'project_type' => $row['project_type'] ?? null,
            'methodology' => $row['methodology'] ?? 'waterfall',
            'start_date' => !empty($row['start_date']) ? $row['start_date'] : null,
            'end_date' => !empty($row['end_date']) ? $row['end_date'] : null,
            'baseline_start' => !empty($row['baseline_start']) ? $row['baseline_start'] : null,
            'baseline_end' => !empty($row['baseline_end']) ? $row['baseline_end'] : null,
            'budget' => !empty($row['budget']) ? $row['budget'] : null,
            'actual_cost' => !empty($row['actual_cost']) ? $row['actual_cost'] : 0,
            'status' => $row['status'] ?? 'initiation',
            'priority' => $row['priority'] ?? 'medium',
            'health_status' => $row['health_status'] ?? 'green',
            'completion_percentage' => !empty($row['completion_percentage']) ? $row['completion_percentage'] : 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|unique:projects,code|max:255',
            'name' => 'required|string|max:255',
            'client_organization_id' => 'required|exists:organizations,id',
            'methodology' => 'required|in:waterfall,agile,hybrid',
            'status' => 'required|in:initiation,planning,execution,monitoring,closure,on_hold,cancelled',
            'priority' => 'required|in:low,medium,high,critical',
            'health_status' => 'required|in:green,yellow,red',
            'completion_percentage' => 'required|integer|min:0|max:100',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'code.unique' => 'Ce code projet existe déjà',
            'client_organization_id.exists' => 'Cette organisation n\'existe pas',
        ];
    }
}

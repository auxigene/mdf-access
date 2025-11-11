<?php

namespace App\Imports;

use App\Models\ProjectOrganization;
use App\Models\Project;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ProjectOrganizationsImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    public function model(array $row)
    {
        $project = Project::where('code', $row['project_code'])->first();
        if (!$project) {
            throw new \Exception("Projet non trouvé: {$row['project_code']}");
        }

        $isPrimary = $this->parseBoolean($row['is_primary'] ?? 'non');

        return new ProjectOrganization([
            'project_id' => $project->id,
            'organization_id' => $row['organization_id'],
            'role' => $row['role'],
            'reference' => $row['reference'] ?? null,
            'scope_description' => $row['scope_description'] ?? null,
            'is_primary' => $isPrimary,
            'start_date' => !empty($row['start_date']) ? $row['start_date'] : null,
            'end_date' => !empty($row['end_date']) ? $row['end_date'] : null,
            'status' => $row['status'] ?? 'active',
        ]);
    }

    public function rules(): array
    {
        return [
            'project_code' => 'required|exists:projects,code',
            'organization_id' => 'required|exists:organizations,id',
            'role' => 'required|in:sponsor,moa,moe,subcontractor',
            'is_primary' => 'required',
            'status' => 'required|in:active,inactive,completed',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'project_code.exists' => 'Ce projet n\'existe pas',
            'organization_id.exists' => 'Cette organisation n\'existe pas',
            'role.in' => 'Le rôle doit être: sponsor, moa, moe ou subcontractor',
        ];
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

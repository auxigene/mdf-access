<?php

namespace App\Imports;

use App\Models\Portfolio;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class PortfoliosImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    public function model(array $row)
    {
        $managerId = null;
        if (!empty($row['manager_email'])) {
            $manager = User::where('email', $row['manager_email'])->first();
            $managerId = $manager?->id;
        }

        return new Portfolio([
            'name' => $row['name'],
            'organization_id' => $row['organization_id'],
            'manager_id' => $managerId,
            'description' => $row['description'] ?? null,
            'budget' => !empty($row['budget']) ? $row['budget'] : null,
            'start_date' => !empty($row['start_date']) ? $row['start_date'] : null,
            'end_date' => !empty($row['end_date']) ? $row['end_date'] : null,
            'status' => $row['status'] ?? 'active',
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'organization_id' => 'required|exists:organizations,id',
            'manager_email' => 'nullable|email|exists:users,email',
            'status' => 'required|in:active,inactive,completed,on_hold',
        ];
    }
}

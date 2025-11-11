<?php

namespace App\Imports;

use App\Models\Program;
use App\Models\Portfolio;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ProgramsImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    public function model(array $row)
    {
        $portfolioId = null;
        if (!empty($row['portfolio_name'])) {
            $portfolio = Portfolio::where('name', $row['portfolio_name'])->first();
            $portfolioId = $portfolio?->id;
        }

        $managerId = null;
        if (!empty($row['manager_email'])) {
            $manager = User::where('email', $row['manager_email'])->first();
            $managerId = $manager?->id;
        }

        return new Program([
            'portfolio_id' => $portfolioId,
            'name' => $row['name'],
            'manager_id' => $managerId,
            'description' => $row['description'] ?? null,
            'budget' => !empty($row['budget']) ? $row['budget'] : null,
            'objectives' => $row['objectives'] ?? null,
            'status' => $row['status'] ?? 'active',
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'portfolio_name' => 'nullable|exists:portfolios,name',
            'manager_email' => 'nullable|email|exists:users,email',
            'status' => 'required|in:active,inactive,completed,on_hold',
        ];
    }
}

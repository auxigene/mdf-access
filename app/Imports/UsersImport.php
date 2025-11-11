<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class UsersImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    /**
     * Convertir chaque ligne en modèle User
     */
    public function model(array $row)
    {
        return new User([
            'name' => $row['name'],
            'email' => $row['email'],
            'password' => Hash::make($row['password']),
            'organization_id' => $row['organization_id'],
            'is_system_admin' => $this->parseBoolean($row['is_system_admin']),
        ]);
    }

    /**
     * Règles de validation
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'organization_id' => 'required|exists:organizations,id',
            'is_system_admin' => 'required',
        ];
    }

    /**
     * Messages d'erreur personnalisés
     */
    public function customValidationMessages()
    {
        return [
            'name.required' => 'Le nom est obligatoire',
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'L\'email doit être valide',
            'email.unique' => 'Cet email existe déjà',
            'password.required' => 'Le mot de passe est obligatoire',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères',
            'organization_id.required' => 'L\'organisation est obligatoire',
            'organization_id.exists' => 'Cette organisation n\'existe pas',
        ];
    }

    /**
     * Convertir texte en booléen
     */
    private function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower(trim($value));
        return in_array($value, ['oui', 'yes', '1', 'true', 'vrai']);
    }
}

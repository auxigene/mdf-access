<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use App\Models\Project;
use App\Models\Program;
use App\Models\Portfolio;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class UserRolesImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    /**
     * Convertir chaque ligne en modèle UserRole
     */
    public function model(array $row)
    {
        // Récupérer l'utilisateur par email
        $user = User::where('email', $row['user_email'])->first();
        if (!$user) {
            throw new \Exception("Utilisateur non trouvé: {$row['user_email']}");
        }

        // Récupérer le rôle par slug
        $role = Role::where('slug', $row['role_slug'])->first();
        if (!$role) {
            throw new \Exception("Rôle non trouvé: {$row['role_slug']}");
        }

        // Déterminer les IDs de scope
        $portfolioId = null;
        $programId = null;
        $projectId = null;

        $scopeType = trim($row['scope_type'] ?? '');
        $scopeId = !empty($row['scope_id']) ? (int)$row['scope_id'] : null;

        if ($scopeType && $scopeId) {
            switch (strtolower($scopeType)) {
                case 'portfolio':
                    $portfolioId = $scopeId;
                    break;
                case 'program':
                    $programId = $scopeId;
                    break;
                case 'project':
                    $projectId = $scopeId;
                    break;
            }
        }

        return new UserRole([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'portfolio_id' => $portfolioId,
            'program_id' => $programId,
            'project_id' => $projectId,
        ]);
    }

    /**
     * Règles de validation
     */
    public function rules(): array
    {
        return [
            'user_email' => 'required|email|exists:users,email',
            'role_slug' => 'required|exists:roles,slug',
            'scope_type' => 'nullable|in:global,portfolio,program,project',
            'scope_id' => 'nullable|integer',
        ];
    }

    /**
     * Messages d'erreur personnalisés
     */
    public function customValidationMessages()
    {
        return [
            'user_email.required' => 'L\'email utilisateur est obligatoire',
            'user_email.exists' => 'Cet utilisateur n\'existe pas',
            'role_slug.required' => 'Le slug du rôle est obligatoire',
            'role_slug.exists' => 'Ce rôle n\'existe pas',
            'scope_type.in' => 'Le type de scope doit être: global, portfolio, program ou project',
        ];
    }
}

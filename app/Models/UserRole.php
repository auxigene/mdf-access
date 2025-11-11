<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserRole extends Pivot
{
    // ===================================
    // CONFIGURATION
    // ===================================

    protected $table = 'user_roles';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'role_id',
        'portfolio_id',
        'program_id',
        'project_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'role_id' => 'integer',
        'portfolio_id' => 'integer',
        'program_id' => 'integer',
        'project_id' => 'integer',
    ];

    // ===================================
    // RELATIONS - Utilisateur
    // ===================================

    /**
     * Utilisateur ayant ce rôle
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ===================================
    // RELATIONS - Rôle
    // ===================================

    /**
     * Rôle assigné à l'utilisateur
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // ===================================
    // RELATIONS - Scopes
    // ===================================

    /**
     * Portfolio auquel ce rôle est scopé (si applicable)
     */
    public function portfolio()
    {
        return $this->belongsTo(Portfolio::class);
    }

    /**
     * Programme auquel ce rôle est scopé (si applicable)
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Projet auquel ce rôle est scopé (si applicable)
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // ===================================
    // SCOPES - Type de Scope
    // ===================================

    /**
     * Filtrer les rôles globaux (sans scope)
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('portfolio_id')
                     ->whereNull('program_id')
                     ->whereNull('project_id');
    }

    /**
     * Filtrer les rôles scopés à un portfolio
     */
    public function scopePortfolioScoped($query, ?int $portfolioId = null)
    {
        $q = $query->whereNotNull('portfolio_id');

        if ($portfolioId !== null) {
            $q->where('portfolio_id', $portfolioId);
        }

        return $q;
    }

    /**
     * Filtrer les rôles scopés à un programme
     */
    public function scopeProgramScoped($query, ?int $programId = null)
    {
        $q = $query->whereNotNull('program_id');

        if ($programId !== null) {
            $q->where('program_id', $programId);
        }

        return $q;
    }

    /**
     * Filtrer les rôles scopés à un projet
     */
    public function scopeProjectScoped($query, ?int $projectId = null)
    {
        $q = $query->whereNotNull('project_id');

        if ($projectId !== null) {
            $q->where('project_id', $projectId);
        }

        return $q;
    }

    // ===================================
    // SCOPES - Utilisateur et Rôle
    // ===================================

    /**
     * Filtrer par utilisateur
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Filtrer par rôle
     */
    public function scopeForRole($query, int $roleId)
    {
        return $query->where('role_id', $roleId);
    }

    /**
     * Filtrer par rôle slug
     */
    public function scopeForRoleSlug($query, string $roleSlug)
    {
        return $query->whereHas('role', function ($q) use ($roleSlug) {
            $q->where('slug', $roleSlug);
        });
    }

    // ===================================
    // HELPERS - Type de Scope
    // ===================================

    /**
     * Vérifier si le rôle est global (sans scope)
     */
    public function isGlobal(): bool
    {
        return $this->portfolio_id === null
            && $this->program_id === null
            && $this->project_id === null;
    }

    /**
     * Vérifier si le rôle est scopé à un portfolio
     */
    public function isPortfolioScoped(): bool
    {
        return $this->portfolio_id !== null;
    }

    /**
     * Vérifier si le rôle est scopé à un programme
     */
    public function isProgramScoped(): bool
    {
        return $this->program_id !== null;
    }

    /**
     * Vérifier si le rôle est scopé à un projet
     */
    public function isProjectScoped(): bool
    {
        return $this->project_id !== null;
    }

    /**
     * Obtenir le type de scope (global, portfolio, program, project)
     */
    public function getScopeType(): string
    {
        if ($this->isProjectScoped()) {
            return 'project';
        }

        if ($this->isProgramScoped()) {
            return 'program';
        }

        if ($this->isPortfolioScoped()) {
            return 'portfolio';
        }

        return 'global';
    }

    /**
     * Obtenir l'ID du scope
     */
    public function getScopeId(): ?int
    {
        if ($this->isProjectScoped()) {
            return $this->project_id;
        }

        if ($this->isProgramScoped()) {
            return $this->program_id;
        }

        if ($this->isPortfolioScoped()) {
            return $this->portfolio_id;
        }

        return null;
    }

    /**
     * Obtenir le modèle de scope (Project, Program, Portfolio, ou null)
     */
    public function getScopeModel(): ?Model
    {
        if ($this->isProjectScoped()) {
            return $this->project;
        }

        if ($this->isProgramScoped()) {
            return $this->program;
        }

        if ($this->isPortfolioScoped()) {
            return $this->portfolio;
        }

        return null;
    }

    // ===================================
    // HELPERS - Description
    // ===================================

    /**
     * Obtenir le nom du scope en français
     */
    public function getScopeLabel(): string
    {
        $labels = [
            'global' => 'Global',
            'portfolio' => 'Portfolio',
            'program' => 'Programme',
            'project' => 'Projet',
        ];

        return $labels[$this->getScopeType()] ?? 'Inconnu';
    }

    /**
     * Obtenir le nom complet du scope
     */
    public function getScopeName(): ?string
    {
        $scopeModel = $this->getScopeModel();

        if ($scopeModel && isset($scopeModel->name)) {
            return $scopeModel->name;
        }

        return null;
    }

    /**
     * Obtenir la description complète de l'attribution
     */
    public function getFullDescription(): string
    {
        $desc = $this->role?->name ?? 'Rôle inconnu';

        if ($this->isGlobal()) {
            $desc .= ' (Global)';
        } else {
            $scopeName = $this->getScopeName();
            $desc .= ' (' . $this->getScopeLabel() . ($scopeName ? ': ' . $scopeName : '') . ')';
        }

        return $desc;
    }

    // ===================================
    // HELPERS - Validation Scope
    // ===================================

    /**
     * Vérifier si le scope est valide pour ce rôle
     * Un rôle global ne devrait pas avoir de scope
     * Un rôle organization devrait avoir un scope organization/project
     * Un rôle project devrait avoir un scope project
     */
    public function hasValidScope(): bool
    {
        if (!$this->role) {
            return false;
        }

        // Rôle global ne devrait pas avoir de scope
        if ($this->role->isGlobal() && !$this->isGlobal()) {
            return false;
        }

        // Rôle organization peut être global ou scopé
        if ($this->role->isOrganizationScope()) {
            return true; // Flexible
        }

        // Rôle project devrait idéalement avoir un scope projet
        if ($this->role->isProjectScope() && $this->isGlobal()) {
            return false; // Un rôle projet sans scope n'a pas de sens
        }

        return true;
    }

    // ===================================
    // HELPERS - Permissions
    // ===================================

    /**
     * Obtenir toutes les permissions de ce rôle
     */
    public function getPermissions()
    {
        return $this->role?->permissions ?? collect();
    }

    /**
     * Vérifier si cette attribution donne une permission spécifique
     */
    public function hasPermission(string $permissionSlug): bool
    {
        return $this->role?->hasPermission($permissionSlug) ?? false;
    }

    // ===================================
    // HELPERS - Recherche et Création
    // ===================================

    /**
     * Créer ou récupérer une attribution de rôle
     */
    public static function assignRole(
        int $userId,
        int $roleId,
        ?int $portfolioId = null,
        ?int $programId = null,
        ?int $projectId = null
    ): UserRole {
        return static::firstOrCreate([
            'user_id' => $userId,
            'role_id' => $roleId,
            'portfolio_id' => $portfolioId,
            'program_id' => $programId,
            'project_id' => $projectId,
        ]);
    }

    /**
     * Retirer une attribution de rôle
     */
    public static function removeRole(
        int $userId,
        int $roleId,
        ?int $portfolioId = null,
        ?int $programId = null,
        ?int $projectId = null
    ): bool {
        return static::where([
            'user_id' => $userId,
            'role_id' => $roleId,
            'portfolio_id' => $portfolioId,
            'program_id' => $programId,
            'project_id' => $projectId,
        ])->delete() > 0;
    }

    /**
     * Vérifier si une attribution existe
     */
    public static function hasAssignment(
        int $userId,
        int $roleId,
        ?int $portfolioId = null,
        ?int $programId = null,
        ?int $projectId = null
    ): bool {
        return static::where([
            'user_id' => $userId,
            'role_id' => $roleId,
            'portfolio_id' => $portfolioId,
            'program_id' => $programId,
            'project_id' => $projectId,
        ])->exists();
    }
}

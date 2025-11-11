<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Portfolio extends Model
{
    use SoftDeletes;

    // ===================================
    // CONFIGURATION
    // ===================================

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'manager_id',
        'budget',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // ===================================
    // RELATIONS - Organisation
    // ===================================

    /**
     * Organisation propriétaire de ce portefeuille
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // ===================================
    // RELATIONS - Gestionnaire
    // ===================================

    /**
     * Gestionnaire du portefeuille (Portfolio Manager)
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    // ===================================
    // RELATIONS - Programmes et Projets
    // ===================================

    /**
     * Programmes contenus dans ce portefeuille
     */
    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    /**
     * Projets contenus dans ce portefeuille (via programmes)
     */
    public function projects()
    {
        return $this->hasManyThrough(Project::class, Program::class);
    }

    // ===================================
    // RELATIONS - Rôles Utilisateurs
    // ===================================

    /**
     * Attributions de rôles scopées à ce portefeuille
     */
    public function userRoles()
    {
        return $this->hasMany(UserRole::class);
    }

    // ===================================
    // SCOPES - Statut
    // ===================================

    /**
     * Filtrer les portefeuilles actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Filtrer les portefeuilles inactifs
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Filtrer les portefeuilles terminés
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Filtrer les portefeuilles en pause
     */
    public function scopeOnHold($query)
    {
        return $query->where('status', 'on_hold');
    }

    // ===================================
    // SCOPES - Organisation
    // ===================================

    /**
     * Filtrer par organisation
     */
    public function scopeForOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    // ===================================
    // HELPERS - Statut
    // ===================================

    /**
     * Vérifier si le portefeuille est actif
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Vérifier si le portefeuille est inactif
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    /**
     * Vérifier si le portefeuille est terminé
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Vérifier si le portefeuille est en pause
     */
    public function isOnHold(): bool
    {
        return $this->status === 'on_hold';
    }

    // ===================================
    // HELPERS - Budget
    // ===================================

    /**
     * Calculer le budget total des programmes
     */
    public function getProgramsBudget(): float
    {
        return $this->programs()->sum('budget') ?? 0;
    }

    /**
     * Calculer le budget total des projets
     */
    public function getProjectsBudget(): float
    {
        return $this->projects()->sum('budget') ?? 0;
    }

    /**
     * Calculer l'écart budgétaire portefeuille
     */
    public function getBudgetVariance(): float
    {
        return ($this->budget ?? 0) - $this->getProjectsBudget();
    }

    /**
     * Calculer le pourcentage d'utilisation du budget
     */
    public function getBudgetUsagePercentage(): float
    {
        if (!$this->budget || $this->budget == 0) {
            return 0;
        }
        return ($this->getProjectsBudget() / $this->budget) * 100;
    }

    // ===================================
    // HELPERS - Statistiques
    // ===================================

    /**
     * Compter le nombre de programmes
     */
    public function getProgramsCount(): int
    {
        return $this->programs()->count();
    }

    /**
     * Compter le nombre de projets
     */
    public function getProjectsCount(): int
    {
        return $this->projects()->count();
    }

    /**
     * Compter les programmes actifs
     */
    public function getActiveProgramsCount(): int
    {
        return $this->programs()->active()->count();
    }

    /**
     * Compter les projets actifs
     */
    public function getActiveProjectsCount(): int
    {
        return $this->projects()->active()->count();
    }

    // ===================================
    // HELPERS - Durée
    // ===================================

    /**
     * Calculer la durée du portefeuille en jours
     */
    public function getDuration(): ?int
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }
        return $this->start_date->diffInDays($this->end_date);
    }

    /**
     * Vérifier si le portefeuille est en cours
     */
    public function isOngoing(): bool
    {
        if (!$this->start_date || !$this->end_date) {
            return $this->isActive();
        }

        $now = now();
        return $this->isActive()
            && $this->start_date->lte($now)
            && $this->end_date->gte($now);
    }

    /**
     * Vérifier si le portefeuille est futur
     */
    public function isFuture(): bool
    {
        if (!$this->start_date) {
            return false;
        }

        return $this->start_date->isFuture();
    }

    /**
     * Vérifier si le portefeuille est passé
     */
    public function isPast(): bool
    {
        if (!$this->end_date) {
            return false;
        }

        return $this->end_date->isPast();
    }
}

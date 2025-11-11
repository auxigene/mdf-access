<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    // ===================================
    // CONFIGURATION
    // ===================================

    protected $fillable = [
        'user_id',
        'role',
        'department',
        'cost_per_hour',
        'availability_percentage',
        'skills',
        'status',
    ];

    protected $casts = [
        'cost_per_hour' => 'decimal:2',
        'availability_percentage' => 'integer',
        'skills' => 'array',
    ];

    // ===================================
    // RELATIONS - Utilisateur
    // ===================================

    /**
     * Utilisateur associé à cette ressource
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ===================================
    // RELATIONS - Allocations
    // ===================================

    /**
     * Allocations de cette ressource sur des projets
     */
    public function allocations()
    {
        return $this->hasMany(ResourceAllocation::class);
    }

    /**
     * Projets auxquels cette ressource est allouée
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'resource_allocations')
                    ->withPivot([
                        'task_id',
                        'allocation_percentage',
                        'start_date',
                        'end_date',
                        'hours_allocated',
                        'hours_worked'
                    ])
                    ->withTimestamps();
    }

    // ===================================
    // SCOPES - Statut
    // ===================================

    /**
     * Filtrer les ressources disponibles
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Filtrer les ressources assignées
     */
    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    /**
     * Filtrer les ressources indisponibles
     */
    public function scopeUnavailable($query)
    {
        return $query->where('status', 'unavailable');
    }

    // ===================================
    // SCOPES - Département et Rôle
    // ===================================

    /**
     * Filtrer par département
     */
    public function scopeDepartment($query, string $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Filtrer par rôle
     */
    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    // ===================================
    // SCOPES - Compétences
    // ===================================

    /**
     * Filtrer par compétence
     */
    public function scopeWithSkill($query, string $skill)
    {
        return $query->whereJsonContains('skills', $skill);
    }

    // ===================================
    // HELPERS - Statut
    // ===================================

    /**
     * Vérifier si la ressource est disponible
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    /**
     * Vérifier si la ressource est assignée
     */
    public function isAssigned(): bool
    {
        return $this->status === 'assigned';
    }

    /**
     * Vérifier si la ressource est indisponible
     */
    public function isUnavailable(): bool
    {
        return $this->status === 'unavailable';
    }

    // ===================================
    // HELPERS - Compétences
    // ===================================

    /**
     * Vérifier si la ressource a une compétence
     */
    public function hasSkill(string $skill): bool
    {
        return in_array($skill, $this->skills ?? []);
    }

    /**
     * Ajouter une compétence
     */
    public function addSkill(string $skill): void
    {
        $skills = $this->skills ?? [];
        if (!in_array($skill, $skills)) {
            $skills[] = $skill;
            $this->skills = $skills;
            $this->save();
        }
    }

    /**
     * Retirer une compétence
     */
    public function removeSkill(string $skill): void
    {
        $skills = $this->skills ?? [];
        $skills = array_diff($skills, [$skill]);
        $this->skills = array_values($skills);
        $this->save();
    }

    // ===================================
    // HELPERS - Allocation
    // ===================================

    /**
     * Calculer le pourcentage d'allocation total actuel
     */
    public function getCurrentAllocationPercentage(): int
    {
        return $this->allocations()
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->sum('allocation_percentage');
    }

    /**
     * Calculer le pourcentage de disponibilité restant
     */
    public function getRemainingAvailability(): int
    {
        return max(0, $this->availability_percentage - $this->getCurrentAllocationPercentage());
    }

    /**
     * Vérifier si la ressource est sur-allouée
     */
    public function isOverallocated(): bool
    {
        return $this->getCurrentAllocationPercentage() > $this->availability_percentage;
    }

    /**
     * Vérifier si la ressource peut être allouée à hauteur de X%
     */
    public function canBeAllocated(int $percentage): bool
    {
        return $this->getRemainingAvailability() >= $percentage;
    }

    // ===================================
    // HELPERS - Coût
    // ===================================

    /**
     * Calculer le coût total des heures travaillées
     */
    public function getTotalCost(): float
    {
        if (!$this->cost_per_hour) {
            return 0;
        }

        $totalHours = $this->allocations()->sum('hours_worked');
        return $totalHours * $this->cost_per_hour;
    }

    /**
     * Calculer le coût pour une allocation spécifique
     */
    public function getCostForAllocation(ResourceAllocation $allocation): float
    {
        if (!$this->cost_per_hour) {
            return 0;
        }

        return $allocation->hours_worked * $this->cost_per_hour;
    }

    // ===================================
    // HELPERS - Statistiques
    // ===================================

    /**
     * Compter le nombre de projets actifs
     */
    public function getActiveProjectsCount(): int
    {
        return $this->allocations()
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->distinct('project_id')
                    ->count('project_id');
    }

    /**
     * Compter le total d'heures allouées
     */
    public function getTotalAllocatedHours(): float
    {
        return $this->allocations()->sum('hours_allocated') ?? 0;
    }

    /**
     * Compter le total d'heures travaillées
     */
    public function getTotalWorkedHours(): float
    {
        return $this->allocations()->sum('hours_worked') ?? 0;
    }
}

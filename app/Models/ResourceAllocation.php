<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResourceAllocation extends Model
{
    // ===================================
    // CONFIGURATION
    // ===================================

    protected $fillable = [
        'resource_id',
        'project_id',
        'task_id',
        'allocation_percentage',
        'start_date',
        'end_date',
        'hours_allocated',
        'hours_worked',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'allocation_percentage' => 'integer',
        'hours_allocated' => 'decimal:2',
        'hours_worked' => 'decimal:2',
    ];

    // ===================================
    // RELATIONS - Ressource
    // ===================================

    /**
     * Ressource allouée
     */
    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    // ===================================
    // RELATIONS - Projet
    // ===================================

    /**
     * Projet auquel la ressource est allouée
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // ===================================
    // RELATIONS - Tâche
    // ===================================

    /**
     * Tâche spécifique (optionnelle)
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // ===================================
    // SCOPES - Dates
    // ===================================

    /**
     * Filtrer les allocations actives (en cours)
     */
    public function scopeActive($query)
    {
        return $query->where('start_date', '<=', now())
                     ->where('end_date', '>=', now());
    }

    /**
     * Filtrer les allocations futures
     */
    public function scopeFuture($query)
    {
        return $query->where('start_date', '>', now());
    }

    /**
     * Filtrer les allocations passées
     */
    public function scopePast($query)
    {
        return $query->where('end_date', '<', now());
    }

    // ===================================
    // SCOPES - Projet et Ressource
    // ===================================

    /**
     * Filtrer par projet
     */
    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Filtrer par ressource
     */
    public function scopeForResource($query, int $resourceId)
    {
        return $query->where('resource_id', $resourceId);
    }

    // ===================================
    // HELPERS - Période
    // ===================================

    /**
     * Vérifier si l'allocation est active
     */
    public function isActive(): bool
    {
        $now = now();
        return $this->start_date->lte($now) && $this->end_date->gte($now);
    }

    /**
     * Vérifier si l'allocation est future
     */
    public function isFuture(): bool
    {
        return $this->start_date->isFuture();
    }

    /**
     * Vérifier si l'allocation est passée
     */
    public function isPast(): bool
    {
        return $this->end_date->isPast();
    }

    /**
     * Calculer la durée de l'allocation en jours
     */
    public function getDuration(): int
    {
        return $this->start_date->diffInDays($this->end_date);
    }

    // ===================================
    // HELPERS - Heures
    // ===================================

    /**
     * Calculer le nombre d'heures restantes
     */
    public function getRemainingHours(): float
    {
        return max(0, ($this->hours_allocated ?? 0) - $this->hours_worked);
    }

    /**
     * Calculer le pourcentage d'heures utilisées
     */
    public function getHoursUsagePercentage(): float
    {
        if (!$this->hours_allocated || $this->hours_allocated == 0) {
            return 0;
        }
        return ($this->hours_worked / $this->hours_allocated) * 100;
    }

    /**
     * Vérifier si l'allocation dépasse les heures prévues
     */
    public function isOverHours(): bool
    {
        if (!$this->hours_allocated) {
            return false;
        }
        return $this->hours_worked > $this->hours_allocated;
    }

    // ===================================
    // HELPERS - Actions
    // ===================================

    /**
     * Enregistrer des heures travaillées
     */
    public function logHours(float $hours): void
    {
        $this->hours_worked += $hours;
        $this->save();
    }

    /**
     * Calculer le coût de cette allocation
     */
    public function getCost(): float
    {
        return $this->resource?->getCostForAllocation($this) ?? 0;
    }

    // ===================================
    // HELPERS - Conflits
    // ===================================

    /**
     * Vérifier si cette allocation chevauche une autre période
     */
    public function overlaps($startDate, $endDate): bool
    {
        return $this->start_date->lte($endDate)
            && $this->end_date->gte($startDate);
    }

    /**
     * Trouver les allocations qui se chevauchent pour la même ressource
     */
    public function getConflictingAllocations()
    {
        return static::where('resource_id', $this->resource_id)
                     ->where('id', '!=', $this->id)
                     ->where(function ($query) {
                         $query->whereBetween('start_date', [$this->start_date, $this->end_date])
                               ->orWhereBetween('end_date', [$this->start_date, $this->end_date])
                               ->orWhere(function ($q) {
                                   $q->where('start_date', '<=', $this->start_date)
                                     ->where('end_date', '>=', $this->end_date);
                               });
                     })
                     ->get();
    }

    /**
     * Calculer le pourcentage d'allocation total pendant cette période
     */
    public function getTotalAllocationDuringPeriod(): int
    {
        $conflicting = $this->getConflictingAllocations();
        $total = $this->allocation_percentage;

        foreach ($conflicting as $allocation) {
            $total += $allocation->allocation_percentage;
        }

        return $total;
    }

    /**
     * Vérifier si l'allocation crée une sur-allocation
     */
    public function causesOverallocation(): bool
    {
        return $this->getTotalAllocationDuringPeriod() > $this->resource?->availability_percentage ?? 100;
    }
}

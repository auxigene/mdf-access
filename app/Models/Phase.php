<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phase extends Model
{
    // ===================================
    // CONFIGURATION
    // ===================================

    protected $table = 'project_phases';

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'sequence',
        'start_date',
        'end_date',
        'status',
        'completion_percentage',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'sequence' => 'integer',
        'completion_percentage' => 'integer',
    ];

    // ===================================
    // RELATIONS - Projet
    // ===================================

    /**
     * Projet auquel appartient cette phase
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // ===================================
    // RELATIONS - Structure WBS
    // ===================================

    /**
     * Éléments WBS de cette phase
     */
    public function wbsElements()
    {
        return $this->hasMany(WbsElement::class);
    }

    /**
     * Livrables de cette phase
     */
    public function deliverables()
    {
        return $this->hasMany(Deliverable::class);
    }

    /**
     * Tâches de cette phase
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Jalons de cette phase
     */
    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }

    // ===================================
    // SCOPES - Statut
    // ===================================

    /**
     * Filtrer les phases non démarrées
     */
    public function scopeNotStarted($query)
    {
        return $query->where('status', 'not_started');
    }

    /**
     * Filtrer les phases en cours
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Filtrer les phases terminées
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Filtrer les phases en pause
     */
    public function scopeOnHold($query)
    {
        return $query->where('status', 'on_hold');
    }

    /**
     * Filtrer les phases actives (en cours ou en pause)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['in_progress', 'on_hold']);
    }

    // ===================================
    // SCOPES - Séquence
    // ===================================

    /**
     * Ordonner par séquence
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence');
    }

    // ===================================
    // HELPERS - Statut
    // ===================================

    /**
     * Vérifier si la phase n'est pas démarrée
     */
    public function isNotStarted(): bool
    {
        return $this->status === 'not_started';
    }

    /**
     * Vérifier si la phase est en cours
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Vérifier si la phase est terminée
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Vérifier si la phase est en pause
     */
    public function isOnHold(): bool
    {
        return $this->status === 'on_hold';
    }

    /**
     * Vérifier si la phase est active
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['in_progress', 'on_hold']);
    }

    // ===================================
    // HELPERS - Durée et Planning
    // ===================================

    /**
     * Calculer la durée de la phase en jours
     */
    public function getDuration(): ?int
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }
        return $this->start_date->diffInDays($this->end_date);
    }

    /**
     * Vérifier si la phase est en cours chronologiquement
     */
    public function isOngoing(): bool
    {
        if (!$this->start_date || !$this->end_date) {
            return $this->isInProgress();
        }

        $now = now();
        return $this->start_date->lte($now) && $this->end_date->gte($now);
    }

    /**
     * Vérifier si la phase est future
     */
    public function isFuture(): bool
    {
        if (!$this->start_date) {
            return $this->isNotStarted();
        }

        return $this->start_date->isFuture();
    }

    /**
     * Vérifier si la phase est passée
     */
    public function isPast(): bool
    {
        if (!$this->end_date) {
            return false;
        }

        return $this->end_date->isPast();
    }

    /**
     * Vérifier si la phase est en retard
     */
    public function isDelayed(): bool
    {
        if (!$this->end_date) {
            return false;
        }

        return now()->isAfter($this->end_date) && !$this->isCompleted();
    }

    // ===================================
    // HELPERS - Progression
    // ===================================

    /**
     * Calculer la progression basée sur les tâches
     */
    public function calculateProgressFromTasks(): int
    {
        $tasks = $this->tasks;

        if ($tasks->isEmpty()) {
            return $this->completion_percentage;
        }

        $totalWeight = $tasks->count();
        $completedWeight = $tasks->where('status', 'completed')->count();

        return (int) (($completedWeight / $totalWeight) * 100);
    }

    /**
     * Mettre à jour le pourcentage de complétion automatiquement
     */
    public function updateCompletionPercentage(): void
    {
        $this->completion_percentage = $this->calculateProgressFromTasks();
        $this->save();
    }

    // ===================================
    // HELPERS - Navigation
    // ===================================

    /**
     * Obtenir la phase précédente
     */
    public function getPreviousPhase(): ?Phase
    {
        return static::where('project_id', $this->project_id)
                     ->where('sequence', '<', $this->sequence)
                     ->orderBy('sequence', 'desc')
                     ->first();
    }

    /**
     * Obtenir la phase suivante
     */
    public function getNextPhase(): ?Phase
    {
        return static::where('project_id', $this->project_id)
                     ->where('sequence', '>', $this->sequence)
                     ->orderBy('sequence', 'asc')
                     ->first();
    }

    /**
     * Vérifier si c'est la première phase
     */
    public function isFirstPhase(): bool
    {
        return $this->sequence === static::where('project_id', $this->project_id)->min('sequence');
    }

    /**
     * Vérifier si c'est la dernière phase
     */
    public function isLastPhase(): bool
    {
        return $this->sequence === static::where('project_id', $this->project_id)->max('sequence');
    }

    // ===================================
    // HELPERS - Statistiques
    // ===================================

    /**
     * Compter les tâches de la phase
     */
    public function getTasksCount(): int
    {
        return $this->tasks()->count();
    }

    /**
     * Compter les tâches terminées
     */
    public function getCompletedTasksCount(): int
    {
        return $this->tasks()->where('status', 'completed')->count();
    }

    /**
     * Compter les livrables de la phase
     */
    public function getDeliverablesCount(): int
    {
        return $this->deliverables()->count();
    }

    /**
     * Compter les jalons de la phase
     */
    public function getMilestonesCount(): int
    {
        return $this->milestones()->count();
    }
}

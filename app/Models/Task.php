<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    // ===================================
    // CONFIGURATION
    // ===================================

    protected $fillable = [
        'project_id',
        'wbs_element_id',
        'assigned_organization_id',
        'parent_task_id',
        'name',
        'description',
        'assigned_to',
        'priority',
        'status',
        'estimated_hours',
        'actual_hours',
        'start_date',
        'end_date',
        'dependencies',
        'completion_percentage',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'dependencies' => 'array',
        'completion_percentage' => 'integer',
    ];

    // ===================================
    // RELATIONS - Projet
    // ===================================

    /**
     * Projet auquel appartient cette tâche
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // ===================================
    // RELATIONS - Structure WBS
    // ===================================

    /**
     * Élément WBS auquel est rattachée cette tâche
     */
    public function wbsElement()
    {
        return $this->belongsTo(WbsElement::class);
    }

    // ===================================
    // RELATIONS - Hiérarchie Tâches
    // ===================================

    /**
     * Tâche parente
     */
    public function parentTask()
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    /**
     * Sous-tâches
     */
    public function subtasks()
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    // ===================================
    // RELATIONS - Attribution
    // ===================================

    /**
     * Utilisateur assigné à cette tâche
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Organisation assignée pour exécuter cette tâche
     */
    public function assignedOrganization()
    {
        return $this->belongsTo(Organization::class, 'assigned_organization_id');
    }

    // ===================================
    // SCOPES - Statut
    // ===================================

    /**
     * Filtrer les tâches non démarrées
     */
    public function scopeNotStarted($query)
    {
        return $query->where('status', 'not_started');
    }

    /**
     * Filtrer les tâches en cours
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Filtrer les tâches terminées
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Filtrer les tâches bloquées
     */
    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    /**
     * Filtrer les tâches annulées
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Filtrer les tâches actives (en cours ou bloquées)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['not_started', 'in_progress', 'blocked']);
    }

    // ===================================
    // SCOPES - Priorité
    // ===================================

    /**
     * Filtrer par priorité
     */
    public function scopePriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Filtrer les tâches critiques
     */
    public function scopeCritical($query)
    {
        return $query->where('priority', 'critical');
    }

    /**
     * Filtrer les tâches haute priorité
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'critical']);
    }

    // ===================================
    // SCOPES - Attribution
    // ===================================

    /**
     * Filtrer par utilisateur assigné
     */
    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Filtrer par organisation assignée
     */
    public function scopeForOrganization($query, int $organizationId)
    {
        return $query->where('assigned_organization_id', $organizationId);
    }

    /**
     * Filtrer les tâches non assignées
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to')
                     ->whereNull('assigned_organization_id');
    }

    // ===================================
    // SCOPES - Dates
    // ===================================

    /**
     * Filtrer les tâches en retard
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotNull('end_date')
                     ->where('end_date', '<', now())
                     ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Filtrer les tâches dues bientôt (dans les N jours)
     */
    public function scopeDueSoon($query, int $days = 7)
    {
        return $query->whereNotNull('end_date')
                     ->whereBetween('end_date', [now(), now()->addDays($days)])
                     ->whereNotIn('status', ['completed', 'cancelled']);
    }

    // ===================================
    // HELPERS - Statut
    // ===================================

    /**
     * Vérifier si la tâche n'est pas démarrée
     */
    public function isNotStarted(): bool
    {
        return $this->status === 'not_started';
    }

    /**
     * Vérifier si la tâche est en cours
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Vérifier si la tâche est terminée
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Vérifier si la tâche est bloquée
     */
    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }

    /**
     * Vérifier si la tâche est annulée
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    // ===================================
    // HELPERS - Hiérarchie
    // ===================================

    /**
     * Vérifier si c'est une tâche parente (a des sous-tâches)
     */
    public function hasSubtasks(): bool
    {
        return $this->subtasks()->exists();
    }

    /**
     * Vérifier si c'est une sous-tâche
     */
    public function isSubtask(): bool
    {
        return $this->parent_task_id !== null;
    }

    /**
     * Calculer la progression basée sur les sous-tâches
     */
    public function calculateProgressFromSubtasks(): int
    {
        $subtasks = $this->subtasks;

        if ($subtasks->isEmpty()) {
            return $this->completion_percentage;
        }

        $totalWeight = $subtasks->count();
        $totalProgress = $subtasks->sum('completion_percentage');

        return (int) ($totalProgress / $totalWeight);
    }

    // ===================================
    // HELPERS - Dates et Planning
    // ===================================

    /**
     * Calculer la durée en jours
     */
    public function getDuration(): ?int
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }
        return $this->start_date->diffInDays($this->end_date);
    }

    /**
     * Vérifier si la tâche est en retard
     */
    public function isOverdue(): bool
    {
        if (!$this->end_date) {
            return false;
        }

        return now()->isAfter($this->end_date)
            && !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Vérifier si la tâche est due bientôt
     */
    public function isDueSoon(int $days = 7): bool
    {
        if (!$this->end_date) {
            return false;
        }

        return $this->end_date->isBetween(now(), now()->addDays($days))
            && !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Calculer le nombre de jours avant l'échéance
     */
    public function getDaysUntilDue(): ?int
    {
        if (!$this->end_date) {
            return null;
        }

        return now()->diffInDays($this->end_date, false);
    }

    // ===================================
    // HELPERS - Effort et Performance
    // ===================================

    /**
     * Calculer l'écart d'effort (estimé vs réel)
     */
    public function getEffortVariance(): float
    {
        return ($this->estimated_hours ?? 0) - $this->actual_hours;
    }

    /**
     * Calculer le pourcentage d'effort utilisé
     */
    public function getEffortUsagePercentage(): float
    {
        if (!$this->estimated_hours || $this->estimated_hours == 0) {
            return 0;
        }
        return ($this->actual_hours / $this->estimated_hours) * 100;
    }

    /**
     * Vérifier si la tâche dépasse l'estimation
     */
    public function isOverEstimate(): bool
    {
        return $this->getEffortVariance() < 0;
    }

    // ===================================
    // HELPERS - Dépendances
    // ===================================

    /**
     * Vérifier si la tâche a des dépendances
     */
    public function hasDependencies(): bool
    {
        return !empty($this->dependencies);
    }

    /**
     * Obtenir les tâches dépendantes
     */
    public function getDependentTasks()
    {
        if (!$this->hasDependencies()) {
            return collect();
        }

        return static::whereIn('id', $this->dependencies ?? [])->get();
    }

    /**
     * Vérifier si toutes les dépendances sont complétées
     */
    public function areDependenciesCompleted(): bool
    {
        if (!$this->hasDependencies()) {
            return true;
        }

        $dependentTasks = $this->getDependentTasks();
        return $dependentTasks->every(fn($task) => $task->isCompleted());
    }

    /**
     * Ajouter une dépendance
     */
    public function addDependency(int $taskId): void
    {
        $dependencies = $this->dependencies ?? [];
        if (!in_array($taskId, $dependencies)) {
            $dependencies[] = $taskId;
            $this->dependencies = $dependencies;
            $this->save();
        }
    }

    /**
     * Retirer une dépendance
     */
    public function removeDependency(int $taskId): void
    {
        $dependencies = $this->dependencies ?? [];
        $dependencies = array_diff($dependencies, [$taskId]);
        $this->dependencies = array_values($dependencies);
        $this->save();
    }

    // ===================================
    // HELPERS - Statistiques
    // ===================================

    /**
     * Compter les sous-tâches
     */
    public function getSubtasksCount(): int
    {
        return $this->subtasks()->count();
    }

    /**
     * Compter les sous-tâches terminées
     */
    public function getCompletedSubtasksCount(): int
    {
        return $this->subtasks()->completed()->count();
    }
}

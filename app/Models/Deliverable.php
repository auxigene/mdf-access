<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deliverable extends Model
{
    use SoftDeletes, TenantScoped;

    // ===================================
    // CONFIGURATION
    // ===================================

    protected $fillable = [
        'project_id',
        'wbs_element_id',
        'assigned_organization_id',
        'name',
        'description',
        'type',
        'due_date',
        'delivery_date',
        'status',
        'acceptance_criteria',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'delivery_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // ===================================
    // RELATIONS - Projet
    // ===================================

    /**
     * Projet auquel appartient ce livrable
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // ===================================
    // RELATIONS - Structure WBS
    // ===================================

    /**
     * Élément WBS auquel est rattaché ce livrable
     */
    public function wbsElement()
    {
        return $this->belongsTo(WbsElement::class);
    }

    // ===================================
    // RELATIONS - Organisation et Approbation
    // ===================================

    /**
     * Organisation assignée pour produire ce livrable
     */
    public function assignedOrganization()
    {
        return $this->belongsTo(Organization::class, 'assigned_organization_id');
    }

    /**
     * Utilisateur ayant approuvé le livrable
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ===================================
    // RELATIONS - Tâches
    // ===================================

    /**
     * Tâches associées à ce livrable
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    // ===================================
    // SCOPES - Statut
    // ===================================

    /**
     * Filtrer les livrables non démarrés
     */
    public function scopeNotStarted($query)
    {
        return $query->where('status', 'not_started');
    }

    /**
     * Filtrer les livrables en cours
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Filtrer les livrables terminés
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Filtrer les livrables rejetés
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Filtrer les livrables approuvés
     */
    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    /**
     * Filtrer les livrables en attente d'approbation
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', 'completed')
                     ->whereNull('approved_at');
    }

    // ===================================
    // SCOPES - Organisation
    // ===================================

    /**
     * Filtrer par organisation assignée
     */
    public function scopeForOrganization($query, int $organizationId)
    {
        return $query->where('assigned_organization_id', $organizationId);
    }

    /**
     * Filtrer les livrables sans organisation assignée
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_organization_id');
    }

    // ===================================
    // SCOPES - Dates
    // ===================================

    /**
     * Filtrer les livrables en retard
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
                     ->where('due_date', '<', now())
                     ->whereNotIn('status', ['completed', 'rejected']);
    }

    /**
     * Filtrer les livrables dus bientôt (dans les N jours)
     */
    public function scopeDueSoon($query, int $days = 7)
    {
        return $query->whereNotNull('due_date')
                     ->whereBetween('due_date', [now(), now()->addDays($days)])
                     ->whereNotIn('status', ['completed', 'rejected']);
    }

    // ===================================
    // HELPERS - Statut
    // ===================================

    /**
     * Vérifier si le livrable n'est pas démarré
     */
    public function isNotStarted(): bool
    {
        return $this->status === 'not_started';
    }

    /**
     * Vérifier si le livrable est en cours
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Vérifier si le livrable est terminé
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Vérifier si le livrable est rejeté
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Vérifier si le livrable est approuvé
     */
    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    /**
     * Vérifier si le livrable est en attente d'approbation
     */
    public function isPendingApproval(): bool
    {
        return $this->isCompleted() && !$this->isApproved();
    }

    // ===================================
    // HELPERS - Dates
    // ===================================

    /**
     * Vérifier si le livrable est en retard
     */
    public function isOverdue(): bool
    {
        if (!$this->due_date) {
            return false;
        }

        return now()->isAfter($this->due_date)
            && !in_array($this->status, ['completed', 'rejected']);
    }

    /**
     * Vérifier si le livrable est dû bientôt
     */
    public function isDueSoon(int $days = 7): bool
    {
        if (!$this->due_date) {
            return false;
        }

        return $this->due_date->isBetween(now(), now()->addDays($days))
            && !in_array($this->status, ['completed', 'rejected']);
    }

    /**
     * Calculer le nombre de jours avant l'échéance
     */
    public function getDaysUntilDue(): ?int
    {
        if (!$this->due_date) {
            return null;
        }

        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Calculer le retard en jours
     */
    public function getDelayInDays(): ?int
    {
        if (!$this->isOverdue()) {
            return null;
        }

        return $this->due_date->diffInDays(now());
    }

    // ===================================
    // HELPERS - Approbation
    // ===================================

    /**
     * Approuver le livrable
     */
    public function approve(User $approver): void
    {
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        $this->status = 'completed';
        $this->save();
    }

    /**
     * Rejeter le livrable
     */
    public function reject(): void
    {
        $this->status = 'rejected';
        $this->approved_by = null;
        $this->approved_at = null;
        $this->save();
    }

    /**
     * Marquer comme livré
     */
    public function markAsDelivered(): void
    {
        $this->delivery_date = now();
        $this->status = 'completed';
        $this->save();
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
            return $this->isCompleted() ? 100 : 0;
        }

        $totalWeight = $tasks->count();
        $completedWeight = $tasks->where('status', 'completed')->count();

        return (int) (($completedWeight / $totalWeight) * 100);
    }

    // ===================================
    // HELPERS - Statistiques
    // ===================================

    /**
     * Compter les tâches du livrable
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
     * Calculer le pourcentage de tâches terminées
     */
    public function getTasksCompletionPercentage(): float
    {
        $total = $this->getTasksCount();
        if ($total == 0) {
            return 0;
        }
        return ($this->getCompletedTasksCount() / $total) * 100;
    }
}

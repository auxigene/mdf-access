<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class Phase extends Model
{
    // ===================================
    // CONFIGURATION
    // ===================================

    protected $table = 'project_phases';

    protected $fillable = [
        'project_id',
        'phase_template_id',    // 🆕 Référence au template utilisé
        'parent_phase_id',      // 🆕 Hiérarchie de phases
        'level',                // 🆕 Niveau hiérarchique
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
        'level' => 'integer',
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
    // RELATIONS - Template
    // ===================================

    /**
     * Template de phase utilisé pour créer cette phase
     */
    public function template()
    {
        return $this->belongsTo(PhaseTemplate::class, 'phase_template_id');
    }

    // ===================================
    // RELATIONS - Hiérarchie
    // ===================================

    /**
     * Phase parente (pour sous-phases)
     */
    public function parentPhase()
    {
        return $this->belongsTo(Phase::class, 'parent_phase_id');
    }

    /**
     * Sous-phases (phases enfants)
     */
    public function childPhases()
    {
        return $this->hasMany(Phase::class, 'parent_phase_id')->orderBy('sequence');
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
    // SCOPES - Hiérarchie
    // ===================================

    /**
     * Filtrer les phases racines uniquement (pas de parent)
     */
    public function scopeRootPhases($query)
    {
        return $query->whereNull('parent_phase_id');
    }

    /**
     * Filtrer les sous-phases uniquement (ont un parent)
     */
    public function scopeSubPhases($query)
    {
        return $query->whereNotNull('parent_phase_id');
    }

    /**
     * Filtrer par niveau hiérarchique
     */
    public function scopeLevel($query, int $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Filtrer les phases créées depuis un template
     */
    public function scopeFromTemplate($query)
    {
        return $query->whereNotNull('phase_template_id');
    }

    /**
     * Filtrer les phases custom (sans template)
     */
    public function scopeCustomPhases($query)
    {
        return $query->whereNull('phase_template_id');
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
    // HELPERS - Hiérarchie
    // ===================================

    /**
     * Vérifier si c'est une phase racine (pas de parent)
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_phase_id);
    }

    /**
     * Vérifier si c'est une sous-phase (a un parent)
     */
    public function isSubPhase(): bool
    {
        return !is_null($this->parent_phase_id);
    }

    /**
     * Vérifier si la phase a des sous-phases
     */
    public function hasChildren(): bool
    {
        return $this->childPhases()->exists();
    }

    /**
     * Vérifier si c'est une feuille (pas de sous-phases)
     */
    public function isLeaf(): bool
    {
        return !$this->hasChildren();
    }

    /**
     * Obtenir tous les ancêtres (parent, grand-parent, etc.)
     */
    public function getAncestors(): Collection
    {
        $ancestors = collect();
        $current = $this;

        while ($current->parentPhase) {
            $ancestors->push($current->parentPhase);
            $current = $current->parentPhase;
        }

        return $ancestors->reverse();
    }

    /**
     * Obtenir tous les descendants (enfants, petits-enfants, etc.)
     */
    public function getDescendants(): Collection
    {
        $descendants = collect();

        foreach ($this->childPhases as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getDescendants());
        }

        return $descendants;
    }

    /**
     * Obtenir la phase racine (ancêtre le plus haut)
     */
    public function getRootPhase(): Phase
    {
        $current = $this;

        while ($current->parentPhase) {
            $current = $current->parentPhase;
        }

        return $current;
    }

    /**
     * Obtenir le nom complet avec hiérarchie
     * Ex: "Exécution > Premier Passage Sites > Zone Nord"
     */
    public function getFullName(): string
    {
        $ancestors = $this->getAncestors();
        $names = $ancestors->pluck('name')->push($this->name);
        return $names->implode(' > ');
    }

    /**
     * Vérifier si cette phase a été créée depuis un template
     */
    public function isFromTemplate(): bool
    {
        return !is_null($this->phase_template_id);
    }

    /**
     * Vérifier si c'est une phase custom (créée manuellement)
     */
    public function isCustomPhase(): bool
    {
        return is_null($this->phase_template_id);
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
     * Calculer la progression incluant les sous-phases
     * Si la phase a des sous-phases, la progression est la moyenne pondérée des sous-phases
     * Sinon, calcul basé sur les tâches
     */
    public function calculateProgressFromTasksAndSubPhases(): int
    {
        // Si a des sous-phases, calculer depuis les sous-phases
        if ($this->hasChildren()) {
            $subPhases = $this->childPhases;

            if ($subPhases->isEmpty()) {
                return $this->completion_percentage;
            }

            // Moyenne pondérée des sous-phases
            $avgProgress = $subPhases->avg('completion_percentage');
            return (int) round($avgProgress);
        }

        // Sinon, calculer depuis les tâches (logique existante)
        return $this->calculateProgressFromTasks();
    }

    /**
     * Mettre à jour le pourcentage de complétion automatiquement
     */
    public function updateCompletionPercentage(): void
    {
        $this->completion_percentage = $this->calculateProgressFromTasks();
        $this->save();
    }

    /**
     * Mettre à jour le pourcentage de complétion incluant sous-phases
     */
    public function updateCompletionPercentageWithSubPhases(): void
    {
        $this->completion_percentage = $this->calculateProgressFromTasksAndSubPhases();
        $this->save();

        // Mettre à jour récursivement le parent si existe
        if ($this->parentPhase) {
            $this->parentPhase->updateCompletionPercentageWithSubPhases();
        }
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

    /**
     * Compter le nombre de sous-phases directes
     */
    public function getChildPhasesCount(): int
    {
        return $this->childPhases()->count();
    }

    /**
     * Compter le nombre total de descendants
     */
    public function getDescendantsCount(): int
    {
        return $this->getDescendants()->count();
    }
}


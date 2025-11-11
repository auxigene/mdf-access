<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WbsElement extends Model
{
    use SoftDeletes;

    // ===================================
    // CONFIGURATION
    // ===================================

    protected $fillable = [
        'project_id',
        'parent_id',
        'assigned_organization_id',
        'code',
        'name',
        'description',
        'level',
        'deliverable_type',
        'responsible_id',
        'start_date',
        'end_date',
        'completion_percentage',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'level' => 'integer',
        'completion_percentage' => 'integer',
    ];

    // ===================================
    // RELATIONS - Projet
    // ===================================

    /**
     * Projet auquel appartient cet élément WBS
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // ===================================
    // RELATIONS - Hiérarchie WBS
    // ===================================

    /**
     * Élément WBS parent
     */
    public function parent()
    {
        return $this->belongsTo(WbsElement::class, 'parent_id');
    }

    /**
     * Éléments WBS enfants
     */
    public function children()
    {
        return $this->hasMany(WbsElement::class, 'parent_id');
    }

    /**
     * Tous les descendants (récursif)
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Obtenir tous les ancêtres (chemin vers la racine)
     */
    public function ancestors()
    {
        $ancestors = collect();
        $current = $this->parent;

        while ($current) {
            $ancestors->push($current);
            $current = $current->parent;
        }

        return $ancestors;
    }

    // ===================================
    // RELATIONS - Responsable et Organisation
    // ===================================

    /**
     * Utilisateur responsable de cet élément WBS
     */
    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    /**
     * Organisation assignée pour cet élément WBS
     */
    public function assignedOrganization()
    {
        return $this->belongsTo(Organization::class, 'assigned_organization_id');
    }

    // ===================================
    // RELATIONS - Livrables et Tâches
    // ===================================

    /**
     * Livrables associés à cet élément WBS
     */
    public function deliverables()
    {
        return $this->hasMany(Deliverable::class);
    }

    /**
     * Tâches associées à cet élément WBS
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    // ===================================
    // SCOPES - Niveau
    // ===================================

    /**
     * Filtrer par niveau hiérarchique
     */
    public function scopeLevel($query, int $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Filtrer les éléments racine (niveau 1, sans parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id')->where('level', 1);
    }

    /**
     * Filtrer les éléments feuilles (sans enfants)
     */
    public function scopeLeaves($query)
    {
        return $query->whereDoesntHave('children');
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
     * Filtrer les éléments sans organisation assignée
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_organization_id');
    }

    // ===================================
    // HELPERS - Hiérarchie
    // ===================================

    /**
     * Vérifier si c'est un élément racine
     */
    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * Vérifier si c'est une feuille (pas d'enfants)
     */
    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }

    /**
     * Obtenir la profondeur dans l'arbre
     */
    public function getDepth(): int
    {
        return $this->ancestors()->count();
    }

    /**
     * Obtenir le chemin complet (codes des ancêtres)
     */
    public function getPath(): string
    {
        $ancestors = $this->ancestors()->reverse();
        $path = $ancestors->pluck('code')->implode(' > ');

        return $path ? $path . ' > ' . $this->code : $this->code;
    }

    /**
     * Obtenir le nom complet avec chemin
     */
    public function getFullName(): string
    {
        $ancestors = $this->ancestors()->reverse();
        $path = $ancestors->pluck('name')->implode(' > ');

        return $path ? $path . ' > ' . $this->name : $this->name;
    }

    // ===================================
    // HELPERS - Progression
    // ===================================

    /**
     * Calculer la progression basée sur les enfants
     */
    public function calculateProgressFromChildren(): int
    {
        $children = $this->children;

        if ($children->isEmpty()) {
            // Si pas d'enfants, utiliser la progression des tâches
            return $this->calculateProgressFromTasks();
        }

        $totalWeight = $children->count();
        $totalProgress = $children->sum('completion_percentage');

        return (int) ($totalProgress / $totalWeight);
    }

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
        $this->completion_percentage = $this->calculateProgressFromChildren();
        $this->save();

        // Propager vers le parent
        if ($this->parent) {
            $this->parent->updateCompletionPercentage();
        }
    }

    // ===================================
    // HELPERS - Durée
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
     * Vérifier si l'élément est en retard
     */
    public function isDelayed(): bool
    {
        if (!$this->end_date) {
            return false;
        }

        return now()->isAfter($this->end_date) && $this->completion_percentage < 100;
    }

    // ===================================
    // HELPERS - Statistiques
    // ===================================

    /**
     * Compter le nombre d'enfants
     */
    public function getChildrenCount(): int
    {
        return $this->children()->count();
    }

    /**
     * Compter le nombre total de descendants
     */
    public function getDescendantsCount(): int
    {
        $count = $this->children()->count();

        foreach ($this->children as $child) {
            $count += $child->getDescendantsCount();
        }

        return $count;
    }

    /**
     * Compter les tâches
     */
    public function getTasksCount(): int
    {
        return $this->tasks()->count();
    }

    /**
     * Compter les livrables
     */
    public function getDeliverablesCount(): int
    {
        return $this->deliverables()->count();
    }

    // ===================================
    // HELPERS - Code WBS
    // ===================================

    /**
     * Générer le prochain code enfant
     */
    public function generateNextChildCode(): string
    {
        $maxChild = $this->children()->max('code');

        if (!$maxChild) {
            return $this->code . '.1';
        }

        // Extraire le dernier segment et incrémenter
        $parts = explode('.', $maxChild);
        $lastSegment = (int) end($parts);
        $parts[count($parts) - 1] = $lastSegment + 1;

        return implode('.', $parts);
    }
}

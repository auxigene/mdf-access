<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class PhaseTemplate extends Model
{
    // ===================================
    // CONFIGURATION
    // ===================================

    protected $table = 'phase_templates';

    protected $fillable = [
        'methodology_template_id',
        'parent_phase_id',
        'name',
        'name_fr',
        'description',
        'phase_type',
        'sequence',
        'level',
        'typical_duration_days',
        'typical_duration_percent',
        'key_activities',
        'key_deliverables',
        'entry_criteria',
        'exit_criteria',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'level' => 'integer',
        'typical_duration_days' => 'integer',
        'typical_duration_percent' => 'decimal:2',
        'key_activities' => 'array',
        'key_deliverables' => 'array',
        'entry_criteria' => 'array',
        'exit_criteria' => 'array',
    ];

    // ===================================
    // RELATIONS - Méthodologie
    // ===================================

    /**
     * Méthodologie à laquelle appartient ce template de phase
     */
    public function methodologyTemplate()
    {
        return $this->belongsTo(MethodologyTemplate::class);
    }

    // ===================================
    // RELATIONS - Hiérarchie
    // ===================================

    /**
     * Phase parente (pour sous-phases)
     */
    public function parentPhase()
    {
        return $this->belongsTo(PhaseTemplate::class, 'parent_phase_id');
    }

    /**
     * Sous-phases (phases enfants)
     */
    public function childPhases()
    {
        return $this->hasMany(PhaseTemplate::class, 'parent_phase_id')->orderBy('sequence');
    }

    // ===================================
    // RELATIONS - Instances
    // ===================================

    /**
     * Phases réelles instanciées dans les projets à partir de ce template
     */
    public function instances()
    {
        return $this->hasMany(Phase::class, 'phase_template_id');
    }

    // ===================================
    // SCOPES - Hiérarchie
    // ===================================

    /**
     * Phases racines uniquement (pas de parent)
     */
    public function scopeRootPhases($query)
    {
        return $query->whereNull('parent_phase_id');
    }

    /**
     * Sous-phases uniquement (ont un parent)
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

    // ===================================
    // SCOPES - Type de Phase
    // ===================================

    /**
     * Phases d'initiation
     */
    public function scopeInitiation($query)
    {
        return $query->where('phase_type', 'initiation');
    }

    /**
     * Phases de planification
     */
    public function scopePlanning($query)
    {
        return $query->where('phase_type', 'planning');
    }

    /**
     * Phases d'exécution
     */
    public function scopeExecution($query)
    {
        return $query->where('phase_type', 'execution');
    }

    /**
     * Phases de surveillance
     */
    public function scopeMonitoring($query)
    {
        return $query->where('phase_type', 'monitoring');
    }

    /**
     * Phases de clôture
     */
    public function scopeClosure($query)
    {
        return $query->where('phase_type', 'closure');
    }

    /**
     * Phases custom
     */
    public function scopeCustom($query)
    {
        return $query->where('phase_type', 'custom');
    }

    // ===================================
    // SCOPES - Tri
    // ===================================

    /**
     * Trier par séquence
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
    public function getRootPhase(): PhaseTemplate
    {
        $current = $this;

        while ($current->parentPhase) {
            $current = $current->parentPhase;
        }

        return $current;
    }

    /**
     * Obtenir toutes les phases feuilles (sans enfants) dans cette branche
     */
    public function getLeafPhases(): Collection
    {
        if ($this->isLeaf()) {
            return collect([$this]);
        }

        $leaves = collect();

        foreach ($this->childPhases as $child) {
            $leaves = $leaves->merge($child->getLeafPhases());
        }

        return $leaves;
    }

    // ===================================
    // HELPERS - Nommage
    // ===================================

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
     * Obtenir le nom traduit (français si disponible, sinon anglais)
     */
    public function getLocalizedName(): string
    {
        return $this->name_fr ?? $this->name;
    }

    /**
     * Obtenir le nom complet traduit avec hiérarchie
     */
    public function getFullLocalizedName(): string
    {
        $ancestors = $this->getAncestors();
        $names = $ancestors->map(fn($a) => $a->getLocalizedName())->push($this->getLocalizedName());
        return $names->implode(' > ');
    }

    // ===================================
    // HELPERS - Type de Phase
    // ===================================

    /**
     * Vérifier si c'est une phase d'initiation
     */
    public function isInitiation(): bool
    {
        return $this->phase_type === 'initiation';
    }

    /**
     * Vérifier si c'est une phase de planification
     */
    public function isPlanning(): bool
    {
        return $this->phase_type === 'planning';
    }

    /**
     * Vérifier si c'est une phase d'exécution
     */
    public function isExecution(): bool
    {
        return $this->phase_type === 'execution';
    }

    /**
     * Vérifier si c'est une phase de surveillance
     */
    public function isMonitoring(): bool
    {
        return $this->phase_type === 'monitoring';
    }

    /**
     * Vérifier si c'est une phase de clôture
     */
    public function isClosure(): bool
    {
        return $this->phase_type === 'closure';
    }

    /**
     * Vérifier si c'est une phase custom
     */
    public function isCustom(): bool
    {
        return $this->phase_type === 'custom' || is_null($this->phase_type);
    }

    // ===================================
    // HELPERS - Navigation
    // ===================================

    /**
     * Obtenir la phase précédente (dans la même méthodologie)
     */
    public function getPreviousPhase(): ?PhaseTemplate
    {
        return static::where('methodology_template_id', $this->methodology_template_id)
                     ->where('parent_phase_id', $this->parent_phase_id)
                     ->where('sequence', '<', $this->sequence)
                     ->orderBy('sequence', 'desc')
                     ->first();
    }

    /**
     * Obtenir la phase suivante (dans la même méthodologie)
     */
    public function getNextPhase(): ?PhaseTemplate
    {
        return static::where('methodology_template_id', $this->methodology_template_id)
                     ->where('parent_phase_id', $this->parent_phase_id)
                     ->where('sequence', '>', $this->sequence)
                     ->orderBy('sequence', 'asc')
                     ->first();
    }

    /**
     * Vérifier si c'est la première phase (dans son niveau)
     */
    public function isFirstPhase(): bool
    {
        return $this->sequence === static::where('methodology_template_id', $this->methodology_template_id)
                                          ->where('parent_phase_id', $this->parent_phase_id)
                                          ->min('sequence');
    }

    /**
     * Vérifier si c'est la dernière phase (dans son niveau)
     */
    public function isLastPhase(): bool
    {
        return $this->sequence === static::where('methodology_template_id', $this->methodology_template_id)
                                          ->where('parent_phase_id', $this->parent_phase_id)
                                          ->max('sequence');
    }

    // ===================================
    // HELPERS - Statistiques
    // ===================================

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

    /**
     * Compter le nombre d'instances réelles créées
     */
    public function getInstancesCount(): int
    {
        return $this->instances()->count();
    }

    // ===================================
    // HELPERS - Durée
    // ===================================

    /**
     * Obtenir la durée typique en jours
     * Si typical_duration_days est défini, le retourner
     * Sinon, calculer depuis typical_duration_percent si fourni
     */
    public function getTypicalDurationDays(?int $projectTotalDays = null): ?int
    {
        if ($this->typical_duration_days) {
            return $this->typical_duration_days;
        }

        if ($this->typical_duration_percent && $projectTotalDays) {
            return (int) round(($this->typical_duration_percent / 100) * $projectTotalDays);
        }

        return null;
    }

    /**
     * Obtenir la durée typique en pourcentage
     */
    public function getTypicalDurationPercent(): ?float
    {
        return $this->typical_duration_percent;
    }

    // ===================================
    // HELPERS - Contenu JSON
    // ===================================

    /**
     * Obtenir les activités clés
     */
    public function getKeyActivities(): array
    {
        return $this->key_activities ?? [];
    }

    /**
     * Obtenir les livrables clés
     */
    public function getKeyDeliverables(): array
    {
        return $this->key_deliverables ?? [];
    }

    /**
     * Obtenir les critères d'entrée
     */
    public function getEntryCriteria(): array
    {
        return $this->entry_criteria ?? [];
    }

    /**
     * Obtenir les critères de sortie
     */
    public function getExitCriteria(): array
    {
        return $this->exit_criteria ?? [];
    }

    /**
     * Ajouter une activité clé
     */
    public function addKeyActivity(string $activity): void
    {
        $activities = $this->getKeyActivities();
        $activities[] = $activity;
        $this->key_activities = $activities;
        $this->save();
    }

    /**
     * Ajouter un livrable clé
     */
    public function addKeyDeliverable(string $deliverable): void
    {
        $deliverables = $this->getKeyDeliverables();
        $deliverables[] = $deliverable;
        $this->key_deliverables = $deliverables;
        $this->save();
    }
}

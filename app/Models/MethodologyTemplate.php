<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class MethodologyTemplate extends Model
{
    // ===================================
    // CONFIGURATION
    // ===================================

    protected $table = 'methodology_templates';

    protected $fillable = [
        'name',
        'name_fr',
        'slug',
        'description',
        'category',
        'organization_id',
        'parent_methodology_id',
        'is_system',
        'is_active',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ===================================
    // RELATIONS - Organisation
    // ===================================

    /**
     * Organisation propriétaire du template (null = template système)
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // ===================================
    // RELATIONS - Héritage de Méthodologie
    // ===================================

    /**
     * Méthodologie parente (pour héritage)
     */
    public function parentMethodology()
    {
        return $this->belongsTo(MethodologyTemplate::class, 'parent_methodology_id');
    }

    /**
     * Méthodologies enfants (qui héritent de celle-ci)
     */
    public function childMethodologies()
    {
        return $this->hasMany(MethodologyTemplate::class, 'parent_methodology_id');
    }

    // ===================================
    // RELATIONS - Phases Templates
    // ===================================

    /**
     * Toutes les phases du template (ordonnées par séquence)
     */
    public function phaseTemplates()
    {
        return $this->hasMany(PhaseTemplate::class)->orderBy('sequence');
    }

    /**
     * Phases racines uniquement (pas de parent)
     */
    public function rootPhaseTemplates()
    {
        return $this->hasMany(PhaseTemplate::class)
                    ->whereNull('parent_phase_id')
                    ->orderBy('sequence');
    }

    // ===================================
    // SCOPES - Filtres
    // ===================================

    /**
     * Templates système uniquement
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Templates custom (non système)
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Templates actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Templates par catégorie
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Templates PMBOK
     */
    public function scopePmbok($query)
    {
        return $query->where('category', 'pmbok');
    }

    /**
     * Templates Agile
     */
    public function scopeAgile($query)
    {
        return $query->where('category', 'agile');
    }

    /**
     * Templates disponibles pour une organisation
     * Inclut : templates système + templates de l'organisation
     */
    public function scopeForOrganization($query, int $organizationId)
    {
        return $query->where(function ($q) use ($organizationId) {
            $q->whereNull('organization_id')                  // Templates système
              ->orWhere('organization_id', $organizationId);  // Templates de l'org
        });
    }

    /**
     * Templates racines (sans parent)
     */
    public function scopeRootOnly($query)
    {
        return $query->whereNull('parent_methodology_id');
    }

    // ===================================
    // HELPERS - Identification
    // ===================================

    /**
     * Vérifier si c'est un template système
     */
    public function isSystem(): bool
    {
        return $this->is_system;
    }

    /**
     * Vérifier si c'est un template custom
     */
    public function isCustom(): bool
    {
        return !$this->is_system;
    }

    /**
     * Vérifier si c'est spécifique à une organisation
     */
    public function isOrganizationSpecific(): bool
    {
        return !is_null($this->organization_id);
    }

    /**
     * Vérifier si c'est un template global (disponible pour tous)
     */
    public function isGlobal(): bool
    {
        return is_null($this->organization_id);
    }

    /**
     * Vérifier si le template est actif
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    // ===================================
    // HELPERS - Héritage
    // ===================================

    /**
     * Vérifier si ce template hérite d'un autre
     */
    public function hasParent(): bool
    {
        return !is_null($this->parent_methodology_id);
    }

    /**
     * Vérifier si ce template a des enfants
     */
    public function hasChildren(): bool
    {
        return $this->childMethodologies()->exists();
    }

    /**
     * Obtenir tous les ancêtres (parents, grands-parents, etc.)
     */
    public function getAncestors(): Collection
    {
        $ancestors = collect();
        $current = $this;

        while ($current->parentMethodology) {
            $ancestors->push($current->parentMethodology);
            $current = $current->parentMethodology;
        }

        return $ancestors->reverse();
    }

    /**
     * Obtenir tous les descendants (enfants, petits-enfants, etc.)
     */
    public function getDescendants(): Collection
    {
        $descendants = collect();

        foreach ($this->childMethodologies as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getDescendants());
        }

        return $descendants;
    }

    /**
     * Obtenir le template racine (ancêtre le plus haut)
     */
    public function getRootMethodology(): MethodologyTemplate
    {
        $current = $this;

        while ($current->parentMethodology) {
            $current = $current->parentMethodology;
        }

        return $current;
    }

    // ===================================
    // HELPERS - Phases
    // ===================================

    /**
     * Obtenir toutes les phases incluant celles héritées du parent
     * Les phases du template actuel peuvent overrider celles du parent (même sequence)
     */
    public function getAllPhases(): Collection
    {
        $phases = $this->phaseTemplates;

        // Si a un parent, récupérer ses phases
        if ($this->hasParent()) {
            $parentPhases = $this->parentMethodology->getAllPhases();

            // Merger intelligemment : éviter les doublons
            // Si une phase du template actuel a la même séquence qu'une phase parent,
            // la phase actuelle override (remplace) celle du parent
            $currentSequences = $phases->pluck('sequence')->toArray();

            $parentPhasesFiltered = $parentPhases->filter(function ($parentPhase) use ($currentSequences) {
                return !in_array($parentPhase->sequence, $currentSequences);
            });

            $phases = $phases->merge($parentPhasesFiltered);
        }

        return $phases->sortBy('sequence')->values();
    }

    /**
     * Compter le nombre total de phases (incluant héritées)
     */
    public function getTotalPhasesCount(): int
    {
        return $this->getAllPhases()->count();
    }

    /**
     * Compter uniquement les phases propres (pas héritées)
     */
    public function getOwnPhasesCount(): int
    {
        return $this->phaseTemplates()->count();
    }

    /**
     * Compter les phases héritées du parent
     */
    public function getInheritedPhasesCount(): int
    {
        if (!$this->hasParent()) {
            return 0;
        }

        return $this->parentMethodology->getAllPhases()->count();
    }

    // ===================================
    // HELPERS - Catégories
    // ===================================

    /**
     * Vérifier si c'est une méthodologie PMBOK
     */
    public function isPmbok(): bool
    {
        return $this->category === 'pmbok';
    }

    /**
     * Vérifier si c'est une méthodologie Agile
     */
    public function isAgile(): bool
    {
        return $this->category === 'agile';
    }

    /**
     * Vérifier si c'est une méthodologie Hybrid
     */
    public function isHybrid(): bool
    {
        return $this->category === 'hybrid';
    }

    // ===================================
    // HELPERS - Noms
    // ===================================

    /**
     * Obtenir le nom traduit (français si disponible, sinon anglais)
     */
    public function getLocalizedName(): string
    {
        return $this->name_fr ?? $this->name;
    }

    /**
     * Obtenir le nom complet avec héritage
     * Ex: "PMBOK Waterfall > SAMSIC Telecom"
     */
    public function getFullName(): string
    {
        $ancestors = $this->getAncestors();
        $names = $ancestors->pluck('name')->push($this->name);
        return $names->implode(' > ');
    }

    // ===================================
    // METHODS - Manipulation
    // ===================================

    /**
     * Activer le template
     */
    public function activate(): void
    {
        $this->is_active = true;
        $this->save();
    }

    /**
     * Désactiver le template
     */
    public function deactivate(): void
    {
        $this->is_active = false;
        $this->save();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class ProjectOrganization extends Model
{
    // ===================================
    // CONFIGURATION
    // ===================================

    protected $fillable = [
        'project_id',
        'organization_id',
        'role',
        'reference',
        'scope_description',
        'is_primary',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_primary' => 'boolean',
    ];

    // ===================================
    // RELATIONS - Projet
    // ===================================

    /**
     * Projet auquel cette participation se rapporte
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // ===================================
    // RELATIONS - Organisation
    // ===================================

    /**
     * Organisation participante
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // ===================================
    // SCOPES - Rôle
    // ===================================

    /**
     * Filtrer les sponsors
     */
    public function scopeSponsors($query)
    {
        return $query->where('role', 'sponsor');
    }

    /**
     * Filtrer les MOA (Maître d'Ouvrage)
     */
    public function scopeMoa($query)
    {
        return $query->where('role', 'moa');
    }

    /**
     * Filtrer les MOE (Maître d'Œuvre)
     */
    public function scopeMoe($query)
    {
        return $query->where('role', 'moe');
    }

    /**
     * Filtrer les sous-traitants
     */
    public function scopeSubcontractors($query)
    {
        return $query->where('role', 'subcontractor');
    }

    /**
     * Filtrer par rôle spécifique
     */
    public function scopeOfRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    // ===================================
    // SCOPES - Statut
    // ===================================

    /**
     * Filtrer les participations actives
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Filtrer les participations inactives
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Filtrer les participations terminées
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // ===================================
    // SCOPES - Primauté
    // ===================================

    /**
     * Filtrer les MOE/sous-traitants primaires
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Filtrer les MOE/sous-traitants secondaires
     */
    public function scopeSecondary($query)
    {
        return $query->where('is_primary', false);
    }

    // ===================================
    // HELPERS - Rôle
    // ===================================

    /**
     * Vérifier si c'est un sponsor
     */
    public function isSponsor(): bool
    {
        return $this->role === 'sponsor';
    }

    /**
     * Vérifier si c'est un MOA
     */
    public function isMoa(): bool
    {
        return $this->role === 'moa';
    }

    /**
     * Vérifier si c'est un MOE
     */
    public function isMoe(): bool
    {
        return $this->role === 'moe';
    }

    /**
     * Vérifier si c'est un sous-traitant
     */
    public function isSubcontractor(): bool
    {
        return $this->role === 'subcontractor';
    }

    /**
     * Vérifier si le rôle permet d'être marqué comme primaire
     */
    public function canBePrimary(): bool
    {
        return in_array($this->role, ['moe', 'subcontractor']);
    }

    // ===================================
    // HELPERS - Statut
    // ===================================

    /**
     * Vérifier si la participation est active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Vérifier si la participation est inactive
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    /**
     * Vérifier si la participation est terminée
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    // ===================================
    // HELPERS - Période d'Intervention
    // ===================================

    /**
     * Calculer la durée d'intervention en jours
     */
    public function getDuration(): ?int
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }
        return $this->start_date->diffInDays($this->end_date);
    }

    /**
     * Vérifier si l'intervention est en cours
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
     * Vérifier si l'intervention est future
     */
    public function isFuture(): bool
    {
        if (!$this->start_date) {
            return false;
        }

        return $this->start_date->isFuture();
    }

    /**
     * Vérifier si l'intervention est passée
     */
    public function isPast(): bool
    {
        if (!$this->end_date) {
            return false;
        }

        return $this->end_date->isPast();
    }

    // ===================================
    // VALIDATION - Contraintes Métier
    // ===================================

    /**
     * Boot method pour validation automatique
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($projectOrganization) {
            $projectOrganization->validateBusinessRules();
        });
    }

    /**
     * Valider toutes les règles métier
     */
    protected function validateBusinessRules(): void
    {
        $this->validatePrimaryFlag();
        $this->validateScopeDescription();
        $this->validateDates();
        $this->validateUniqueness();
    }

    /**
     * Règle 1: is_primary ne peut être true que pour moe/subcontractor
     */
    protected function validatePrimaryFlag(): void
    {
        if ($this->is_primary && !$this->canBePrimary()) {
            throw ValidationException::withMessages([
                'is_primary' => "Le champ 'is_primary' ne peut être activé que pour les rôles 'moe' ou 'subcontractor'."
            ]);
        }
    }

    /**
     * Règle 2 & 3: sponsor et moa ne peuvent pas avoir de scope_description
     */
    protected function validateScopeDescription(): void
    {
        if ($this->isSponsor() && !empty($this->scope_description)) {
            throw ValidationException::withMessages([
                'scope_description' => "Un sponsor ne peut pas avoir de description de scope (il concerne tout le projet)."
            ]);
        }

        if ($this->isMoa() && !empty($this->scope_description)) {
            throw ValidationException::withMessages([
                'scope_description' => "Un MOA ne peut pas avoir de description de scope (il gère tout le projet)."
            ]);
        }
    }

    /**
     * Règle 5: Les dates de sous-traitance doivent être dans les bornes du projet
     */
    protected function validateDates(): void
    {
        if (!$this->start_date || !$this->end_date) {
            return;
        }

        // Vérifier que start_date < end_date
        if ($this->start_date->gte($this->end_date)) {
            throw ValidationException::withMessages([
                'start_date' => "La date de début doit être antérieure à la date de fin."
            ]);
        }

        // Pour les sous-traitants, vérifier les bornes du projet
        if ($this->isSubcontractor() && $this->project) {
            if ($this->project->start_date && $this->start_date->lt($this->project->start_date)) {
                throw ValidationException::withMessages([
                    'start_date' => "La date de début du sous-traitant ne peut pas être avant la date de début du projet."
                ]);
            }

            if ($this->project->end_date && $this->end_date->gt($this->project->end_date)) {
                throw ValidationException::withMessages([
                    'end_date' => "La date de fin du sous-traitant ne peut pas être après la date de fin du projet."
                ]);
            }
        }
    }

    /**
     * Règle 4: Validation de l'unicité (sponsor, MOA, MOE primaire)
     * Cette validation est partiellement gérée par les contraintes DB,
     * mais on peut ajouter une validation applicative supplémentaire
     */
    protected function validateUniqueness(): void
    {
        // Si c'est un sponsor actif, vérifier qu'il n'y en a pas déjà un
        if ($this->isSponsor() && $this->isActive()) {
            $existingSponsor = static::where('project_id', $this->project_id)
                ->where('role', 'sponsor')
                ->where('status', 'active')
                ->where('id', '!=', $this->id)
                ->exists();

            if ($existingSponsor) {
                throw ValidationException::withMessages([
                    'role' => "Ce projet a déjà un sponsor actif. Désactivez l'ancien avant d'en ajouter un nouveau."
                ]);
            }
        }

        // Si c'est un MOA actif, vérifier qu'il n'y en a pas déjà un
        if ($this->isMoa() && $this->isActive()) {
            $existingMoa = static::where('project_id', $this->project_id)
                ->where('role', 'moa')
                ->where('status', 'active')
                ->where('id', '!=', $this->id)
                ->exists();

            if ($existingMoa) {
                throw ValidationException::withMessages([
                    'role' => "Ce projet a déjà un MOA actif. Désactivez l'ancien avant d'en ajouter un nouveau."
                ]);
            }
        }

        // Si c'est un MOE/subcontractor primaire actif, vérifier qu'il n'y en a pas déjà un
        if ($this->is_primary && $this->isActive() && $this->canBePrimary()) {
            $existingPrimaryMoe = static::where('project_id', $this->project_id)
                ->whereIn('role', ['moe', 'subcontractor'])
                ->where('is_primary', true)
                ->where('status', 'active')
                ->where('id', '!=', $this->id)
                ->exists();

            if ($existingPrimaryMoe) {
                throw ValidationException::withMessages([
                    'is_primary' => "Ce projet a déjà un MOE primaire actif. Désactivez l'ancien avant d'en marquer un nouveau comme primaire."
                ]);
            }
        }
    }

    // ===================================
    // HELPERS - Vérifications Projet
    // ===================================

    /**
     * Vérifier si le projet a un sponsor actif
     */
    public static function projectHasActiveSponsor(int $projectId): bool
    {
        return static::where('project_id', $projectId)
            ->where('role', 'sponsor')
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Vérifier si le projet a un MOA actif
     */
    public static function projectHasActiveMoa(int $projectId): bool
    {
        return static::where('project_id', $projectId)
            ->where('role', 'moa')
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Vérifier si le projet a un MOE actif (primaire ou non)
     */
    public static function projectHasActiveMoe(int $projectId): bool
    {
        return static::where('project_id', $projectId)
            ->whereIn('role', ['moe', 'subcontractor'])
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Vérifier si le projet a tous les acteurs requis
     */
    public static function projectHasRequiredActors(int $projectId): bool
    {
        return static::projectHasActiveSponsor($projectId)
            && static::projectHasActiveMoa($projectId)
            && static::projectHasActiveMoe($projectId);
    }
}

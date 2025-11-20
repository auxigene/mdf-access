<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes, TenantScoped;

    // ===================================
    // CONFIGURATION
    // ===================================

    protected $fillable = [
        'program_id',
        'client_organization_id',
        'client_reference',
        'code',
        'name',
        'description',
        'project_manager_id',
        'project_type',
        'methodology',
        'start_date',
        'end_date',
        'baseline_start',
        'baseline_end',
        'budget',
        'actual_cost',
        'status',
        'priority',
        'health_status',
        'charter_approved_at',
        'charter_approved_by',
        'completion_percentage',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'baseline_start' => 'date',
        'baseline_end' => 'date',
        'budget' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'charter_approved_at' => 'datetime',
        'completion_percentage' => 'integer',
    ];

    // ===================================
    // RELATIONS - Programme
    // ===================================

    /**
     * Programme auquel appartient ce projet
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    // ===================================
    // RELATIONS - Organisation Client
    // ===================================

    /**
     * Organisation cliente/sponsor principal du projet
     */
    public function clientOrganization()
    {
        return $this->belongsTo(Organization::class, 'client_organization_id');
    }

    // ===================================
    // RELATIONS - Participations Organisations
    // ===================================

    /**
     * Participations d'organisations dans ce projet
     * (enregistrements dans project_organizations avec détails du rôle)
     */
    public function projectOrganizations()
    {
        return $this->hasMany(ProjectOrganization::class);
    }

    /**
     * Organisations participantes (tous rôles confondus)
     * Via la table pivot project_organizations
     */
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'project_organizations')
                    ->withPivot([
                        'role',
                        'reference',
                        'scope_description',
                        'is_primary',
                        'status',
                        'start_date',
                        'end_date'
                    ])
                    ->withTimestamps();
    }

    // ===================================
    // RELATIONS - Équipe Projet
    // ===================================

    /**
     * Chef de projet (Project Manager)
     */
    public function projectManager()
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    /**
     * Utilisateur ayant approuvé la charte projet
     */
    public function charterApprovedBy()
    {
        return $this->belongsTo(User::class, 'charter_approved_by');
    }

    // ===================================
    // RELATIONS - Structure Projet (PMBOK)
    // ===================================

    /**
     * Phases du projet
     */
    public function phases()
    {
        return $this->hasMany(Phase::class);
    }

    /**
     * Éléments WBS du projet
     */
    public function wbsElements()
    {
        return $this->hasMany(WbsElement::class);
    }

    /**
     * Livrables du projet
     */
    public function deliverables()
    {
        return $this->hasMany(Deliverable::class);
    }

    /**
     * Tâches du projet
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Jalons du projet
     */
    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }

    /**
     * Risques du projet
     */
    public function risks()
    {
        return $this->hasMany(Risk::class);
    }

    /**
     * Problèmes du projet
     */
    public function issues()
    {
        return $this->hasMany(Issue::class);
    }

    /**
     * Demandes de changement
     */
    public function changeRequests()
    {
        return $this->hasMany(ChangeRequest::class);
    }

    /**
     * Allocations de ressources pour ce projet
     */
    public function resourceAllocations()
    {
        return $this->hasMany(ResourceAllocation::class);
    }

    // ===================================
    // SCOPES - Statut
    // ===================================

    /**
     * Filtrer les projets en phase d'initiation
     */
    public function scopeInitiation($query)
    {
        return $query->where('status', 'initiation');
    }

    /**
     * Filtrer les projets en planification
     */
    public function scopePlanning($query)
    {
        return $query->where('status', 'planning');
    }

    /**
     * Filtrer les projets en exécution
     */
    public function scopeExecution($query)
    {
        return $query->where('status', 'execution');
    }

    /**
     * Filtrer les projets en suivi/contrôle
     */
    public function scopeMonitoring($query)
    {
        return $query->where('status', 'monitoring');
    }

    /**
     * Filtrer les projets en clôture
     */
    public function scopeClosure($query)
    {
        return $query->where('status', 'closure');
    }

    /**
     * Filtrer les projets en pause
     */
    public function scopeOnHold($query)
    {
        return $query->where('status', 'on_hold');
    }

    /**
     * Filtrer les projets annulés
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Filtrer les projets actifs (non terminés, non annulés)
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['closure', 'cancelled']);
    }

    // ===================================
    // SCOPES - Santé du Projet
    // ===================================

    /**
     * Projets en bonne santé
     */
    public function scopeHealthy($query)
    {
        return $query->where('health_status', 'green');
    }

    /**
     * Projets avec vigilance
     */
    public function scopeAtRisk($query)
    {
        return $query->where('health_status', 'yellow');
    }

    /**
     * Projets en danger
     */
    public function scopeCritical($query)
    {
        return $query->where('health_status', 'red');
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
     * Projets critiques
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'critical']);
    }

    // ===================================
    // SCOPES - Méthodologie
    // ===================================

    /**
     * Filtrer par méthodologie
     */
    public function scopeMethodology($query, string $methodology)
    {
        return $query->where('methodology', $methodology);
    }

    // ===================================
    // HELPERS - Organisations par Rôle
    // ===================================

    /**
     * Récupérer l'organisation sponsor active du projet
     */
    public function getSponsor(): ?Organization
    {
        return $this->projectOrganizations()
                    ->where('role', 'sponsor')
                    ->where('status', 'active')
                    ->with('organization')
                    ->first()
                    ?->organization;
    }

    /**
     * Récupérer l'organisation MOA (Maître d'Ouvrage) active du projet
     */
    public function getMoa(): ?Organization
    {
        return $this->projectOrganizations()
                    ->where('role', 'moa')
                    ->where('status', 'active')
                    ->with('organization')
                    ->first()
                    ?->organization;
    }

    /**
     * Récupérer l'organisation MOE (Maître d'Œuvre) primaire active du projet
     */
    public function getPrimaryMoe(): ?Organization
    {
        return $this->projectOrganizations()
                    ->where('role', 'moe')
                    ->where('is_primary', true)
                    ->where('status', 'active')
                    ->with('organization')
                    ->first()
                    ?->organization;
    }

    /**
     * Récupérer toutes les organisations MOE actives (primaire + secondaires)
     */
    public function getAllMoe()
    {
        return $this->projectOrganizations()
                    ->where('role', 'moe')
                    ->where('status', 'active')
                    ->with('organization')
                    ->get()
                    ->pluck('organization');
    }

    /**
     * Récupérer tous les sous-traitants actifs du projet
     */
    public function getSubcontractors()
    {
        return $this->projectOrganizations()
                    ->where('role', 'subcontractor')
                    ->where('status', 'active')
                    ->with('organization')
                    ->get()
                    ->pluck('organization');
    }

    // ===================================
    // HELPERS - Charte Projet
    // ===================================

    /**
     * Vérifier si la charte projet est approuvée
     */
    public function isCharterApproved(): bool
    {
        return $this->charter_approved_at !== null;
    }

    /**
     * Approuver la charte projet
     */
    public function approveCharter(User $approver): void
    {
        $this->charter_approved_at = now();
        $this->charter_approved_by = $approver->id;
        $this->save();
    }

    // ===================================
    // HELPERS - Budget et Coûts
    // ===================================

    /**
     * Calculer l'écart budgétaire (Budget - Coûts réels)
     */
    public function getBudgetVariance(): float
    {
        return ($this->budget ?? 0) - $this->actual_cost;
    }

    /**
     * Calculer le pourcentage d'utilisation du budget
     */
    public function getBudgetUsagePercentage(): float
    {
        if (!$this->budget || $this->budget == 0) {
            return 0;
        }
        return ($this->actual_cost / $this->budget) * 100;
    }

    /**
     * Vérifier si le projet est en dépassement budgétaire
     */
    public function isOverBudget(): bool
    {
        return $this->getBudgetVariance() < 0;
    }

    // ===================================
    // HELPERS - Planning
    // ===================================

    /**
     * Calculer la durée planifiée du projet en jours
     */
    public function getPlannedDuration(): ?int
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }
        return $this->start_date->diffInDays($this->end_date);
    }

    /**
     * Calculer la durée baseline du projet en jours
     */
    public function getBaselineDuration(): ?int
    {
        if (!$this->baseline_start || !$this->baseline_end) {
            return null;
        }
        return $this->baseline_start->diffInDays($this->baseline_end);
    }

    /**
     * Calculer l'écart de planning (jours)
     */
    public function getScheduleVariance(): ?int
    {
        $planned = $this->getPlannedDuration();
        $baseline = $this->getBaselineDuration();

        if ($planned === null || $baseline === null) {
            return null;
        }

        return $baseline - $planned;
    }

    /**
     * Vérifier si le projet est en retard
     */
    public function isBehindSchedule(): bool
    {
        $variance = $this->getScheduleVariance();
        return $variance !== null && $variance < 0;
    }

    /**
     * Vérifier si le projet est terminé
     */
    public function isCompleted(): bool
    {
        return $this->status === 'closure' && $this->completion_percentage === 100;
    }

    // ===================================
    // HELPERS - Statut
    // ===================================

    /**
     * Vérifier si le projet est actif
     */
    public function isActive(): bool
    {
        return !in_array($this->status, ['closure', 'cancelled']);
    }

    /**
     * Vérifier si le projet est annulé
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Vérifier si le projet est en pause
     */
    public function isOnHold(): bool
    {
        return $this->status === 'on_hold';
    }
}

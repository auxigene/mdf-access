<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class ChangeRequest extends Model
{
    use TenantScoped;

    // ===================================
    // CONFIGURATION
    // ===================================

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'justification',
        'impact_analysis',
        'cost_impact',
        'schedule_impact',
        'status',
        'requested_by',
        'approved_by',
        'approval_date',
    ];

    protected $casts = [
        'cost_impact' => 'decimal:2',
        'schedule_impact' => 'integer',
        'approval_date' => 'datetime',
    ];

    // ===================================
    // RELATIONS - Projet
    // ===================================

    /**
     * Projet auquel appartient cette demande de changement
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // ===================================
    // RELATIONS - Utilisateurs
    // ===================================

    /**
     * Utilisateur ayant demandé le changement
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Utilisateur ayant approuvé le changement
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ===================================
    // SCOPES - Statut
    // ===================================

    /**
     * Filtrer les demandes soumises
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    /**
     * Filtrer les demandes en cours de révision
     */
    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    /**
     * Filtrer les demandes approuvées
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Filtrer les demandes rejetées
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Filtrer les demandes implémentées
     */
    public function scopeImplemented($query)
    {
        return $query->where('status', 'implemented');
    }

    /**
     * Filtrer les demandes en attente (soumises ou en révision)
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['submitted', 'under_review']);
    }

    // ===================================
    // SCOPES - Impact
    // ===================================

    /**
     * Filtrer les demandes avec impact budgétaire
     */
    public function scopeWithCostImpact($query)
    {
        return $query->whereNotNull('cost_impact')
                     ->where('cost_impact', '>', 0);
    }

    /**
     * Filtrer les demandes avec impact sur le planning
     */
    public function scopeWithScheduleImpact($query)
    {
        return $query->whereNotNull('schedule_impact')
                     ->where('schedule_impact', '>', 0);
    }

    /**
     * Filtrer les demandes sans impact
     */
    public function scopeMinorImpact($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('cost_impact')
              ->orWhere('cost_impact', 0);
        })->where(function ($q) {
            $q->whereNull('schedule_impact')
              ->orWhere('schedule_impact', 0);
        });
    }

    // ===================================
    // HELPERS - Statut
    // ===================================

    /**
     * Vérifier si la demande est soumise
     */
    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    /**
     * Vérifier si la demande est en révision
     */
    public function isUnderReview(): bool
    {
        return $this->status === 'under_review';
    }

    /**
     * Vérifier si la demande est approuvée
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Vérifier si la demande est rejetée
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Vérifier si la demande est implémentée
     */
    public function isImplemented(): bool
    {
        return $this->status === 'implemented';
    }

    /**
     * Vérifier si la demande est en attente
     */
    public function isPending(): bool
    {
        return in_array($this->status, ['submitted', 'under_review']);
    }

    // ===================================
    // HELPERS - Impact
    // ===================================

    /**
     * Vérifier si la demande a un impact budgétaire
     */
    public function hasCostImpact(): bool
    {
        return $this->cost_impact !== null && $this->cost_impact > 0;
    }

    /**
     * Vérifier si la demande a un impact sur le planning
     */
    public function hasScheduleImpact(): bool
    {
        return $this->schedule_impact !== null && $this->schedule_impact > 0;
    }

    /**
     * Vérifier si la demande a un impact majeur
     */
    public function hasMajorImpact(): bool
    {
        return ($this->cost_impact && $this->cost_impact > 10000)
            || ($this->schedule_impact && $this->schedule_impact > 30);
    }

    /**
     * Obtenir le niveau d'impact global
     */
    public function getImpactLevel(): string
    {
        if ($this->hasMajorImpact()) {
            return 'major';
        } elseif ($this->hasCostImpact() || $this->hasScheduleImpact()) {
            return 'moderate';
        }
        return 'minor';
    }

    // ===================================
    // HELPERS - Actions
    // ===================================

    /**
     * Mettre en révision
     */
    public function startReview(): void
    {
        $this->status = 'under_review';
        $this->save();
    }

    /**
     * Approuver la demande
     */
    public function approve(User $approver): void
    {
        $this->status = 'approved';
        $this->approved_by = $approver->id;
        $this->approval_date = now();
        $this->save();
    }

    /**
     * Rejeter la demande
     */
    public function reject(): void
    {
        $this->status = 'rejected';
        $this->approved_by = null;
        $this->approval_date = null;
        $this->save();
    }

    /**
     * Marquer comme implémentée
     */
    public function markAsImplemented(): void
    {
        if (!$this->isApproved()) {
            throw new \Exception('Cannot implement a change request that is not approved.');
        }

        $this->status = 'implemented';
        $this->save();
    }

    // ===================================
    // HELPERS - Temps
    // ===================================

    /**
     * Calculer le temps d'approbation en jours
     */
    public function getApprovalTime(): ?int
    {
        if (!$this->approval_date) {
            return null;
        }

        return $this->created_at->diffInDays($this->approval_date);
    }

    /**
     * Calculer l'âge de la demande en jours
     */
    public function getAge(): int
    {
        return $this->created_at->diffInDays(now());
    }
}

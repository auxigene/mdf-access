<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    use TenantScoped;

    // ===================================
    // CONFIGURATION
    // ===================================

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'due_date',
        'status',
        'critical',
        'achieved_date',
    ];

    protected $casts = [
        'due_date' => 'date',
        'achieved_date' => 'date',
        'critical' => 'boolean',
    ];

    // ===================================
    // RELATIONS - Projet
    // ===================================

    /**
     * Projet auquel appartient ce jalon
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // ===================================
    // SCOPES - Statut
    // ===================================

    /**
     * Filtrer les jalons en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Filtrer les jalons atteints
     */
    public function scopeAchieved($query)
    {
        return $query->where('status', 'achieved');
    }

    /**
     * Filtrer les jalons manqués
     */
    public function scopeMissed($query)
    {
        return $query->where('status', 'missed');
    }

    // ===================================
    // SCOPES - Criticité
    // ===================================

    /**
     * Filtrer les jalons critiques
     */
    public function scopeCritical($query)
    {
        return $query->where('critical', true);
    }

    // ===================================
    // SCOPES - Dates
    // ===================================

    /**
     * Filtrer les jalons en retard
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
                     ->where('due_date', '<', now());
    }

    /**
     * Filtrer les jalons à venir bientôt
     */
    public function scopeUpcoming($query, int $days = 30)
    {
        return $query->where('status', 'pending')
                     ->whereBetween('due_date', [now(), now()->addDays($days)]);
    }

    // ===================================
    // HELPERS - Statut
    // ===================================

    /**
     * Vérifier si le jalon est en attente
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Vérifier si le jalon est atteint
     */
    public function isAchieved(): bool
    {
        return $this->status === 'achieved';
    }

    /**
     * Vérifier si le jalon est manqué
     */
    public function isMissed(): bool
    {
        return $this->status === 'missed';
    }

    /**
     * Vérifier si le jalon est critique
     */
    public function isCritical(): bool
    {
        return $this->critical === true;
    }

    // ===================================
    // HELPERS - Dates
    // ===================================

    /**
     * Vérifier si le jalon est en retard
     */
    public function isOverdue(): bool
    {
        return $this->isPending() && now()->isAfter($this->due_date);
    }

    /**
     * Vérifier si le jalon est à venir bientôt
     */
    public function isUpcoming(int $days = 30): bool
    {
        return $this->isPending()
            && $this->due_date->isBetween(now(), now()->addDays($days));
    }

    /**
     * Calculer le nombre de jours avant l'échéance
     */
    public function getDaysUntilDue(): ?int
    {
        if (!$this->isPending()) {
            return null;
        }

        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Calculer le retard en jours
     */
    public function getDelayInDays(): ?int
    {
        if ($this->isAchieved() && $this->achieved_date) {
            return $this->due_date->diffInDays($this->achieved_date, false);
        }

        if ($this->isOverdue()) {
            return $this->due_date->diffInDays(now());
        }

        return null;
    }

    // ===================================
    // HELPERS - Actions
    // ===================================

    /**
     * Marquer le jalon comme atteint
     */
    public function markAsAchieved(): void
    {
        $this->status = 'achieved';
        $this->achieved_date = now();
        $this->save();
    }

    /**
     * Marquer le jalon comme manqué
     */
    public function markAsMissed(): void
    {
        $this->status = 'missed';
        $this->save();
    }
}

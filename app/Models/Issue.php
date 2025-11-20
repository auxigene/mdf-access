<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    use TenantScoped;

    // ===================================
    // CONFIGURATION
    // ===================================

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'severity',
        'priority',
        'status',
        'reported_by',
        'assigned_to',
        'reported_date',
        'resolved_date',
        'resolution',
    ];

    protected $casts = [
        'reported_date' => 'date',
        'resolved_date' => 'date',
    ];

    // ===================================
    // RELATIONS - Projet
    // ===================================

    /**
     * Projet auquel appartient ce problème
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // ===================================
    // RELATIONS - Utilisateurs
    // ===================================

    /**
     * Utilisateur ayant rapporté le problème
     */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Utilisateur assigné pour résoudre le problème
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // ===================================
    // SCOPES - Statut
    // ===================================

    /**
     * Filtrer les problèmes ouverts
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Filtrer les problèmes en cours
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Filtrer les problèmes résolus
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Filtrer les problèmes clôturés
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Filtrer les problèmes actifs (ouverts ou en cours)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    // ===================================
    // SCOPES - Sévérité
    // ===================================

    /**
     * Filtrer par sévérité
     */
    public function scopeSeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Filtrer les problèmes critiques
     */
    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    /**
     * Filtrer les problèmes de sévérité élevée
     */
    public function scopeHighSeverity($query)
    {
        return $query->whereIn('severity', ['high', 'critical']);
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
     * Filtrer les problèmes haute priorité
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
     * Filtrer les problèmes non assignés
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    // ===================================
    // HELPERS - Statut
    // ===================================

    /**
     * Vérifier si le problème est ouvert
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Vérifier si le problème est en cours
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Vérifier si le problème est résolu
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Vérifier si le problème est clôturé
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Vérifier si le problème est actif
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['open', 'in_progress']);
    }

    // ===================================
    // HELPERS - Sévérité et Priorité
    // ===================================

    /**
     * Vérifier si le problème est critique
     */
    public function isCritical(): bool
    {
        return $this->severity === 'critical' || $this->priority === 'critical';
    }

    /**
     * Vérifier si haute sévérité
     */
    public function isHighSeverity(): bool
    {
        return in_array($this->severity, ['high', 'critical']);
    }

    /**
     * Vérifier si haute priorité
     */
    public function isHighPriority(): bool
    {
        return in_array($this->priority, ['high', 'critical']);
    }

    // ===================================
    // HELPERS - Temps de Résolution
    // ===================================

    /**
     * Calculer le temps de résolution en jours
     */
    public function getResolutionTime(): ?int
    {
        if (!$this->resolved_date) {
            return null;
        }

        return $this->reported_date->diffInDays($this->resolved_date);
    }

    /**
     * Calculer l'âge du problème en jours
     */
    public function getAge(): int
    {
        $endDate = $this->resolved_date ?? now();
        return $this->reported_date->diffInDays($endDate);
    }

    // ===================================
    // HELPERS - Actions
    // ===================================

    /**
     * Marquer comme en cours
     */
    public function markAsInProgress(): void
    {
        $this->status = 'in_progress';
        $this->save();
    }

    /**
     * Résoudre le problème
     */
    public function resolve(string $resolution): void
    {
        $this->status = 'resolved';
        $this->resolved_date = now();
        $this->resolution = $resolution;
        $this->save();
    }

    /**
     * Clôturer le problème
     */
    public function close(): void
    {
        $this->status = 'closed';
        if (!$this->resolved_date) {
            $this->resolved_date = now();
        }
        $this->save();
    }

    /**
     * Réouvrir le problème
     */
    public function reopen(): void
    {
        $this->status = 'open';
        $this->resolved_date = null;
        $this->resolution = null;
        $this->save();
    }

    /**
     * Assigner à un utilisateur
     */
    public function assignTo(User $user): void
    {
        $this->assigned_to = $user->id;
        if ($this->isOpen()) {
            $this->status = 'in_progress';
        }
        $this->save();
    }
}

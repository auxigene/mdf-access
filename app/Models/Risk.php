<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Risk extends Model
{
    // ===================================
    // CONFIGURATION
    // ===================================

    protected $fillable = [
        'project_id',
        'category',
        'description',
        'probability',
        'impact',
        'risk_score',
        'mitigation_strategy',
        'owner_id',
        'status',
        'identified_date',
        'review_date',
    ];

    protected $casts = [
        'probability' => 'integer',
        'impact' => 'integer',
        'risk_score' => 'integer',
        'identified_date' => 'date',
        'review_date' => 'date',
    ];

    // ===================================
    // RELATIONS - Projet
    // ===================================

    /**
     * Projet auquel appartient ce risque
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // ===================================
    // RELATIONS - Propriétaire
    // ===================================

    /**
     * Utilisateur propriétaire/responsable du risque
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // ===================================
    // SCOPES - Statut
    // ===================================

    /**
     * Filtrer les risques identifiés
     */
    public function scopeIdentified($query)
    {
        return $query->where('status', 'identified');
    }

    /**
     * Filtrer les risques évalués
     */
    public function scopeAssessed($query)
    {
        return $query->where('status', 'assessed');
    }

    /**
     * Filtrer les risques atténués
     */
    public function scopeMitigated($query)
    {
        return $query->where('status', 'mitigated');
    }

    /**
     * Filtrer les risques clôturés
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Filtrer les risques survenus
     */
    public function scopeOccurred($query)
    {
        return $query->where('status', 'occurred');
    }

    /**
     * Filtrer les risques actifs
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['closed', 'occurred']);
    }

    // ===================================
    // SCOPES - Niveau de Risque
    // ===================================

    /**
     * Filtrer les risques critiques (score > 75)
     */
    public function scopeCritical($query)
    {
        return $query->where('risk_score', '>', 75);
    }

    /**
     * Filtrer les risques élevés (score 50-75)
     */
    public function scopeHigh($query)
    {
        return $query->whereBetween('risk_score', [50, 75]);
    }

    /**
     * Filtrer les risques moyens (score 25-50)
     */
    public function scopeMedium($query)
    {
        return $query->whereBetween('risk_score', [25, 50]);
    }

    /**
     * Filtrer les risques faibles (score < 25)
     */
    public function scopeLow($query)
    {
        return $query->where('risk_score', '<', 25);
    }

    // ===================================
    // HELPERS - Statut
    // ===================================

    /**
     * Vérifier si le risque est actif
     */
    public function isActive(): bool
    {
        return !in_array($this->status, ['closed', 'occurred']);
    }

    /**
     * Vérifier si le risque est survenu
     */
    public function hasOccurred(): bool
    {
        return $this->status === 'occurred';
    }

    // ===================================
    // HELPERS - Calcul Score
    // ===================================

    /**
     * Calculer le score de risque (probabilité x impact)
     */
    public function calculateRiskScore(): int
    {
        return (int) (($this->probability * $this->impact) / 100);
    }

    /**
     * Mettre à jour le score de risque automatiquement
     */
    public function updateRiskScore(): void
    {
        $this->risk_score = $this->calculateRiskScore();
        $this->save();
    }

    /**
     * Obtenir le niveau de risque (critical, high, medium, low)
     */
    public function getRiskLevel(): string
    {
        if ($this->risk_score > 75) {
            return 'critical';
        } elseif ($this->risk_score >= 50) {
            return 'high';
        } elseif ($this->risk_score >= 25) {
            return 'medium';
        }
        return 'low';
    }

    /**
     * Obtenir la couleur du risque
     */
    public function getRiskColor(): string
    {
        $level = $this->getRiskLevel();
        return match($level) {
            'critical' => 'red',
            'high' => 'orange',
            'medium' => 'yellow',
            'low' => 'green',
        };
    }

    // ===================================
    // HELPERS - Actions
    // ===================================

    /**
     * Marquer le risque comme survenu
     */
    public function markAsOccurred(): void
    {
        $this->status = 'occurred';
        $this->save();
    }

    /**
     * Clôturer le risque
     */
    public function close(): void
    {
        $this->status = 'closed';
        $this->save();
    }

    // ===================================
    // BOOT
    // ===================================

    protected static function boot()
    {
        parent::boot();

        // Calculer automatiquement le score lors de la création/modification
        static::saving(function ($risk) {
            if ($risk->isDirty(['probability', 'impact'])) {
                $risk->risk_score = $risk->calculateRiskScore();
            }
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use SoftDeletes;

    // ===================================
    // CONFIGURATION
    // ===================================

    protected $fillable = [
        'portfolio_id',
        'name',
        'description',
        'manager_id',
        'budget',
        'objectives',
        'status',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
    ];

    // ===================================
    // RELATIONS - Portfolio
    // ===================================

    /**
     * Portfolio auquel appartient ce programme
     */
    public function portfolio()
    {
        return $this->belongsTo(Portfolio::class);
    }

    // ===================================
    // RELATIONS - Gestionnaire
    // ===================================

    /**
     * Gestionnaire du programme (Program Manager)
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    // ===================================
    // RELATIONS - Projets
    // ===================================

    /**
     * Projets contenus dans ce programme
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    // ===================================
    // RELATIONS - Rôles Utilisateurs
    // ===================================

    /**
     * Attributions de rôles scopées à ce programme
     */
    public function userRoles()
    {
        return $this->hasMany(UserRole::class);
    }

    // ===================================
    // SCOPES - Statut
    // ===================================

    /**
     * Filtrer les programmes actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Filtrer les programmes inactifs
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Filtrer les programmes terminés
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Filtrer les programmes en pause
     */
    public function scopeOnHold($query)
    {
        return $query->where('status', 'on_hold');
    }

    // ===================================
    // SCOPES - Portfolio
    // ===================================

    /**
     * Filtrer par portfolio
     */
    public function scopeForPortfolio($query, int $portfolioId)
    {
        return $query->where('portfolio_id', $portfolioId);
    }

    /**
     * Filtrer les programmes sans portfolio
     */
    public function scopeStandalone($query)
    {
        return $query->whereNull('portfolio_id');
    }

    // ===================================
    // HELPERS - Statut
    // ===================================

    /**
     * Vérifier si le programme est actif
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Vérifier si le programme est inactif
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    /**
     * Vérifier si le programme est terminé
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Vérifier si le programme est en pause
     */
    public function isOnHold(): bool
    {
        return $this->status === 'on_hold';
    }

    /**
     * Vérifier si le programme est autonome (sans portfolio)
     */
    public function isStandalone(): bool
    {
        return $this->portfolio_id === null;
    }

    // ===================================
    // HELPERS - Budget
    // ===================================

    /**
     * Calculer le budget total des projets
     */
    public function getProjectsBudget(): float
    {
        return $this->projects()->sum('budget') ?? 0;
    }

    /**
     * Calculer l'écart budgétaire du programme
     */
    public function getBudgetVariance(): float
    {
        return ($this->budget ?? 0) - $this->getProjectsBudget();
    }

    /**
     * Calculer le pourcentage d'utilisation du budget
     */
    public function getBudgetUsagePercentage(): float
    {
        if (!$this->budget || $this->budget == 0) {
            return 0;
        }
        return ($this->getProjectsBudget() / $this->budget) * 100;
    }

    /**
     * Vérifier si le programme est en dépassement budgétaire
     */
    public function isOverBudget(): bool
    {
        return $this->getBudgetVariance() < 0;
    }

    // ===================================
    // HELPERS - Statistiques
    // ===================================

    /**
     * Compter le nombre de projets
     */
    public function getProjectsCount(): int
    {
        return $this->projects()->count();
    }

    /**
     * Compter les projets actifs
     */
    public function getActiveProjectsCount(): int
    {
        return $this->projects()->active()->count();
    }

    /**
     * Compter les projets terminés
     */
    public function getCompletedProjectsCount(): int
    {
        return $this->projects()->where('status', 'closure')->count();
    }

    /**
     * Calculer le pourcentage de projets terminés
     */
    public function getCompletionPercentage(): float
    {
        $total = $this->getProjectsCount();
        if ($total == 0) {
            return 0;
        }
        return ($this->getCompletedProjectsCount() / $total) * 100;
    }

    // ===================================
    // HELPERS - Santé du Programme
    // ===================================

    /**
     * Évaluer la santé globale du programme
     * basée sur la santé des projets
     */
    public function getHealthStatus(): string
    {
        $projects = $this->projects()->active()->get();

        if ($projects->isEmpty()) {
            return 'green';
        }

        $redCount = $projects->where('health_status', 'red')->count();
        $yellowCount = $projects->where('health_status', 'yellow')->count();

        // Si plus de 30% des projets sont en rouge
        if ($redCount / $projects->count() > 0.3) {
            return 'red';
        }

        // Si plus de 40% des projets sont en jaune/rouge
        if (($redCount + $yellowCount) / $projects->count() > 0.4) {
            return 'yellow';
        }

        return 'green';
    }

    /**
     * Compter les projets en bonne santé
     */
    public function getHealthyProjectsCount(): int
    {
        return $this->projects()->active()->healthy()->count();
    }

    /**
     * Compter les projets à risque
     */
    public function getAtRiskProjectsCount(): int
    {
        return $this->projects()->active()->atRisk()->count();
    }

    /**
     * Compter les projets critiques
     */
    public function getCriticalProjectsCount(): int
    {
        return $this->projects()->active()->critical()->count();
    }
}

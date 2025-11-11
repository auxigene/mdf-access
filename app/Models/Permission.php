<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    // ===================================
    // CONFIGURATION
    // ===================================

    protected $fillable = [
        'name',
        'slug',
        'description',
        'resource_id',
        'action_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ===================================
    // RELATIONS
    // ===================================

    /**
     * Rôles possédant cette permission
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission')
                    ->withTimestamps();
    }

    /**
     * Ressource ACL associée
     */
    public function aclResource()
    {
        return $this->belongsTo(AclResource::class, 'resource_id');
    }

    /**
     * Action associée
     */
    public function action()
    {
        return $this->belongsTo(Action::class, 'action_id');
    }

    // ===================================
    // SCOPES - Resource
    // ===================================

    /**
     * Filtrer par resource slug
     */
    public function scopeForResource($query, string $resourceSlug)
    {
        return $query->whereHas('aclResource', function ($q) use ($resourceSlug) {
            $q->where('slug', $resourceSlug);
        });
    }

    /**
     * Filtrer les permissions liées aux projets
     */
    public function scopeProjects($query)
    {
        return $query->forResource('projects');
    }

    /**
     * Filtrer les permissions liées aux tâches
     */
    public function scopeTasks($query)
    {
        return $query->forResource('tasks');
    }

    /**
     * Filtrer les permissions liées aux budgets
     */
    public function scopeBudgets($query)
    {
        return $query->forResource('budgets');
    }

    /**
     * Filtrer les permissions liées aux risques
     */
    public function scopeRisks($query)
    {
        return $query->forResource('risks');
    }

    /**
     * Filtrer les permissions liées aux livrables
     */
    public function scopeDeliverables($query)
    {
        return $query->forResource('deliverables');
    }

    // ===================================
    // SCOPES - Action
    // ===================================

    /**
     * Filtrer par action slug
     */
    public function scopeForAction($query, string $actionSlug)
    {
        return $query->whereHas('action', function ($q) use ($actionSlug) {
            $q->where('slug', $actionSlug);
        });
    }

    /**
     * Filtrer les permissions de visualisation
     */
    public function scopeView($query)
    {
        return $query->forAction('view');
    }

    /**
     * Filtrer les permissions de création
     */
    public function scopeCreate($query)
    {
        return $query->forAction('create');
    }

    /**
     * Filtrer les permissions de modification
     */
    public function scopeEdit($query)
    {
        return $query->forAction('edit');
    }

    /**
     * Filtrer les permissions de suppression
     */
    public function scopeDelete($query)
    {
        return $query->forAction('delete');
    }

    /**
     * Filtrer les permissions d'export
     */
    public function scopeExport($query)
    {
        return $query->forAction('export');
    }

    /**
     * Filtrer les permissions d'approbation
     */
    public function scopeApprove($query)
    {
        return $query->forAction('approve');
    }

    /**
     * Filtrer les permissions actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ===================================
    // SCOPES - Combinés
    // ===================================

    /**
     * Filtrer par resource et action slugs
     */
    public function scopeForResourceAction($query, string $resourceSlug, string $actionSlug)
    {
        return $query->forResource($resourceSlug)
                     ->forAction($actionSlug);
    }

    // ===================================
    // HELPERS - Identification
    // ===================================

    /**
     * Vérifier si c'est une permission de visualisation
     */
    public function isViewPermission(): bool
    {
        return $this->action?->slug === 'view';
    }

    /**
     * Vérifier si c'est une permission de création
     */
    public function isCreatePermission(): bool
    {
        return $this->action?->slug === 'create';
    }

    /**
     * Vérifier si c'est une permission de modification
     */
    public function isEditPermission(): bool
    {
        return $this->action?->slug === 'edit';
    }

    /**
     * Vérifier si c'est une permission de suppression
     */
    public function isDeletePermission(): bool
    {
        return $this->action?->slug === 'delete';
    }

    /**
     * Vérifier si c'est une permission d'export
     */
    public function isExportPermission(): bool
    {
        return $this->action?->slug === 'export';
    }

    /**
     * Vérifier si c'est une permission d'approbation
     */
    public function isApprovePermission(): bool
    {
        return $this->action?->slug === 'approve';
    }

    // ===================================
    // HELPERS - Resource
    // ===================================

    /**
     * Obtenir le nom de la resource en français
     */
    public function getResourceLabel(): string
    {
        return $this->aclResource?->name ?? 'Inconnu';
    }

    /**
     * Obtenir le slug de la resource
     */
    public function getResourceSlug(): ?string
    {
        return $this->aclResource?->slug;
    }

    /**
     * Obtenir le nom de l'action en français
     */
    public function getActionLabel(): string
    {
        return $this->action?->getLabel() ?? 'Inconnu';
    }

    /**
     * Obtenir le slug de l'action
     */
    public function getActionSlug(): ?string
    {
        return $this->action?->slug;
    }

    /**
     * Obtenir la description complète de la permission
     */
    public function getFullDescription(): string
    {
        return $this->getActionLabel() . ' ' . strtolower($this->getResourceLabel());
    }

    // ===================================
    // HELPERS - Recherche
    // ===================================

    /**
     * Trouver une permission par son slug
     */
    public static function findBySlug(string $slug): ?Permission
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Trouver ou créer une permission par resource et action (slugs)
     */
    public static function findOrCreateByResourceAction(
        string $resourceSlug,
        string $actionSlug,
        ?string $name = null,
        ?string $description = null
    ): Permission {
        $resource = AclResource::findBySlug($resourceSlug);
        $action = \App\Models\Action::findBySlug($actionSlug);

        if (!$resource || !$action) {
            throw new \InvalidArgumentException(
                "Resource '{$resourceSlug}' ou Action '{$actionSlug}' introuvable"
            );
        }

        $slug = $resourceSlug . '_' . $actionSlug;

        return static::firstOrCreate(
            ['resource_id' => $resource->id, 'action_id' => $action->id],
            [
                'name' => $name ?? ucfirst($actionSlug) . ' ' . ucfirst($resourceSlug),
                'slug' => $slug,
                'description' => $description,
                'is_active' => true,
            ]
        );
    }

    // ===================================
    // HELPERS - Groupement
    // ===================================

    /**
     * Grouper les permissions par resource
     */
    public static function groupedByResource()
    {
        return static::with('aclResource')->get()->groupBy(function ($permission) {
            return $permission->aclResource?->slug ?? 'unknown';
        });
    }

    /**
     * Grouper les permissions par action
     */
    public static function groupedByAction()
    {
        return static::with('action')->get()->groupBy(function ($permission) {
            return $permission->action?->slug ?? 'unknown';
        });
    }

    /**
     * Obtenir toutes les resources distinctes
     */
    public static function getDistinctResources()
    {
        return AclResource::whereHas('permissions')->pluck('slug');
    }

    /**
     * Obtenir toutes les actions distinctes
     */
    public static function getDistinctActions()
    {
        return \App\Models\Action::whereHas('permissions')->pluck('slug');
    }
}

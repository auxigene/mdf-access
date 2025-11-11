<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Action
 *
 * Représente une action du système de permissions (view, create, edit, delete, etc.)
 */
class Action extends Model
{
    // ===================================
    // CONFIGURATION
    // ===================================

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    // ===================================
    // RELATIONS
    // ===================================

    /**
     * Permissions utilisant cette action
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'action_id');
    }

    /**
     * Ressources ACL pour lesquelles cette action est applicable
     */
    public function applicableResources(): BelongsToMany
    {
        return $this->belongsToMany(
            AclResource::class,
            'acl_resource_actions',
            'action_id',
            'resource_id'
        )->withTimestamps();
    }

    // ===================================
    // SCOPES
    // ===================================

    /**
     * Filtrer par slug
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    // ===================================
    // HELPERS
    // ===================================

    /**
     * Vérifier si cette action est applicable à une ressource
     */
    public function isApplicableToResource(string $resourceSlug): bool
    {
        return $this->applicableResources()
                    ->where('slug', $resourceSlug)
                    ->exists();
    }

    /**
     * Obtenir toutes les ressources applicables (slugs)
     */
    public function getApplicableResourceSlugs(): array
    {
        return $this->applicableResources()
                    ->pluck('slug')
                    ->toArray();
    }

    /**
     * Trouver par slug
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Obtenir le label en français
     */
    public function getLabel(): string
    {
        $labels = [
            'view' => 'Voir',
            'create' => 'Créer',
            'edit' => 'Modifier',
            'delete' => 'Supprimer',
            'export' => 'Exporter',
            'approve' => 'Approuver',
        ];

        return $labels[$this->slug] ?? ucfirst($this->name);
    }
}

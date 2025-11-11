<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model AclResource
 *
 * Représente une ressource du système ACL (Access Control List)
 * Distincte de la table "resources" qui gère les ressources humaines PMBOK
 */
class AclResource extends Model
{
    // ===================================
    // CONFIGURATION
    // ===================================

    protected $fillable = [
        'name',
        'slug',
        'description',
        'model_class',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ===================================
    // RELATIONS
    // ===================================

    /**
     * Permissions associées à cette ressource
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'resource_id');
    }

    /**
     * Actions applicables à cette ressource (via table pivot)
     */
    public function applicableActions(): BelongsToMany
    {
        return $this->belongsToMany(
            Action::class,
            'acl_resource_actions',
            'resource_id',
            'action_id'
        )->withTimestamps();
    }

    // ===================================
    // SCOPES
    // ===================================

    /**
     * Filtrer les ressources actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

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
     * Vérifier si une action est applicable à cette ressource
     */
    public function canPerformAction(string $actionSlug): bool
    {
        return $this->applicableActions()
                    ->where('slug', $actionSlug)
                    ->exists();
    }

    /**
     * Obtenir toutes les actions applicables (slugs)
     */
    public function getApplicableActionSlugs(): array
    {
        return $this->applicableActions()
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
}

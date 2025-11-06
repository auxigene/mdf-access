<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ApiKey extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'key',
        'api_type',
        'access_level',
        'permissions',
        'user_id',
        'expires_at',
        'last_used_at',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'key',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'expires_at' => 'datetime',
            'last_used_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the API key.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Vérifie si la clé API est valide.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Vérifie si la clé a une permission spécifique.
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->permissions) {
            return false;
        }

        return in_array($permission, $this->permissions);
    }

    /**
     * Vérifie si la clé a le type d'API spécifié.
     */
    public function hasApiType(string $apiType): bool
    {
        return $this->api_type === $apiType;
    }

    /**
     * Vérifie si la clé a au minimum le niveau d'accès requis.
     */
    public function hasAccessLevel(string $requiredLevel): bool
    {
        $levels = ['read' => 1, 'write' => 2, 'admin' => 3];

        $currentLevel = $levels[$this->access_level] ?? 0;
        $required = $levels[$requiredLevel] ?? 0;

        return $currentLevel >= $required;
    }

    /**
     * Met à jour la date de dernière utilisation.
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Génère une nouvelle clé API.
     */
    public static function generateKey(): string
    {
        return Str::random(64);
    }

    /**
     * Hash une clé API.
     */
    public static function hashKey(string $key): string
    {
        return hash('sha256', $key);
    }

    /**
     * Vérifie si une clé correspond au hash stocké.
     */
    public function verifyKey(string $key): bool
    {
        return hash_equals($this->key, self::hashKey($key));
    }

    /**
     * Scope pour récupérer uniquement les clés actives.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour récupérer uniquement les clés non expirées.
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope pour récupérer uniquement les clés valides (actives et non expirées).
     */
    public function scopeValid($query)
    {
        return $query->active()->notExpired();
    }

    /**
     * Scope pour filtrer par type d'API.
     */
    public function scopeByApiType($query, string $apiType)
    {
        return $query->where('api_type', $apiType);
    }

    /**
     * Scope pour filtrer par niveau d'accès.
     */
    public function scopeByAccessLevel($query, string $accessLevel)
    {
        return $query->where('access_level', $accessLevel);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    protected $fillable = [
        'key',
        'name',
        'last_used_at',
        'is_active',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Generate a unique API key
     */
    public static function generateKey(): string
    {
        do {
            $key = 'mdf_' . Str::random(60);
        } while (self::where('key', $key)->exists());

        return $key;
    }

    /**
     * Update the last used timestamp
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Check if the key is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }
}

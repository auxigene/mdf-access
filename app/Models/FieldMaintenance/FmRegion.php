<?php

namespace App\Models\FieldMaintenance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FmRegion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'zone_geographique',
        'description',
        'parent_region_id',
        'level',
        'status',
    ];

    protected $casts = [
        'level' => 'integer',
    ];

    // ===================================
    // RELATIONS
    // ===================================

    /**
     * Région parente (hiérarchie)
     */
    public function parent()
    {
        return $this->belongsTo(FmRegion::class, 'parent_region_id');
    }

    /**
     * Régions enfants (sous-régions)
     */
    public function children()
    {
        return $this->hasMany(FmRegion::class, 'parent_region_id');
    }

    /**
     * Sites de cette région
     */
    public function sites()
    {
        return $this->hasMany(FmSite::class, 'fm_region_id');
    }

    // ===================================
    // SCOPES
    // ===================================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByZone($query, string $zone)
    {
        return $query->where('zone_geographique', $zone);
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_region_id');
    }

    // ===================================
    // HELPERS
    // ===================================

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getSitesCount(): int
    {
        return $this->sites()->count();
    }

    public function getActiveSitesCount(): int
    {
        return $this->sites()->where('status', 'active')->count();
    }
}

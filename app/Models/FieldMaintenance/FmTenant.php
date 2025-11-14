<?php

namespace App\Models\FieldMaintenance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FmTenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
    ];

    // ===================================
    // RELATIONS
    // ===================================

    /**
     * Sites où ce tenant est présent (many-to-many)
     */
    public function sites()
    {
        return $this->belongsToMany(FmSite::class, 'fm_site_tenant', 'fm_tenant_id', 'fm_site_id')
                    ->withPivot(['tenant_rank', 'is_primary', 'scope_description', 'status'])
                    ->withTimestamps();
    }

    /**
     * Configurations de colocation incluant ce tenant
     */
    public function colocationConfigs()
    {
        return $this->hasMany(FmSiteTypeColocation::class)
                    ->whereJsonContains('tenants', $this->code);
    }

    // ===================================
    // SCOPES
    // ===================================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
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

    public function getPrimarySitesCount(): int
    {
        return $this->sites()->wherePivot('is_primary', true)->count();
    }
}

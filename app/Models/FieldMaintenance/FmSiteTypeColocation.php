<?php

namespace App\Models\FieldMaintenance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FmSiteTypeColocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'tenant_count',
        'tenants',
        'description',
        'status',
    ];

    protected $casts = [
        'tenant_count' => 'integer',
        'tenants' => 'array',
    ];

    // ===================================
    // RELATIONS
    // ===================================

    /**
     * Sites utilisant cette configuration de colocation
     */
    public function sites()
    {
        return $this->hasMany(FmSite::class, 'fm_site_type_colocation_id');
    }

    // ===================================
    // SCOPES
    // ===================================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByTenantCount($query, int $count)
    {
        return $query->where('tenant_count', $count);
    }

    public function scopeHasTenant($query, string $tenantCode)
    {
        return $query->whereJsonContains('tenants', $tenantCode);
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

    public function getTenantsList(): array
    {
        return $this->tenants ?? [];
    }

    public function hasTenant(string $tenantCode): bool
    {
        return in_array($tenantCode, $this->getTenantsList());
    }
}

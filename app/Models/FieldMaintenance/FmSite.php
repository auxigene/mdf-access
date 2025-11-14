<?php

namespace App\Models\FieldMaintenance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FmSite extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'site_code',
        'gsm_id',
        'site_name',
        'fm_region_id',
        'fm_site_class_id',
        'fm_energy_source_id',
        'fm_maintenance_typology_id',
        'fm_site_type_colocation_id',
        'is_colocation',
        'colocation_details',
        'latitude',
        'longitude',
        'address',
        'technical_specs',
        'metadata',
        'status',
        'activated_at',
        'deactivated_at',
    ];

    protected $casts = [
        'is_colocation' => 'boolean',
        'colocation_details' => 'array',
        'technical_specs' => 'array',
        'metadata' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'activated_at' => 'datetime',
        'deactivated_at' => 'datetime',
    ];

    // ===================================
    // RELATIONS - Référence
    // ===================================

    public function region()
    {
        return $this->belongsTo(FmRegion::class, 'fm_region_id');
    }

    public function siteClass()
    {
        return $this->belongsTo(FmSiteClass::class, 'fm_site_class_id');
    }

    public function energySource()
    {
        return $this->belongsTo(FmEnergySource::class, 'fm_energy_source_id');
    }

    public function maintenanceTypology()
    {
        return $this->belongsTo(FmMaintenanceTypology::class, 'fm_maintenance_typology_id');
    }

    public function colocationConfig()
    {
        return $this->belongsTo(FmSiteTypeColocation::class, 'fm_site_type_colocation_id');
    }

    // ===================================
    // RELATIONS - Tenants (Many-to-Many)
    // ===================================

    public function tenants()
    {
        return $this->belongsToMany(FmTenant::class, 'fm_site_tenant', 'fm_site_id', 'fm_tenant_id')
                    ->withPivot(['tenant_rank', 'is_primary', 'scope_description', 'status'])
                    ->withTimestamps();
    }

    public function primaryTenant()
    {
        return $this->belongsToMany(FmTenant::class, 'fm_site_tenant', 'fm_site_id', 'fm_tenant_id')
                    ->wherePivot('is_primary', true)
                    ->withPivot(['tenant_rank', 'is_primary', 'scope_description', 'status'])
                    ->withTimestamps();
    }

    // ===================================
    // RELATIONS - Historique
    // ===================================

    public function history()
    {
        return $this->hasMany(FmSiteHistory::class, 'fm_site_id');
    }

    // ===================================
    // SCOPES - Statut
    // ===================================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeDecommissioned($query)
    {
        return $query->where('status', 'decommissioned');
    }

    // ===================================
    // SCOPES - Filtres
    // ===================================

    public function scopeByRegion($query, $regionId)
    {
        return $query->where('fm_region_id', $regionId);
    }

    public function scopeByClass($query, $classId)
    {
        return $query->where('fm_site_class_id', $classId);
    }

    public function scopeWithColocation($query)
    {
        return $query->where('is_colocation', true);
    }

    public function scopeWithoutColocation($query)
    {
        return $query->where('is_colocation', false);
    }

    /**
     * Recherche avancée dans le site et ses relations
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            // Recherche dans les champs du site
            $q->where('site_code', 'ILIKE', "%{$term}%")
              ->orWhere('gsm_id', 'ILIKE', "%{$term}%")
              ->orWhere('site_name', 'ILIKE', "%{$term}%")
              ->orWhere('address', 'ILIKE', "%{$term}%")

              // Recherche dans la région
              ->orWhereHas('region', function ($regionQuery) use ($term) {
                  $regionQuery->where('name', 'ILIKE', "%{$term}%")
                              ->orWhere('code', 'ILIKE', "%{$term}%")
                              ->orWhere('zone_geographique', 'ILIKE', "%{$term}%");
              })

              // Recherche dans la classe de site
              ->orWhereHas('siteClass', function ($classQuery) use ($term) {
                  $classQuery->where('name', 'ILIKE', "%{$term}%")
                            ->orWhere('code', 'ILIKE', "%{$term}%");
              })

              // Recherche dans la source d'énergie
              ->orWhereHas('energySource', function ($energyQuery) use ($term) {
                  $energyQuery->where('name', 'ILIKE', "%{$term}%")
                             ->orWhere('code', 'ILIKE', "%{$term}%");
              })

              // Recherche dans la typologie de maintenance
              ->orWhereHas('maintenanceTypology', function ($typologyQuery) use ($term) {
                  $typologyQuery->where('name', 'ILIKE', "%{$term}%")
                               ->orWhere('code', 'ILIKE', "%{$term}%");
              })

              // Recherche dans les tenants
              ->orWhereHas('tenants', function ($tenantQuery) use ($term) {
                  $tenantQuery->where('name', 'ILIKE', "%{$term}%")
                             ->orWhere('code', 'ILIKE', "%{$term}%");
              })

              // Recherche dans la configuration de colocation
              ->orWhereHas('colocationConfig', function ($colocQuery) use ($term) {
                  $colocQuery->where('name', 'ILIKE', "%{$term}%");
              });
        });
    }

    // ===================================
    // HELPERS - Statut
    // ===================================

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    public function isDecommissioned(): bool
    {
        return $this->status === 'decommissioned';
    }

    public function activate(): void
    {
        $this->status = 'active';
        $this->activated_at = now();
        $this->deactivated_at = null;
        $this->save();
    }

    public function deactivate(): void
    {
        $this->status = 'inactive';
        $this->deactivated_at = now();
        $this->save();
    }

    // ===================================
    // HELPERS - Colocation
    // ===================================

    public function hasColocation(): bool
    {
        return $this->is_colocation;
    }

    public function getTenantsCount(): int
    {
        return $this->tenants()->count();
    }

    public function getPrimaryTenant(): ?FmTenant
    {
        return $this->primaryTenant()->first();
    }

    // ===================================
    // HELPERS - Localisation
    // ===================================

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function getCoordinates(): ?array
    {
        if (!$this->hasCoordinates()) {
            return null;
        }

        return [
            'lat' => (float)$this->latitude,
            'lng' => (float)$this->longitude,
        ];
    }
}

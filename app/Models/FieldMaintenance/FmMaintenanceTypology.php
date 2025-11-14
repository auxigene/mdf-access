<?php

namespace App\Models\FieldMaintenance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FmMaintenanceTypology extends Model
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
     * Sites de cette typologie
     */
    public function sites()
    {
        return $this->hasMany(FmSite::class, 'fm_maintenance_typology_id');
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
}

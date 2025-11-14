<?php

namespace App\Models\FieldMaintenance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FmSiteClass extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'priority',
        'status',
    ];

    protected $casts = [
        'priority' => 'integer',
    ];

    // ===================================
    // RELATIONS
    // ===================================

    /**
     * Sites de cette classe
     */
    public function sites()
    {
        return $this->hasMany(FmSite::class, 'fm_site_class_id');
    }

    // ===================================
    // SCOPES
    // ===================================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOrderedByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
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

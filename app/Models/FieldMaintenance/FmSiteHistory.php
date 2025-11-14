<?php

namespace App\Models\FieldMaintenance;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FmSiteHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'fm_site_history';

    protected $fillable = [
        'fm_site_id',
        'field_name',
        'old_value',
        'new_value',
        'changed_by',
        'change_reason',
        'change_type',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    // ===================================
    // RELATIONS
    // ===================================

    public function site()
    {
        return $this->belongsTo(FmSite::class, 'fm_site_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // ===================================
    // SCOPES
    // ===================================

    public function scopeForSite($query, int $siteId)
    {
        return $query->where('fm_site_id', $siteId);
    }

    public function scopeForField($query, string $fieldName)
    {
        return $query->where('field_name', $fieldName);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('changed_by', $userId);
    }

    public function scopeByChangeType($query, string $type)
    {
        return $query->where('change_type', $type);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('changed_at', '>=', now()->subDays($days));
    }

    // ===================================
    // HELPERS
    // ===================================

    public function getChangedBy(): ?User
    {
        return $this->user;
    }

    public function getChangeDescription(): string
    {
        return "{$this->field_name}: {$this->old_value} → {$this->new_value}";
    }
}

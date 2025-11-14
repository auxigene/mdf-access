<?php

namespace App\Models\FieldMaintenance;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FmParcSnapshot extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'snapshot_date',
        'version',
        'description',
        'total_sites',
        'active_sites',
        'inactive_sites',
        'colocation_sites',
        'data_snapshot',
        'statistics',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'data_snapshot' => 'array',
        'statistics' => 'array',
        'total_sites' => 'integer',
        'active_sites' => 'integer',
        'inactive_sites' => 'integer',
        'colocation_sites' => 'integer',
        'created_at' => 'datetime',
    ];

    // ===================================
    // RELATIONS
    // ===================================

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ===================================
    // SCOPES
    // ===================================

    public function scopeForDate($query, $date)
    {
        return $query->where('snapshot_date', $date);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('snapshot_date', $year);
    }

    public function scopeRecent($query, int $count = 10)
    {
        return $query->orderBy('snapshot_date', 'desc')->limit($count);
    }

    // ===================================
    // HELPERS
    // ===================================

    public function getActiveSitesPercentage(): float
    {
        if ($this->total_sites == 0) {
            return 0;
        }
        return ($this->active_sites / $this->total_sites) * 100;
    }

    public function getColocationPercentage(): float
    {
        if ($this->total_sites == 0) {
            return 0;
        }
        return ($this->colocation_sites / $this->total_sites) * 100;
    }

    public function getStatistic(string $key, $default = null)
    {
        return data_get($this->statistics, $key, $default);
    }
}

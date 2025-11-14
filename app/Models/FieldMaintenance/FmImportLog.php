<?php

namespace App\Models\FieldMaintenance;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FmImportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'file_path',
        'file_hash',
        'total_rows',
        'successful_imports',
        'failed_imports',
        'warnings_count',
        'updated_records',
        'created_records',
        'errors',
        'warnings',
        'metadata',
        'status',
        'failure_reason',
        'imported_by',
        'started_at',
        'completed_at',
        'duration_seconds',
    ];

    protected $casts = [
        'errors' => 'array',
        'warnings' => 'array',
        'metadata' => 'array',
        'total_rows' => 'integer',
        'successful_imports' => 'integer',
        'failed_imports' => 'integer',
        'warnings_count' => 'integer',
        'updated_records' => 'integer',
        'created_records' => 'integer',
        'duration_seconds' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ===================================
    // RELATIONS
    // ===================================

    public function importer()
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    // ===================================
    // SCOPES
    // ===================================

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('imported_by', $userId);
    }

    // ===================================
    // HELPERS - Statut
    // ===================================

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function markAsStarted(): void
    {
        $this->status = 'processing';
        $this->started_at = now();
        $this->save();
    }

    public function markAsCompleted(): void
    {
        $this->status = 'completed';
        $this->completed_at = now();
        if ($this->started_at) {
            $this->duration_seconds = $this->started_at->diffInSeconds($this->completed_at);
        }
        $this->save();
    }

    public function markAsFailed(string $reason): void
    {
        $this->status = 'failed';
        $this->failure_reason = $reason;
        $this->completed_at = now();
        if ($this->started_at) {
            $this->duration_seconds = $this->started_at->diffInSeconds($this->completed_at);
        }
        $this->save();
    }

    // ===================================
    // HELPERS - Statistiques
    // ===================================

    public function getSuccessRate(): float
    {
        if ($this->total_rows == 0) {
            return 0;
        }
        return ($this->successful_imports / $this->total_rows) * 100;
    }

    public function getFailureRate(): float
    {
        if ($this->total_rows == 0) {
            return 0;
        }
        return ($this->failed_imports / $this->total_rows) * 100;
    }

    public function hasErrors(): bool
    {
        return $this->failed_imports > 0 || !empty($this->errors);
    }

    public function hasWarnings(): bool
    {
        return $this->warnings_count > 0 || !empty($this->warnings);
    }

    public function getDurationFormatted(): ?string
    {
        if (!$this->duration_seconds) {
            return null;
        }

        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;

        if ($minutes > 0) {
            return "{$minutes}m {$seconds}s";
        }

        return "{$seconds}s";
    }
}

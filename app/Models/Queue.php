<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id', 'queue_number', 'customer_session', 'queue_qr',
        'status', 'joined_at', 'served_at', 'finished_at', 'notes',
    ];

    protected $casts = [
        'joined_at'   => 'datetime',
        'served_at'   => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function getFormattedQueueNumberAttribute()
    {
        return str_pad($this->queue_number, 3, '0', STR_PAD_LEFT);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /**
     * Antrian yang masih aktif (belum selesai): waiting, in_progress, serving (legacy).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['waiting', 'in_progress', 'serving']);
    }

    public function scopeWaiting(Builder $query): Builder
    {
        return $query->where('status', 'waiting');
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('joined_at', today());
    }

    public function scopeForBranch(Builder $query, int $branchId): Builder
    {
        return $query->where('branch_id', $branchId);
    }

    // ── Status Lifecycle ────────────────────────────────────────────────────

    /**
     * Label status dalam Bahasa Indonesia.
     * Mendukung lifecycle baru: waiting → in_progress → completed
     * dan status lama: serving, done (backward compat).
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'waiting'     => 'Menunggu',
            'in_progress' => 'Sedang Dicukur',
            'completed'   => 'Selesai',
            'serving'     => 'Dilayani',      // legacy
            'done'        => 'Selesai',        // legacy
            'timeout'     => 'Timeout',
            'cancelled'   => 'Dibatalkan',
            default       => 'Tidak Diketahui',
        };
    }

    /**
     * Warna badge status.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'waiting'     => 'yellow',
            'in_progress' => 'blue',
            'completed'   => 'green',
            'serving'     => 'blue',    // legacy
            'done'        => 'green',   // legacy
            'timeout'     => 'red',
            'cancelled'   => 'gray',
            default       => 'gray',
        };
    }

    /**
     * Apakah antrian ini sudah selesai (tidak aktif lagi).
     */
    public function getIsFinishedAttribute(): bool
    {
        return in_array($this->status, ['completed', 'done', 'timeout', 'cancelled']);
    }

    /**
     * Apakah antrian ini masih aktif (menunggu atau sedang dilayani).
     */
    public function getIsActiveAttribute(): bool
    {
        return in_array($this->status, ['waiting', 'in_progress', 'serving']);
    }

    // ── Position & Wait ─────────────────────────────────────────────────────

    public function getPositionAttribute(): int
    {
        if (!$this->is_active) return 0;

        return Queue::forBranch($this->branch_id)
            ->active()
            ->where('joined_at', '<=', $this->joined_at)
            ->count();
    }

    public function getEstimatedWaitAttribute(): int
    {
        $position = $this->position;
        return max(0, ($position - 1) * ($this->branch->avg_service_minutes ?? 15));
    }
}

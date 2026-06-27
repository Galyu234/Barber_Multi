<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'barbershop_id', 'name', 'code', 'address', 'phone',
        'open_time', 'close_time', 'is_active',
        'queue_timeout_minutes', 'avg_service_minutes',
    ];

    protected $casts = [
        'is_active'              => 'boolean',
        'queue_timeout_minutes'  => 'integer',
        'avg_service_minutes'    => 'integer',
    ];

    public function barbershop(): BelongsTo
    {
        return $this->belongsTo(Barbershop::class);
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    /**
     * Antrian aktif: waiting, in_progress (baru) + serving (legacy).
     */
    public function activeQueues(): HasMany
    {
        return $this->hasMany(Queue::class)
            ->whereIn('status', ['waiting', 'in_progress', 'serving']);
    }

    public function todayQueues(): HasMany
    {
        return $this->hasMany(Queue::class)->whereDate('joined_at', today());
    }

    // ── Computed Attributes ─────────────────────────────────────────────────

    public function getActiveQueueCountAttribute(): int
    {
        return $this->activeQueues()->count();
    }

    public function getQueueStatusAttribute(): string
    {
        $count = $this->active_queue_count;
        if ($count <= 2) return 'sepi';
        if ($count <= 6) return 'sedang';
        return 'ramai';
    }

    public function getQueueStatusLabelAttribute(): string
    {
        return match ($this->queue_status) {
            'sepi'   => '🟢 Sepi',
            'sedang' => '🟡 Sedang',
            'ramai'  => '🔴 Ramai',
            default  => '⚪ Tidak Diketahui',
        };
    }

    public function getEstimatedWaitMinutesAttribute(): int
    {
        $waitingCount = $this->queues()->where('status', 'waiting')->count();
        return $waitingCount * $this->avg_service_minutes;
    }

    public function getQueueStatusColorAttribute(): string
    {
        return match ($this->queue_status) {
            'sepi'   => 'green',
            'sedang' => 'yellow',
            'ramai'  => 'red',
            default  => 'gray',
        };
    }

    public function isOpen(): bool
    {
        $now = now()->format('H:i:s');
        return $now >= $this->open_time && $now <= $this->close_time;
    }
}

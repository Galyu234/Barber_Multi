<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Barbershop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'owner_name', 'phone', 'address', 'logo', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function activeBranches(): HasMany
    {
        return $this->hasMany(Branch::class)->where('is_active', true);
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(User::class, Branch::class);
    }

    public function getTotalActiveQueuesAttribute(): int
    {
        return $this->branches()
            ->withCount(['queues' => fn($q) => $q->whereIn('status', ['waiting', 'serving'])])
            ->get()
            ->sum('queues_count');
    }
}

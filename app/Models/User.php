<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'branch_id', 'barbershop_id',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    // ── Relationships ───────────────────────────────────────────────────────

    /**
     * Cabang utama yang di-assign ke user ini (legacy / single-branch admin).
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Barbershop yang dimiliki admin ini (multi-branch ownership).
     */
    public function barbershop(): BelongsTo
    {
        return $this->belongsTo(Barbershop::class);
    }

    /**
     * Semua cabang yang dimiliki oleh tenant admin ini
     * (melalui barbershop_id).
     */
    public function ownedBranches(): HasMany
    {
        if ($this->barbershop_id) {
            return $this->barbershop()->first()?->branches() ?? Branch::whereNull('id');
        }
        // Fallback untuk admin lama yang hanya punya branch_id
        return Branch::where('id', $this->branch_id);
    }

    // ── Role Helpers ────────────────────────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Apakah user ini adalah tenant admin yang memiliki barbershop
     * (multi-branch capable).
     */
    public function isTenantAdmin(): bool
    {
        return $this->role === 'admin' && !is_null($this->barbershop_id);
    }

    // ── Authorization ───────────────────────────────────────────────────────

    /**
     * Periksa apakah user boleh mengelola cabang tertentu.
     *
     * Logika:
     * 1. Super admin → boleh semua
     * 2. Admin dengan barbershop_id → boleh semua cabang barbershop itu
     * 3. Admin dengan branch_id saja → hanya boleh cabangnya sendiri
     */
    public function canManageBranch(int $branchId): bool
    {
        if ($this->isSuperAdmin()) return true;

        // Multi-branch: cek apakah branch milik barbershop admin ini
        if ($this->barbershop_id) {
            $branch = Branch::find($branchId);
            return $branch && $branch->barbershop_id == $this->barbershop_id;
        }

        // Legacy single-branch
        return $this->branch_id == $branchId;
    }

    /**
     * Ambil daftar branch_id yang boleh dikelola oleh user ini.
     * Berguna untuk filter query.
     *
     * @return int[]
     */
    public function managedBranchIds(): array
    {
        if ($this->isSuperAdmin()) return [];   // kosong = semua

        if ($this->barbershop_id) {
            return Branch::where('barbershop_id', $this->barbershop_id)
                ->pluck('id')
                ->toArray();
        }

        return $this->branch_id ? [$this->branch_id] : [];
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive migration: tambahkan kolom barbershop_id ke tabel users.
 *
 * Ini memungkinkan satu akun admin memiliki banyak cabang (multi-branch ownership).
 * Admin yang memiliki barbershop_id dapat mengelola semua cabang dari barbershop tsb.
 * Kolom branch_id yang lama tetap ada untuk backward compatibility.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hanya tambah jika belum ada
            if (!Schema::hasColumn('users', 'barbershop_id')) {
                $table->foreignId('barbershop_id')
                    ->nullable()
                    ->after('branch_id')
                    ->constrained('barbershops')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'barbershop_id')) {
                $table->dropForeign(['barbershop_id']);
                $table->dropColumn('barbershop_id');
            }
        });
    }
};

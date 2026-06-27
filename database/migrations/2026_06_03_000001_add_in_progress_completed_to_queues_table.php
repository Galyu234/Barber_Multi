<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Additive migration: tambahkan status 'in_progress' dan 'completed'
 * ke kolom ENUM queues.status.
 *
 * Status lama (waiting, serving, done, timeout, cancelled) TIDAK diubah.
 * Ini memastikan data historis tetap valid.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Modifikasi ENUM secara langsung via raw SQL agar aman di MySQL
        DB::statement("
            ALTER TABLE queues
            MODIFY COLUMN status ENUM(
                'waiting',
                'in_progress',
                'completed',
                'serving',
                'done',
                'timeout',
                'cancelled'
            ) NOT NULL DEFAULT 'waiting'
        ");
    }

    public function down(): void
    {
        // Kembalikan ke enum lama (hanya bisa jika tidak ada row in_progress/completed)
        DB::statement("
            ALTER TABLE queues
            MODIFY COLUMN status ENUM(
                'waiting',
                'serving',
                'done',
                'timeout',
                'cancelled'
            ) NOT NULL DEFAULT 'waiting'
        ");
    }
};

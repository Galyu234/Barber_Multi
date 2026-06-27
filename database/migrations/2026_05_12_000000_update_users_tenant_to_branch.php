<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key if exists, then drop column
            // We'll wrap in a try-catch to avoid errors if FK doesn't exist
            try {
                $table->dropForeign(['barbershop_id']);
            } catch (\Exception $e) {
                // Ignore if constraint doesn't exist
            }
            $table->dropColumn('barbershop_id');

            $table->foreignId('branch_id')->nullable()->after('role')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            try {
                $table->dropForeign(['branch_id']);
            } catch (\Exception $e) {}
            $table->dropColumn('branch_id');

            $table->foreignId('barbershop_id')->nullable()->after('role')->constrained()->nullOnDelete();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barbershop_id')->constrained('barbershops')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->time('open_time')->default('08:00:00');
            $table->time('close_time')->default('21:00:00');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('queue_timeout_minutes')->default(60);
            $table->unsignedSmallInteger('avg_service_minutes')->default(15);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};

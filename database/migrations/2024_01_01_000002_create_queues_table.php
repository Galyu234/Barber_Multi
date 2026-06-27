<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->unsignedSmallInteger('queue_number');
            $table->string('customer_session', 64)->index();
            $table->enum('status', ['waiting', 'serving', 'done', 'timeout', 'cancelled'])->default('waiting');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index(['branch_id', 'joined_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};

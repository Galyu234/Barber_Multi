<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            // QR token unik untuk identitas tiket pelanggan (generate setelah join)
            $table->string('queue_qr', 64)->nullable()->unique()->after('customer_session');
            $table->index('queue_qr');
        });
    }

    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->dropColumn('queue_qr');
        });
    }
};

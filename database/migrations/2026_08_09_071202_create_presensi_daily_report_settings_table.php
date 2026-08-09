<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('presensi_daily_report_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(false);
            $table->time('cutoff_time')->default('08:00:00');
            $table->text('template_pesan')->nullable();
            $table->json('recipients')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_daily_report_settings');
    }
};

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
        Schema::create('presensi_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('status_presensi')->unique()->comment('hadir, sakit, izin, alpa, telat, pulang');
            $table->boolean('is_active')->default(false);
            $table->json('recipients')->nullable()->comment('Kombinasi dari ortu, wali_kelas, atau nama jabatan');
            $table->text('template_pesan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_notification_settings');
    }
};

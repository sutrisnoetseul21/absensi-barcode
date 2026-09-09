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
        Schema::table('spikap_notif_settings', function (Blueprint $table) {
            $table->boolean('notify_guru_laporan_biasa')->default(true)->after('emergency_handlers');
            $table->boolean('notify_siswa_apresiasi')->default(true)->after('notify_guru_laporan_biasa');
            $table->boolean('notify_siswa_tindak_lanjut')->default(true)->after('notify_siswa_apresiasi');
            $table->json('target_penerima_siswa')->nullable()->after('notify_siswa_tindak_lanjut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spikap_notif_settings', function (Blueprint $table) {
            $table->dropColumn([
                'notify_guru_laporan_biasa',
                'notify_siswa_apresiasi',
                'notify_siswa_tindak_lanjut',
                'target_penerima_siswa',
            ]);
        });
    }
};

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
            $table->string('nama_aplikasi', 50)->default('SPIKAP')->nullable()->after('recipients');
            $table->string('sub_judul', 100)->default('Anti-Perundungan & Pengaduan Siswa')->nullable()->after('nama_aplikasi');
            $table->text('penjelasan_aplikasi')->nullable()->after('sub_judul');
            $table->string('slug_url', 50)->default('spikap')->nullable()->after('penjelasan_aplikasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spikap_notif_settings', function (Blueprint $table) {
            $table->dropColumn([
                'nama_aplikasi',
                'sub_judul',
                'penjelasan_aplikasi',
                'slug_url',
            ]);
        });
    }
};

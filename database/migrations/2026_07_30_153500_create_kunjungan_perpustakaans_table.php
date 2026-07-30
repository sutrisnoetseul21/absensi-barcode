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
        Schema::create('kunjungan_perpustakaans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('pengunjung_type'); // 'siswa' atau 'guru'
            $table->uuid('pengunjung_id');
            $table->date('tanggal')->index();
            $table->time('waktu_masuk');
            $table->string('tujuan_kunjungan')->default('Membaca / Belajar');
            $table->string('catatan')->nullable();
            $table->uuid('petugas_id')->nullable();
            $table->timestamps();

            $table->index(['pengunjung_type', 'pengunjung_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kunjungan_perpustakaans');
    }
};

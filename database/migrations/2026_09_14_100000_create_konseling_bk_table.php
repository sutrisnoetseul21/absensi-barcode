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
        Schema::create('konseling_bk', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('academic_year_id');
            $table->uuid('teacher_id');
            $table->uuid('student_id');
            $table->uuid('jurnal_guru_wali_id')->nullable();

            $table->dateTime('tanggal_waktu');
            $table->string('jenis_layanan'); // Konseling Individu, Bimbingan Kelompok, Mediasi Kasus, Kunjungan Rumah, Konsultasi Orang Tua
            $table->string('bidang_bimbingan'); // Pribadi, Sosial, Belajar, Karir
            $table->string('topik_masalah');
            $table->text('uraian_kasus');
            $table->string('pendekatan_teknik')->nullable();
            $table->text('hasil_konseling');
            $table->text('rencana_tindak_lanjut')->nullable();
            $table->string('status_kasus')->default('Dalam Proses'); // Dalam Proses, Tuntas / Selesai, Perlu Sesi Lanjutan, Alih Tangan Kasus
            $table->text('rekomendasi_untuk_guru_wali')->nullable();
            $table->boolean('is_rahasia')->default(true);

            $table->timestamps();

            // Foreign Keys
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->cascadeOnDelete();
            $table->foreign('teacher_id')->references('id')->on('teachers')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('jurnal_guru_wali_id')->references('id')->on('jurnal_guru_wali')->nullOnDelete();

            // Indexing for rapid queries
            $table->index(['academic_year_id', 'teacher_id']);
            $table->index(['student_id', 'tanggal_waktu']);
            $table->index('status_kasus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konseling_bk');
    }
};

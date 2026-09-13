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
        Schema::create('konsultasi_guru_wali', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignUuid('teacher_id')->constrained('teachers')->restrictOnDelete();
            $table->foreignUuid('kelompok_id')->constrained('kelompok_guru_wali')->cascadeOnDelete();
            $table->enum('kategori_pendampingan', [
                'Akademik',
                'Karakter & Kedisiplinan',
                'Minat & Bakat / Ekskul',
                'Sosial & Psikologis',
            ]);
            $table->string('topik_konsultasi', 255);
            $table->text('detail_permasalahan');
            $table->enum('mode_konsultasi', [
                'Tatap Muka',
                'Pesan Portal',
            ])->default('Tatap Muka');
            $table->dateTime('usulan_tanggal_waktu')->nullable();
            $table->dateTime('jadwal_pasti')->nullable();
            $table->enum('status_pengajuan', [
                'Menunggu Konfirmasi',
                'Dijadwalkan',
                'Selesai',
                'Ditolak',
                'Dikonversi ke Jurnal',
            ])->default('Menunggu Konfirmasi');
            $table->text('tanggapan_guru')->nullable();
            $table->string('alasan_penolakan', 255)->nullable();
            $table->foreignUuid('jurnal_id')->nullable()->constrained('jurnal_guru_wali')->nullOnDelete();
            $table->timestamps();

            $table->index(['student_id', 'status_pengajuan'], 'idx_student_status');
            $table->index(['teacher_id', 'status_pengajuan'], 'idx_teacher_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konsultasi_guru_wali');
    }
};

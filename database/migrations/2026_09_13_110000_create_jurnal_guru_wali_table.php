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
        Schema::create('jurnal_guru_wali', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('teacher_id')->constrained('teachers')->restrictOnDelete();
            $table->foreignUuid('kelompok_id')->constrained('kelompok_guru_wali')->restrictOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->restrictOnDelete();
            $table->dateTime('tanggal_waktu');
            $table->enum('jenis_pendampingan', [
                'Individu',
                'Kelompok Kecil',
                'Klasikal',
            ]);
            $table->enum('kategori_pendampingan', [
                'Akademik',
                'Karakter & Kedisiplinan',
                'Minat & Bakat / Ekskul',
                'Sosial & Psikologis',
            ]);
            $table->text('uraian_pembahasan');
            $table->enum('status_sesi', [
                'Tuntas / Selesai',
                'Dalam Pemantauan',
                'Bimbingan Lanjutan',
            ]);
            $table->enum('rujukan_kolaborasi', [
                'Mandiri',
                'Wali Kelas',
                'Guru BK',
                'Orang Tua / Wali',
            ])->default('Mandiri');
            $table->text('rencana_tindak_lanjut');
            $table->boolean('is_public_note')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['teacher_id', 'tanggal_waktu'], 'idx_teacher_tanggal');
            $table->index(['student_id', 'tanggal_waktu'], 'idx_student_tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal_guru_wali');
    }
};

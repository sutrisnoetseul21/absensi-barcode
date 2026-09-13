<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modul Guru Wali — Langkah 1
     *
     * Membuat dua tabel:
     *  1. kelompok_guru_wali        — entitas kelompok dampingan (multi-tahun, seperti "kelas")
     *  2. kelompok_guru_wali_siswa  — mapping siswa ke kelompok (1 siswa aktif = 1 kelompok)
     */
    public function up(): void
    {
        // -----------------------------------------------------------------
        // Tabel 1: kelompok_guru_wali
        // -----------------------------------------------------------------
        Schema::create('kelompok_guru_wali', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('nama_kelompok', 100);

            // FK ke tabel teachers — restrict: tidak bisa hapus guru yang punya kelompok
            $table->foreignUuid('teacher_id')
                  ->constrained('teachers')
                  ->restrictOnDelete();

            $table->year('tahun_masuk'); // Tahun angkatan masuk siswa, contoh: 2024

            $table->boolean('status_aktif')->default(true);

            $table->timestamps();
            $table->softDeletes(); // Hapus kelompok = soft delete
        });

        // -----------------------------------------------------------------
        // Tabel 2: kelompok_guru_wali_siswa
        // -----------------------------------------------------------------
        Schema::create('kelompok_guru_wali_siswa', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // FK ke kelompok — cascade: hapus kelompok = hapus semua mapping siswa-nya
            $table->foreignUuid('kelompok_id')
                  ->constrained('kelompok_guru_wali')
                  ->cascadeOnDelete();

            // FK ke siswa — restrict: tidak bisa hapus siswa yang masih di kelompok
            $table->foreignUuid('student_id')
                  ->constrained('students')
                  ->restrictOnDelete();

            $table->boolean('status_aktif')->default(true);

            $table->timestamps();
            // Tidak pakai softDeletes — hapus baris = siswa keluar dari kelompok

            // Aturan: 1 siswa aktif hanya boleh ada di 1 kelompok pada satu waktu
            $table->unique(['student_id', 'status_aktif'], 'unique_student_aktif');
        });
    }

    /**
     * Urutan drop HARUS terbalik karena foreign key:
     * kelompok_guru_wali_siswa (punya FK ke kelompok_guru_wali) dihapus dulu.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelompok_guru_wali_siswa');
        Schema::dropIfExists('kelompok_guru_wali');
    }
};

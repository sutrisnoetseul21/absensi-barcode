<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom tahun_masuk ke kelompok_guru_wali_siswa (nullable dulu, isi setelah backfill)
        Schema::table('kelompok_guru_wali_siswa', function (Blueprint $table) {
            $table->year('tahun_masuk')->nullable()->after('student_id');
        });

        // 2. Backfill: copy tahun_masuk dari kelompok ke tiap baris anggotanya
        DB::statement('
            UPDATE kelompok_guru_wali_siswa kgs
            INNER JOIN kelompok_guru_wali kg ON kgs.kelompok_id = kg.id
            SET kgs.tahun_masuk = kg.tahun_masuk
        ');

        // 3. Jadikan tahun_masuk NOT NULL setelah terisi
        Schema::table('kelompok_guru_wali_siswa', function (Blueprint $table) {
            $table->year('tahun_masuk')->nullable(false)->change();
        });

        // 4. Hapus kolom tahun_masuk dari kelompok_guru_wali (sudah pindah ke siswa)
        Schema::table('kelompok_guru_wali', function (Blueprint $table) {
            $table->dropColumn('tahun_masuk');
        });

        // 5. Tambah unique constraint: 1 guru wali cuma boleh pegang 1 kelompok
        Schema::table('kelompok_guru_wali', function (Blueprint $table) {
            $table->unique('teacher_id', 'unique_teacher_kelompok');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop unique constraint pada kelompok_guru_wali
        Schema::table('kelompok_guru_wali', function (Blueprint $table) {
            $table->dropUnique('unique_teacher_kelompok');
        });

        // 2. Tambah kembali kolom tahun_masuk ke kelompok_guru_wali (nullable dulu)
        Schema::table('kelompok_guru_wali', function (Blueprint $table) {
            $table->year('tahun_masuk')->nullable()->after('teacher_id');
        });

        // 3. Backfill balik tahun_masuk ke kelompok_guru_wali dari salah satu anggotanya atau default
        DB::statement('
            UPDATE kelompok_guru_wali kg
            LEFT JOIN (
                SELECT kelompok_id, MIN(tahun_masuk) as tahun_masuk
                FROM kelompok_guru_wali_siswa
                GROUP BY kelompok_id
            ) kgs ON kg.id = kgs.kelompok_id
            SET kg.tahun_masuk = COALESCE(kgs.tahun_masuk, YEAR(CURDATE()))
        ');

        // 4. Jadikan tahun_masuk NOT NULL di kelompok_guru_wali
        Schema::table('kelompok_guru_wali', function (Blueprint $table) {
            $table->year('tahun_masuk')->nullable(false)->change();
        });

        // 5. Drop kolom tahun_masuk dari kelompok_guru_wali_siswa
        Schema::table('kelompok_guru_wali_siswa', function (Blueprint $table) {
            $table->dropColumn('tahun_masuk');
        });
    }
};

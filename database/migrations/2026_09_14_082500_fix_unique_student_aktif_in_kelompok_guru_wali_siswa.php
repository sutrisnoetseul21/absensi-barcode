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
        $indexes = collect(DB::select("SHOW INDEX FROM kelompok_guru_wali_siswa"))->pluck('Key_name')->unique()->toArray();

        // 1. Buat index baru untuk student_id terlebih dahulu agar foreign key constraint tetap terpenuhi
        if (!in_array('kgws_student_id_index', $indexes)) {
            Schema::table('kelompok_guru_wali_siswa', function (Blueprint $table) {
                $table->index('student_id', 'kgws_student_id_index');
            });
        }

        // 2. Sekarang index lama 'unique_student_aktif' aman di-drop
        if (in_array('unique_student_aktif', $indexes)) {
            Schema::table('kelompok_guru_wali_siswa', function (Blueprint $table) {
                $table->dropUnique('unique_student_aktif');
            });
        }

        // 3. Tambahkan Virtual Generated Column untuk memastikan integritas data:
        // Hanya 1 kelompok aktif per siswa pada satu waktu.
        // - Saat status_aktif = 1 -> active_student_id = student_id (dijamin UNIK oleh database).
        // - Saat status_aktif = 0 -> active_student_id = NULL (MySQL/MariaDB mengizinkan multiple NULL di UNIQUE index).
        // Catatan MariaDB: kolom CHAR(36) memerlukan TRIM() agar ekspresi GENERATED ALWAYS AS dapat diindeks UNIQUE.
        if (!Schema::hasColumn('kelompok_guru_wali_siswa', 'active_student_id')) {
            DB::statement('ALTER TABLE kelompok_guru_wali_siswa ADD COLUMN active_student_id VARCHAR(36) GENERATED ALWAYS AS (IF(status_aktif = 1, TRIM(student_id), NULL)) VIRTUAL');
        }

        $indexes = collect(DB::select("SHOW INDEX FROM kelompok_guru_wali_siswa"))->pluck('Key_name')->unique()->toArray();
        if (!in_array('unique_active_student_id', $indexes)) {
            DB::statement('ALTER TABLE kelompok_guru_wali_siswa ADD UNIQUE KEY unique_active_student_id (active_student_id)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexes = collect(DB::select("SHOW INDEX FROM kelompok_guru_wali_siswa"))->pluck('Key_name')->unique()->toArray();
        if (in_array('unique_active_student_id', $indexes)) {
            DB::statement('ALTER TABLE kelompok_guru_wali_siswa DROP KEY unique_active_student_id');
        }

        if (Schema::hasColumn('kelompok_guru_wali_siswa', 'active_student_id')) {
            DB::statement('ALTER TABLE kelompok_guru_wali_siswa DROP COLUMN active_student_id');
        }

        Schema::table('kelompok_guru_wali_siswa', function (Blueprint $table) {
            $table->unique(['student_id', 'status_aktif'], 'unique_student_aktif');
            $table->dropIndex('kgws_student_id_index');
        });
    }
};

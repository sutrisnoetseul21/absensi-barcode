<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Data Ayah
            $table->string('nama_ayah', 255)->nullable()->after('child_order');
            $table->string('pekerjaan_ayah', 255)->nullable()->after('nama_ayah');

            // Data Ibu
            $table->string('nama_ibu', 255)->nullable()->after('pekerjaan_ayah');
            $table->string('pekerjaan_ibu', 255)->nullable()->after('nama_ibu');

            // Data Wali (opsional)
            $table->string('nama_wali', 255)->nullable()->after('pekerjaan_ibu');
            $table->string('pekerjaan_wali', 255)->nullable()->after('nama_wali');

            // Cukup 1 nomor HP kontak Orang Tua / Wali
            $table->string('no_hp_orang_tua', 50)->nullable()->after('pekerjaan_wali');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'nama_ayah',
                'pekerjaan_ayah',
                'nama_ibu',
                'pekerjaan_ibu',
                'nama_wali',
                'pekerjaan_wali',
                'no_hp_orang_tua',
            ]);
        });
    }
};

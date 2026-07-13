<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // aktif = masih bersekolah
            // lulus = sudah lulus (kelas 9 diluluskan)
            // mutasi = pindah/keluar sekolah
            $table->enum('status', ['aktif', 'lulus', 'mutasi'])
                  ->default('aktif')
                  ->after('must_change_password');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};

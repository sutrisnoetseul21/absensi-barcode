<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom mutasi dan tracer study ke tabel students
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'tujuan_mutasi')) {
                $table->string('tujuan_mutasi')->nullable()->after('status');
                $table->text('alasan_mutasi')->nullable()->after('tujuan_mutasi');
                $table->date('tanggal_mutasi')->nullable()->after('alasan_mutasi');
                $table->boolean('status_melanjutkan')->default(false)->after('tanggal_mutasi');
                $table->foreignId('jenjang_lanjutan_id')->nullable()->constrained('alumni_jenjangs')->nullOnDelete()->after('status_melanjutkan');
                $table->string('nama_sekolah_lanjutan')->nullable()->after('jenjang_lanjutan_id');
                $table->integer('tahun_lulus_override')->nullable()->after('nama_sekolah_lanjutan');
            }
        });

        // 2. Tambah kolom source dan student_id ke tabel alumnis
        Schema::table('alumnis', function (Blueprint $table) {
            if (!Schema::hasColumn('alumnis', 'source')) {
                $table->string('student_id', 36)->nullable()->after('id');
                $table->enum('source', ['sistem', 'web_mandiri'])->default('web_mandiri')->after('student_id');
                $table->string('no_hp')->nullable()->after('nama_sekolah');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['jenjang_lanjutan_id']);
            $table->dropColumn([
                'tujuan_mutasi',
                'alasan_mutasi',
                'tanggal_mutasi',
                'status_melanjutkan',
                'jenjang_lanjutan_id',
                'nama_sekolah_lanjutan',
                'tahun_lulus_override',
            ]);
        });

        Schema::table('alumnis', function (Blueprint $table) {
            $table->dropColumn(['student_id', 'source', 'no_hp']);
        });
    }
};

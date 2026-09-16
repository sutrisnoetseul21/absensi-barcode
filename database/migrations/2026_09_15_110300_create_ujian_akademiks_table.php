<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ujian_akademiks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Known behavior proteksi: cascadeOnDelete dari academic_years, namun jika sudah ada nilai siswa di tabel nilai_ujians,
            // MySQL InnoDB akan otomatis memblokir penghapusan via RESTRICT constraint pada nilai_ujians.
            $table->foreignUuid('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
            $table->foreignId('mata_pelajaran_id')->nullable()->constrained('mata_pelajarans')->nullOnDelete();
            $table->foreignUuid('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();

            $table->integer('cbt_ujian_id')->nullable()->index();
            $table->string('cbt_event_nama', 100)->nullable();

            $table->string('nama_ujian');
            $table->enum('jenis_ujian', ['harian', 'sts', 'sas', 'tryout', 'remedial'])->default('harian');
            $table->decimal('kkm', 5, 2)->default(75.00);
            $table->integer('durasi_menit')->default(60);
            $table->integer('total_soal')->default(0);

            $table->dateTime('tanggal_mulai');
            $table->dateTime('tanggal_selesai');
            $table->enum('status', ['draft', 'aktif', 'selesai', 'arsip'])->default('draft');
            $table->text('keterangan')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ujian_akademiks');
    }
};

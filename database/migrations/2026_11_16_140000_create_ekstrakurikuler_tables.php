<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ekstrakurikulers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->string('nama_ekskul', 120);
            $table->text('keterangan')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['academic_year_id', 'status_aktif'], 'ekskul_year_active_idx');
            $table->index(['academic_year_id', 'nama_ekskul'], 'ekskul_year_nama_idx');
        });

        Schema::create('ekstrakurikuler_pembinas', function (Blueprint $table) {
            $table->foreignUuid('ekstrakurikuler_id')->constrained('ekstrakurikulers')->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->constrained('teachers')->restrictOnDelete();
            $table->timestamps();

            $table->primary(['ekstrakurikuler_id', 'teacher_id'], 'ekskul_pembina_pk');
            $table->index('teacher_id', 'ekskul_pembina_teacher_idx');
        });

        Schema::create('ekstrakurikuler_siswas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ekstrakurikuler_id')->constrained('ekstrakurikulers')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->restrictOnDelete();
            $table->date('tanggal_gabung')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();

            $table->unique(['ekstrakurikuler_id', 'student_id'], 'ekskul_siswa_unique');
            $table->index(['ekstrakurikuler_id', 'status_aktif'], 'ekskul_siswa_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekstrakurikuler_siswas');
        Schema::dropIfExists('ekstrakurikuler_pembinas');
        Schema::dropIfExists('ekstrakurikulers');
    }
};

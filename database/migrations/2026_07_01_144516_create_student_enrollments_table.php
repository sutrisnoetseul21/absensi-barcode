<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('class_id')->constrained('classes');
            $table->foreignUuid('academic_year_id')->constrained('academic_years');
            $table->enum('status', ['aktif', 'naik', 'tinggal', 'pindah', 'lulus'])->default('aktif');
            $table->timestamps();

            // Satu siswa hanya 1 kelas per tahun ajaran
            $table->unique(['student_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};

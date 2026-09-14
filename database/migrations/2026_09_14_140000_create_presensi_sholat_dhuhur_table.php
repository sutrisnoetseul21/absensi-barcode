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
        Schema::create('presensi_sholat_dhuhur', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('academic_year_id');
            $table->uuid('class_id');
            $table->uuid('student_id');
            $table->uuid('enrollment_id')->nullable();
            $table->date('date');
            $table->enum('status', ['hadir', 'ijin', 'tidak_hadir', 'libur'])->default('hadir');
            $table->string('keterangan')->nullable();
            $table->uuid('input_by_teacher_id')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->cascadeOnDelete();
            $table->foreign('class_id')->references('id')->on('classes')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('enrollment_id')->references('id')->on('student_enrollments')->nullOnDelete();
            $table->foreign('input_by_teacher_id')->references('id')->on('teachers')->nullOnDelete();

            // Unique constraint: 1 student has 1 record per date
            $table->unique(['student_id', 'date'], 'unique_student_date_sholat');

            // Indexes for fast querying
            $table->index(['class_id', 'date']);
            $table->index(['academic_year_id', 'class_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_sholat_dhuhur');
    }
};

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
        Schema::create('riwayat_pindah_kelas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->foreignUuid('enrollment_id')->constrained('student_enrollments')->cascadeOnDelete();
            // Denormalisasi untuk mempercepat pencarian (mengacu pada aturan di penjelasan-relasi-data.md)
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            
            $table->foreignUuid('from_class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignUuid('to_class_id')->nullable()->constrained('classes')->nullOnDelete();
            
            $table->string('reason')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_pindah_kelas');
    }
};

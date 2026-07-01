<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students');
            $table->foreignUuid('enrollment_id')->constrained('student_enrollments');

            // DENORMALIZED — disalin dari enrollment saat insert untuk mempercepat query dashboard
            $table->foreignUuid('class_id')->constrained('classes');
            $table->foreignUuid('academic_year_id')->constrained('academic_years');

            $table->date('date');
            $table->time('scan_time')->nullable();
            $table->enum('status', ['hadir', 'telat', 'alpa', 'sakit', 'izin']);
            $table->unsignedInteger('late_minutes')->default(0);
            $table->string('note')->nullable(); // alasan Izin/Sakit dari wali kelas

            $table->boolean('is_manual_input')->default(false);
            // Polymorphic: bisa Teacher (wali_kelas) atau User (admin)
            $table->uuid('manual_input_by_id')->nullable();
            $table->string('manual_input_by_type')->nullable();

            // Admin yang scan (guard Filament = tabel users)
            $table->foreignUuid('scanned_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Satu siswa hanya 1 record per hari
            $table->unique(['student_id', 'date']);
            // Index untuk query dashboard tanpa join berlapis
            $table->index(['class_id', 'academic_year_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_log_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('promotion_log_id')->constrained('promotion_logs')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('students');
            $table->foreignUuid('old_enrollment_id')->constrained('student_enrollments');
            $table->foreignUuid('new_enrollment_id')->nullable()->constrained('student_enrollments')->nullOnDelete();
            // null = lulus (tidak ada enrollment baru)
            $table->enum('decision', ['naik', 'tinggal', 'pindah', 'lulus']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_log_details');
    }
};

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
        Schema::dropIfExists('teacher_mata_pelajaran');

        Schema::create('pengajarans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('class_academic_year_id');
            $table->uuid('teacher_id');
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajarans')->cascadeOnDelete();
            $table->timestamps();

            $table->foreign('class_academic_year_id')->references('id')->on('class_academic_year')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajarans');

        // Recreate the old table in case of rollback
        Schema::create('teacher_mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->uuid('teacher_id');
            $table->uuid('mata_pelajaran_id');
            $table->timestamps();

            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            $table->foreign('mata_pelajaran_id')->references('id')->on('mata_pelajarans')->onDelete('cascade');
        });
    }
};

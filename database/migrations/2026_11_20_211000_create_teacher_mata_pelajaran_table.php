<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('teacher_mata_pelajaran')) {
            Schema::create('teacher_mata_pelajaran', function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('teacher_id')->constrained('teachers')->cascadeOnDelete();
                $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajarans')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['teacher_id', 'mata_pelajaran_id'], 'teacher_mapel_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_mata_pelajaran');
    }
};


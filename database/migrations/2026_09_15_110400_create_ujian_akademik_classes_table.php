<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ujian_akademik_classes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ujian_akademik_id')->constrained('ujian_akademiks')->cascadeOnDelete();
            $table->foreignUuid('class_id')->constrained('classes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['ujian_akademik_id', 'class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ujian_akademik_classes');
    }
};

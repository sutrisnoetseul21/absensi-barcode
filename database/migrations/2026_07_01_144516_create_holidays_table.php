<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('start_date');          // mulai libur (support range)
            $table->date('end_date')->nullable(); // null = 1 hari saja
            $table->string('description');
            $table->enum('type', ['nasional', 'cuti_bersama', 'khusus']);
            $table->foreignUuid('class_id')->nullable()->constrained('classes')->nullOnDelete();
            // null = semua kelas libur; ada class_id = hanya kelas tertentu
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};

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
        Schema::create('jenis_ujians', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique(); // Misal: ASAJ, UH, PAS
            $table->string('nama', 100); // Misal: Asesmen Sumatif Akhir Jenjang
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_ujians');
    }
};

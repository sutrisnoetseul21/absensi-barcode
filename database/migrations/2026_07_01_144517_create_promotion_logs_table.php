<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('academic_year_from_id')->constrained('academic_years');
            $table->foreignUuid('academic_year_to_id')->constrained('academic_years');
            $table->foreignUuid('executed_by')->constrained('users'); // Admin (Filament) = tabel users
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_logs');
    }
};

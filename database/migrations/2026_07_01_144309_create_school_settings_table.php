<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('school_name');
            $table->text('school_address')->nullable();
            $table->string('school_logo_path')->nullable();
            $table->string('principal_name')->nullable(); // nama kepala sekolah untuk TTD
            $table->time('checkin_time')->default('07:00:00'); // jam batas "Hadir" global
            $table->unsignedInteger('late_threshold_minutes')->default(0); // menit toleransi
            // FK ke academic_years ditambahkan setelah tabel academic_years dibuat
            // (dihandle via separate migration jika diperlukan, atau nullable saja)
            $table->uuid('academic_year_id_active')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};

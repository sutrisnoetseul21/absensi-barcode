<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_guardians', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->foreign('student_id')
                  ->references('id')
                  ->on('students')
                  ->cascadeOnDelete();

            // Menggunakan string(20) bukan enum, validasi di level Filament
            // supaya lebih fleksibel jika ada tipe wali baru di masa mendatang
            $table->string('type', 20); // 'ayah', 'ibu', 'wali'

            $table->string('name', 255);
            $table->string('occupation', 255)->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 50)->nullable();

            $table->timestamps();
            // SoftDeletes — konsisten dengan pola audit trail di modul lain
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_guardians');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nisn')->unique();          // INDEX wajib
            $table->string('name');
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->text('address')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('barcode_code')->unique();  // INDEX wajib, default = NISN
            $table->boolean('barcode_active')->default(true);
            $table->string('username')->unique();      // default = NISN
            $table->string('password');
            $table->boolean('must_change_password')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Alumni Settings
        Schema::create('alumni_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->boolean('show_table')->default(true);
            $table->string('banner_title')->default('Tracer Study Alumni');
            $table->text('banner_text')->nullable();
            $table->string('button_text')->default('Daftarkan Data Saya');
            $table->timestamps();
        });

        // 2. Tabel Alumni Jenjang (Pilihan Jenjang Lanjutan)
        Schema::create('alumni_jenjangs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jenjang');
            $table->timestamps();
        });

        // 3. Tabel Data Alumni
        Schema::create('alumnis', function (Blueprint $table) {
            $table->id();
            $table->string('nisn')->unique();
            $table->string('nama');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->integer('tahun_lulus');
            $table->boolean('melanjutkan')->default(false);
            $table->foreignId('jenjang_id')->nullable()->constrained('alumni_jenjangs')->nullOnDelete();
            $table->string('nama_sekolah')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumnis');
        Schema::dropIfExists('alumni_jenjangs');
        Schema::dropIfExists('alumni_settings');
    }
};

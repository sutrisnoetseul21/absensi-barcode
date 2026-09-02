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
        // 1. Pengaturan Layanan Pengaduan
        Schema::create('pengaduan_settings', function (Blueprint $table) {
            $table->id();
            $table->string('module_name')->default('Pengaduan');
            $table->string('banner_title')->default('Layanan Aspirasi & Pengaduan');
            $table->text('banner_text')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Kategori Pengaduan
        Schema::create('pengaduan_kategoris', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori');
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        // 3. Data Pengaduan Masuk
        Schema::create('pengaduans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('email');
            $table->string('no_hp')->nullable();
            $table->foreignId('pengaduan_kategori_id')->constrained('pengaduan_kategoris')->onDelete('cascade');
            $table->text('isi_pengaduan');
            $table->string('status')->default('menunggu'); // menunggu, diproses, selesai
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaduans');
        Schema::dropIfExists('pengaduan_kategoris');
        Schema::dropIfExists('pengaduan_settings');
    }
};

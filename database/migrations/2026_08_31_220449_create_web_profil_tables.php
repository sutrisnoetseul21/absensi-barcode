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
        Schema::create('web_settings', function (Blueprint $table) {
            $table->id();
            $table->string('hero_image')->nullable();
            $table->text('running_text')->nullable();
            $table->string('foto_kepsek')->nullable();
            $table->text('sambutan_kepsek')->nullable();
            $table->string('link_youtube')->nullable();
            $table->string('link_tiktok')->nullable();
            $table->string('link_ig')->nullable();
            $table->string('link_fb')->nullable();
            $table->string('link_pengaduan')->nullable();
            $table->integer('stat_tenaga_kependidikan')->default(0);
            $table->timestamps();
        });

        Schema::create('web_sarpras', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_fasilitas');
            $table->string('foto')->nullable();
            $table->string('deskripsi')->nullable();
            $table->integer('urutan')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('web_artikels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->enum('tipe', ['berita', 'pengumuman']);
            $table->text('konten');
            $table->string('thumbnail')->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('web_galeris', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('judul')->nullable();
            $table->string('foto_path');
            $table->integer('urutan')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('web_widgets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_widget');
            $table->string('url_link');
            $table->string('icon')->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_widgets');
        Schema::dropIfExists('web_galeris');
        Schema::dropIfExists('web_artikels');
        Schema::dropIfExists('web_sarpras');
        Schema::dropIfExists('web_settings');
    }
};

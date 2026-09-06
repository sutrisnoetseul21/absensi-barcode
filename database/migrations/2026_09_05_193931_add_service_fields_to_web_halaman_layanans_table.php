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
        Schema::table('web_halaman_layanans', function (Blueprint $table) {
            $table->string('kategori')->nullable()->after('judul');
            $table->text('deskripsi_singkat')->nullable()->after('kategori');
            $table->string('file_pdf')->nullable()->after('konten');
            $table->string('biaya')->default('Gratis (Rp 0)')->after('file_pdf');
            $table->string('waktu_layanan')->default('15 - 30 Menit')->after('biaya');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('web_halaman_layanans', function (Blueprint $table) {
            $table->dropColumn(['kategori', 'deskripsi_singkat', 'file_pdf', 'biaya', 'waktu_layanan']);
        });
    }
};

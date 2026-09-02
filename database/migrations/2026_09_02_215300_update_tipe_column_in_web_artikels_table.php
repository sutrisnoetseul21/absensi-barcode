<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter column 'tipe' to string to support 'prestasi' and any future post types
        DB::statement("ALTER TABLE `web_artikels` MODIFY COLUMN `tipe` VARCHAR(50) NOT NULL DEFAULT 'berita'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `web_artikels` MODIFY COLUMN `tipe` ENUM('berita', 'pengumuman') NOT NULL DEFAULT 'berita'");
    }
};

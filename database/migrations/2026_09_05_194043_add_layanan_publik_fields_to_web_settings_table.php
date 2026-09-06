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
        Schema::table('web_settings', function (Blueprint $table) {
            $table->string('gambar_visi_misi_pelayanan')->nullable();
            $table->string('gambar_maklumat_pelayanan')->nullable();
            $table->string('link_sp4n_lapor')->nullable();
            $table->string('link_cariyanlik')->nullable();
            $table->string('link_pengaduan_daerah')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('web_settings', function (Blueprint $table) {
            $table->dropColumn([
                'gambar_visi_misi_pelayanan',
                'gambar_maklumat_pelayanan',
                'link_sp4n_lapor',
                'link_cariyanlik',
                'link_pengaduan_daerah'
            ]);
        });
    }
};

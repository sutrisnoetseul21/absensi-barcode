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
        Schema::table('school_settings', function (Blueprint $table) {
            $table->string('theme_primary')->nullable()->after('lama_pinjam_buku_hari');
            $table->string('theme_secondary')->nullable()->after('theme_primary');
            $table->string('theme_accent')->nullable()->after('theme_secondary');
            $table->string('theme_warning')->nullable()->after('theme_accent');
            $table->string('theme_danger')->nullable()->after('theme_warning');
            $table->string('theme_info')->nullable()->after('theme_danger');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn([
                'theme_primary',
                'theme_secondary',
                'theme_accent',
                'theme_warning',
                'theme_danger',
                'theme_info',
            ]);
        });
    }
};

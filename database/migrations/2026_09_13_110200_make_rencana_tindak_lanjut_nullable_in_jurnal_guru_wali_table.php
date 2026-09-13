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
        Schema::table('jurnal_guru_wali', function (Blueprint $table) {
            $table->text('rencana_tindak_lanjut')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jurnal_guru_wali', function (Blueprint $table) {
            $table->text('rencana_tindak_lanjut')->nullable(false)->change();
        });
    }
};

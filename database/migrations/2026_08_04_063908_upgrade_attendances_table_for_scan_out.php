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
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('status')->change();
            $table->time('scan_out_time')->nullable()->after('scan_time');
            $table->string('status_pulang')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['scan_out_time', 'status_pulang']);
            // Untuk rollback tipe status kembali ke enum agak rumit, kita biarkan sebagai string di down() atau tidak rollback tipe data
        });
    }
};

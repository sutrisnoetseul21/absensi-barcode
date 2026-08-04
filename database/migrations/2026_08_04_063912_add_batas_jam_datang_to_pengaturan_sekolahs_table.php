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
            $table->time('batas_scan_datang_time')->default('09:00:00')->after('checkin_time');
            $table->time('start_scan_out_time')->default('13:00:00')->after('batas_scan_datang_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn(['batas_scan_datang_time', 'start_scan_out_time']);
        });
    }
};

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
        Schema::table('presensi_daily_report_settings', function (Blueprint $table) {
            $table->date('manual_send_date')->nullable()->after('recipients');
            $table->tinyInteger('manual_send_count')->default(0)->after('manual_send_date');
        });

        Schema::table('presensi_school_summary_settings', function (Blueprint $table) {
            $table->date('manual_send_date')->nullable()->after('recipients');
            $table->tinyInteger('manual_send_count')->default(0)->after('manual_send_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensi_daily_report_settings', function (Blueprint $table) {
            $table->dropColumn(['manual_send_date', 'manual_send_count']);
        });

        Schema::table('presensi_school_summary_settings', function (Blueprint $table) {
            $table->dropColumn(['manual_send_date', 'manual_send_count']);
        });
    }
};

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
            $table->boolean('auto_alpa_active')->default(false)->after('batas_scan_datang_time');
            $table->time('auto_alpa_time')->default('09:00:00')->after('auto_alpa_active');
            $table->date('last_auto_alpa_run_date')->nullable()->after('auto_alpa_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn(['auto_alpa_active', 'auto_alpa_time', 'last_auto_alpa_run_date']);
        });
    }
};

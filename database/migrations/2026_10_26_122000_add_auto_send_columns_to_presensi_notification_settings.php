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
        Schema::table('presensi_notification_settings', function (Blueprint $table) {
            $table->boolean('is_auto_send')->default(false)->after('is_active');
            $table->time('auto_send_time')->nullable()->default('07:30:00')->after('is_auto_send');
            $table->date('last_auto_send_date')->nullable()->after('auto_send_time');
            $table->date('manual_send_date')->nullable()->after('last_auto_send_date');
            $table->integer('manual_send_count')->default(0)->after('manual_send_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensi_notification_settings', function (Blueprint $table) {
            $table->dropColumn([
                'is_auto_send',
                'auto_send_time',
                'last_auto_send_date',
                'manual_send_date',
                'manual_send_count',
            ]);
        });
    }
};

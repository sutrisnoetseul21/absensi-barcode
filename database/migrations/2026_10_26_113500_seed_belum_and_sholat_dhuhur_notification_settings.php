<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\PresensiNotificationSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        PresensiNotificationSetting::firstOrCreate(
            ['status_presensi' => 'belum'],
            [
                'is_active' => false,
                'recipients' => ['ortu'],
                'template_pesan' => PresensiNotificationSetting::getDefaultTemplate('belum'),
            ]
        );

        PresensiNotificationSetting::firstOrCreate(
            ['status_presensi' => 'sholat_dhuhur'],
            [
                'is_active' => false,
                'recipients' => ['ortu'],
                'template_pesan' => PresensiNotificationSetting::getDefaultTemplate('sholat_dhuhur'),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        PresensiNotificationSetting::whereIn('status_presensi', ['belum', 'sholat_dhuhur'])->delete();
    }
};

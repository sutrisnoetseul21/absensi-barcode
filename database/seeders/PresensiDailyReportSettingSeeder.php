<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PresensiDailyReportSetting;

class PresensiDailyReportSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PresensiDailyReportSetting::current();
    }
}

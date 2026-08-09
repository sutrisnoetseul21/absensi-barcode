<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PresensiSchoolSummarySetting;

class PresensiSchoolSummarySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PresensiSchoolSummarySetting::current();
    }
}

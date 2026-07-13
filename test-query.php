<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$data = \App\Models\Presensi::where('date', '2026-07-10')->get();
echo count($data) . " records for 2026-07-10\n";

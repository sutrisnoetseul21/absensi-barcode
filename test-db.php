<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$data = \Illuminate\Support\Facades\DB::table('attendances')->orderBy('created_at', 'desc')->limit(5)->get();
file_put_contents('/home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/db-result.txt', json_encode($data, JSON_PRETTY_PRINT));
echo "Done";

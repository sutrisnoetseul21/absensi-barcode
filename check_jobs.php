<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$requests = App\Models\LeaveRequest::orderBy('created_at', 'desc')->take(3)->get(['id', 'student_id', 'status', 'approval_token', 'wa_text_sent_at', 'created_at']);
echo "=== LEAVE REQUESTS ===" . PHP_EOL;
echo $requests->toJson(JSON_PRETTY_PRINT) . PHP_EOL;

$jobs = Illuminate\Support\Facades\DB::table('jobs')->get();
echo "=== JOBS IN QUEUE ===" . PHP_EOL;
echo $jobs->toJson(JSON_PRETTY_PRINT) . PHP_EOL;

<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$logs = App\Models\WhatsAppNotificationLog::orderBy('id', 'desc')->take(3)->get(['id', 'recipient_type', 'status', 'response_payload', 'created_at']);
echo json_encode($logs, JSON_PRETTY_PRINT);

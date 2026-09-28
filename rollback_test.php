<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$token = 'fXFrpvRN';
$record = App\Models\LeaveRequest::where('approval_token', $token)->first();

if ($record) {
    $record->status = 'pending';
    $record->token_used_at = null;
    $record->approved_at = null;
    $record->approved_by = null;
    $record->approved_by_type = null;
    $record->save();
    
    // Hapus presensi yang ter-sync (optional tapi bikin state bersih)
    app(App\Services\LeaveRequestService::class)->removeAttendances($record);
    
    echo "✅ Ijin dengan token {$token} telah dikembalikan ke 'pending'. Siap diuji coba lagi.\n";
} else {
    echo "❌ Token tidak ditemukan.\n";
}

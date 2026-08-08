<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $uuid = \Illuminate\Support\Facades\DB::table('bukus')->first()->id;

    \Illuminate\Support\Facades\DB::transaction(function () use ($uuid) {
        $result = \App\Models\EksemplarBuku::generateKodeEksemplar('RACE-', 50);
        $inserts = [];
        foreach ($result['codes'] as $code) {
            $inserts[] = [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'buku_id' => $uuid,
                'kode_eksemplar' => $code,
                'status' => 'tersedia',
                'kondisi_fisik' => 'baik',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        \Illuminate\Support\Facades\DB::table('eksemplar_bukus')->insert($inserts);
    });
    echo "Success pid " . getmypid() . "\n";
} catch (\Exception $e) {
    echo "Failed pid " . getmypid() . ": " . $e->getMessage() . "\n";
}

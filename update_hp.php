<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$target_hp = '082227799114';

// Update Siswa
$siswa = App\Models\Siswa::where('name', 'like', '%1%')->first() ?? App\Models\Siswa::first();
if ($siswa) {
    $siswa->no_hp = $target_hp;
    $siswa->save();
    echo '✅ Siswa diupdate: ' . $siswa->name . ' -> ' . $siswa->no_hp . PHP_EOL;
    
    // Pastikan user-nya juga diupdate
    if ($siswa->user) {
        $siswa->user->no_hp = $target_hp;
        $siswa->user->save();
        echo '✅ User Siswa diupdate: ' . $siswa->user->name . PHP_EOL;
    }
} else {
    echo '❌ Tidak ada Siswa ditemukan' . PHP_EOL;
}

// Update Guru
$guru = App\Models\Guru::where('name', 'like', '%A%')->first() ?? App\Models\Guru::first();
if ($guru) {
    $guru->no_hp = $target_hp;
    $guru->save();
    echo '✅ Guru diupdate: ' . $guru->name . ' -> ' . $guru->no_hp . PHP_EOL;
    
    // Pastikan user-nya juga diupdate
    if ($guru->user) {
        $guru->user->no_hp = $target_hp;
        $guru->user->save();
        echo '✅ User Guru diupdate: ' . $guru->user->name . PHP_EOL;
    }
} else {
    echo '❌ Tidak ada Guru ditemukan' . PHP_EOL;
}

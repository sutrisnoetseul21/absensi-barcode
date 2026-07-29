$action = new App\Actions\ProcessSirkulasiAction();
$user = App\Models\User::first();

echo "==========================================\n";
echo "1. TEST SCAN SISWA\n";
$siswaProfile = App\Models\StudentPresensiProfile::where('barcode_active', true)->whereHas('student.enrollmentAktif')->first();
if (!$siswaProfile) {
    echo "Gagal menemukan profil siswa yang aktif dan punya enrollment.\n";
} else {
    $resSiswa = $action->execute(['jenis_scan' => 'PEMINJAM', 'barcode' => $siswaProfile->barcode_code], $user->id);
    echo json_encode($resSiswa, JSON_PRETTY_PRINT) . "\n";
    if (isset($resSiswa['sub_info']) && strpos($resSiswa['sub_info'], 'Siswa') !== false) {
        echo "OK! sub_info Siswa valid.\n";
    } else {
        echo "ERROR! sub_info Siswa tidak valid.\n";
    }
}

echo "\n==========================================\n";
echo "2. TEST SCAN GURU\n";
$guruProfile = App\Models\TeacherPresensiProfile::where('barcode_active', true)->first();
if (!$guruProfile) {
    echo "Gagal menemukan profil guru yang aktif.\n";
} else {
    $resGuru = $action->execute(['jenis_scan' => 'PEMINJAM', 'barcode' => $guruProfile->barcode_code], $user->id);
    echo json_encode($resGuru, JSON_PRETTY_PRINT) . "\n";
    if (isset($resGuru['sub_info']) && strpos($resGuru['sub_info'], 'Guru') !== false) {
        echo "OK! sub_info Guru valid.\n";
    } else {
        echo "ERROR! sub_info Guru tidak valid.\n";
    }
}

echo "\n==========================================\n";
echo "3. TEST PEMINJAMAN OLEH SISWA & PENGEMBALIAN\n";
$eksemplar = App\Models\EksemplarBuku::first();
if ($eksemplar && isset($resSiswa) && $resSiswa['status'] === 'success') {
    $eksemplar->update(['status' => 'tersedia']);
    
    // Pinjam
    $resPinjam = $action->execute([
        'jenis_scan' => 'BUKU',
        'peminjam_id' => $resSiswa['peminjam_id'],
        'peminjam_type' => $resSiswa['peminjam_type'],
        'barcode' => $eksemplar->kode_eksemplar
    ], $user->id);
    
    echo "Hasil Pinjam: " . json_encode($resPinjam, JSON_PRETTY_PRINT) . "\n";
    $peminjaman = App\Models\Peminjaman::latest('id')->first();
    if ($peminjaman) {
        echo "Peminjaman type di DB: " . $peminjaman->peminjam_type . "\n";
    }
    
    // Kembali (ProsesPengembalianAction)
    // Note: Assuming App\Actions\ProcessPengembalianAction exists
    if (class_exists('App\Actions\ProcessPengembalianAction')) {
        $kembaliAction = new App\Actions\ProcessPengembalianAction();
        $resKembali = $kembaliAction->execute(['barcode' => $eksemplar->kode_eksemplar], $user->id);
        echo "Hasil Kembali: " . json_encode($resKembali, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "ProsesPengembalianAction tidak ditemukan. Abaikan test kembali.\n";
    }
} else {
    echo "Gagal menemukan eksemplar atau Siswa belum di-scan.\n";
}

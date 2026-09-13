<?php

namespace App\Listeners\GuruWali;

use App\Events\Student\StudentMutated;
use App\Models\KelompokGuruWaliSiswa;

/**
 * Listener milik Modul Guru Wali.
 *
 * Bereaksi terhadap Event StudentMutated dengan mengarsipkan
 * keanggotaan aktif siswa di kelompok Guru Wali (status_aktif = false).
 *
 * Baris KelompokGuruWaliSiswa TIDAK dihapus — diarsipkan saja —
 * agar riwayat siswa pernah didampingi Guru Wali tetap tersimpan.
 * Jurnal pendampingan (jurnal_guru_wali) tidak disentuh.
 *
 * Dieksekusi di dalam DB::transaction yang sudah dibuka oleh
 * MutateStudentAction, sehingga jika Listener ini throw exception,
 * seluruh transaksi akan di-rollback.
 */
class HandleGuruWaliStudentMutated
{
    public function handle(StudentMutated $event): void
    {
        KelompokGuruWaliSiswa::where('student_id', $event->siswa->id)
            ->where('status_aktif', true)
            ->update(['status_aktif' => false]);
    }
}

<?php

namespace App\Actions;

use App\Models\StudentPresensiProfile;
use App\Models\TeacherPresensiProfile;
use App\Models\EksemplarBuku;
use App\Models\Peminjaman;
use App\Models\PengaturanSekolah;
use Illuminate\Support\Facades\DB;

class ProcessSirkulasiAction
{
    public function execute(array $payload, $petugasId)
    {
        $jenisScan = $payload['jenis_scan'] ?? 'PEMINJAM';
        $barcode = $payload['barcode'] ?? '';

        if ($jenisScan === 'PEMINJAM') {
            return $this->processPeminjamScan($barcode);
        } else if ($jenisScan === 'BUKU') {
            $peminjamId = $payload['peminjam_id'] ?? null;
            $peminjamType = $payload['peminjam_type'] ?? null;
            return $this->processBukuScan($barcode, $peminjamId, $peminjamType, $petugasId);
        }

        return ['status' => 'error', 'message' => 'Jenis scan tidak valid'];
    }

    private function processPeminjamScan(string $barcode)
    {
        // 1. Cek Siswa
        $studentProfile = StudentPresensiProfile::where('barcode_code', $barcode)->first();
        if ($studentProfile) {
            if (!$studentProfile->barcode_active) {
                return ['status' => 'inactive', 'message' => 'Kartu siswa ini telah dinonaktifkan atau dilaporkan hilang.'];
            }
            $siswa = $studentProfile->student;
            return [
                'status' => 'success',
                'peminjam_id' => $siswa->id,
                'peminjam_type' => 'siswa',
                'name' => $siswa->name,
                'sub_info' => 'Siswa ' . ($siswa->kelasAjaranAktif() ? $siswa->kelasAjaranAktif()->kelas->name : '')
            ];
        }

        // 2. Cek Guru
        $teacherProfile = TeacherPresensiProfile::where('barcode_code', $barcode)->first();
        if ($teacherProfile) {
            if (!$teacherProfile->barcode_active) {
                return ['status' => 'inactive', 'message' => 'Kartu guru ini telah dinonaktifkan atau dilaporkan hilang.'];
            }
            $guru = $teacherProfile->teacher;
            return [
                'status' => 'success',
                'peminjam_id' => $guru->id,
                'peminjam_type' => 'guru',
                'name' => $guru->name,
                'sub_info' => 'Guru / Staff'
            ];
        }

        return ['status' => 'error', 'message' => 'Kartu anggota tidak ditemukan di sistem.'];
    }

    private function processBukuScan(string $barcodeBuku, $peminjamId, $peminjamType, $petugasId)
    {
        if (!$peminjamId || !$peminjamType) {
            return ['status' => 'error', 'message' => 'Data peminjam tidak valid. Silakan ulangi scan anggota.'];
        }

        // Cari eksemplar
        $eksemplar = EksemplarBuku::where('kode_eksemplar', $barcodeBuku)->with('buku')->first();
        if (!$eksemplar) {
            return ['status' => 'error', 'message' => "Buku dengan kode {$barcodeBuku} tidak ditemukan."];
        }

        return DB::transaction(function () use ($eksemplar, $peminjamId, $peminjamType, $petugasId) {
            if ($eksemplar->status === 'tersedia') {
                // PROSES PINJAM
                $settings = PengaturanSekolah::current();
                $lamaHari = $settings->lama_pinjam_buku_hari ?? 7;
                $jatuhTempo = now()->addDays($lamaHari)->startOfDay();

                Peminjaman::create([
                    'eksemplar_id' => $eksemplar->id,
                    'peminjam_type' => $peminjamType, // Harus eksplisit 'guru' atau 'siswa'
                    'peminjam_id' => $peminjamId,
                    'tanggal_pinjam' => now(),
                    'tanggal_jatuh_tempo' => $jatuhTempo,
                    'status' => 'dipinjam',
                    'petugas_id' => $petugasId
                ]);

                $eksemplar->update(['status' => 'dipinjam']);

                return [
                    'status' => 'success_pinjam',
                    'buku_title' => $eksemplar->buku->judul,
                    'jatuh_tempo' => $jatuhTempo->format('d M Y')
                ];
            } else if ($eksemplar->status === 'dipinjam') {
                // PROSES KEMBALI atau TOLAK
                // Cari record peminjaman aktif untuk eksemplar ini
                $peminjaman = Peminjaman::where('eksemplar_id', $eksemplar->id)
                    ->where('status', 'dipinjam')
                    ->first();

                if (!$peminjaman) {
                    // Anomali: status eksemplar dipinjam tapi gak ada record peminjaman aktif. Auto-fix atau tolak.
                    return ['status' => 'error', 'message' => 'Terjadi anomali data (buku dipinjam tapi tidak ada record). Hubungi admin.'];
                }

                // Cek apakah peminjamnya SAMA
                if ($peminjaman->peminjam_id === $peminjamId && $peminjaman->peminjam_type === $peminjamType) {
                    // Pengembalian Sukses
                    $peminjaman->update([
                        'status' => 'dikembalikan',
                        'tanggal_kembali' => now()
                    ]);

                    $eksemplar->update(['status' => 'tersedia']);

                    return [
                        'status' => 'success_kembali',
                        'buku_title' => $eksemplar->buku->judul
                    ];
                } else {
                    // Dipinjam orang lain
                    return ['status' => 'error', 'message' => "Buku ini sedang dipinjam oleh anggota lain. Tidak bisa dipinjam/dikembalikan oleh Anda."];
                }
            } else {
                return ['status' => 'error', 'message' => "Status buku tidak tersedia ({$eksemplar->status})."];
            }
        });
    }
}

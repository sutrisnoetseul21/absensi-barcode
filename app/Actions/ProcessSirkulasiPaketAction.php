<?php

namespace App\Actions;

use App\Models\StudentPresensiProfile;
use App\Models\TeacherPresensiProfile;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\EksemplarBuku;
use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProcessSirkulasiPaketAction
{
    public function execute(array $payload, $petugasId)
    {
        $jenisScan = $payload['jenis_scan'] ?? 'PEMINJAM';

        if ($jenisScan === 'PEMINJAM') {
            $barcode = trim($payload['barcode'] ?? '');
            return $this->processPeminjamScan($barcode);
        } else if ($jenisScan === 'CHECK_BUKU') {
            $barcode = trim($payload['barcode'] ?? '');
            $peminjamId = $payload['peminjam_id'] ?? null;
            $peminjamType = $payload['peminjam_type'] ?? null;
            return $this->processCheckBuku($barcode, $peminjamId, $peminjamType);
        } else if ($jenisScan === 'SUBMIT_BATCH') {
            $peminjamId = $payload['peminjam_id'] ?? null;
            $peminjamType = $payload['peminjam_type'] ?? null;
            $items = $payload['items'] ?? [];
            return $this->processBatchSirkulasi($items, $peminjamId, $peminjamType, $petugasId);
        }

        return ['status' => 'error', 'message' => 'Jenis scan tidak valid'];
    }

    private function processPeminjamScan(string $barcode)
    {
        if (empty($barcode)) {
            return ['status' => 'error', 'message' => 'Silakan scan atau masukkan barcode anggota.'];
        }

        // 1. Cek Siswa via profile barcode
        $studentProfile = StudentPresensiProfile::where('barcode_code', $barcode)->first();
        $siswa = null;
        if ($studentProfile) {
            if (!$studentProfile->barcode_active) {
                return ['status' => 'inactive', 'message' => 'Kartu siswa ini telah dinonaktifkan atau dilaporkan hilang.'];
            }
            $siswa = $studentProfile->student;
        } else {
            // Fallback: cari siswa by NISN atau NIS
            $siswa = Siswa::where('nisn', $barcode)->orWhere('nis', $barcode)->first();
        }

        if ($siswa) {
            $kelas = $siswa->enrollmentAktif?->kelas?->name ?? '';
            $subInfo = 'Siswa' . ($kelas ? ' Kelas ' . $kelas : '');
            $activeLoans = $this->getActivePackageLoans($siswa->id, 'siswa');

            return [
                'status' => 'success',
                'peminjam_id' => $siswa->id,
                'peminjam_type' => 'siswa',
                'name' => $siswa->name,
                'kelas' => $kelas,
                'sub_info' => $subInfo,
                'active_loans' => $activeLoans,
            ];
        }

        // 2. Cek Guru via profile barcode atau NIP
        $teacherProfile = TeacherPresensiProfile::where('barcode_code', $barcode)->first();
        $guru = null;
        if ($teacherProfile) {
            if (!$teacherProfile->barcode_active) {
                return ['status' => 'inactive', 'message' => 'Kartu guru ini telah dinonaktifkan atau dilaporkan hilang.'];
            }
            $guru = $teacherProfile->teacher;
        } else {
            $guru = Guru::where('nip', $barcode)->first();
        }

        if ($guru) {
            $activeLoans = $this->getActivePackageLoans($guru->id, 'guru');

            return [
                'status' => 'success',
                'peminjam_id' => $guru->id,
                'peminjam_type' => 'guru',
                'name' => $guru->name,
                'kelas' => '',
                'sub_info' => 'Guru / Staff',
                'active_loans' => $activeLoans,
            ];
        }

        return ['status' => 'error', 'message' => "Kartu anggota dengan barcode '{$barcode}' tidak ditemukan."];
    }

    private function getActivePackageLoans($peminjamId, $peminjamType)
    {
        return Peminjaman::where('peminjam_id', $peminjamId)
            ->where('peminjam_type', $peminjamType)
            ->where('tipe_peminjaman', 'paket')
            ->where('status', 'dipinjam')
            ->with(['eksemplarBuku.buku'])
            ->get()
            ->map(function ($p) {
                $isTerlambat = $p->tanggal_jatuh_tempo && now()->startOfDay()->gt($p->tanggal_jatuh_tempo->startOfDay());
                $eksemplar = $p->eksemplarBuku;
                return [
                    'peminjaman_id' => $p->id,
                    'eksemplar_id' => $p->eksemplar_id,
                    'kode_eksemplar' => $eksemplar?->kode_eksemplar ?? '-',
                    'buku_title' => $eksemplar?->buku?->judul ?? 'Buku Tidak Diketahui',
                    'grade_level' => $eksemplar?->buku?->grade_level ?? null,
                    'tanggal_pinjam' => $p->tanggal_pinjam ? $p->tanggal_pinjam->format('d M Y') : '-',
                    'tanggal_jatuh_tempo' => $p->tanggal_jatuh_tempo ? $p->tanggal_jatuh_tempo->format('d M Y') : '-',
                    'is_terlambat' => (bool) $isTerlambat,
                    'can_extend' => false,
                ];
            })->values()->toArray();
    }

    private function processCheckBuku(string $barcodeBuku, $peminjamId, $peminjamType)
    {
        if (!$peminjamId || !$peminjamType) {
            return ['status' => 'error', 'message' => 'Data peminjam tidak valid. Silakan scan kartu anggota terlebih dahulu.'];
        }

        if (empty($barcodeBuku)) {
            return ['status' => 'error', 'message' => 'Barcode buku tidak boleh kosong.'];
        }

        $eksemplar = EksemplarBuku::with(['buku.kategoriBuku', 'buku.mataPelajaran'])
            ->where('kode_eksemplar', $barcodeBuku)
            ->first();

        if (!$eksemplar) {
            return ['status' => 'error', 'message' => "Buku paket dengan barcode '{$barcodeBuku}' tidak terdaftar di sistem."];
        }

        $judulBuku = $eksemplar->buku ? $eksemplar->buku->judul : 'Buku Tanpa Judul';
        $gradeLevel = $eksemplar->buku?->grade_level;

        // Cek apakah buku ini sedang dipinjam oleh anggota yang sama
        $currentLoan = Peminjaman::where('eksemplar_id', $eksemplar->id)
            ->where('peminjam_id', $peminjamId)
            ->where('peminjam_type', $peminjamType)
            ->where('status', 'dipinjam')
            ->first();

        if ($currentLoan) {
            return [
                'status' => 'success',
                'action_type' => 'KEMBALI',
                'eksemplar_id' => $eksemplar->id,
                'kode_eksemplar' => $eksemplar->kode_eksemplar,
                'buku_title' => $judulBuku,
                'grade_level' => $gradeLevel,
                'message' => "Buku '{$judulBuku}' sedang dipinjam oleh anggota ini. Ditambahkan ke transaksi pengembalian.",
                'can_extend' => false,
            ];
        }

        // Cek ketersediaan
        if ($eksemplar->status !== 'tersedia') {
            $peminjamanLain = Peminjaman::where('eksemplar_id', $eksemplar->id)
                ->where('status', 'dipinjam')
                ->with('peminjam')
                ->first();

            $peminjamName = $peminjamanLain?->peminjam?->name ?? 'orang lain';
            return [
                'status' => 'error',
                'message' => "Buku '{$judulBuku}' saat ini berstatus " . strtoupper($eksemplar->status) . " (sedang dipinjam oleh {$peminjamName}).",
            ];
        }

        // Cek apakah referensi / tidak boleh dipinjam
        if (!($eksemplar->buku?->kategoriBuku?->is_bisa_dipinjam ?? true)) {
            return [
                'status' => 'referensi',
                'message' => "Koleksi '{$judulBuku}' adalah bahan referensi yang hanya boleh dibaca di tempat.",
            ];
        }

        return [
            'status' => 'success',
            'action_type' => 'PINJAM',
            'eksemplar_id' => $eksemplar->id,
            'kode_eksemplar' => $eksemplar->kode_eksemplar,
            'buku_title' => $judulBuku,
            'grade_level' => $gradeLevel,
            'durasi' => '1 Tahun',
            'tanggal_jatuh_tempo' => now()->addYear()->format('d M Y'),
            'message' => "Buku paket '{$judulBuku}' siap dipinjamkan (1 Tahun).",
        ];
    }

    private function processBatchSirkulasi(array $items, $peminjamId, $peminjamType, $petugasId)
    {
        if (empty($items)) {
            return ['status' => 'error', 'message' => 'Tidak ada buku di dalam keranjang transaksi.'];
        }

        if (!$peminjamId || !$peminjamType) {
            return ['status' => 'error', 'message' => 'Data peminjam tidak valid.'];
        }

        return DB::transaction(function () use ($items, $peminjamId, $peminjamType, $petugasId) {
            $totalPinjam = 0;
            $totalKembali = 0;

            $validPetugasId = null;
            if ($petugasId) {
                $user = \App\Models\User::find($petugasId);
                $validPetugasId = $user ? $user->id : null;
            }

            foreach ($items as $item) {
                $eksemplarId = $item['eksemplar_id'] ?? null;
                $actionType = $item['action_type'] ?? 'PINJAM';

                if (!$eksemplarId) continue;

                $eksemplar = EksemplarBuku::find($eksemplarId);
                if (!$eksemplar) continue;

                if ($actionType === 'PINJAM') {
                    // Durasi peminjaman buku paket: Tepat 1 Tahun (365 hari)
                    $jatuhTempo = now()->addYear()->startOfDay();

                    Peminjaman::create([
                        'eksemplar_id' => $eksemplar->id,
                        'peminjam_type' => $peminjamType,
                        'peminjam_id' => $peminjamId,
                        'tipe_peminjaman' => 'paket',
                        'tanggal_pinjam' => now(),
                        'tanggal_jatuh_tempo' => $jatuhTempo,
                        'catatan' => 'Peminjaman Buku Paket 1 Tahun Ajaran',
                        'status' => 'dipinjam',
                        'petugas_id' => $validPetugasId,
                    ]);

                    $eksemplar->update(['status' => 'dipinjam']);
                    $totalPinjam++;
                } else if ($actionType === 'KEMBALI') {
                    $peminjaman = Peminjaman::where('eksemplar_id', $eksemplar->id)
                        ->where('status', 'dipinjam')
                        ->first();

                    if ($peminjaman) {
                        $peminjaman->update([
                            'status' => 'dikembalikan',
                            'tanggal_kembali' => now(),
                        ]);
                        $eksemplar->update(['status' => 'tersedia']);
                        $totalKembali++;
                    }
                }
            }

            $messages = [];
            if ($totalPinjam > 0) {
                $messages[] = "{$totalPinjam} buku paket berhasil dipinjamkan (durasi 1 tahun)";
            }
            if ($totalKembali > 0) {
                $messages[] = "{$totalKembali} buku berhasil dikembalikan";
            }

            return [
                'status' => 'success',
                'message' => !empty($messages) ? implode(' dan ', $messages) . '.' : 'Transaksi selesai diproses.',
                'total_pinjam' => $totalPinjam,
                'total_kembali' => $totalKembali,
                'active_loans' => $this->getActivePackageLoans($peminjamId, $peminjamType),
            ];
        });
    }
}

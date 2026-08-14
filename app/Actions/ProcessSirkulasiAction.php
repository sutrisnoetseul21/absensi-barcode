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

        if ($jenisScan === 'PEMINJAM') {
            $barcode = $payload['barcode'] ?? '';
            return $this->processPeminjamScan($barcode);
        } else if ($jenisScan === 'CHECK_BUKU') {
            $barcode = $payload['barcode'] ?? '';
            $peminjamId = $payload['peminjam_id'] ?? null;
            $peminjamType = $payload['peminjam_type'] ?? null;
            return $this->processCheckBuku($barcode, $peminjamId, $peminjamType);
        } else if ($jenisScan === 'SUBMIT_BATCH') {
            $peminjamId = $payload['peminjam_id'] ?? null;
            $peminjamType = $payload['peminjam_type'] ?? null;
            $items = $payload['items'] ?? [];
            return $this->processBatchSirkulasi($items, $peminjamId, $peminjamType, $petugasId);
        } else if ($jenisScan === 'BUKU') {
            // Legacy single scan fallback
            $barcode = $payload['barcode'] ?? '';
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
            if (!$siswa) {
                return ['status' => 'error', 'message' => 'Data siswa terkait tidak ditemukan di database.'];
            }

            $activeLoans = $this->getActiveLoans($siswa->id, 'siswa');

            return [
                'status' => 'success',
                'peminjam_id' => $siswa->id,
                'peminjam_type' => 'siswa',
                'name' => $siswa->name,
                'sub_info' => 'Siswa ' . ($siswa->enrollmentAktif && $siswa->enrollmentAktif->kelas ? $siswa->enrollmentAktif->kelas->name : ''),
                'active_loans' => $activeLoans,
            ];
        }

        // 2. Cek Guru
        $teacherProfile = TeacherPresensiProfile::where('barcode_code', $barcode)->first();
        if ($teacherProfile) {
            if (!$teacherProfile->barcode_active) {
                return ['status' => 'inactive', 'message' => 'Kartu guru ini telah dinonaktifkan atau dilaporkan hilang.'];
            }
            $guru = $teacherProfile->teacher;
            if (!$guru) {
                return ['status' => 'error', 'message' => 'Data guru terkait tidak ditemukan di database.'];
            }

            $activeLoans = $this->getActiveLoans($guru->id, 'guru');

            return [
                'status' => 'success',
                'peminjam_id' => $guru->id,
                'peminjam_type' => 'guru',
                'name' => $guru->name,
                'sub_info' => 'Guru / Staff',
                'active_loans' => $activeLoans,
            ];
        }

        return ['status' => 'error', 'message' => 'Kartu anggota tidak ditemukan di sistem.'];
    }

    private function getActiveLoans($peminjamId, $peminjamType)
    {
        return Peminjaman::where('peminjam_id', $peminjamId)
            ->where('peminjam_type', $peminjamType)
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
                    'tanggal_pinjam' => $p->tanggal_pinjam ? $p->tanggal_pinjam->format('d M Y') : '-',
                    'tanggal_jatuh_tempo' => $p->tanggal_jatuh_tempo ? $p->tanggal_jatuh_tempo->format('d M Y') : '-',
                    'is_terlambat' => (bool)$isTerlambat,
                ];
            })->values()->toArray();
    }

    private function processCheckBuku(string $barcodeBuku, $peminjamId, $peminjamType)
    {
        if (!$peminjamId || !$peminjamType) {
            return ['status' => 'error', 'message' => 'Data peminjam tidak valid. Silakan scan kartu anggota terlebih dahulu.'];
        }

        $eksemplar = EksemplarBuku::where('kode_eksemplar', $barcodeBuku)->with('buku.kategoriBuku')->first();
        if (!$eksemplar) {
            return ['status' => 'error', 'message' => "Buku dengan kode barcode {$barcodeBuku} tidak ditemukan."];
        }

        // Cek apakah kategori buku boleh dipinjam
        $isBisaDipinjam = $eksemplar->buku?->kategoriBuku?->is_bisa_dipinjam ?? true;
        if (!$isBisaDipinjam) {
            $judulBuku = $eksemplar->buku->judul ?? 'Buku ini';
            $kategoriNama = $eksemplar->buku?->kategoriBuku?->nama_kategori ?? 'Koleksi Khusus';
            return [
                'status' => 'referensi',
                'message' => "⚠️ <strong>{$judulBuku}</strong> ({$eksemplar->kode_eksemplar}) adalah <strong>{$kategoriNama}</strong> yang tidak boleh dipinjam dan hanya dapat dibaca di tempat.",
            ];
        }

        if ($eksemplar->status === 'tersedia') {
            return [
                'status' => 'success',
                'eksemplar_id' => $eksemplar->id,
                'kode_eksemplar' => $eksemplar->kode_eksemplar,
                'buku_title' => $eksemplar->buku->judul ?? 'Judul Tidak Tersedia',
                'action_type' => 'PINJAM', // Default aksi: Peminjaman Baru
                'action_label' => 'Pinjam Baru',
            ];
        } else if ($eksemplar->status === 'dipinjam') {
            $peminjaman = Peminjaman::where('eksemplar_id', $eksemplar->id)
                ->where('status', 'dipinjam')
                ->first();

            if ($peminjaman && $peminjaman->peminjam_id === $peminjamId && $peminjaman->peminjam_type === $peminjamType) {
                return [
                    'status' => 'success',
                    'eksemplar_id' => $eksemplar->id,
                    'peminjaman_id' => $peminjaman->id,
                    'kode_eksemplar' => $eksemplar->kode_eksemplar,
                    'buku_title' => $eksemplar->buku->judul ?? 'Judul Tidak Tersedia',
                    'action_type' => 'KEMBALI', // Default aksi: Pengembalian
                    'action_label' => 'Pengembalian',
                    'can_extend' => true,
                ];
            } else if ($peminjaman) {
                $borrowerName = 'Anggota Lain';
                if ($peminjaman->peminjam_type === 'siswa') {
                    $student = \App\Models\Siswa::find($peminjaman->peminjam_id);
                    if ($student) {
                        $kelas = $student->enrollmentAktif?->kelas?->name ? " (Kelas {$student->enrollmentAktif->kelas->name})" : "";
                        $borrowerName = "Siswa {$student->name}{$kelas}";
                    }
                } else if ($peminjaman->peminjam_type === 'guru' || $peminjaman->peminjam_type === 'wali_kelas') {
                    $teacher = \App\Models\Guru::find($peminjaman->peminjam_id);
                    if ($teacher) {
                        $borrowerName = "Guru/Staff {$teacher->name}";
                    }
                }

                $jatuhTempo = $peminjaman->tanggal_jatuh_tempo ? $peminjaman->tanggal_jatuh_tempo->format('d M Y') : '-';
                $bukuJudul = $eksemplar->buku?->judul ?? 'Buku';

                return [
                    'status' => 'error',
                    'message' => "Buku <strong>{$bukuJudul}</strong> ({$eksemplar->kode_eksemplar}) sedang dipinjam oleh <strong>{$borrowerName}</strong> (Batas kembali: {$jatuhTempo})."
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => "Buku ini ({$eksemplar->kode_eksemplar}) sedang berstatus dipinjam namun data peminjam tidak ditemukan."
                ];
            }
        } else {
            return [
                'status' => 'error',
                'message' => "Buku ini sedang berstatus {$eksemplar->status} (tidak dapat dipinjam)."
            ];
        }
    }

    private function processBatchSirkulasi(array $items, $peminjamId, $peminjamType, $petugasId)
    {
        if (!$peminjamId || !$peminjamType) {
            return ['status' => 'error', 'message' => 'Data peminjam tidak valid.'];
        }

        if (empty($items)) {
            return ['status' => 'error', 'message' => 'Belum ada buku dalam daftar transaksi.'];
        }

        return DB::transaction(function () use ($items, $peminjamId, $peminjamType, $petugasId) {
            $settings = PengaturanSekolah::current();
            $lamaHari = $settings->lama_pinjam_buku_hari ?? 7;

            $totalPinjam = 0;
            $totalKembali = 0;
            $totalPerpanjang = 0;

            foreach ($items as $item) {
                $eksemplarId = $item['eksemplar_id'] ?? null;
                $actionType = $item['action_type'] ?? 'PINJAM';

                if (!$eksemplarId) continue;

                $eksemplar = EksemplarBuku::find($eksemplarId);
                if (!$eksemplar) continue;

                if ($actionType === 'PINJAM') {
                    $jatuhTempo = now()->addDays($lamaHari)->startOfDay();
                    Peminjaman::create([
                        'eksemplar_id' => $eksemplar->id,
                        'peminjam_type' => $peminjamType,
                        'peminjam_id' => $peminjamId,
                        'tanggal_pinjam' => now(),
                        'tanggal_jatuh_tempo' => $jatuhTempo,
                        'status' => 'dipinjam',
                        'petugas_id' => $petugasId
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
                            'tanggal_kembali' => now()
                        ]);
                        $eksemplar->update(['status' => 'tersedia']);
                        $totalKembali++;
                    }
                } else if ($actionType === 'PERPANJANG') {
                    $peminjaman = Peminjaman::where('eksemplar_id', $eksemplar->id)
                        ->where('status', 'dipinjam')
                        ->first();

                    if ($peminjaman) {
                        $baseDate = ($peminjaman->tanggal_jatuh_tempo && $peminjaman->tanggal_jatuh_tempo->gt(now()))
                            ? $peminjaman->tanggal_jatuh_tempo
                            : now();

                        $peminjaman->update([
                            'tanggal_jatuh_tempo' => $baseDate->addDays($lamaHari)->startOfDay()
                        ]);
                        $totalPerpanjang++;
                    }
                }
            }

            $summaryParts = [];
            if ($totalPinjam > 0) $summaryParts[] = "{$totalPinjam} Buku Dipinjam";
            if ($totalKembali > 0) $summaryParts[] = "{$totalKembali} Buku Dikembalikan";
            if ($totalPerpanjang > 0) $summaryParts[] = "{$totalPerpanjang} Buku Diperpanjang";

            $summaryMessage = implode(', ', $summaryParts);
            if (empty($summaryMessage)) $summaryMessage = "Transaksi berhasil diproses.";

            return [
                'status' => 'success_batch',
                'message' => $summaryMessage,
                'stats' => [
                    'pinjam' => $totalPinjam,
                    'kembali' => $totalKembali,
                    'perpanjang' => $totalPerpanjang,
                ]
            ];
        });
    }

    private function processBukuScan(string $barcodeBuku, $peminjamId, $peminjamType, $petugasId)
    {
        $check = $this->processCheckBuku($barcodeBuku, $peminjamId, $peminjamType);
        if ($check['status'] !== 'success') {
            return $check;
        }

        $actionType = $check['action_type'] ?? 'PINJAM';
        return $this->processBatchSirkulasi([[
            'eksemplar_id' => $check['eksemplar_id'],
            'action_type' => $actionType
        ]], $peminjamId, $peminjamType, $petugasId);
    }
}

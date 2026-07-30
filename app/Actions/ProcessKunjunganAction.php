<?php

namespace App\Actions;

use App\Models\Siswa;
use App\Models\Guru;
use App\Models\StudentPresensiProfile;
use App\Models\TeacherPresensiProfile;
use App\Models\KunjunganPerpustakaan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class ProcessKunjunganAction
{
    public function execute(string $barcode, ?string $petugasId = null, string $tujuan = 'Membaca / Belajar'): array
    {
        $barcode = trim($barcode);
        if (empty($barcode)) {
            return ['status' => 'not_found', 'message' => 'Kode barcode kosong.'];
        }

        $now = Carbon::now('Asia/Jakarta');
        $date = $now->toDateString();
        $time = $now->toTimeString();

        // 1. Debounce server level (lock 3 detik)
        if (!Cache::add('kunjungan_lock:' . $barcode, true, 3)) {
            return ['status' => 'duplicate_request'];
        }

        // 2. Cari Siswa (Cek Barcode Profile, NISN, NIS, atau ID)
        $siswa = Siswa::with(['enrollmentAktif.kelas', 'presensiProfile'])
            ->where(function ($query) use ($barcode) {
                $query->where('id', $barcode)
                    ->orWhere('nisn', $barcode)
                    ->orWhere('nis', $barcode)
                    ->orWhereHas('presensiProfile', function ($subQuery) use ($barcode) {
                        $subQuery->where('barcode_code', $barcode);
                    });
            })->first();

        if ($siswa) {
            if ($siswa->presensiProfile && !$siswa->presensiProfile->barcode_active) {
                return ['status' => 'barcode_inactive', 'message' => 'Kartu presensi siswa ini dinonaktifkan.'];
            }

            // Cek apakah baru saja absen kunjungan dalam 3 menit terakhir
            $recentVisit = KunjunganPerpustakaan::where('pengunjung_type', 'siswa')
                ->where('pengunjung_id', $siswa->id)
                ->where('tanggal', $date)
                ->where('created_at', '>=', $now->copy()->subMinutes(3))
                ->first();

            if ($recentVisit) {
                return [
                    'status' => 'already_scanned',
                    'name' => $siswa->name,
                    'class_name' => $siswa->enrollmentAktif ? 'Kelas ' . $siswa->enrollmentAktif->kelas->name : 'Siswa',
                    'photo_url' => $siswa->photo_path ? asset('storage/' . $siswa->photo_path) : null,
                    'time' => Carbon::parse($recentVisit->waktu_masuk)->format('H:i:s'),
                    'message' => 'Sudah mencatat kunjungan perpustakaan baru saja.'
                ];
            }

            // Simpan Kunjungan
            KunjunganPerpustakaan::create([
                'pengunjung_type' => 'siswa',
                'pengunjung_id' => $siswa->id,
                'tanggal' => $date,
                'waktu_masuk' => $time,
                'tujuan_kunjungan' => $tujuan,
                'petugas_id' => $petugasId,
            ]);

            return [
                'status' => 'success',
                'name' => $siswa->name,
                'class_name' => $siswa->enrollmentAktif ? 'Kelas ' . $siswa->enrollmentAktif->kelas->name : 'Siswa',
                'photo_url' => $siswa->photo_path ? asset('storage/' . $siswa->photo_path) : null,
                'time' => $now->format('H:i:s'),
                'message' => 'Selamat Datang di Perpustakaan! Selamat membaca.'
            ];
        }

        // 3. Cari Guru (Cek Barcode Profile, NIP, atau ID)
        $guru = Guru::with(['presensiProfile'])
            ->where(function ($query) use ($barcode) {
                $query->where('id', $barcode)
                    ->orWhere('nip', $barcode)
                    ->orWhereHas('presensiProfile', function ($subQuery) use ($barcode) {
                        $subQuery->where('barcode_code', $barcode);
                    });
            })->first();

        if ($guru) {
            if ($guru->presensiProfile && !$guru->presensiProfile->barcode_active) {
                return ['status' => 'barcode_inactive', 'message' => 'Kartu presensi guru ini dinonaktifkan.'];
            }

            $recentVisit = KunjunganPerpustakaan::where('pengunjung_type', 'guru')
                ->where('pengunjung_id', $guru->id)
                ->where('tanggal', $date)
                ->where('created_at', '>=', $now->copy()->subMinutes(3))
                ->first();

            if ($recentVisit) {
                return [
                    'status' => 'already_scanned',
                    'name' => $guru->name,
                    'class_name' => 'Guru / Staff',
                    'photo_url' => null,
                    'time' => Carbon::parse($recentVisit->waktu_masuk)->format('H:i:s'),
                    'message' => 'Sudah mencatat kunjungan perpustakaan baru saja.'
                ];
            }

            KunjunganPerpustakaan::create([
                'pengunjung_type' => 'guru',
                'pengunjung_id' => $guru->id,
                'tanggal' => $date,
                'waktu_masuk' => $time,
                'tujuan_kunjungan' => $tujuan,
                'petugas_id' => $petugasId,
            ]);

            return [
                'status' => 'success',
                'name' => $guru->name,
                'class_name' => 'Guru / Staff',
                'photo_url' => null,
                'time' => $now->format('H:i:s'),
                'message' => 'Selamat Datang di Perpustakaan, Bapak/Ibu Guru!'
            ];
        }

        return ['status' => 'not_found', 'message' => 'Kartu anggota / barcode tidak dikenali di sistem.'];
    }
}

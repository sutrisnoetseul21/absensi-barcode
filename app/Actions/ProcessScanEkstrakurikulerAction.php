<?php

namespace App\Actions;

use App\Models\Ekstrakurikuler;
use App\Models\EkstrakurikulerPertemuan;
use App\Models\EkstrakurikulerPresensi;
use App\Models\EkstrakurikulerSiswa;
use App\Models\LogScan;
use App\Models\Siswa;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ProcessScanEkstrakurikulerAction
{
    public function execute(
        Ekstrakurikuler $ekskul,
        string $barcode,
        ?string $ipAddress = null,
        ?string $judulKegiatan = null
    ): array {
        $now = Carbon::now('Asia/Jakarta');
        $date = $now->toDateString();
        $scanTime = $now->format('H:i:s');

        if (!$ekskul->status_aktif) {
            return [
                'status'  => 'inactive',
                'message' => "Ekstrakurikuler {$ekskul->nama_ekskul} sedang nonaktif.",
            ];
        }

        // 1. Debounce atomik per ekskul + barcode (mencegah double scan dalam 3 detik)
        if (!Cache::add("scan_lock_ekskul:{$ekskul->id}:{$barcode}", true, 3)) {
            return [
                'status'  => 'duplicate_request',
                'message' => 'Sedang memproses barcode, mohon tunggu...',
            ];
        }

        // 2. Cari siswa berdasarkan ID, NISN, NIS, atau barcode_code
        $siswa = Siswa::with([
            'enrollments' => fn ($q) => $q->where('academic_year_id', $ekskul->academic_year_id)
                ->where('status', 'aktif')
                ->with('kelas'),
            'presensiProfile',
        ])->where(function ($query) use ($barcode) {
            $query->where('id', $barcode)
                ->orWhere('nisn', $barcode)
                ->orWhere('nis', $barcode)
                ->orWhereHas('presensiProfile', fn ($sub) => $sub->where('barcode_code', $barcode));
        })->first();

        if (!$siswa) {
            $this->logAttempt($barcode, null, 'not_found', $now, $ipAddress);
            return [
                'status'  => 'not_found',
                'message' => 'Kartu atau nomor NIS/NISN tidak dikenali di sistem.',
            ];
        }

        $className = $siswa->enrollments->first()?->kelas?->name ?? 'Belum ada kelas';

        // 3. Pastikan siswa merupakan anggota aktif dari ekstrakurikuler ini
        $isMember = EkstrakurikulerSiswa::where('ekstrakurikuler_id', $ekskul->id)
            ->where('student_id', $siswa->id)
            ->where('status_aktif', true)
            ->exists();

        if (!$isMember) {
            $this->logAttempt($barcode, $siswa->id, 'not_member', $now, $ipAddress);
            return [
                'status'  => 'not_member',
                'message' => "{$siswa->name} bukan anggota aktif {$ekskul->nama_ekskul}.",
                'student' => $this->formatStudentPayload($siswa, $className),
                'time'    => $now->format('H:i:s'),
                'date'    => $now->locale('id')->translatedFormat('l, d F Y'),
            ];
        }

        // 4. Ambil atau buat otomatis pertemuan hari ini untuk ekstrakurikuler ini
        $user = Auth::user();
        $pertemuan = EkstrakurikulerPertemuan::findOrCreateForDate($ekskul, $date, $user, $judulKegiatan);

        // 5. Cek apakah siswa sudah scan Hadir pada pertemuan ini
        $existing = EkstrakurikulerPresensi::where('pertemuan_id', $pertemuan->id)
            ->where('student_id', $siswa->id)
            ->first();

        if ($existing && $existing->status === 'hadir') {
            $this->logAttempt($barcode, $siswa->id, 'already_scanned', $now, $ipAddress);
            $recordedTime = $existing->scan_time
                ? substr((string) $existing->scan_time, 0, 8)
                : Carbon::parse($existing->updated_at)->format('H:i:s');

            return [
                'status'       => 'already_scanned',
                'message'      => "Sudah tercatat hadir pada Pertemuan ke-{$pertemuan->pertemuan_ke} hari ini.",
                'student'      => $this->formatStudentPayload($siswa, $className),
                'pertemuan_ke' => $pertemuan->pertemuan_ke,
                'time'         => $recordedTime,
                'date'         => $now->locale('id')->translatedFormat('l, d F Y'),
            ];
        }

        // 6. Catat / update kehadiran menjadi Hadir
        EkstrakurikulerPresensi::updateOrCreate(
            [
                'pertemuan_id' => $pertemuan->id,
                'student_id'   => $siswa->id,
            ],
            [
                'ekstrakurikuler_id' => $ekskul->id,
                'status'             => 'hadir',
                'scan_time'          => $scanTime,
                'is_manual_input'    => false,
                'scanned_by'         => $user?->id,
                'keterangan'         => 'Scan Kiosk',
            ]
        );

        $this->logAttempt($barcode, $siswa->id, 'success', $now, $ipAddress);

        return [
            'status'       => 'success',
            'message'      => "Hadir {$ekskul->nama_ekskul} (Pertemuan ke-{$pertemuan->pertemuan_ke}) tercatat!",
            'student'      => $this->formatStudentPayload($siswa, $className),
            'pertemuan_ke' => $pertemuan->pertemuan_ke,
            'time'         => $now->format('H:i:s'),
            'date'         => $now->locale('id')->translatedFormat('l, d F Y'),
        ];
    }

    private function formatStudentPayload(Siswa $siswa, string $className): array
    {
        return [
            'id'         => $siswa->id,
            'name'       => $siswa->name,
            'nisn'       => $siswa->nisn,
            'nis'        => $siswa->nis,
            'class'      => $className,
            'avatar_url' => $siswa->photo_path ? asset('storage/' . $siswa->photo_path) : null,
        ];
    }

    private function logAttempt(string $barcode, ?string $studentId, string $status, Carbon $timestamp, ?string $ipAddress): void
    {
        try {
            LogScan::create([
                'barcode'    => $barcode,
                'student_id' => $studentId,
                'status'     => 'ekskul_' . $status,
                'ip_address' => $ipAddress,
                'scanned_at' => $timestamp,
            ]);
        } catch (\Throwable $e) {
            // Log silently
        }
    }
}


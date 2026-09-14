<?php

namespace App\Actions;

use App\Models\LogScan;
use App\Models\PresensiSholatDhuhur;
use App\Models\Siswa;
use App\Services\PresensiSholatDhuhurService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ProcessScanSholatDhuhurAction
{
    public function execute(string $barcode, ?string $ipAddress = null): array
    {
        $now = Carbon::now('Asia/Jakarta');
        $date = $now->toDateString();
        $scanTime = $now->toTimeString();

        // 1. Debounce atomik (Mencegah double-request di level server)
        if (! Cache::add('scan_lock_sholat:' . $barcode, true, 3)) {
            return ['status' => 'duplicate_request', 'message' => 'Sedang memproses barcode, mohon tunggu...'];
        }

        // 2. Pencarian Siswa (ID, NISN, NIS, atau barcode_code)
        $siswa = Siswa::with(['enrollmentAktif.kelas', 'enrollmentAktif.tahunAjaran', 'presensiProfile'])
            ->where(function ($query) use ($barcode) {
                $query->where('id', $barcode)
                      ->orWhere('nisn', $barcode)
                      ->orWhere('nis', $barcode)
                      ->orWhereHas('presensiProfile', function ($subQuery) use ($barcode) {
                          $subQuery->where('barcode_code', $barcode);
                      });
            })->first();

        if (! $siswa) {
            $this->logAttempt($barcode, null, 'not_found', $now, $ipAddress);
            return [
                'status'  => 'not_found',
                'message' => 'Kartu atau nomor NIS/NISN tidak dikenali di sistem.',
            ];
        }

        $enrollment = $siswa->enrollmentAktif;
        if (! $enrollment) {
            $this->logAttempt($barcode, $siswa->id, 'not_found', $now, $ipAddress);
            return [
                'status'  => 'not_found',
                'message' => 'Siswa ' . $siswa->name . ' tidak memiliki pendaftaran kelas aktif.',
            ];
        }

        $classId = $enrollment->class_id;
        $academicYearId = $enrollment->academic_year_id;
        $className = $enrollment->kelas?->name ?? 'Kelas';

        $sholatService = app(PresensiSholatDhuhurService::class);

        // 3. Pengecekan Hari Libur & Jumat
        $isSholatDay = $sholatService->isHariSholatDhuhur($now, $classId);
        if (! $isSholatDay) {
            $holidayDesc = $sholatService->getHolidayDescription($now, $classId);
            $msg = $now->isFriday()
                ? 'Hari ini hari Jumat (jadwal ibadah Sholat Jumat, libur dhuhur di sekolah).'
                : 'Hari ini libur sholat dhuhur: ' . ($holidayDesc ?: 'Hari Libur');

            $this->logAttempt($barcode, $siswa->id, 'holiday', $now, $ipAddress);

            return [
                'status'  => 'holiday',
                'message' => $msg,
                'student' => [
                    'id'         => $siswa->id,
                    'name'       => $siswa->name,
                    'nisn'       => $siswa->nisn,
                    'nis'        => $siswa->nis,
                    'class'      => $className,
                    'gender'     => $siswa->gender,
                    'avatar_url' => $siswa->photo_path ? asset('storage/' . $siswa->photo_path) : null,
                ],
                'time' => $now->format('H:i:s'),
                'date' => $now->translatedFormat('l, d F Y'),
            ];
        }

        // 4. Pengecekan apakah sudah scan sholat hari ini
        $existing = PresensiSholatDhuhur::where('student_id', $siswa->id)
            ->whereDate('date', $date)
            ->first();

        if ($existing && $existing->status === 'hadir') {
            $this->logAttempt($barcode, $siswa->id, 'already_scanned', $now, $ipAddress);

            return [
                'status'  => 'already_scanned',
                'message' => 'Siswa sudah tercatat Hadir Sholat Dhuhur hari ini.',
                'student' => [
                    'id'         => $siswa->id,
                    'name'       => $siswa->name,
                    'nisn'       => $siswa->nisn,
                    'nis'        => $siswa->nis,
                    'class'      => $className,
                    'gender'     => $siswa->gender,
                    'avatar_url' => $siswa->photo_path ? asset('storage/' . $siswa->photo_path) : null,
                ],
                'time' => Carbon::parse($existing->updated_at)->format('H:i:s'),
                'date' => $now->translatedFormat('l, d F Y'),
            ];
        }

        // 5. Simpan / Perbarui Presensi Sholat Dhuhur
        $teacherId = Auth::user()?->teacher?->id;

        PresensiSholatDhuhur::updateOrCreate(
            [
                'student_id' => $siswa->id,
                'date'       => $date,
            ],
            [
                'academic_year_id'    => $academicYearId,
                'class_id'            => $classId,
                'enrollment_id'       => $enrollment->id,
                'status'              => 'hadir',
                'keterangan'          => 'Scan Kiosk',
                'input_by_teacher_id' => $teacherId,
            ]
        );

        $this->logAttempt($barcode, $siswa->id, 'success', $now, $ipAddress);

        return [
            'status'  => 'success',
            'message' => 'Presensi Sholat Dhuhur Berjamaah Berhasil Dicatat!',
            'student' => [
                'id'         => $siswa->id,
                'name'       => $siswa->name,
                'nisn'       => $siswa->nisn,
                'nis'        => $siswa->nis,
                'class'      => $className,
                'gender'     => $siswa->gender,
                'avatar_url' => $siswa->photo_path ? asset('storage/' . $siswa->photo_path) : null,
            ],
            'time' => $now->format('H:i:s'),
            'date' => $now->translatedFormat('l, d F Y'),
        ];
    }

    private function logAttempt(string $barcode, ?string $studentId, string $status, Carbon $timestamp, ?string $ipAddress): void
    {
        try {
            LogScan::create([
                'barcode'    => $barcode,
                'student_id' => $studentId,
                'status'     => 'sholat_' . $status,
                'ip_address' => $ipAddress,
                'scanned_at' => $timestamp,
            ]);
        } catch (\Throwable $e) {
            // Log silently
        }
    }
}

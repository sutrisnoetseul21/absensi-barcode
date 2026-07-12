<?php

namespace App\Actions;

use App\Models\Presensi;
use App\Models\HariLibur;
use App\Models\LogScan;
use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;

class ProcessScanAction
{
    public function execute(string $barcode, ?string $ipAddress = null, string $type = 'nisn'): array
    {
        $now = Carbon::now('Asia/Jakarta');
        $date = $now->toDateString();
        $scanTime = $now->toTimeString();

        // 1. Debounce atomik (Mencegah duplicate request di level server)
        if (!Cache::add('scan_lock:'.$barcode, true, 3)) {
            return ['status' => 'duplicate_request'];
        }

        // 2. Pencarian Siswa
        if ($type === 'nis') {
            $siswa = Siswa::with('enrollmentAktif')->where('nis', $barcode)->first();
        } else {
            // Default: NISN (barcode_code = nisn)
            $siswa = Siswa::with('enrollmentAktif')->where('barcode_code', $barcode)->first();
        }

        if (!$siswa) {
            $this->logAttempt($barcode, null, 'not_found', $now, $ipAddress);
            return ['status' => 'not_found'];
        }

        if (!$siswa->barcode_active) {
            $this->logAttempt($barcode, $siswa->id, 'barcode_inactive', $now, $ipAddress);
            return ['status' => 'barcode_inactive'];
        }

        $enrollment = $siswa->enrollmentAktif;
        if (!$enrollment) {
            $this->logAttempt($barcode, $siswa->id, 'not_found', $now, $ipAddress);
            return ['status' => 'not_found', 'message' => 'Tidak ada pendaftaran kelas aktif.'];
        }

        $classId = $enrollment->class_id;
        $academicYearId = $enrollment->academic_year_id;

        // 3. Pengecekan Hari Libur
        $isHariSekolah = app(\App\Services\KalenderSekolahService::class)->isHariSekolah($now, $classId);
        if (!$isHariSekolah) {
            $this->logAttempt($barcode, $siswa->id, 'holiday', $now, $ipAddress);
            return [
                'status' => 'holiday',
                'name' => $siswa->name,
                'photo_url' => $siswa->photo_path ? asset('storage/'.$siswa->photo_path) : null,
                'message' => 'Hari ini libur, tidak ada presensi'
            ];
        }

        // 4. Cek Normal Absensi (Sudah Absen Hari Ini?)
        $sudahAbsen = Presensi::where('student_id', $siswa->id)
            ->where('date', $date)
            ->exists();

        if ($sudahAbsen) {
            $this->logAttempt($barcode, $siswa->id, 'already_scanned', $now, $ipAddress);
            return [
                'status' => 'already_scanned',
                'name' => $siswa->name,
                'photo_url' => $siswa->photo_path ? asset('storage/'.$siswa->photo_path) : null
            ];
        }

        // 5. Kalkulasi Keterlambatan
        $settings = PengaturanSekolah::current();
        $checkinTime = $settings ? $settings->checkin_time : '07:00:00';
        $lateThreshold = $settings ? $settings->late_threshold_minutes : 0;

        $checkinCarbon = Carbon::parse($date . ' ' . $checkinTime, 'Asia/Jakarta');
        $lateMinutes = 0;
        $statusAbsen = 'hadir';

        if ($now->greaterThan($checkinCarbon)) {
            $diffInMinutes = $checkinCarbon->diffInMinutes($now);
            $lateMinutes = $diffInMinutes;
            if ($lateMinutes > $lateThreshold) {
                $statusAbsen = 'telat';
            }
        }

        // 6. Insert ke attendances (Dengan Safety Net DB Unique Constraint)
        try {
            Presensi::create([
                'student_id' => $siswa->id,
                'enrollment_id' => $enrollment->id,
                'class_id' => $classId,
                'academic_year_id' => $academicYearId,
                'date' => $date,
                'scan_time' => $scanTime,
                'status' => $statusAbsen,
                'late_minutes' => $lateMinutes,
                'is_manual_input' => false,
            ]);
        } catch (UniqueConstraintViolationException $e) {
            $this->logAttempt($barcode, $siswa->id, 'already_scanned', $now, $ipAddress);
            return [
                'status' => 'already_scanned',
                'name' => $siswa->name,
                'photo_url' => $siswa->photo_path ? asset('storage/'.$siswa->photo_path) : null
            ];
        } catch (\Exception $e) {
            // General exception (bisa jadi driver SQL tidak lempar UniqueConstraintViolationException)
            if (str_contains(strtolower($e->getMessage()), 'duplicate entry') || str_contains(strtolower($e->getMessage()), 'unique constraint')) {
                $this->logAttempt($barcode, $siswa->id, 'already_scanned', $now, $ipAddress);
                return [
                    'status' => 'already_scanned',
                    'name' => $siswa->name,
                    'photo_url' => $siswa->photo_path ? asset('storage/'.$siswa->photo_path) : null
                ];
            }
            throw $e;
        }

        // 7. Log & Return Success
        $statusResponse = $statusAbsen === 'hadir' ? 'success_on_time' : 'success_late';
        $this->logAttempt($barcode, $siswa->id, $statusResponse, $now, $ipAddress);

        return [
            'status' => $statusResponse,
            'name' => $siswa->name,
            'photo_url' => $siswa->photo_path ? asset('storage/'.$siswa->photo_path) : null,
            'late_minutes' => $lateMinutes
        ];
    }

    private function logAttempt(string $barcode, ?string $studentId, string $status, Carbon $time, ?string $ipAddress): void
    {
        LogScan::create([
            'barcode_code' => $barcode,
            'student_id' => $studentId,
            'status' => $status,
            'scan_time' => $time,
            'ip_address' => $ipAddress,
        ]);
    }
}

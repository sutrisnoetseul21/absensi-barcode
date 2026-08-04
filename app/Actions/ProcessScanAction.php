<?php

namespace App\Actions;

use App\Models\Presensi;
use App\Models\HariLibur;
use App\Models\LogScan;
use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

        // 2. Pencarian Siswa (Pencarian Fleksibel: ID (UUID), NISN, NIS, atau barcode_code)
        $siswa = Siswa::with(['enrollmentAktif', 'presensiProfile'])
            ->where(function($query) use ($barcode) {
                $query->where('id', $barcode)
                      ->orWhere('nisn', $barcode)
                      ->orWhere('nis', $barcode)
                      ->orWhereHas('presensiProfile', function($subQuery) use ($barcode) {
                          $subQuery->where('barcode_code', $barcode);
                      });
            })->first();

        if (!$siswa) {
            $this->logAttempt($barcode, null, 'not_found', $now, $ipAddress);
            return ['status' => 'not_found'];
        }

        $isBarcodeActive = $siswa->presensiProfile ? $siswa->presensiProfile->barcode_active : true;
        if (!$isBarcodeActive) {
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
                'class_name' => $siswa->enrollmentAktif ? $siswa->enrollmentAktif->kelas->name : '',
                'photo_url' => $siswa->photo_path ? asset('storage/'.$siswa->photo_path) : null,
                'message' => 'Hari ini libur, tidak ada presensi'
            ];
        }

        $settings = PengaturanSekolah::current();
        $batas_scan_datang_time = $settings ? $settings->batas_scan_datang_time : '09:00:00';
        $start_scan_out_time = $settings ? $settings->start_scan_out_time : '13:00:00';
        
        $batasCarbon = Carbon::parse($date . ' ' . $batas_scan_datang_time, 'Asia/Jakarta');
        $startOutCarbon = Carbon::parse($date . ' ' . $start_scan_out_time, 'Asia/Jakarta');

        // 4. Proses Transaksional State-Based
        $result = DB::transaction(function () use ($siswa, $date, $now, $scanTime, $enrollment, $classId, $academicYearId, $batasCarbon, $startOutCarbon, $settings) {
            
            $presensi = Presensi::where('student_id', $siswa->id)
                ->where('date', $date)
                ->lockForUpdate()
                ->first();

            if ($presensi) {
                // JIKA $presensi ADA (State = Sudah ada rekam jejak)
                if (in_array($presensi->status, Presensi::BLOCKED_OUT_STATUSES)) {
                    return [
                        'status' => 'blocked_status',
                        'name' => $siswa->name,
                        'class_name' => $enrollment->kelas->name ?? '',
                        'photo_url' => $siswa->photo_path ? asset('storage/'.$siswa->photo_path) : null,
                        'message' => 'Anda berstatus '.ucfirst($presensi->status).' hari ini. Tidak bisa absen pulang.',
                        'log_status' => 'rejected_blocked_status'
                    ];
                }

                if ($presensi->scan_out_time != null) {
                    return [
                        'status' => 'already_scanned_out',
                        'name' => $siswa->name,
                        'class_name' => $enrollment->kelas->name ?? '',
                        'photo_url' => $siswa->photo_path ? asset('storage/'.$siswa->photo_path) : null,
                        'message' => 'Anda sudah melakukan absen pulang hari ini.',
                        'log_status' => 'already_scanned_out'
                    ];
                }

                if ($now->lessThan($startOutCarbon)) {
                    return [
                        'status' => 'too_early_out',
                        'name' => $siswa->name,
                        'class_name' => $enrollment->kelas->name ?? '',
                        'photo_url' => $siswa->photo_path ? asset('storage/'.$siswa->photo_path) : null,
                        'message' => 'Belum waktunya absen pulang.',
                        'log_status' => 'rejected_too_early_out'
                    ];
                }

                // Lolos pengecekan -> PROSES PULANG
                $presensi->update([
                    'scan_out_time' => $scanTime,
                    'status_pulang' => 'tepat_waktu'
                ]);

                return [
                    'status' => 'success_out',
                    'name' => $siswa->name,
                    'class_name' => $enrollment->kelas->name ?? '',
                    'photo_url' => $siswa->photo_path ? asset('storage/'.$siswa->photo_path) : null,
                    'message' => 'Berhasil absen pulang',
                    'log_status' => 'success_out'
                ];

            } else {
                // JIKA $presensi TIDAK ADA (State = Belum absen hari ini)
                if ($now->greaterThan($batasCarbon)) {
                    if ($now->greaterThanOrEqualTo($startOutCarbon)) {
                        return [
                            'status' => 'rejected_no_scan_in',
                            'name' => $siswa->name,
                            'class_name' => $enrollment->kelas->name ?? '',
                            'photo_url' => $siswa->photo_path ? asset('storage/'.$siswa->photo_path) : null,
                            'message' => 'Anda tidak tercatat absen datang hari ini, tidak bisa absen pulang.',
                            'log_status' => 'rejected_no_scan_in'
                        ];
                    } else {
                        return [
                            'status' => 'rejected_late_in',
                            'name' => $siswa->name,
                            'class_name' => $enrollment->kelas->name ?? '',
                            'photo_url' => $siswa->photo_path ? asset('storage/'.$siswa->photo_path) : null,
                            'message' => 'Batas waktu absen datang telah habis. Silakan lapor ke Guru piket.',
                            'log_status' => 'rejected_late_in'
                        ];
                    }
                }

                // Lolos -> PROSES DATANG
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
                } catch (\Exception $e) {
                    if (str_contains(strtolower($e->getMessage()), 'duplicate entry') || str_contains(strtolower($e->getMessage()), 'unique constraint')) {
                        return [
                            'status' => 'already_scanned',
                            'name' => $siswa->name,
                            'class_name' => $enrollment->kelas->name ?? '',
                            'photo_url' => $siswa->photo_path ? asset('storage/'.$siswa->photo_path) : null,
                            'log_status' => 'already_scanned'
                        ];
                    }
                    throw $e;
                }

                $statusResponse = $statusAbsen === 'hadir' ? 'success_on_time' : 'success_late';
                return [
                    'status' => $statusResponse,
                    'name' => $siswa->name,
                    'class_name' => $enrollment->kelas->name ?? '',
                    'photo_url' => $siswa->photo_path ? asset('storage/'.$siswa->photo_path) : null,
                    'late_minutes' => $lateMinutes,
                    'log_status' => $statusResponse
                ];
            }
        });

        // 5. Log Attempt based on transaction result
        $logStatus = $result['log_status'] ?? $result['status'];
        $this->logAttempt($barcode, $siswa->id, $logStatus, $now, $ipAddress);

        // Remove log_status from response to frontend
        unset($result['log_status']);

        return $result;
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

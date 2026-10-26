<?php

namespace App\Services;

use App\Models\EnrollmentSiswa;
use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use App\Models\Presensi;
use App\Models\PresensiNotificationSetting;
use App\Models\TahunAjaran;
use App\Models\WhatsAppNotificationLog;
use App\Jobs\SendWhatsAppNotificationJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UnattendedAttendanceNotificationService
{
    /**
     * Dispatch notification for unattended students today.
     *
     * @param string $source 'cron' | 'manual'
     * @return array [
     *     'success' => bool,
     *     'message' => string,
     *     'dispatched' => int,
     *     'skipped' => int,
     *     'errors' => array
     * ]
     */
    public function dispatch(string $source = 'cron'): array
    {
        $setting = PresensiNotificationSetting::where('status_presensi', 'belum')->first();

        if (!$setting) {
            return [
                'success'    => false,
                'message'    => 'Pengaturan notifikasi belum presensi tidak ditemukan.',
                'dispatched' => 0,
                'skipped'    => 0,
                'errors'     => ['Setting belum not found'],
            ];
        }

        // Jika cron, wajib is_active dan is_auto_send
        if ($source === 'cron') {
            if (!$setting->is_active || !$setting->is_auto_send) {
                return [
                    'success'    => false,
                    'message'    => 'Fitur notifikasi otomatis belum presensi tidak aktif.',
                    'dispatched' => 0,
                    'skipped'    => 0,
                    'errors'     => [],
                ];
            }
        } else {
            // Jika manual, minimal is_active harus aktif
            if (!$setting->is_active) {
                return [
                    'success'    => false,
                    'message'    => 'Fitur notifikasi belum presensi sedang dinonaktifkan.',
                    'dispatched' => 0,
                    'skipped'    => 0,
                    'errors'     => [],
                ];
            }
        }

        $now       = Carbon::now('Asia/Jakarta');
        $todayDate = $now->toDateString();
        $dateLabel = $now->locale('id')->translatedFormat('l, d F Y');

        // Cek kalender hari sekolah (bukan hari libur)
        $kalenderService = app(KalenderSekolahService::class);
        if (!$kalenderService->isHariSekolah($now)) {
            return [
                'success'          => false,
                'message'          => 'Hari ini bukan hari efektif sekolah (Libur). Pengiriman notifikasi belum hadir dibatalkan.',
                'dispatched'       => 0,
                'skipped'          => 0,
                'total_unattended' => 0,
                'errors'           => ['Hari ini hari libur sekolah'],
            ];
        }

        $sekolah    = PengaturanSekolah::current();
        $activeYear = TahunAjaran::where('status', 'aktif')->first()
            ?? ($sekolah?->academic_year_id_active ? TahunAjaran::find($sekolah->academic_year_id_active) : null);

        if (!$activeYear) {
            return [
                'success'          => false,
                'message'          => 'Tahun ajaran aktif tidak ditemukan.',
                'dispatched'       => 0,
                'skipped'          => 0,
                'total_unattended' => 0,
                'errors'           => ['No active academic year'],
            ];
        }

        // Ambil semua enrollment siswa aktif
        $enrollments = EnrollmentSiswa::where('academic_year_id', $activeYear->id)
            ->where('status', 'aktif')
            ->with(['siswa', 'kelas'])
            ->get();

        // Ambil ID siswa yang sudah ada presensinya hari ini
        $alreadyAttendedStudentIds = Presensi::where('date', $todayDate)
            ->where('academic_year_id', $activeYear->id)
            ->pluck('student_id')
            ->toArray();

        $dispatched      = 0;
        $skipped         = 0;
        $totalUnattended = 0;
        $errors          = [];

        foreach ($enrollments as $enr) {
            $student = $enr->siswa;
            $kelas   = $enr->kelas;

            if (!$student) {
                $skipped++;
                continue;
            }

            // Jika kelasnya libur khusus hari ini
            if (!$kalenderService->isHariSekolah($now, $enr->class_id)) {
                $skipped++;
                continue;
            }

            // Jika sudah absen hari ini
            if (in_array($student->id, $alreadyAttendedStudentIds)) {
                $skipped++;
                continue;
            }

            $totalUnattended++;

            // No HP Orang Tua / Siswa
            $rawPhone   = $student->no_hp_orang_tua ?: $student->no_hp;
            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone ?? '');
            if (empty($cleanPhone)) {
                $skipped++;
                continue;
            }

            if (str_starts_with($cleanPhone, '0')) {
                $cleanPhone = '62' . substr($cleanPhone, 1);
            } elseif (str_starts_with($cleanPhone, '8')) {
                $cleanPhone = '62' . $cleanPhone;
            }

            // Dedup check hari ini agar tidak mengirim berulang kali ke siswa yang sama dalam hari yang sama
            $alreadySentToday = WhatsAppNotificationLog::where('related_type', 'presensi_belum')
                ->where('related_id', $student->id)
                ->whereDate('created_at', $todayDate)
                ->whereIn('status', ['sent', 'pending'])
                ->exists();

            if ($alreadySentToday) {
                $skipped++;
                continue;
            }

            // Wali Kelas name
            $waliKelas     = $kelas ? $kelas->waliKelas($activeYear->id) : null;
            $namaWaliKelas = $waliKelas ? $waliKelas->name : '-';

            // Render message template
            $message = PresensiNotificationSetting::renderTemplate('belum', [
                'nama_siswa'       => $student->name,
                'kelas'            => $kelas?->name ?? '-',
                'tanggal'          => $dateLabel,
                'status_kehadiran' => 'Belum Presensi',
                'nama_wali_kelas'  => $namaWaliKelas,
            ]);

            // Create Log
            $log = WhatsAppNotificationLog::create([
                'module'           => 'presensi',
                'recipient_type'   => 'ortu',
                'recipient_number' => $cleanPhone,
                'message'          => $message,
                'status'           => 'pending',
                'response_payload' => json_encode(['source' => $source, 'student_id' => $student->id]),
                'related_type'     => 'presensi_belum',
                'related_id'       => $student->id,
            ]);

            // Dispatch Job
            SendWhatsAppNotificationJob::dispatch(
                $cleanPhone,
                $message,
                'presensi_belum',
                $student->id,
                'ortu',
                $log->id
            );

            $dispatched++;
        }

        if ($source === 'cron') {
            $setting->update([
                'last_auto_send_date' => $todayDate,
            ]);
        }

        Log::info("UnattendedAttendanceNotificationService [{$source}]: Berhasil mendispatch {$dispatched} notifikasi (Total belum hadir: {$totalUnattended}, Dilewati/Deduplikasi: {$skipped}).");

        return [
            'success'          => true,
            'message'          => "Berhasil mendispatch {$dispatched} pesan WhatsApp ke orang tua siswa yang belum hadir.",
            'dispatched'       => $dispatched,
            'skipped'          => $skipped,
            'total_unattended' => $totalUnattended,
            'errors'           => $errors,
        ];
    }
}

<?php

namespace App\Services;

use App\Models\EnrollmentSiswa;
use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use App\Models\PresensiNotificationSetting;
use App\Models\PresensiSholatDhuhur;
use App\Models\TahunAjaran;
use App\Models\WhatsAppNotificationLog;
use App\Jobs\SendWhatsAppNotificationJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UnattendedSholatDhuhurNotificationService
{
    /**
     * Dispatch notification for students who haven't attended Sholat Dhuhur today.
     *
     * @param string $source 'cron' | 'manual'
     * @return array [
     *     'success'          => bool,
     *     'message'          => string,
     *     'dispatched'       => int,
     *     'skipped'          => int,
     *     'total_unattended' => int,
     *     'errors'           => array
     * ]
     */
    public function dispatch(string $source = 'cron'): array
    {
        $setting = PresensiNotificationSetting::where('status_presensi', 'sholat_dhuhur')->first();

        if (!$setting) {
            return [
                'success'          => false,
                'message'          => 'Pengaturan notifikasi presensi sholat dhuhur tidak ditemukan.',
                'dispatched'       => 0,
                'skipped'          => 0,
                'total_unattended' => 0,
                'errors'           => ['Setting sholat_dhuhur not found'],
            ];
        }

        // Cek fitur aktif di pengaturan sekolah
        $sekolah = PengaturanSekolah::current();
        if ($sekolah && !($sekolah->enable_sholat_dhuhur ?? true)) {
            return [
                'success'          => false,
                'message'          => 'Fitur Presensi Sholat Dhuhur dinonaktifkan di Pengaturan Sekolah.',
                'dispatched'       => 0,
                'skipped'          => 0,
                'total_unattended' => 0,
                'errors'           => ['Feature disabled in school settings'],
            ];
        }

        // Jika cron, wajib is_active dan is_auto_send
        if ($source === 'cron') {
            if (!$setting->is_active || !$setting->is_auto_send) {
                return [
                    'success'          => false,
                    'message'          => 'Fitur notifikasi otomatis sholat dhuhur tidak aktif.',
                    'dispatched'       => 0,
                    'skipped'          => 0,
                    'total_unattended' => 0,
                    'errors'           => [],
                ];
            }
        } else {
            // Jika manual, minimal is_active harus aktif
            if (!$setting->is_active) {
                return [
                    'success'          => false,
                    'message'          => 'Fitur notifikasi sholat dhuhur sedang dinonaktifkan. Silakan aktifkan fitur terlebih dahulu.',
                    'dispatched'       => 0,
                    'skipped'          => 0,
                    'total_unattended' => 0,
                    'errors'           => [],
                ];
            }
        }

        $now       = Carbon::now('Asia/Jakarta');
        $todayDate = $now->toDateString();
        $dateLabel = $now->locale('id')->translatedFormat('l, d F Y');

        // Cek kalender pelaksanaan sholat dhuhur (hari sekolah & sholat)
        $sholatService = app(PresensiSholatDhuhurService::class);
        if (!$sholatService->isHariSholatDhuhur($now)) {
            $holidayDesc = $sholatService->getHolidayDescription($now) ?? 'Hari Libur / Bukan Hari Sholat';
            return [
                'success'          => false,
                'message'          => "Hari ini bukan hari pelaksanaan sholat dhuhur ({$holidayDesc}). Pengiriman notifikasi dibatalkan.",
                'dispatched'       => 0,
                'skipped'          => 0,
                'total_unattended' => 0,
                'errors'           => ["Bukan hari pelaksanaan sholat dhuhur: {$holidayDesc}"],
            ];
        }

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

        // Ambil ID siswa yang sudah presensi sholat hari ini (status hadir atau ijin)
        $alreadyAttendedStudentIds = PresensiSholatDhuhur::where('date', $todayDate)
            ->where('academic_year_id', $activeYear->id)
            ->whereIn('status', ['hadir', 'ijin'])
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

            // Siswa non-Muslim tidak wajib sholat dhuhur, lewati
            if (!empty($student->religion) && strtolower(trim($student->religion)) !== 'islam') {
                $skipped++;
                continue;
            }

            // Jika kelasnya libur khusus hari ini
            if (!$sholatService->isHariSholatDhuhur($now, $enr->class_id)) {
                $skipped++;
                continue;
            }

            // Jika sudah absen sholat hari ini (hadir / ijin)
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
            $alreadySentToday = WhatsAppNotificationLog::where('related_type', 'presensi_sholat_dhuhur')
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
            $message = PresensiNotificationSetting::renderTemplate('sholat_dhuhur', [
                'nama_siswa'       => $student->name,
                'kelas'            => $kelas?->name ?? '-',
                'tanggal'          => $dateLabel,
                'status_kehadiran' => 'Belum Presensi Sholat Dhuhur',
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
                'related_type'     => 'presensi_sholat_dhuhur',
                'related_id'       => $student->id,
            ]);

            // Dispatch Job
            SendWhatsAppNotificationJob::dispatch(
                $cleanPhone,
                $message,
                'presensi_sholat_dhuhur',
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

        Log::info("UnattendedSholatDhuhurNotificationService [{$source}]: Berhasil mendispatch {$dispatched} notifikasi sholat dhuhur (Total belum sholat: {$totalUnattended}, Dilewati/Deduplikasi: {$skipped}).");

        return [
            'success'          => true,
            'message'          => "Berhasil mendispatch {$dispatched} pesan WhatsApp ke orang tua siswa yang belum presensi sholat dhuhur.",
            'dispatched'       => $dispatched,
            'skipped'          => $skipped,
            'total_unattended' => $totalUnattended,
            'errors'           => $errors,
        ];
    }
}

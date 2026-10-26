<?php

namespace App\Livewire\PortalPresensi;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\PresensiDailyReportSetting;
use App\Models\PresensiSchoolSummarySetting;
use App\Models\PresensiNotificationSetting;
use App\Models\Jabatan;
use App\Services\DailyClassReportService;
use App\Services\SchoolSummaryReportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

#[Layout('components.layouts.portal')]
class SettingNotifikasi extends Component
{
    // Tabs state
    public $activeTab = 'siswa'; // 'siswa', 'harian', 'sekolah'

    // Status scheduler & kalender
    public bool   $schedulerActive   = false;
    public string $schedulerLastRun  = '';
    public string $schedulerAgeLabel = '';
    public bool   $isHariSekolah     = true;
    public string $workDaysLabel     = '';
    public string $holidayReason     = '';

    // Status kirim manual per tab
    public bool $canSendDailyManual  = true;
    public bool $canSendSchoolManual = true;
    public bool $canSendBelumManual  = true;
    public int  $dailyManualSendCount = 0;
    public int  $schoolManualSendCount = 0;
    public int  $belumManualSendCount  = 0;

    // Available options for recipients
    public array $recipientOptions = [];
    public array $dailyRecipientOptions = [];
    public array $schoolRecipientOptions = [];

    // Form Data - Tab 1: Terlambat
    public bool $telat_notif_is_active = false;
    public array $telat_notif_recipients = [];
    public string $telat_notif_template_pesan = '';

    // Form Data - Tab 1: Sakit/Izin/Alpa
    public bool $student_notif_is_active = false;
    public array $student_notif_recipients = [];
    public string $student_notif_template_pesan = '';

    // Form Data - Tab: Belum Hadir
    public bool $belum_notif_is_active = false;
    public bool $belum_notif_is_auto_send = false;
    public string $belum_notif_auto_send_time = '07:30';
    public array $belum_notif_recipients = [];
    public string $belum_notif_template_pesan = '';

    // Form Data - Tab: Sholat Dhuhur
    public bool $sholat_dhuhur_notif_is_active = false;
    public array $sholat_dhuhur_notif_recipients = [];
    public string $sholat_dhuhur_notif_template_pesan = '';

    // Form Data - Tab 1: Pengajuan Ijin (Oleh Siswa)
    public bool $leave_request_notif_is_active = false;
    public array $leave_request_notif_recipients = [];
    public string $leave_request_notif_template_pesan = '';

    // Form Data - Tab 1: Persetujuan Ijin (Oleh Guru)
    public bool $leave_approval_notif_is_active = false;
    public array $leave_approval_notif_recipients = [];
    public string $leave_approval_notif_template_pesan = '';

    // Form Data - Tab 2: Laporan Harian
    public bool $daily_is_active = false;
    public string $daily_cutoff_time = '08:00:00';
    public array $daily_recipients = [];
    public string $daily_template_pesan = '';

    // Form Data - Tab 3: Rekap Sekolah
    public bool $school_is_active = false;
    public string $school_cutoff_time = '08:15:00';
    public array $school_recipients = [];
    public string $school_template_header = '';
    public string $school_template_row = '';
    public string $school_template_footer = '';

    public function mount(): void
    {
        $this->loadRecipientOptions();
        $this->checkSchedulerStatus();
        $this->refreshManualSendStatus();
        $this->loadData();
    }

    private function loadRecipientOptions(): void
    {
        $waGroups = \App\Services\WhatsAppGatewayService::getAvailableGroups();
        $jabatans = Jabatan::pluck('nama_jabatan', 'nama_jabatan')->toArray();
        
        $options = [
            'siswa'      => 'Siswa',
            'ortu'       => 'Orang Tua',
            'wali_kelas' => 'Wali Kelas',
        ];

        $this->recipientOptions = array_merge($options, $jabatans, $waGroups);
        $this->dailyRecipientOptions = array_merge(['wali_kelas' => 'Wali Kelas'], $jabatans, $waGroups);
        $this->schoolRecipientOptions = array_merge($jabatans, $waGroups);
    }

    private function loadData(): void
    {
        // Load data Presensi Notification Settings (Sakit/Izin/Alpa)
        $sakitSetting = PresensiNotificationSetting::where('status_presensi', 'sakit')->first();
        if ($sakitSetting) {
            $this->student_notif_is_active      = $sakitSetting->is_active;
            $this->student_notif_recipients     = $sakitSetting->recipients ?? ['ortu'];
            $this->student_notif_template_pesan = $sakitSetting->template_pesan;
        } else {
            $this->student_notif_is_active      = false;
            $this->student_notif_recipients     = ['ortu'];
            $this->student_notif_template_pesan = "Halo Orang Tua/Wali,\nKami informasikan bahwa ananda {nama_siswa} (Kelas {kelas}) pada tanggal {tanggal} pukul {jam} tercatat berstatus {status_kehadiran}.\n\nSalam,\n{nama_sekolah}";
        }

        // Load data Presensi Notification Settings (Telat)
        $telatSetting = PresensiNotificationSetting::where('status_presensi', 'telat')->first();
        if ($telatSetting) {
            $this->telat_notif_is_active      = $telatSetting->is_active;
            $this->telat_notif_recipients     = $telatSetting->recipients ?? ['ortu'];
            $this->telat_notif_template_pesan = $telatSetting->template_pesan;
        } else {
            $this->telat_notif_is_active      = false;
            $this->telat_notif_recipients     = ['ortu'];
            $this->telat_notif_template_pesan = PresensiNotificationSetting::getDefaultTemplate('telat');
        }

        // Load data Presensi Notification Settings (Belum Presensi Pagi)
        $belumSetting = PresensiNotificationSetting::where('status_presensi', 'belum')->first();
        if ($belumSetting) {
            $this->belum_notif_is_active      = $belumSetting->is_active;
            $this->belum_notif_is_auto_send   = (bool) ($belumSetting->is_auto_send ?? false);
            $this->belum_notif_auto_send_time = substr($belumSetting->auto_send_time ?? '07:30:00', 0, 5);
            $this->belum_notif_recipients     = $belumSetting->recipients ?? ['ortu'];
            $this->belum_notif_template_pesan = $belumSetting->template_pesan;
        } else {
            $this->belum_notif_is_active      = false;
            $this->belum_notif_is_auto_send   = false;
            $this->belum_notif_auto_send_time = '07:30';
            $this->belum_notif_recipients     = ['ortu'];
            $this->belum_notif_template_pesan = PresensiNotificationSetting::getDefaultTemplate('belum');
        }

        // Load data Presensi Notification Settings (Sholat Dhuhur)
        $sholatSetting = PresensiNotificationSetting::where('status_presensi', 'sholat_dhuhur')->first();
        if ($sholatSetting) {
            $this->sholat_dhuhur_notif_is_active      = $sholatSetting->is_active;
            $this->sholat_dhuhur_notif_recipients     = $sholatSetting->recipients ?? ['ortu'];
            $this->sholat_dhuhur_notif_template_pesan = $sholatSetting->template_pesan;
        } else {
            $this->sholat_dhuhur_notif_is_active      = false;
            $this->sholat_dhuhur_notif_recipients     = ['ortu'];
            $this->sholat_dhuhur_notif_template_pesan = PresensiNotificationSetting::getDefaultTemplate('sholat_dhuhur');
        }

        // Load data Notification Settings (Pengajuan Ijin)
        $leaveRequestSetting = PresensiNotificationSetting::where('status_presensi', 'leave_request')->first();
        if ($leaveRequestSetting) {
            $this->leave_request_notif_is_active      = $leaveRequestSetting->is_active;
            $this->leave_request_notif_recipients     = $leaveRequestSetting->recipients ?? ['wali_kelas'];
            $this->leave_request_notif_template_pesan = $leaveRequestSetting->template_pesan;
        } else {
            $this->leave_request_notif_is_active      = false;
            $this->leave_request_notif_recipients     = ['wali_kelas'];
            $this->leave_request_notif_template_pesan = "Halo Bapak/Ibu {nama_wali_kelas},\nSiswa Anda, *{nama_siswa}* dari kelas *{kelas}* telah mengajukan *{jenis_ijin}* pada tanggal {tanggal_mulai} sampai {tanggal_selesai}.\nAlasan: {alasan}\n\nSilakan login ke Portal Guru untuk meninjau:\n{link_detail}\n\n🔗 *Link Konfirmasi:*\n{link_konfirmasi}";
        }

        // Load data Notification Settings (Persetujuan Ijin)
        $leaveApprovalSetting = PresensiNotificationSetting::where('status_presensi', 'leave_approval')->first();
        if ($leaveApprovalSetting) {
            $this->leave_approval_notif_is_active      = $leaveApprovalSetting->is_active;
            $this->leave_approval_notif_recipients     = $leaveApprovalSetting->recipients ?? ['siswa'];
            $this->leave_approval_notif_template_pesan = $leaveApprovalSetting->template_pesan;
        } else {
            $this->leave_approval_notif_is_active      = false;
            $this->leave_approval_notif_recipients     = ['siswa'];
            $this->leave_approval_notif_template_pesan = "Halo *{nama_siswa}*,\nPengajuan *{jenis_ijin}* Anda untuk tanggal {tanggal_mulai} sampai {tanggal_selesai} telah *{status_persetujuan}* oleh Bapak/Ibu {nama_guru}.\n{alasan_penolakan}\n\nSemoga aktivitas Anda berjalan lancar.";
        }

        // Load data Laporan Harian
        $daily                        = PresensiDailyReportSetting::current();
        $this->daily_is_active      = $daily->is_active;
        $this->daily_cutoff_time    = $daily->cutoff_time;
        $this->daily_recipients     = $daily->recipients ?? [];
        $this->daily_template_pesan = $daily->template_pesan;

        // Load data Rekap Seluruh Sekolah
        $school                          = PresensiSchoolSummarySetting::current();
        $this->school_is_active        = $school->is_active;
        $this->school_cutoff_time      = $school->cutoff_time;
        $this->school_recipients       = $school->recipients ?? [];
        $this->school_template_header  = $school->template_header;
        $this->school_template_row     = $school->template_row;
        $this->school_template_footer  = $school->template_footer;
    }

    private function checkSchedulerStatus(): void
    {
        $heartbeatPath = storage_path('framework/schedule-heartbeat');

        if (!file_exists($heartbeatPath)) {
            $this->schedulerActive   = false;
            $this->schedulerLastRun  = '';
            $this->schedulerAgeLabel = 'Belum pernah terdeteksi';
        } else {
            $timestamp  = (int) file_get_contents($heartbeatPath);
            $tz         = config('app.timezone', 'Asia/Jakarta');
            $lastRun    = Carbon::createFromTimestamp($timestamp, $tz);
            $ageMinutes = (int) round($lastRun->floatDiffInMinutes(now()));

            $this->schedulerLastRun = $lastRun->format('H:i:s, d M Y');

            if ($ageMinutes <= 10) {
                $this->schedulerActive   = true;
                $this->schedulerAgeLabel = $ageMinutes <= 1
                    ? 'baru saja (< 1 menit)'
                    : "{$ageMinutes} menit yang lalu";
            } else {
                $this->schedulerActive   = false;
                $this->schedulerAgeLabel = $ageMinutes < 60
                    ? "{$ageMinutes} menit yang lalu"
                    : $lastRun->diffForHumans();
            }
        }

        // Cek status kalender sekolah
        $now = now('Asia/Jakarta');
        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        $this->isHariSekolah = $kalenderService->isHariSekolah($now);
        $workDaysType = $kalenderService->getWorkDaysTypeForDate($now);
        $this->workDaysLabel = $workDaysType === '6_hari' ? '6 Hari Kerja (Senin - Sabtu)' : '5 Hari Kerja (Senin - Jumat)';

        $hariLiburRecord = \App\Models\HariLibur::hariIni($now->toDateString())->first();
        $namaHariIndo = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $namaHariIni = $namaHariIndo[$now->format('l')] ?? $now->format('l');
        $this->holidayReason = $hariLiburRecord ? $hariLiburRecord->name : ($now->isWeekend() ? "Libur Akhir Pekan ({$namaHariIni})" : 'Hari Libur');
    }

    private function refreshManualSendStatus(): void
    {
        $todayStr = now()->toDateString();

        $now = now('Asia/Jakarta');
        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        $isHariSekolah = $kalenderService->isHariSekolah($now);

        $daily = PresensiDailyReportSetting::current();
        $this->canSendDailyManual = $isHariSekolah && $daily->canSendManualToday();
        $dailyLastDate = $daily->manual_send_date instanceof \Carbon\Carbon
            ? $daily->manual_send_date->toDateString()
            : (string) $daily->manual_send_date;
        $this->dailyManualSendCount = ($dailyLastDate === $todayStr) ? (int) $daily->manual_send_count : 0;

        $school = PresensiSchoolSummarySetting::current();
        $this->canSendSchoolManual = $isHariSekolah && $school->canSendManualToday();
        $schoolLastDate = $school->manual_send_date instanceof \Carbon\Carbon
            ? $school->manual_send_date->toDateString()
            : (string) $school->manual_send_date;
        $this->schoolManualSendCount = ($schoolLastDate === $todayStr) ? (int) $school->manual_send_count : 0;

        $belum = PresensiNotificationSetting::where('status_presensi', 'belum')->first();
        $this->canSendBelumManual = $isHariSekolah && ($belum ? $belum->canSendManualToday() : true);
        $belumLastDate = ($belum && $belum->manual_send_date instanceof \Carbon\Carbon)
            ? $belum->manual_send_date->toDateString()
            : ($belum ? (string) $belum->manual_send_date : null);
        $this->belumManualSendCount = ($belumLastDate === $todayStr && $belum) ? (int) $belum->manual_send_count : 0;
    }

    public function setActiveTab($tab): void
    {
        $this->activeTab = $tab;
    }

    public function testSchedulerRun(): void
    {
        try {
            Artisan::call('scheduler:heartbeat');
            $this->checkSchedulerStatus();
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Scheduler berhasil dijalankan manual. Heartbeat telah diperbarui.'
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Gagal menjalankan scheduler: ' . $e->getMessage()
            ]);
        }
    }

    public function confirmSendDailyManual(): void
    {
        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        if (!$kalenderService->isHariSekolah(now('Asia/Jakarta'))) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Hari ini adalah hari libur sekolah. Pengiriman laporan harian dinonaktifkan.'
            ]);
            return;
        }

        $setting = PresensiDailyReportSetting::current();

        if (!$setting->canSendManualToday()) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Pengiriman manual laporan harian sudah mencapai batas maksimal 5x per hari.'
            ]);
            return;
        }

        try {
            /** @var DailyClassReportService $service */
            $service = app(DailyClassReportService::class);
            $result  = $service->dispatch(isManual: true);

            $setting->recordManualSend();
            $this->refreshManualSendStatus();

            if ($result['dispatched'] > 0) {
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => "Laporan harian berhasil dikirim. Total {$result['dispatched']} job antrean."
                ]);
            } else {
                $errorMsg = !empty($result['errors']) ? implode(', ', $result['errors']) : 'Tidak ada penerima valid.';
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'Tidak ada laporan yang dikirim: ' . $errorMsg
                ]);
            }
        } catch (\Throwable $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Gagal mengirim laporan harian: ' . $e->getMessage()
            ]);
        }
    }

    public function confirmSendSchoolManual(): void
    {
        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        if (!$kalenderService->isHariSekolah(now('Asia/Jakarta'))) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Hari ini adalah hari libur sekolah. Pengiriman rekap sekolah dinonaktifkan.'
            ]);
            return;
        }

        $setting = PresensiSchoolSummarySetting::current();

        if (!$setting->canSendManualToday()) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Pengiriman manual rekap sekolah sudah mencapai batas maksimal 5x per hari.'
            ]);
            return;
        }

        try {
            /** @var SchoolSummaryReportService $service */
            $service = app(SchoolSummaryReportService::class);
            $result  = $service->dispatch(isManual: true);

            $setting->recordManualSend();
            $this->refreshManualSendStatus();

            if ($result['dispatched'] > 0) {
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => "Rekap sekolah berhasil dikirim. Total {$result['dispatched']} antrean."
                ]);
            } else {
                $errorMsg = !empty($result['errors']) ? implode(', ', $result['errors']) : 'Tidak ada penerima valid.';
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'Tidak ada rekap yang dikirim: ' . $errorMsg
                ]);
            }
        } catch (\Throwable $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Gagal mengirim rekap sekolah: ' . $e->getMessage()
            ]);
        }
    }

    public function confirmSendBelumManual(): void
    {
        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        if (!$kalenderService->isHariSekolah(now('Asia/Jakarta'))) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Hari ini adalah hari libur sekolah. Pengiriman notifikasi belum hadir dinonaktifkan.'
            ]);
            return;
        }

        $setting = PresensiNotificationSetting::where('status_presensi', 'belum')->first();

        if ($setting && !$setting->canSendManualToday()) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Pengiriman manual notifikasi belum hadir sudah mencapai batas maksimal 5x per hari.'
            ]);
            return;
        }

        try {
            /** @var \App\Services\UnattendedAttendanceNotificationService $service */
            $service = app(\App\Services\UnattendedAttendanceNotificationService::class);
            $result  = $service->dispatch(source: 'manual');

            if ($setting) {
                $setting->recordManualSend();
            }
            $this->refreshManualSendStatus();

            if ($result['dispatched'] > 0) {
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => "Notifikasi belum hadir berhasil dikirim. Total {$result['dispatched']} pesan WA diantrekan (Siswa belum hadir: {$result['total_unattended']})."
                ]);
            } else {
                $msg = $result['message'] ?? 'Tidak ada notifikasi yang dikirim atau semua orang tua sudah menerima notifikasi hari ini.';
                $this->dispatch('notify', [
                    'type' => 'info',
                    'message' => $msg
                ]);
            }
        } catch (\Throwable $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Gagal mengirim notifikasi: ' . $e->getMessage()
            ]);
        }
    }

    public function save(): void
    {
        try {
            // Simpan Aturan Notifikasi (Sakit / Izin / Alpa)
            $exceptionStatuses = ['alpa', 'sakit', 'izin'];
            foreach ($exceptionStatuses as $status) {
                PresensiNotificationSetting::updateOrCreate(
                    ['status_presensi' => $status],
                    [
                        'is_active'      => $this->student_notif_is_active,
                        'recipients'     => $this->student_notif_recipients,
                        'template_pesan' => $this->student_notif_template_pesan,
                    ]
                );
            }

            // Simpan Aturan Notifikasi (Telat)
            PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'telat'],
                [
                    'is_active'      => $this->telat_notif_is_active,
                    'recipients'     => $this->telat_notif_recipients,
                    'template_pesan' => $this->telat_notif_template_pesan,
                ]
            );

            // Simpan Aturan Notifikasi (Belum Presensi Pagi)
            PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'belum'],
                [
                    'is_active'      => $this->belum_notif_is_active,
                    'is_auto_send'   => $this->belum_notif_is_auto_send,
                    'auto_send_time' => $this->belum_notif_auto_send_time ?: '07:30:00',
                    'recipients'     => $this->belum_notif_recipients,
                    'template_pesan' => $this->belum_notif_template_pesan,
                ]
            );

            // Simpan Aturan Notifikasi (Sholat Dhuhur)
            PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'sholat_dhuhur'],
                [
                    'is_active'      => $this->sholat_dhuhur_notif_is_active,
                    'recipients'     => $this->sholat_dhuhur_notif_recipients,
                    'template_pesan' => $this->sholat_dhuhur_notif_template_pesan,
                ]
            );

            // Simpan Aturan Notifikasi (Pengajuan Ijin)
            PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'leave_request'],
                [
                    'is_active'      => $this->leave_request_notif_is_active,
                    'recipients'     => $this->leave_request_notif_recipients,
                    'template_pesan' => $this->leave_request_notif_template_pesan,
                ]
            );

            // Simpan Aturan Notifikasi (Persetujuan Ijin)
            PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'leave_approval'],
                [
                    'is_active'      => $this->leave_approval_notif_is_active,
                    'recipients'     => $this->leave_approval_notif_recipients,
                    'template_pesan' => $this->leave_approval_notif_template_pesan,
                ]
            );

            // Simpan Laporan Harian
            $daily = PresensiDailyReportSetting::current();
            $daily->update([
                'is_active'     => $this->daily_is_active,
                'cutoff_time'   => $this->daily_cutoff_time,
                'recipients'    => $this->daily_recipients,
                'template_pesan'=> $this->daily_template_pesan,
            ]);

            // Simpan Rekap Seluruh Sekolah
            $school = PresensiSchoolSummarySetting::current();
            $school->update([
                'is_active'       => $this->school_is_active,
                'cutoff_time'     => $this->school_cutoff_time,
                'recipients'      => $this->school_recipients,
                'template_header' => $this->school_template_header,
                'template_row'    => $this->school_template_row,
                'template_footer' => $this->school_template_footer,
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Pengaturan berhasil disimpan'
            ]);
        } catch (\Exception $exception) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Gagal menyimpan pengaturan: ' . $exception->getMessage()
            ]);
        }
    }
}

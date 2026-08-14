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

    // Status scheduler
    public bool   $schedulerActive   = false;
    public string $schedulerLastRun  = '';
    public string $schedulerAgeLabel = '';

    // Status kirim manual per tab
    public bool $canSendDailyManual  = true;
    public bool $canSendSchoolManual = true;

    // Available options for recipients
    public $recipientOptions = [];
    public $schoolRecipientOptions = [];

    // Form Data - Tab 1: Terlambat
    public bool $telat_notif_is_active = false;
    public array $telat_notif_recipients = [];
    public string $telat_notif_template_pesan = '';

    // Form Data - Tab 1: Sakit/Izin/Alpa
    public bool $student_notif_is_active = false;
    public array $student_notif_recipients = [];
    public string $student_notif_template_pesan = '';

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
        $jabatans = Jabatan::pluck('nama_jabatan', 'nama_jabatan')->toArray();
        
        $this->recipientOptions = array_merge([
            'ortu'       => 'Orang Tua',
            'wali_kelas' => 'Wali Kelas',
        ], $jabatans);

        $this->schoolRecipientOptions = $jabatans;
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
            $this->telat_notif_template_pesan = "Halo Orang Tua/Wali,\nKami informasikan bahwa ananda {nama_siswa} (Kelas {kelas}) pada tanggal {tanggal} pukul {jam} telah hadir namun terlambat.\n\nSalam,\n{nama_sekolah}";
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
            return;
        }

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

    private function refreshManualSendStatus(): void
    {
        $this->canSendDailyManual  = PresensiDailyReportSetting::current()->canSendManualToday();
        $this->canSendSchoolManual = PresensiSchoolSummarySetting::current()->canSendManualToday();
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
        $setting = PresensiDailyReportSetting::current();

        if (!$setting->canSendManualToday()) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Pengiriman manual laporan harian hanya bisa dilakukan 1x per hari.'
            ]);
            return;
        }

        try {
            /** @var DailyClassReportService $service */
            $service = app(DailyClassReportService::class);
            $result  = $service->dispatch(isManual: true);

            $setting->recordManualSend();
            $this->canSendDailyManual = false;

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
        $setting = PresensiSchoolSummarySetting::current();

        if (!$setting->canSendManualToday()) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Pengiriman manual rekap sekolah hanya bisa dilakukan 1x per hari.'
            ]);
            return;
        }

        try {
            /** @var SchoolSummaryReportService $service */
            $service = app(SchoolSummaryReportService::class);
            $result  = $service->dispatch();

            $setting->recordManualSend();
            $this->canSendSchoolManual = false;

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

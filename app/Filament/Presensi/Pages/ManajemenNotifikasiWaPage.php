<?php

namespace App\Filament\Presensi\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use App\Filament\Traits\HasSimplePageRoleAccess;
use Filament\Support\Exceptions\Halt;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

use App\Models\PresensiDailyReportSetting;
use App\Models\PresensiSchoolSummarySetting;
use App\Services\DailyClassReportService;
use App\Services\SchoolSummaryReportService;
use Carbon\Carbon;
use Illuminate\Support\HtmlString;

class ManajemenNotifikasiWaPage extends Page implements HasForms
{
    use HasSimplePageRoleAccess;

    protected static function getModuleRolePrefix(): string
    {
        return 'presensi';
    }

    protected static function requiresEditorRole(): bool
    {
        return true;
    }

    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected string $view = 'filament.pages.manajemen-notifikasi-wa';

    protected static ?string $navigationLabel = 'Manajemen Notifikasi WA';

    protected static ?string $slug = 'manajemen-notifikasi-wa';

    protected static ?string $title = 'Manajemen Notifikasi WA';

    protected static string|\UnitEnum|null $navigationGroup = 'Presensi';

    protected static ?int $navigationSort = 8;

    public ?array $data = [];

    // Status scheduler
    public bool   $schedulerActive   = false;
    public string $schedulerLastRun  = '';
    public string $schedulerAgeLabel = '';

    // Status kirim manual per tab
    public bool $canSendDailyManual  = true;
    public bool $canSendSchoolManual = true;
    public bool $canSendBelumManual  = true;
    public bool $canSendSholatManual = true;

    public function mount(): void
    {
        $this->checkSchedulerStatus();
        $this->refreshManualSendStatus();

        $data = [];

        // Load data Presensi Notification Settings (Sakit/Izin/Alpa)
        $sakitSetting = \App\Models\PresensiNotificationSetting::where('status_presensi', 'sakit')->first();
        if ($sakitSetting) {
            $data['student_notif_is_active']      = $sakitSetting->is_active;
            $data['student_notif_recipients']     = $sakitSetting->recipients ?? ['ortu'];
            $data['student_notif_template_pesan'] = $sakitSetting->template_pesan;
        } else {
            $data['student_notif_is_active']      = false;
            $data['student_notif_recipients']     = ['ortu'];
            $data['student_notif_template_pesan'] = "Halo Orang Tua/Wali,\nKami informasikan bahwa ananda {nama_siswa} (Kelas {kelas}) pada tanggal {tanggal} pukul {jam} tercatat berstatus {status_kehadiran}.\n\nSalam,\n{nama_sekolah}";
        }

        // Load data Presensi Notification Settings (Telat)
        $telatSetting = \App\Models\PresensiNotificationSetting::where('status_presensi', 'telat')->first();
        if ($telatSetting) {
            $data['telat_notif_is_active']      = $telatSetting->is_active;
            $data['telat_notif_recipients']     = $telatSetting->recipients ?? ['ortu'];
            $data['telat_notif_template_pesan'] = $telatSetting->template_pesan;
        } else {
            $data['telat_notif_is_active']      = false;
            $data['telat_notif_recipients']     = ['ortu'];
            $data['telat_notif_template_pesan'] = \App\Models\PresensiNotificationSetting::getDefaultTemplate('telat');
        }

        // Load data Presensi Notification Settings (Belum Presensi Pagi)
        $belumSetting = \App\Models\PresensiNotificationSetting::where('status_presensi', 'belum')->first();
        if ($belumSetting) {
            $data['belum_notif_is_active']      = $belumSetting->is_active;
            $data['belum_notif_is_auto_send']   = $belumSetting->is_auto_send ?? false;
            $data['belum_notif_auto_send_time'] = substr($belumSetting->auto_send_time ?? '07:30:00', 0, 5);
            $data['belum_notif_recipients']     = $belumSetting->recipients ?? ['ortu'];
            $data['belum_notif_template_pesan'] = $belumSetting->template_pesan;
        } else {
            $data['belum_notif_is_active']      = false;
            $data['belum_notif_is_auto_send']   = false;
            $data['belum_notif_auto_send_time'] = '07:30';
            $data['belum_notif_recipients']     = ['ortu'];
            $data['belum_notif_template_pesan'] = \App\Models\PresensiNotificationSetting::getDefaultTemplate('belum');
        }

        // Load data Presensi Notification Settings (Sholat Dhuhur)
        $sholatSetting = \App\Models\PresensiNotificationSetting::where('status_presensi', 'sholat_dhuhur')->first();
        if ($sholatSetting) {
            $data['sholat_dhuhur_notif_is_active']      = $sholatSetting->is_active;
            $data['sholat_dhuhur_notif_is_auto_send']   = $sholatSetting->is_auto_send ?? false;
            $data['sholat_dhuhur_notif_auto_send_time'] = substr($sholatSetting->auto_send_time ?? '13:00:00', 0, 5);
            $data['sholat_dhuhur_notif_recipients']     = $sholatSetting->recipients ?? ['ortu'];
            $data['sholat_dhuhur_notif_template_pesan'] = $sholatSetting->template_pesan;
        } else {
            $data['sholat_dhuhur_notif_is_active']      = false;
            $data['sholat_dhuhur_notif_is_auto_send']   = false;
            $data['sholat_dhuhur_notif_auto_send_time'] = '13:00';
            $data['sholat_dhuhur_notif_recipients']     = ['ortu'];
            $data['sholat_dhuhur_notif_template_pesan'] = \App\Models\PresensiNotificationSetting::getDefaultTemplate('sholat_dhuhur');
        }

        // Load data Notification Settings (Pengajuan Ijin)
        $leaveRequestSetting = \App\Models\PresensiNotificationSetting::where('status_presensi', 'leave_request')->first();
        if ($leaveRequestSetting) {
            $data['leave_request_notif_is_active']      = $leaveRequestSetting->is_active;
            $data['leave_request_notif_recipients']     = $leaveRequestSetting->recipients ?? ['wali_kelas'];
            $data['leave_request_notif_template_pesan'] = $leaveRequestSetting->template_pesan;
        } else {
            $data['leave_request_notif_is_active']      = false;
            $data['leave_request_notif_recipients']     = ['wali_kelas'];
            $data['leave_request_notif_template_pesan'] = "Halo Bapak/Ibu {nama_wali_kelas},\nSiswa Anda, *{nama_siswa}* dari kelas *{kelas}* telah mengajukan *{jenis_ijin}* pada tanggal {tanggal_mulai} sampai {tanggal_selesai}.\nAlasan: {alasan}\n\nSilakan login ke Portal Guru untuk meninjau:\n{link_detail}\n\n🔗 *Link Konfirmasi:*\n{link_konfirmasi}";
        }

        // Load data Notification Settings (Persetujuan Ijin)
        $leaveApprovalSetting = \App\Models\PresensiNotificationSetting::where('status_presensi', 'leave_approval')->first();
        if ($leaveApprovalSetting) {
            $data['leave_approval_notif_is_active']      = $leaveApprovalSetting->is_active;
            $data['leave_approval_notif_recipients']     = $leaveApprovalSetting->recipients ?? ['siswa'];
            $data['leave_approval_notif_template_pesan'] = $leaveApprovalSetting->template_pesan;
        } else {
            $data['leave_approval_notif_is_active']      = false;
            $data['leave_approval_notif_recipients']     = ['siswa'];
            $data['leave_approval_notif_template_pesan'] = "Halo *{nama_siswa}*,\nPengajuan *{jenis_ijin}* Anda untuk tanggal {tanggal_mulai} sampai {tanggal_selesai} telah *{status_persetujuan}* oleh Bapak/Ibu {nama_guru}.\n{alasan_penolakan}\n\nSemoga aktivitas Anda berjalan lancar.";
        }

        // Load data Laporan Harian
        $daily                        = PresensiDailyReportSetting::current();
        $data['daily_is_active']      = $daily->is_active;
        $data['daily_cutoff_time']    = $daily->cutoff_time;
        $data['daily_recipients']     = $daily->recipients ?? [];
        $data['daily_template_pesan'] = $daily->template_pesan;

        // Load data Rekap Seluruh Sekolah
        $school                          = PresensiSchoolSummarySetting::current();
        $data['school_is_active']        = $school->is_active;
        $data['school_cutoff_time']      = $school->cutoff_time;
        $data['school_recipients']       = $school->recipients ?? [];
        $data['school_template_header']  = $school->template_header;
        $data['school_template_row']     = $school->template_row;
        $data['school_template_footer']  = $school->template_footer;

        $this->form->fill($data);
    }

    /**
     * Cek apakah scheduler aktif berdasarkan heartbeat file.
     */
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

    /**
     * Refresh status tombol kirim manual.
     */
    private function refreshManualSendStatus(): void
    {
        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        $isHariSekolah   = $kalenderService->isHariSekolah(now('Asia/Jakarta'));

        $this->canSendDailyManual  = $isHariSekolah && PresensiDailyReportSetting::current()->canSendManualToday();
        $this->canSendSchoolManual = $isHariSekolah && PresensiSchoolSummarySetting::current()->canSendManualToday();

        $belumSetting = \App\Models\PresensiNotificationSetting::where('status_presensi', 'belum')->first();
        $this->canSendBelumManual  = $isHariSekolah && ($belumSetting ? $belumSetting->canSendManualToday() : true);

        $sholatSetting = \App\Models\PresensiNotificationSetting::where('status_presensi', 'sholat_dhuhur')->first();
        $sholatService = app(\App\Services\PresensiSholatDhuhurService::class);
        $this->canSendSholatManual = $sholatService->isHariSholatDhuhur(now('Asia/Jakarta')) && ($sholatSetting ? $sholatSetting->canSendManualToday() : true);
    }

    public function form(Schema $schema): Schema
    {
        $waGroups = \App\Services\WhatsAppGatewayService::getAvailableGroups();

        return $schema
            ->components([
                // ─── Banner Status Scheduler ───────────────────────────────
                \Filament\Forms\Components\Placeholder::make('scheduler_status_banner')
                    ->hiddenLabel()
                    ->content(fn() => $this->buildSchedulerBanner())
                    ->columnSpanFull(),

                // ─── Tabs ───────────────────────────────────────────────────
                Tabs::make('Manajemen Notifikasi WA')
                    ->tabs([
                        // ── Tab 1: Notifikasi Kehadiran Siswa ─────────────
                        Tab::make('Notifikasi Kehadiran Siswa')
                            ->icon('heroicon-o-bolt')
                            ->schema([
                                Section::make('Aturan Notifikasi: Terlambat')
                                    ->description('Sistem akan otomatis mendeteksi ketika siswa berstatus Telat dan mengirimkan pesan WA sesuai template di bawah ini.')
                                    ->schema([
                                        Toggle::make('telat_notif_is_active')
                                            ->label('Aktifkan Notifikasi Terlambat')
                                            ->columnSpanFull(),
                                        Select::make('telat_notif_recipients')
                                            ->label('Kirim Ke (Penerima)')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Pilih satu atau beberapa penerima...')
                                            ->options(function () use ($waGroups) {
                                                $options  = [
                                                    'siswa'      => 'Siswa',
                                                    'ortu'       => 'Orang Tua',
                                                    'wali_kelas' => 'Wali Kelas',
                                                ];
                                                $jabatans = \App\Models\Jabatan::pluck('nama_jabatan', 'nama_jabatan')->toArray();
                                                return array_merge($options, $jabatans, $waGroups);
                                            })
                                            ->helperText('💡 Pilihan Guru BK/BP otomatis difilter hanya ke Guru BK pembina kelas siswa terkait (sesuai Manajemen Akses Portal).')
                                            ->columnSpanFull(),
                                        Textarea::make('telat_notif_template_pesan')
                                            ->label('Template Pesan Terlambat')
                                            ->rows(5)
                                            ->helperText('Placeholder: {nama_siswa}, {kelas}, {tanggal}, {jam}, {nama_wali_kelas}, {nama_sekolah}')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Aturan Notifikasi: Sakit / Izin / Alpa')
                                    ->description('Sistem akan otomatis mendeteksi ketika siswa berstatus Sakit, Izin, atau Alpa dan mengirimkan pesan WA sesuai template di bawah ini.')
                                    ->schema([
                                        Toggle::make('student_notif_is_active')
                                            ->label('Aktifkan Notifikasi (Sakit / Izin / Alpa)')
                                            ->columnSpanFull(),
                                        Select::make('student_notif_recipients')
                                            ->label('Kirim Ke (Penerima)')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Pilih satu atau beberapa penerima...')
                                            ->options(function () use ($waGroups) {
                                                $options  = [
                                                    'ortu'       => 'Orang Tua',
                                                    'wali_kelas' => 'Wali Kelas',
                                                ];
                                                $jabatans = \App\Models\Jabatan::pluck('nama_jabatan', 'nama_jabatan')->toArray();
                                                return array_merge($options, $jabatans, $waGroups);
                                            })
                                            ->helperText('💡 Pilihan Guru BK/BP otomatis difilter hanya ke Guru BK pembina kelas siswa terkait (sesuai Manajemen Akses Portal).')
                                            ->columnSpanFull(),
                                        Textarea::make('student_notif_template_pesan')
                                            ->label('Template Pesan Sakit/Izin/Alpa')
                                            ->rows(5)
                                            ->helperText('Placeholder {status_kehadiran} akan otomatis terisi secara dinamis dengan kata: Sakit, Izin, atau Alpa. Placeholder lain: {nama_siswa}, {kelas}, {tanggal}, {jam}, {nama_wali_kelas}, {nama_sekolah}')
                                            ->columnSpanFull(),
                                    ]),

                            ]),

                        // ── Tab 2: Notifikasi Belum Hadir ──────────────────
                        Tab::make('Notifikasi Belum Hadir')
                            ->icon('heroicon-o-exclamation-triangle')
                            ->schema([
                                Section::make('Pengaturan Notifikasi Belum Presensi Pagi')
                                    ->description('Notifikasi konfirmasi kehadiran dikirim otomatis oleh sistem sesuai jam cut-off atau secara manual kepada orang tua siswa yang belum presensi pagi di Kiosk atau Portal Presensi.')
                                    ->schema([
                                        Toggle::make('belum_notif_is_active')
                                            ->label('Aktifkan Fitur Notifikasi Belum Presensi')
                                            ->columnSpanFull(),
                                        Toggle::make('belum_notif_is_auto_send')
                                            ->label('Kirim Otomatis Notifikasi Belum Presensi')
                                            ->helperText('Jika diaktifkan, sistem akan otomatis mengirim WA ke orang tua siswa yang belum absen saat jam cut-off tercapai.')
                                            ->columnSpanFull(),
                                        TimePicker::make('belum_notif_auto_send_time')
                                            ->label('Jam Pengiriman (Cut-off Time)')
                                            ->seconds(false)
                                            ->default('07:30')
                                            ->required(),
                                        Select::make('belum_notif_recipients')
                                            ->label('Kirim Ke (Penerima)')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Pilih satu atau beberapa penerima...')
                                            ->options(function () use ($waGroups) {
                                                $options  = [
                                                    'ortu'       => 'Orang Tua',
                                                    'wali_kelas' => 'Wali Kelas',
                                                ];
                                                $jabatans = \App\Models\Jabatan::pluck('nama_jabatan', 'nama_jabatan')->toArray();
                                                return array_merge($options, $jabatans, $waGroups);
                                            })
                                            ->helperText('💡 Pilihan Guru BK/BP otomatis difilter hanya ke Guru BK pembina kelas siswa terkait.')
                                            ->columnSpanFull(),
                                        Textarea::make('belum_notif_template_pesan')
                                            ->label('Template Pesan Belum Hadir')
                                            ->rows(8)
                                            ->helperText('Placeholder: {nama_siswa}, {kelas}, {tanggal}, {nama_wali_kelas}, {nama_sekolah}')
                                            ->columnSpanFull(),
                                    ]),

                                // ── Kirim Manual Notifikasi Belum Hadir ────
                                Section::make('Kirim Manual Notifikasi Belum Hadir')
                                    ->icon('heroicon-o-paper-airplane')
                                    ->description('Kirim notifikasi ke orang tua siswa yang belum hadir hari ini secara manual. Maksimal 5x per hari.')
                                    ->schema([
                                        \Filament\Forms\Components\Placeholder::make('belum_manual_status')
                                            ->hiddenLabel()
                                            ->content(fn() => $this->buildBelumManualStatusHtml())
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // ── Tab 3: Notifikasi Sholat Dhuhur ────────────────
                        Tab::make('Notifikasi Sholat Dhuhur')
                            ->icon('heroicon-o-sun')
                            ->schema([
                                Section::make('Aturan Notifikasi: Presensi Sholat Dhuhur')
                                    ->description('Notifikasi konfirmasi kehadiran sholat dhuhur dikirim otomatis oleh sistem sesuai jam cut-off atau secara manual kepada orang tua siswa Muslim yang belum tercatat presensi Sholat Dhuhur berjamaah di sekolah.')
                                    ->schema([
                                        Toggle::make('sholat_dhuhur_notif_is_active')
                                            ->label('Aktifkan Fitur Notifikasi Sholat Dhuhur')
                                            ->columnSpanFull(),
                                        Toggle::make('sholat_dhuhur_notif_is_auto_send')
                                            ->label('Kirim Otomatis Notifikasi Sholat Dhuhur')
                                            ->helperText('Jika diaktifkan, sistem akan otomatis mengirim WA ke orang tua siswa yang belum absen sholat saat jam cut-off tercapai.')
                                            ->columnSpanFull(),
                                        TimePicker::make('sholat_dhuhur_notif_auto_send_time')
                                            ->label('Jam Pengiriman (Cut-off Time)')
                                            ->seconds(false)
                                            ->default('13:00')
                                            ->required(),
                                        Select::make('sholat_dhuhur_notif_recipients')
                                            ->label('Kirim Ke (Penerima)')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Pilih satu atau beberapa penerima...')
                                            ->options(function () use ($waGroups) {
                                                $options  = [
                                                    'ortu'       => 'Orang Tua',
                                                    'wali_kelas' => 'Wali Kelas',
                                                ];
                                                $jabatans = \App\Models\Jabatan::pluck('nama_jabatan', 'nama_jabatan')->toArray();
                                                return array_merge($options, $jabatans, $waGroups);
                                            })
                                            ->helperText('💡 Pilihan Guru BK/BP otomatis difilter hanya ke Guru BK pembina kelas siswa terkait.')
                                            ->columnSpanFull(),
                                        Textarea::make('sholat_dhuhur_notif_template_pesan')
                                            ->label('Template Pesan Sholat Dhuhur')
                                            ->rows(8)
                                            ->helperText('Placeholder: {nama_siswa}, {kelas}, {tanggal}, {nama_wali_kelas}, {nama_sekolah}')
                                            ->columnSpanFull(),
                                    ]),

                                // ── Kirim Manual Notifikasi Sholat Dhuhur ──
                                Section::make('Kirim Manual Notifikasi Sholat Dhuhur')
                                    ->icon('heroicon-o-paper-airplane')
                                    ->description('Kirim notifikasi ke orang tua siswa yang belum presensi sholat dhuhur hari ini secara manual. Maksimal 5x per hari.')
                                    ->schema([
                                        \Filament\Forms\Components\Placeholder::make('sholat_dhuhur_manual_status')
                                            ->hiddenLabel()
                                            ->content(fn() => $this->buildSholatDhuhurManualStatusHtml())
                                            ->columnSpanFull(),
                                    ]),
                            ]),


                        // ── Tab: Notifikasi Ijin Online ───────────────────
                        Tab::make('Notifikasi Ijin Online')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make('Aturan Notifikasi: Pengajuan Ijin (Oleh Siswa)')
                                    ->description('Sistem akan mengirim pesan WA saat siswa membuat pengajuan Ijin/Sakit melalui Portal Siswa.')
                                    ->schema([
                                        Toggle::make('leave_request_notif_is_active')
                                            ->label('Aktifkan Notifikasi Pengajuan Ijin')
                                            ->columnSpanFull(),
                                        Select::make('leave_request_notif_recipients')
                                            ->label('Kirim Ke (Penerima)')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Pilih satu atau beberapa penerima...')
                                            ->options(function () use ($waGroups) {
                                                $options  = [
                                                    'siswa'      => 'Siswa',
                                                    'ortu'       => 'Orang Tua',
                                                    'wali_kelas' => 'Wali Kelas',
                                                ];
                                                $jabatans = \App\Models\Jabatan::pluck('nama_jabatan', 'nama_jabatan')->toArray();
                                                return array_merge($options, $jabatans, $waGroups);
                                            })
                                            ->columnSpanFull(),
                                        Textarea::make('leave_request_notif_template_pesan')
                                            ->label('Template Pesan Pengajuan Ijin')
                                            ->rows(6)
                                            ->helperText('Placeholder: {nama_siswa}, {kelas}, {jenis_ijin}, {tanggal_mulai}, {tanggal_selesai}, {alasan}, {nama_wali_kelas}, {link_detail}, {link_konfirmasi}')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Aturan Notifikasi: Persetujuan Ijin (Oleh Guru)')
                                    ->description('Sistem akan mengirim pesan WA saat guru (Wali Kelas) memberikan persetujuan atau menolak pengajuan ijin.')
                                    ->schema([
                                        Toggle::make('leave_approval_notif_is_active')
                                            ->label('Aktifkan Notifikasi Persetujuan Ijin')
                                            ->columnSpanFull(),
                                        Select::make('leave_approval_notif_recipients')
                                            ->label('Kirim Ke (Penerima)')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Pilih satu atau beberapa penerima...')
                                            ->options(function () use ($waGroups) {
                                                $options  = [
                                                    'siswa'      => 'Siswa',
                                                    'ortu'       => 'Orang Tua',
                                                    'wali_kelas' => 'Wali Kelas',
                                                ];
                                                $jabatans = \App\Models\Jabatan::pluck('nama_jabatan', 'nama_jabatan')->toArray();
                                                return array_merge($options, $jabatans, $waGroups);
                                            })
                                            ->columnSpanFull(),
                                        Textarea::make('leave_approval_notif_template_pesan')
                                            ->label('Template Pesan Persetujuan Ijin')
                                            ->rows(5)
                                            ->helperText('Placeholder: {nama_siswa}, {jenis_ijin}, {tanggal_mulai}, {tanggal_selesai}, {status_persetujuan}, {nama_guru}, {alasan_penolakan}')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // ── Tab 2: Laporan Harian Kelas ───────────────────
                        Tab::make('Laporan Harian Kelas')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Section::make('Pengaturan Laporan Harian Wali Kelas')
                                    ->description('Laporan harian dikirim otomatis oleh sistem sesuai jam cutoff khusus pada Hari Efektif Sekolah (Senin-Jumat/Sabtu sesuai kalender). Toleransi pengiriman: 1 jam setelah jam cutoff.')
                                    ->schema([
                                        Toggle::make('daily_is_active')
                                            ->label('Aktifkan Laporan Harian')
                                            ->columnSpanFull(),
                                        TimePicker::make('daily_cutoff_time')
                                            ->label('Jam Pengiriman (Cut-off Time)')
                                            ->seconds(false)
                                            ->required(),
                                        Select::make('daily_recipients')
                                            ->label('Kirim Ke (Penerima)')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Pilih satu atau beberapa penerima...')
                                            ->options(function () use ($waGroups) {
                                                $options  = ['wali_kelas' => 'Wali Kelas'];
                                                $jabatans = \App\Models\Jabatan::pluck('nama_jabatan', 'nama_jabatan')->toArray();
                                                return array_merge($options, $jabatans, $waGroups);
                                            })
                                            ->columnSpanFull(),
                                        Textarea::make('daily_template_pesan')
                                            ->label('Template Pesan')
                                            ->rows(8)
                                            ->helperText(new HtmlString('
                                                <div class="space-y-1.5 text-xs text-gray-600 dark:text-gray-400 mt-2">
                                                    <p class="font-bold text-gray-700 dark:text-gray-300">📌 Daftar Placeholder Tersedia:</p>
                                                    <p>• <strong>Statistik Angka:</strong> <code>{nama_kelas}</code>, <code>{tanggal}</code>, <code>{total_siswa}</code>, <code>{jumlah_hadir}</code>, <code>{jumlah_terlambat}</code>, <code>{jumlah_sakit}</code>, <code>{jumlah_izin}</code>, <code>{jumlah_alpa}</code></p>
                                                    <p>• <strong>Format Baris Koma (Segaris):</strong> <code>{nama_sakit}</code>, <code>{nama_izin}</code>, <code>{nama_alpa}</code>, <code>{nama_terlambat}</code>, <code>{nama_belum_presensi}</code><br><span class="italic text-gray-500">Contoh hasil: Budi Santoso, Ani Wijaya (atau "-" jika tidak ada).</span></p>
                                                    <p>• <strong>Format Daftar Baris per Baris:</strong> <code>{daftar_sakit}</code>, <code>{daftar_izin}</code>, <code>{daftar_alpa}</code>, <code>{daftar_terlambat}</code>, <code>{daftar_belum_presensi}</code><br><span class="italic text-gray-500">Contoh hasil:<br>- Budi Santoso<br>- Ani Wijaya<br>(atau "Tidak ada" jika tidak ada).</span></p>
                                                </div>
                                            '))
                                            ->columnSpanFull(),
                                    ]),

                                // ── Kirim Manual ──────────────────────────
                                Section::make('Kirim Manual Laporan Harian')
                                    ->icon('heroicon-o-paper-airplane')
                                    ->description('Kirim laporan harian hari ini secara manual. Maksimal 5x per hari.')
                                    ->schema([
                                        \Filament\Forms\Components\Placeholder::make('daily_manual_status')
                                            ->hiddenLabel()
                                            ->content(fn() => $this->buildDailyManualStatusHtml())
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // ── Tab 3: Rekap Seluruh Sekolah ──────────────────
                        Tab::make('Rekap Seluruh Sekolah')
                            ->icon('heroicon-o-building-office-2')
                            ->schema([
                                Section::make('Pengaturan Rekap Seluruh Sekolah')
                                    ->description('Rekap presensi seluruh kelas sekaligus (Helicopter View) untuk Manajemen Sekolah pada Hari Efektif Sekolah (Senin-Jumat/Sabtu sesuai kalender). Toleransi pengiriman: 1 jam setelah jam cutoff.')
                                    ->schema([
                                        Toggle::make('school_is_active')
                                            ->label('Aktifkan Laporan Rekap Sekolah')
                                            ->columnSpanFull(),
                                        TimePicker::make('school_cutoff_time')
                                            ->label('Jam Pengiriman (Cut-off Time)')
                                            ->seconds(false)
                                            ->required(),
                                        Select::make('school_recipients')
                                            ->label('Kirim Ke (Penerima)')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Pilih satu atau beberapa penerima...')
                                            ->options(function () use ($waGroups) {
                                                $jabatans = \App\Models\Jabatan::pluck('nama_jabatan', 'nama_jabatan')->toArray();
                                                return array_merge($jabatans, $waGroups);
                                            })
                                            ->columnSpanFull(),
                                        Textarea::make('school_template_header')
                                            ->label('Template Header')
                                            ->rows(3)
                                            ->helperText('Placeholder: {nama_sekolah}, {hari}, {tanggal}, {angkatan}, {tingkat}')
                                            ->columnSpanFull(),
                                        Textarea::make('school_template_row')
                                            ->label('Template Baris per Kelas')
                                            ->rows(9)
                                            ->helperText(new HtmlString('
                                                <div class="space-y-1 text-xs text-gray-600 dark:text-gray-400 mt-2">
                                                    <p>Pesan otomatis dikirim terpisah per angkatan (Kelas 7, 8, 9). Template diulang untuk setiap kelas pada angkatan bersangkutan.</p>
                                                    <p><strong>Placeholder Tersedia:</strong> <code>{nama_kelas}</code>, <code>{total_siswa}</code>, <code>{jumlah_hadir}</code>, <code>{jumlah_terlambat}</code>, <code>{nama_terlambat}</code>, <code>{jumlah_sakit}</code>, <code>{nama_sakit}</code>, <code>{jumlah_izin}</code>, <code>{nama_izin}</code>, <code>{jumlah_alpa}</code>, <code>{nama_alpa}</code>, <code>{jumlah_belum_presensi}</code>, <code>{nama_belum_presensi}</code></p>
                                                    <p class="italic text-gray-500">Keterangan: <code>{nama_sakit}</code>, <code>{nama_izin}</code>, <code>{nama_alpa}</code>, <code>{nama_belum_presensi}</code> otomatis terisi format tanda kurung <code>(Budi, Ani)</code> jika ada siswa, dan kosong jika 0.</p>
                                                </div>
                                            '))
                                            ->columnSpanFull(),
                                        Textarea::make('school_template_footer')
                                            ->label('Template Footer')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),

                                // ── Kirim Manual ──────────────────────────
                                Section::make('Kirim Manual Rekap Sekolah')
                                    ->icon('heroicon-o-paper-airplane')
                                    ->description('Kirim rekap seluruh sekolah hari ini secara manual. Maksimal 5x per hari.')
                                    ->schema([
                                        \Filament\Forms\Components\Placeholder::make('school_manual_status')
                                            ->hiddenLabel()
                                            ->content(fn() => $this->buildSchoolManualStatusHtml())
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),

                // ── Disclaimer ────────────────────────────────────────────
                \Filament\Forms\Components\Placeholder::make('evolution_api_disclaimer')
                    ->hiddenLabel()
                    ->content(new HtmlString('
                        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400 space-y-1">
                            <p class="font-semibold text-gray-700 dark:text-gray-300">ℹ️ Pemberitahuan Lisensi &amp; Sanggahan (Attribution &amp; Trademark Notice):</p>
                            <p>• Layanan notifikasi ini didukung oleh infrastruktur open-source <strong>Evolution API</strong> (Apache License 2.0 / Evolution Foundation).</p>
                            <p>• <em>Disclaimer:</em> Sistem ERP ini merupakan perangkat lunak independen dan tidak berafiliasi, diawasi, atau didukung secara resmi oleh WhatsApp maupun Meta Platforms, Inc. WhatsApp® adalah merek dagang terdaftar milik Meta Platforms, Inc.</p>
                        </div>
                    '))
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HTML Builders
    // ─────────────────────────────────────────────────────────────────────────

    private function buildSchedulerBanner(): HtmlString
    {
        $now = now('Asia/Jakarta');
        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        $isHariSekolah = $kalenderService->isHariSekolah($now);
        $workDaysType = $kalenderService->getWorkDaysTypeForDate($now);
        $workDaysLabel = $workDaysType === '6_hari' ? '6 Hari Kerja (Senin - Sabtu)' : '5 Hari Kerja (Senin - Jumat)';

        $hariLiburRecord = \App\Models\HariLibur::hariIni($now->toDateString())->first();
        $namaHariIndo = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $namaHariIni = $namaHariIndo[$now->format('l')] ?? $now->format('l');
        $holidayReason = $hariLiburRecord ? $hariLiburRecord->name : ($now->isWeekend() ? "Libur Akhir Pekan ({$namaHariIni})" : 'Hari Libur');

        $calendarHtml = $isHariSekolah
            ? "<div class='mt-2 pt-2 border-t border-green-200/60 dark:border-green-800 text-xs font-medium text-green-700 dark:text-green-300 flex items-center gap-1.5'><span>📅</span> Status Kalender: <strong>Hari Efektif Belajar ({$workDaysLabel})</strong> — Laporan otomatis aktif.</div>"
            : "<div class='mt-2 pt-2 border-t border-amber-300 dark:border-amber-700 text-xs font-medium text-amber-800 dark:text-amber-300 flex items-center gap-1.5'><span>🏖️</span> Status Kalender: <strong>Hari Libur Sekolah ({$holidayReason} / {$workDaysLabel})</strong> — Laporan harian otomatis dijeda & tidak dikirim.</div>";

        if ($this->schedulerActive) {
            $html = <<<HTML
            <div class="rounded-xl border border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-700 p-4 flex items-start gap-3">
                <span class="text-2xl mt-0.5">🟢</span>
                <div class="flex-1">
                    <p class="font-semibold text-green-800 dark:text-green-300">Scheduler Aktif</p>
                    <p class="text-sm text-green-700 dark:text-green-400 mt-0.5">
                        Terakhir berjalan: <strong>{$this->schedulerLastRun}</strong> ({$this->schedulerAgeLabel})
                    </p>
                    <p class="text-sm text-green-600 dark:text-green-500 mt-1">✅ Laporan otomatis terjadwal oleh sistem.</p>
                    {$calendarHtml}
                </div>
            </div>
            HTML;
        } else {
            $cronCommand = htmlspecialchars('/usr/bin/php8.4 ' . base_path('artisan') . ' schedule:run >> /dev/null 2>&1');
            $notFoundMsg = $this->schedulerLastRun
                ? "Terakhir terdeteksi: <strong>{$this->schedulerLastRun}</strong> ({$this->schedulerAgeLabel})"
                : "Belum pernah terdeteksi sejak instalasi.";

            $html = <<<HTML
            <div class="rounded-xl border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-700 p-4 space-y-3">
                <div class="flex items-start gap-3">
                    <span class="text-2xl mt-0.5">🔴</span>
                    <div class="flex-1">
                        <p class="font-semibold text-red-800 dark:text-red-300">Scheduler Tidak Terdeteksi</p>
                        <p class="text-sm text-red-600 dark:text-red-400 mt-0.5">{$notFoundMsg}</p>
                        <p class="text-sm text-red-700 dark:text-red-300 mt-1 font-medium">⚠️ Laporan otomatis TIDAK akan berjalan sampai scheduler diaktifkan.</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-lg border border-red-100 dark:border-red-800 p-3 space-y-2">
                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">📋 Panduan Mengaktifkan Scheduler (HestiaCP)</p>
                    <ol class="text-xs text-gray-600 dark:text-gray-400 space-y-1 list-decimal list-inside">
                        <li>Login ke <strong>HestiaCP</strong> → menu <strong>Cron Jobs</strong></li>
                        <li>Klik tombol <strong>Add Cron Job</strong></li>
                        <li>Set semua kolom waktu (Minute, Hour, Day, Month, Day of Week) ke <strong>*</strong> (setiap menit)</li>
                        <li>Isi kolom <strong>Command</strong> dengan perintah di bawah ini:</li>
                    </ol>
                    <div class="bg-gray-900 dark:bg-gray-950 text-green-400 text-xs font-mono rounded p-2 break-all select-all">
                        {$cronCommand}
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-500">Setelah disimpan, tunggu 1-2 menit lalu refresh halaman ini. Status akan berubah menjadi 🟢 Aktif.</p>
                </div>
                <div class="flex gap-2 flex-wrap">
                    <button
                        wire:click="testSchedulerRun"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="testSchedulerRun">▶ Test Jalankan Sekarang</span>
                        <span wire:loading wire:target="testSchedulerRun">⏳ Menjalankan...</span>
                    </button>
                </div>
            </div>
            HTML;
        }

        return new HtmlString($html);
    }

    private function buildDailyManualStatusHtml(): HtmlString
    {
        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        $isHariSekolah   = $kalenderService->isHariSekolah(now('Asia/Jakarta'));
        $setting         = PresensiDailyReportSetting::current();
        $canSend         = $isHariSekolah && $setting->canSendManualToday();
        $todayDate       = now()->format('d M Y');
        $todayStr        = now()->toDateString();
        $lastSendDate    = $setting->manual_send_date instanceof \Carbon\Carbon
            ? $setting->manual_send_date->toDateString()
            : (string) $setting->manual_send_date;
        $sendCount       = ($lastSendDate === $todayStr) ? (int) $setting->manual_send_count : 0;
        $nextCount       = $sendCount + 1;

        if (!$isHariSekolah) {
            $hariLiburRecord = \App\Models\HariLibur::hariIni(now('Asia/Jakarta')->toDateString())->first();
            $namaHariIndo    = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
            $namaHariIni     = $namaHariIndo[now('Asia/Jakarta')->format('l')] ?? now('Asia/Jakarta')->format('l');
            $holidayReason   = $hariLiburRecord ? $hariLiburRecord->name : (now('Asia/Jakarta')->isWeekend() ? "Libur Akhir Pekan ({$namaHariIni})" : 'Hari Libur Sekolah');

            $html = <<<HTML
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-sm">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                        🏖️ Status: Hari Libur Sekolah ({$holidayReason})
                    </span>
                </div>
                <button
                    disabled
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                >
                    📤 Kirim Laporan Harian Sekarang
                </button>
                <p class="text-xs text-amber-600 dark:text-amber-400">⛔ Hari ini adalah hari libur sekolah. Pengiriman laporan harian dinonaktifkan demi mencegah terkirimnya pesan di hari libur.</p>
            </div>
            HTML;
            return new HtmlString($html);
        }

        if ($canSend) {
            $badgeText = $sendCount > 0
                ? "Terkirim {$sendCount} dari 5x hari ini"
                : "Belum dikirim hari ini (Maks 5x)";

            $html = <<<HTML
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                    <span>📅</span>
                    <span>Tanggal hari ini: <strong>{$todayDate}</strong></span>
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                        {$badgeText}
                    </span>
                </div>
                <button
                    wire:click="confirmSendDailyManual"
                    wire:loading.attr="disabled"
                    wire:confirm="Yakin ingin kirim laporan harian presensi ke semua wali kelas sekarang? (Pengiriman ke-{$nextCount} dari maksimal 5x hari ini)"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-primary-600 hover:bg-primary-700 text-white transition disabled:opacity-50 shadow-sm"
                >
                    <span wire:loading.remove wire:target="confirmSendDailyManual">📤 Kirim Laporan Harian Sekarang ({$sendCount}/5)</span>
                    <span wire:loading wire:target="confirmSendDailyManual">⏳ Memproses...</span>
                </button>
                <p class="text-xs text-gray-500 dark:text-gray-500">ℹ️ Laporan akan dikirim ke seluruh wali kelas yang memiliki nomor HP terdaftar. Tombol ini bisa digunakan maksimal 5x per hari.</p>
            </div>
            HTML;
        } else {
            $html = <<<HTML
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-sm">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                        ✅ Batas pengiriman tercapai: 5 dari 5x hari ini ({$todayDate})
                    </span>
                </div>
                <button
                    disabled
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                >
                    📤 Kirim Laporan Harian Sekarang (5/5)
                </button>
                <p class="text-xs text-gray-500 dark:text-gray-500">⛔ Pengiriman manual sudah mencapai batas maksimal 5x hari ini. Tersedia kembali besok.</p>
            </div>
            HTML;
        }

        return new HtmlString($html);
    }

    private function buildSchoolManualStatusHtml(): HtmlString
    {
        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        $isHariSekolah   = $kalenderService->isHariSekolah(now('Asia/Jakarta'));
        $setting         = PresensiSchoolSummarySetting::current();
        $canSend         = $isHariSekolah && $setting->canSendManualToday();
        $todayDate       = now()->format('d M Y');
        $todayStr        = now()->toDateString();
        $lastSendDate    = $setting->manual_send_date instanceof \Carbon\Carbon
            ? $setting->manual_send_date->toDateString()
            : (string) $setting->manual_send_date;
        $sendCount       = ($lastSendDate === $todayStr) ? (int) $setting->manual_send_count : 0;
        $nextCount       = $sendCount + 1;

        if (!$isHariSekolah) {
            $hariLiburRecord = \App\Models\HariLibur::hariIni(now('Asia/Jakarta')->toDateString())->first();
            $namaHariIndo    = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
            $namaHariIni     = $namaHariIndo[now('Asia/Jakarta')->format('l')] ?? now('Asia/Jakarta')->format('l');
            $holidayReason   = $hariLiburRecord ? $hariLiburRecord->name : (now('Asia/Jakarta')->isWeekend() ? "Libur Akhir Pekan ({$namaHariIni})" : 'Hari Libur Sekolah');

            $html = <<<HTML
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-sm">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                        🏖️ Status: Hari Libur Sekolah ({$holidayReason})
                    </span>
                </div>
                <button
                    disabled
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                >
                    📤 Kirim Rekap Sekolah Sekarang
                </button>
                <p class="text-xs text-amber-600 dark:text-amber-400">⛔ Hari ini adalah hari libur sekolah. Pengiriman rekap sekolah dinonaktifkan demi mencegah terkirimnya pesan di hari libur.</p>
            </div>
            HTML;
            return new HtmlString($html);
        }

        if ($canSend) {
            $badgeText = $sendCount > 0
                ? "Terkirim {$sendCount} dari 5x hari ini"
                : "Belum dikirim hari ini (Maks 5x)";

            $html = <<<HTML
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                    <span>📅</span>
                    <span>Tanggal hari ini: <strong>{$todayDate}</strong></span>
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                        {$badgeText}
                    </span>
                </div>
                <button
                    wire:click="confirmSendSchoolManual"
                    wire:loading.attr="disabled"
                    wire:confirm="Yakin ingin kirim rekap presensi seluruh sekolah sekarang? (Pengiriman ke-{$nextCount} dari maksimal 5x hari ini)"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-primary-600 hover:bg-primary-700 text-white transition disabled:opacity-50 shadow-sm"
                >
                    <span wire:loading.remove wire:target="confirmSendSchoolManual">📤 Kirim Rekap Sekolah Sekarang ({$sendCount}/5)</span>
                    <span wire:loading wire:target="confirmSendSchoolManual">⏳ Memproses...</span>
                </button>
                <p class="text-xs text-gray-500 dark:text-gray-500">ℹ️ Rekap akan dikirim ke seluruh penerima yang dipilih. Tombol ini bisa digunakan maksimal 5x per hari.</p>
            </div>
            HTML;
        } else {
            $html = <<<HTML
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-sm">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                        ✅ Batas pengiriman tercapai: 5 dari 5x hari ini ({$todayDate})
                    </span>
                </div>
                <button
                    disabled
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                >
                    📤 Kirim Rekap Sekolah Sekarang (5/5)
                </button>
                <p class="text-xs text-gray-500 dark:text-gray-500">⛔ Pengiriman manual sudah mencapai batas maksimal 5x hari ini. Tersedia kembali besok.</p>
            </div>
            HTML;
        }

        return new HtmlString($html);
    }

    private function buildBelumManualStatusHtml(): HtmlString
    {
        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        $isHariSekolah   = $kalenderService->isHariSekolah(now('Asia/Jakarta'));
        $setting         = \App\Models\PresensiNotificationSetting::where('status_presensi', 'belum')->first();
        $canSend         = $isHariSekolah && ($setting ? $setting->canSendManualToday() : true);
        $todayDate       = now()->format('d M Y');
        $todayStr        = now()->toDateString();
        $lastSendDate    = ($setting && $setting->manual_send_date instanceof \Carbon\Carbon)
            ? $setting->manual_send_date->toDateString()
            : ($setting ? (string) $setting->manual_send_date : null);
        $sendCount       = ($lastSendDate === $todayStr && $setting) ? (int) $setting->manual_send_count : 0;
        $nextCount       = $sendCount + 1;

        if (!$isHariSekolah) {
            $hariLiburRecord = \App\Models\HariLibur::hariIni(now('Asia/Jakarta')->toDateString())->first();
            $namaHariIndo    = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
            $namaHariIni     = $namaHariIndo[now('Asia/Jakarta')->format('l')] ?? now('Asia/Jakarta')->format('l');
            $holidayReason   = $hariLiburRecord ? $hariLiburRecord->name : (now('Asia/Jakarta')->isWeekend() ? "Libur Akhir Pekan ({$namaHariIni})" : 'Hari Libur Sekolah');

            $html = <<<HTML
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-sm">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                        🏖️ Status: Hari Libur Sekolah ({$holidayReason})
                    </span>
                </div>
                <button
                    disabled
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                >
                    📤 Kirim Notifikasi Belum Hadir Sekarang
                </button>
                <p class="text-xs text-amber-600 dark:text-amber-400">⛔ Hari ini adalah hari libur sekolah. Pengiriman notifikasi belum hadir dinonaktifkan demi mencegah terkirimnya pesan di hari libur.</p>
            </div>
            HTML;
            return new HtmlString($html);
        }

        if ($canSend) {
            $badgeText = $sendCount > 0
                ? "Terkirim {$sendCount} dari 5x hari ini"
                : "Belum dikirim hari ini (Maks 5x)";

            $html = <<<HTML
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                    <span>📅</span>
                    <span>Tanggal hari ini: <strong>{$todayDate}</strong></span>
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                        {$badgeText}
                    </span>
                </div>
                <button
                    wire:click="confirmSendBelumManual"
                    wire:loading.attr="disabled"
                    wire:confirm="Yakin ingin mengirim notifikasi WA ke orang tua dari semua siswa yang belum hadir sekarang? (Pengiriman ke-{$nextCount} dari maksimal 5x hari ini)"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-primary-600 hover:bg-primary-700 text-white transition disabled:opacity-50 shadow-sm"
                >
                    <span wire:loading.remove wire:target="confirmSendBelumManual">📤 Kirim Notifikasi Belum Hadir Sekarang ({$sendCount}/5)</span>
                    <span wire:loading wire:target="confirmSendBelumManual">⏳ Memproses...</span>
                </button>
                <p class="text-xs text-gray-500 dark:text-gray-500">ℹ️ Notifikasi akan dikirim ke nomor HP orang tua dari siswa yang belum melakukan presensi hari ini. Tombol ini bisa digunakan maksimal 5x per hari.</p>
            </div>
            HTML;
        } else {
            $html = <<<HTML
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-sm">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                        ✅ Batas pengiriman tercapai: 5 dari 5x hari ini ({$todayDate})
                    </span>
                </div>
                <button
                    disabled
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                >
                    📤 Kirim Notifikasi Belum Hadir Sekarang (5/5)
                </button>
                <p class="text-xs text-gray-500 dark:text-gray-500">⛔ Pengiriman manual sudah mencapai batas maksimal 5x hari ini. Tersedia kembali besok.</p>
            </div>
            HTML;
        }

        return new HtmlString($html);
    }

    private function buildSholatDhuhurManualStatusHtml(): HtmlString
    {
        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        $sholatService   = app(\App\Services\PresensiSholatDhuhurService::class);
        $isHariSholat    = $sholatService->isHariSholatDhuhur(now('Asia/Jakarta'));
        $setting         = \App\Models\PresensiNotificationSetting::where('status_presensi', 'sholat_dhuhur')->first();
        $canSend         = $isHariSholat && ($setting ? $setting->canSendManualToday() : true);
        $todayDate       = now()->format('d M Y');
        $todayStr        = now()->toDateString();
        $lastSendDate    = ($setting && $setting->manual_send_date instanceof \Carbon\Carbon)
            ? $setting->manual_send_date->toDateString()
            : ($setting ? (string) $setting->manual_send_date : null);
        $sendCount       = ($lastSendDate === $todayStr && $setting) ? (int) $setting->manual_send_count : 0;
        $nextCount       = $sendCount + 1;

        if (!$isHariSholat) {
            $holidayDesc = $sholatService->getHolidayDescription(now('Asia/Jakarta')) ?? 'Hari Libur / Bukan Hari Sholat';

            $html = <<<HTML
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-sm">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                        🏖️ Status: {$holidayDesc}
                    </span>
                </div>
                <button
                    disabled
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                >
                    📤 Kirim Notifikasi Sholat Dhuhur Sekarang
                </button>
                <p class="text-xs text-amber-600 dark:text-amber-400">⛔ Hari ini bukan hari pelaksanaan sholat dhuhur. Pengiriman notifikasi dinonaktifkan demi mencegah terkirimnya pesan di hari libur.</p>
            </div>
            HTML;
            return new HtmlString($html);
        }

        if ($canSend) {
            $badgeText = $sendCount > 0
                ? "Terkirim {$sendCount} dari 5x hari ini"
                : "Belum dikirim hari ini (Maks 5x)";

            $html = <<<HTML
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                    <span>📅</span>
                    <span>Tanggal hari ini: <strong>{$todayDate}</strong></span>
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                        {$badgeText}
                    </span>
                </div>
                <button
                    wire:click="confirmSendSholatDhuhurManual"
                    wire:loading.attr="disabled"
                    wire:confirm="Yakin ingin mengirim notifikasi WA ke orang tua dari semua siswa yang belum presensi sholat dhuhur sekarang? (Pengiriman ke-{$nextCount} dari maksimal 5x hari ini)"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-primary-600 hover:bg-primary-700 text-white transition disabled:opacity-50 shadow-sm"
                >
                    <span wire:loading.remove wire:target="confirmSendSholatDhuhurManual">📤 Kirim Notifikasi Sholat Dhuhur Sekarang ({$sendCount}/5)</span>
                    <span wire:loading wire:target="confirmSendSholatDhuhurManual">⏳ Memproses...</span>
                </button>
                <p class="text-xs text-gray-500 dark:text-gray-500">ℹ️ Notifikasi akan dikirim ke nomor HP orang tua dari siswa Muslim yang belum tercatat presensi sholat dhuhur hari ini. Tombol ini bisa digunakan maksimal 5x per hari.</p>
            </div>
            HTML;
        } else {
            $html = <<<HTML
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-sm">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                        ✅ Batas pengiriman tercapai: 5 dari 5x hari ini ({$todayDate})
                    </span>
                </div>
                <button
                    disabled
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                >
                    📤 Kirim Notifikasi Sholat Dhuhur Sekarang (5/5)
                </button>
                <p class="text-xs text-gray-500 dark:text-gray-500">⛔ Pengiriman manual sudah mencapai batas maksimal 5x hari ini. Tersedia kembali besok.</p>
            </div>
            HTML;
        }

        return new HtmlString($html);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Livewire Actions
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Test jalankan scheduler heartbeat via tombol di UI.
     */
    public function testSchedulerRun(): void
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('scheduler:heartbeat');
            $this->checkSchedulerStatus();
            Notification::make()
                ->title('Scheduler berhasil dijalankan manual')
                ->body('Heartbeat telah diperbarui. Jika ini berjalan, berarti PHP & Artisan bisa dieksekusi dari server.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal menjalankan scheduler')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Kirim laporan harian secara manual (dipanggil setelah konfirmasi wire:confirm).
     */
    public function confirmSendDailyManual(): void
    {
        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        if (!$kalenderService->isHariSekolah(now('Asia/Jakarta'))) {
            Notification::make()
                ->title('Hari Libur Sekolah')
                ->body('Hari ini adalah hari libur sekolah. Pengiriman laporan harian tidak dapat dilakukan.')
                ->warning()
                ->send();
            return;
        }

        $setting = PresensiDailyReportSetting::current();

        if (!$setting->canSendManualToday()) {
            Notification::make()
                ->title('Batas pengiriman tercapai')
                ->body('Pengiriman manual laporan harian sudah mencapai batas maksimal 5x per hari.')
                ->warning()
                ->send();
            return;
        }

        try {
            /** @var DailyClassReportService $service */
            $service = app(DailyClassReportService::class);
            $result  = $service->dispatch(isManual: true);

            $setting->recordManualSend();
            $this->canSendDailyManual = $setting->fresh()->canSendManualToday();

            if ($result['dispatched'] > 0) {
                Notification::make()
                    ->title('Laporan harian berhasil dikirim')
                    ->body("Total {$result['dispatched']} job berhasil di-dispatch ke queue.")
                    ->success()
                    ->send();
            } else {
                $errorMsg = !empty($result['errors']) ? implode(', ', $result['errors']) : 'Tidak ada penerima valid.';
                Notification::make()
                    ->title('Tidak ada laporan yang dikirim')
                    ->body($errorMsg)
                    ->warning()
                    ->send();
            }
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal mengirim laporan harian')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Kirim rekap sekolah secara manual (dipanggil setelah konfirmasi wire:confirm).
     */
    public function confirmSendSchoolManual(): void
    {
        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        if (!$kalenderService->isHariSekolah(now('Asia/Jakarta'))) {
            Notification::make()
                ->title('Hari Libur Sekolah')
                ->body('Hari ini adalah hari libur sekolah. Pengiriman rekap presensi seluruh sekolah tidak dapat dilakukan.')
                ->warning()
                ->send();
            return;
        }

        $setting = PresensiSchoolSummarySetting::current();

        if (!$setting->canSendManualToday()) {
            Notification::make()
                ->title('Batas pengiriman tercapai')
                ->body('Pengiriman manual rekap sekolah sudah mencapai batas maksimal 5x per hari.')
                ->warning()
                ->send();
            return;
        }

        try {
            /** @var SchoolSummaryReportService $service */
            $service = app(SchoolSummaryReportService::class);
            $result  = $service->dispatch(isManual: true);

            $setting->recordManualSend();
            $this->canSendSchoolManual = $setting->fresh()->canSendManualToday();

            if ($result['dispatched'] > 0) {
                Notification::make()
                    ->title('Rekap sekolah berhasil dikirim')
                    ->body("Total {$result['dispatched']} job berhasil di-dispatch ke queue.")
                    ->success()
                    ->send();
            } else {
                $errorMsg = !empty($result['errors']) ? implode(', ', $result['errors']) : 'Tidak ada penerima valid.';
                Notification::make()
                    ->title('Tidak ada rekap yang dikirim')
                    ->body($errorMsg)
                    ->warning()
                    ->send();
            }
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal mengirim rekap sekolah')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Kirim notifikasi siswa belum hadir secara manual (dipanggil setelah konfirmasi wire:confirm).
     */
    public function confirmSendBelumManual(): void
    {
        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        if (!$kalenderService->isHariSekolah(now('Asia/Jakarta'))) {
            Notification::make()
                ->title('Hari Libur Sekolah')
                ->body('Hari ini adalah hari libur sekolah. Pengiriman notifikasi belum hadir tidak dapat dilakukan.')
                ->warning()
                ->send();
            return;
        }

        $setting = \App\Models\PresensiNotificationSetting::where('status_presensi', 'belum')->first();

        if ($setting && !$setting->canSendManualToday()) {
            Notification::make()
                ->title('Batas pengiriman tercapai')
                ->body('Pengiriman manual notifikasi belum hadir sudah mencapai batas maksimal 5x per hari.')
                ->warning()
                ->send();
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
                Notification::make()
                    ->title('Notifikasi Belum Hadir Berhasil Dikirim')
                    ->body("Total {$result['dispatched']} pesan WA berhasil diantrekan (Siswa belum hadir: {$result['total_unattended']}).")
                    ->success()
                    ->send();
            } else {
                $msg = $result['message'] ?? 'Tidak ada notifikasi yang dikirim atau semua orang tua sudah menerima notifikasi hari ini.';
                Notification::make()
                    ->title('Tidak ada notifikasi yang dikirim')
                    ->body($msg)
                    ->info()
                    ->send();
            }
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal mengirim notifikasi')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function confirmSendSholatDhuhurManual(): void
    {
        $sholatService = app(\App\Services\PresensiSholatDhuhurService::class);
        if (!$sholatService->isHariSholatDhuhur(now('Asia/Jakarta'))) {
            Notification::make()
                ->title('Bukan Hari Sholat Dhuhur')
                ->body('Hari ini bukan hari pelaksanaan sholat dhuhur. Pengiriman notifikasi tidak dapat dilakukan.')
                ->warning()
                ->send();
            return;
        }

        $setting = \App\Models\PresensiNotificationSetting::where('status_presensi', 'sholat_dhuhur')->first();

        if ($setting && !$setting->canSendManualToday()) {
            Notification::make()
                ->title('Batas pengiriman tercapai')
                ->body('Pengiriman manual notifikasi sholat dhuhur sudah mencapai batas maksimal 5x per hari.')
                ->warning()
                ->send();
            return;
        }

        try {
            /** @var \App\Services\UnattendedSholatDhuhurNotificationService $service */
            $service = app(\App\Services\UnattendedSholatDhuhurNotificationService::class);
            $result  = $service->dispatch(source: 'manual');

            if ($setting) {
                $setting->recordManualSend();
            }
            $this->refreshManualSendStatus();

            if ($result['dispatched'] > 0) {
                Notification::make()
                    ->title('Notifikasi Sholat Dhuhur Berhasil Dikirim')
                    ->body("Total {$result['dispatched']} pesan WA berhasil diantrekan (Siswa belum sholat: {$result['total_unattended']}).")
                    ->success()
                    ->send();
            } else {
                $msg = $result['message'] ?? 'Tidak ada notifikasi yang dikirim atau semua orang tua sudah menerima notifikasi hari ini.';
                Notification::make()
                    ->title('Tidak ada notifikasi yang dikirim')
                    ->body($msg)
                    ->info()
                    ->send();
            }
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal mengirim notifikasi')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Save
    // ─────────────────────────────────────────────────────────────────────────

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            // Simpan Aturan Notifikasi (Sakit / Izin / Alpa)
            $exceptionStatuses = ['alpa', 'sakit', 'izin'];
            foreach ($exceptionStatuses as $status) {
                \App\Models\PresensiNotificationSetting::updateOrCreate(
                    ['status_presensi' => $status],
                    [
                        'is_active'      => $data['student_notif_is_active'] ?? false,
                        'recipients'     => $data['student_notif_recipients'] ?? [],
                        'template_pesan' => $data['student_notif_template_pesan'] ?? '',
                    ]
                );
            }

            // Simpan Aturan Notifikasi (Telat)
            \App\Models\PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'telat'],
                [
                    'is_active'      => $data['telat_notif_is_active'] ?? false,
                    'recipients'     => $data['telat_notif_recipients'] ?? [],
                    'template_pesan' => $data['telat_notif_template_pesan'] ?? '',
                ]
            );

            // Simpan Aturan Notifikasi (Belum Presensi Pagi)
            \App\Models\PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'belum'],
                [
                    'is_active'      => $data['belum_notif_is_active'] ?? false,
                    'is_auto_send'   => $data['belum_notif_is_auto_send'] ?? false,
                    'auto_send_time' => $data['belum_notif_auto_send_time'] ?? '07:30:00',
                    'recipients'     => $data['belum_notif_recipients'] ?? [],
                    'template_pesan' => $data['belum_notif_template_pesan'] ?? '',
                ]
            );

            // Simpan Aturan Notifikasi (Sholat Dhuhur)
            \App\Models\PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'sholat_dhuhur'],
                [
                    'is_active'      => $data['sholat_dhuhur_notif_is_active'] ?? false,
                    'is_auto_send'   => $data['sholat_dhuhur_notif_is_auto_send'] ?? false,
                    'auto_send_time' => $data['sholat_dhuhur_notif_auto_send_time'] ?? '13:00:00',
                    'recipients'     => $data['sholat_dhuhur_notif_recipients'] ?? [],
                    'template_pesan' => $data['sholat_dhuhur_notif_template_pesan'] ?? '',
                ]
            );

            // Simpan Aturan Notifikasi (Pengajuan Ijin)
            \App\Models\PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'leave_request'],
                [
                    'is_active'      => $data['leave_request_notif_is_active'] ?? false,
                    'recipients'     => $data['leave_request_notif_recipients'] ?? [],
                    'template_pesan' => $data['leave_request_notif_template_pesan'] ?? '',
                ]
            );

            // Simpan Aturan Notifikasi (Persetujuan Ijin)
            \App\Models\PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'leave_approval'],
                [
                    'is_active'      => $data['leave_approval_notif_is_active'] ?? false,
                    'recipients'     => $data['leave_approval_notif_recipients'] ?? [],
                    'template_pesan' => $data['leave_approval_notif_template_pesan'] ?? '',
                ]
            );

            // Simpan Laporan Harian
            $daily = PresensiDailyReportSetting::current();
            $daily->update([
                'is_active'     => $data['daily_is_active'] ?? false,
                'cutoff_time'   => $data['daily_cutoff_time'] ?? '08:00:00',
                'recipients'    => $data['daily_recipients'] ?? [],
                'template_pesan'=> $data['daily_template_pesan'] ?? '',
            ]);

            // Simpan Rekap Seluruh Sekolah
            $school = PresensiSchoolSummarySetting::current();
            $school->update([
                'is_active'       => $data['school_is_active'] ?? false,
                'cutoff_time'     => $data['school_cutoff_time'] ?? '08:15:00',
                'recipients'      => $data['school_recipients'] ?? [],
                'template_header' => $data['school_template_header'] ?? '',
                'template_row'    => $data['school_template_row'] ?? '',
                'template_footer' => $data['school_template_footer'] ?? '',
            ]);

            Notification::make()
                ->title('Pengaturan berhasil disimpan')
                ->success()
                ->send();
        } catch (Halt $exception) {
            return;
        }
    }
}

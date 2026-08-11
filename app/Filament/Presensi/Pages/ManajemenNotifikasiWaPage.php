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
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected string $view = 'filament.pages.manajemen-notifikasi-wa';

    protected static ?string $navigationLabel = 'Manajemen Notifikasi WA';

    protected static ?string $slug = 'manajemen-notifikasi-wa';

    protected static ?string $title = 'Manajemen Notifikasi WA';

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';

    public ?array $data = [];

    // Status scheduler
    public bool   $schedulerActive   = false;
    public string $schedulerLastRun  = '';
    public string $schedulerAgeLabel = '';

    // Status kirim manual per tab
    public bool $canSendDailyManual  = true;
    public bool $canSendSchoolManual = true;

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->checkSchedulerStatus();
        $this->refreshManualSendStatus();

        $data = [];

        // Load data Presensi Notification Settings
        $telatSetting = \App\Models\PresensiNotificationSetting::where('status_presensi', 'telat')->first();
        if ($telatSetting) {
            $data['student_notif_is_active']      = $telatSetting->is_active;
            $data['student_notif_recipients']     = $telatSetting->recipients ?? ['ortu'];
            $data['student_notif_template_pesan'] = $telatSetting->template_pesan;
        } else {
            $data['student_notif_is_active']      = false;
            $data['student_notif_recipients']     = ['ortu'];
            $data['student_notif_template_pesan'] = "Halo Orang Tua/Wali,\nKami informasikan bahwa ananda {nama_siswa} (Kelas {kelas}) pada tanggal {tanggal} pukul {jam} tercatat berstatus {status_kehadiran}.\n\nSalam,\n{nama_sekolah}";
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
        $this->canSendDailyManual  = PresensiDailyReportSetting::current()->canSendManualToday();
        $this->canSendSchoolManual = PresensiSchoolSummarySetting::current()->canSendManualToday();
    }

    public function form(Schema $schema): Schema
    {
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
                                Section::make('Aturan Otomatis Notifikasi Pengecualian')
                                    ->description('Sistem akan otomatis mendeteksi ketika siswa berstatus Telat, Sakit, Izin, atau Alpa dan mengirimkan pesan WA sesuai template di bawah ini.')
                                    ->schema([
                                        Toggle::make('student_notif_is_active')
                                            ->label('Aktifkan Notifikasi Kehadiran (Telat / Sakit / Izin / Alpa)')
                                            ->columnSpanFull(),
                                        Select::make('student_notif_recipients')
                                            ->label('Kirim Ke (Penerima)')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Pilih satu atau beberapa penerima...')
                                            ->options(function () {
                                                $options  = [
                                                    'ortu'       => 'Orang Tua',
                                                    'wali_kelas' => 'Wali Kelas',
                                                ];
                                                $jabatans = \App\Models\Jabatan::pluck('nama_jabatan', 'nama_jabatan')->toArray();
                                                return array_merge($options, $jabatans);
                                            })
                                            ->columnSpanFull(),
                                        Textarea::make('student_notif_template_pesan')
                                            ->label('Template Pesan Otomatis')
                                            ->rows(5)
                                            ->helperText('Placeholder {status_kehadiran} akan otomatis terisi secara dinamis dengan kata: Telat, Sakit, Izin, atau Alpa. Placeholder lain: {nama_siswa}, {kelas}, {tanggal}, {jam}, {nama_wali_kelas}, {nama_sekolah}')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // ── Tab 2: Laporan Harian Kelas ───────────────────
                        Tab::make('Laporan Harian Kelas')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Section::make('Pengaturan Laporan Harian Wali Kelas')
                                    ->description('Laporan harian dikirim otomatis oleh sistem sesuai jam cutoff. Toleransi pengiriman: 1 jam setelah jam cutoff.')
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
                                            ->options(function () {
                                                $options  = ['wali_kelas' => 'Wali Kelas'];
                                                $jabatans = \App\Models\Jabatan::pluck('nama_jabatan', 'nama_jabatan')->toArray();
                                                return array_merge($options, $jabatans);
                                            })
                                            ->columnSpanFull(),
                                        Textarea::make('daily_template_pesan')
                                            ->label('Template Pesan')
                                            ->rows(6)
                                            ->helperText('Placeholder tersedia: {nama_kelas}, {tanggal}, {total_siswa}, {jumlah_hadir}, {jumlah_terlambat}, {jumlah_alpa}, {jumlah_sakit}, {jumlah_izin}, {daftar_belum_presensi}')
                                            ->columnSpanFull(),
                                    ]),

                                // ── Kirim Manual ──────────────────────────
                                Section::make('Kirim Manual Laporan Harian')
                                    ->icon('heroicon-o-paper-airplane')
                                    ->description('Kirim laporan harian hari ini secara manual. Maksimal 1x per hari.')
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
                                    ->description('Rekap presensi seluruh kelas sekaligus (Helicopter View) untuk Manajemen Sekolah. Toleransi pengiriman: 1 jam setelah jam cutoff.')
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
                                            ->options(function () {
                                                return \App\Models\Jabatan::pluck('nama_jabatan', 'nama_jabatan')->toArray();
                                            })
                                            ->columnSpanFull(),
                                        Textarea::make('school_template_header')
                                            ->label('Template Header')
                                            ->rows(3)
                                            ->helperText('Placeholder: {nama_sekolah}, {hari}, {tanggal}')
                                            ->columnSpanFull(),
                                        Textarea::make('school_template_row')
                                            ->label('Template Baris per Kelas')
                                            ->rows(3)
                                            ->helperText('Diulang untuk setiap kelas. Placeholder: {nama_kelas}, {jumlah_hadir}, {jumlah_terlambat}, {nama_terlambat}, {jumlah_sakit}, {nama_sakit}, {jumlah_izin}, {nama_izin}, {jumlah_alpa}, {nama_alpa}, {jumlah_belum_presensi}, {nama_belum_presensi}')
                                            ->columnSpanFull(),
                                        Textarea::make('school_template_footer')
                                            ->label('Template Footer')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),

                                // ── Kirim Manual ──────────────────────────
                                Section::make('Kirim Manual Rekap Sekolah')
                                    ->icon('heroicon-o-paper-airplane')
                                    ->description('Kirim rekap seluruh sekolah hari ini secara manual. Maksimal 1x per hari.')
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
        if ($this->schedulerActive) {
            $html = <<<HTML
            <div class="rounded-xl border border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-700 p-4 flex items-start gap-3">
                <span class="text-2xl mt-0.5">🟢</span>
                <div class="flex-1">
                    <p class="font-semibold text-green-800 dark:text-green-300">Scheduler Aktif</p>
                    <p class="text-sm text-green-700 dark:text-green-400 mt-0.5">
                        Terakhir berjalan: <strong>{$this->schedulerLastRun}</strong> ({$this->schedulerAgeLabel})
                    </p>
                    <p class="text-sm text-green-600 dark:text-green-500 mt-1">✅ Laporan otomatis akan dikirim sesuai jadwal.</p>
                </div>
            </div>
            HTML;
        } else {
            $cronCommand = htmlspecialchars('/usr/bin/php8.3 ' . base_path('artisan') . ' schedule:run >> /dev/null 2>&1');
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
        $setting   = PresensiDailyReportSetting::current();
        $canSend   = $setting->canSendManualToday();
        $todayDate = now()->format('d M Y');

        if ($canSend) {
            $html = <<<HTML
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                    <span>📅</span>
                    <span>Tanggal hari ini: <strong>{$todayDate}</strong></span>
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                        Belum dikirim manual hari ini
                    </span>
                </div>
                <button
                    wire:click="confirmSendDailyManual"
                    wire:loading.attr="disabled"
                    wire:confirm="Yakin ingin kirim laporan harian presensi ke semua wali kelas sekarang? Tindakan ini hanya bisa dilakukan 1x per hari."
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-primary-600 hover:bg-primary-700 text-white transition disabled:opacity-50 shadow-sm"
                >
                    <span wire:loading.remove wire:target="confirmSendDailyManual">📤 Kirim Laporan Harian Sekarang</span>
                    <span wire:loading wire:target="confirmSendDailyManual">⏳ Memproses...</span>
                </button>
                <p class="text-xs text-gray-500 dark:text-gray-500">ℹ️ Laporan akan dikirim ke seluruh wali kelas yang memiliki nomor HP terdaftar. Tombol ini hanya bisa digunakan 1x per hari.</p>
            </div>
            HTML;
        } else {
            $html = <<<HTML
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-sm">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                        ✅ Sudah dikirim manual hari ini ({$todayDate})
                    </span>
                </div>
                <button
                    disabled
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                >
                    📤 Kirim Laporan Harian Sekarang
                </button>
                <p class="text-xs text-gray-500 dark:text-gray-500">⛔ Pengiriman manual sudah dilakukan hari ini. Tersedia kembali besok.</p>
            </div>
            HTML;
        }

        return new HtmlString($html);
    }

    private function buildSchoolManualStatusHtml(): HtmlString
    {
        $setting   = PresensiSchoolSummarySetting::current();
        $canSend   = $setting->canSendManualToday();
        $todayDate = now()->format('d M Y');

        if ($canSend) {
            $html = <<<HTML
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                    <span>📅</span>
                    <span>Tanggal hari ini: <strong>{$todayDate}</strong></span>
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                        Belum dikirim manual hari ini
                    </span>
                </div>
                <button
                    wire:click="confirmSendSchoolManual"
                    wire:loading.attr="disabled"
                    wire:confirm="Yakin ingin kirim rekap presensi seluruh sekolah sekarang? Tindakan ini hanya bisa dilakukan 1x per hari."
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-primary-600 hover:bg-primary-700 text-white transition disabled:opacity-50 shadow-sm"
                >
                    <span wire:loading.remove wire:target="confirmSendSchoolManual">📤 Kirim Rekap Sekolah Sekarang</span>
                    <span wire:loading wire:target="confirmSendSchoolManual">⏳ Memproses...</span>
                </button>
                <p class="text-xs text-gray-500 dark:text-gray-500">ℹ️ Rekap akan dikirim ke seluruh penerima yang dipilih. Tombol ini hanya bisa digunakan 1x per hari.</p>
            </div>
            HTML;
        } else {
            $html = <<<HTML
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-sm">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                        ✅ Sudah dikirim manual hari ini ({$todayDate})
                    </span>
                </div>
                <button
                    disabled
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                >
                    📤 Kirim Rekap Sekolah Sekarang
                </button>
                <p class="text-xs text-gray-500 dark:text-gray-500">⛔ Pengiriman manual sudah dilakukan hari ini. Tersedia kembali besok.</p>
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
        $setting = PresensiDailyReportSetting::current();

        if (!$setting->canSendManualToday()) {
            Notification::make()
                ->title('Sudah dikirim hari ini')
                ->body('Pengiriman manual laporan harian hanya bisa dilakukan 1x per hari.')
                ->warning()
                ->send();
            return;
        }

        try {
            /** @var DailyClassReportService $service */
            $service = app(DailyClassReportService::class);
            $result  = $service->dispatch(isManual: true);

            $setting->recordManualSend();
            $this->canSendDailyManual = false;

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
        $setting = PresensiSchoolSummarySetting::current();

        if (!$setting->canSendManualToday()) {
            Notification::make()
                ->title('Sudah dikirim hari ini')
                ->body('Pengiriman manual rekap sekolah hanya bisa dilakukan 1x per hari.')
                ->warning()
                ->send();
            return;
        }

        try {
            /** @var SchoolSummaryReportService $service */
            $service = app(SchoolSummaryReportService::class);
            $result  = $service->dispatch();

            $setting->recordManualSend();
            $this->canSendSchoolManual = false;

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

    // ─────────────────────────────────────────────────────────────────────────
    // Save
    // ─────────────────────────────────────────────────────────────────────────

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            // Simpan Aturan Notifikasi Terpadu
            $exceptionStatuses = ['telat', 'alpa', 'sakit', 'izin'];
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

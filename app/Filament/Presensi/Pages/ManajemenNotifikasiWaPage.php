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

class ManajemenNotifikasiWaPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected string $view = 'filament.pages.school-settings';

    protected static ?string $navigationLabel = 'Manajemen Notifikasi WA';

    protected static ?string $slug = 'manajemen-notifikasi-wa';

    protected static ?string $title = 'Manajemen Notifikasi WA';

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function mount(): void
    {
        $data = [];

        // Load data Presensi Notification Settings (Single Unified Form for Telat, Alpa, Sakit, Izin)
        $telatSetting = \App\Models\PresensiNotificationSetting::where('status_presensi', 'telat')->first();
        if ($telatSetting) {
            $data['student_notif_is_active'] = $telatSetting->is_active;
            $data['student_notif_recipients'] = $telatSetting->recipients ?? ['ortu'];
            $data['student_notif_template_pesan'] = $telatSetting->template_pesan;
        } else {
            $data['student_notif_is_active'] = false;
            $data['student_notif_recipients'] = ['ortu'];
            $data['student_notif_template_pesan'] = "Halo Orang Tua/Wali,\nKami informasikan bahwa ananda {nama_siswa} (Kelas {kelas}) pada tanggal {tanggal} pukul {jam} tercatat berstatus {status_kehadiran}.\n\nSalam,\n{nama_sekolah}";
        }

        // Load data Laporan Harian
        $daily = \App\Models\PresensiDailyReportSetting::current();
        $data['daily_is_active'] = $daily->is_active;
        $data['daily_cutoff_time'] = $daily->cutoff_time;
        $data['daily_recipients'] = $daily->recipients ?? [];
        $data['daily_template_pesan'] = $daily->template_pesan;

        // Load data Rekap Seluruh Sekolah
        $school = \App\Models\PresensiSchoolSummarySetting::current();
        $data['school_is_active'] = $school->is_active;
        $data['school_cutoff_time'] = $school->cutoff_time;
        $data['school_recipients'] = $school->recipients ?? [];
        $data['school_template_header'] = $school->template_header;
        $data['school_template_row'] = $school->template_row;
        $data['school_template_footer'] = $school->template_footer;

        $this->form->fill($data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Manajemen Notifikasi WA')
                    ->tabs([
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
                                                $options = [
                                                    'ortu' => 'Orang Tua',
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

                        Tab::make('Laporan Harian Kelas')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Section::make('Pengaturan Laporan Harian Wali Kelas')
                                    ->description('Pengaturan pengiriman rekap harian presensi per kelas beserta daftar siswa yang belum presensi.')
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
                                                $options = [
                                                    'wali_kelas' => 'Wali Kelas',
                                                ];
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
                            ]),

                        Tab::make('Rekap Seluruh Sekolah')
                            ->icon('heroicon-o-building-office-2')
                            ->schema([
                                Section::make('Pengaturan Rekap Seluruh Sekolah')
                                    ->description('Pengaturan pengiriman rekap presensi seluruh kelas sekaligus (Helicopter View) untuk Manajemen Sekolah.')
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
                            ]),
                    ])
                    ->columnSpanFull(),

                \Filament\Forms\Components\Placeholder::make('evolution_api_disclaimer')
                    ->hiddenLabel()
                    ->content(new \Illuminate\Support\HtmlString('
                        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400 space-y-1">
                            <p class="font-semibold text-gray-700 dark:text-gray-300">ℹ️ Pemberitahuan Lisensi & Sanggahan (Attribution & Trademark Notice):</p>
                            <p>• Layanan notifikasi ini didukung oleh infrastruktur open-source <strong>Evolution API</strong> (Apache License 2.0 / Evolution Foundation).</p>
                            <p>• <em>Disclaimer:</em> Sistem ERP ini merupakan perangkat lunak independen dan tidak berafiliasi, diawasi, atau didukung secara resmi oleh WhatsApp maupun Meta Platforms, Inc. WhatsApp® adalah merek dagang terdaftar milik Meta Platforms, Inc.</p>
                        </div>
                    '))
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            // Simpan Aturan Notifikasi Terpadu (Satu pengaturan untuk telat, alpa, sakit, izin)
            $exceptionStatuses = ['telat', 'alpa', 'sakit', 'izin'];
            foreach ($exceptionStatuses as $status) {
                \App\Models\PresensiNotificationSetting::where('status_presensi', $status)
                    ->update([
                        'is_active' => $data['student_notif_is_active'] ?? false,
                        'recipients' => $data['student_notif_recipients'] ?? [],
                        'template_pesan' => $data['student_notif_template_pesan'] ?? '',
                    ]);
            }

            // Simpan Laporan Harian
            $daily = \App\Models\PresensiDailyReportSetting::current();
            $daily->update([
                'is_active' => $data['daily_is_active'] ?? false,
                'cutoff_time' => $data['daily_cutoff_time'] ?? '08:00:00',
                'recipients' => $data['daily_recipients'] ?? [],
                'template_pesan' => $data['daily_template_pesan'] ?? '',
            ]);

            // Simpan Rekap Seluruh Sekolah
            $school = \App\Models\PresensiSchoolSummarySetting::current();
            $school->update([
                'is_active' => $data['school_is_active'] ?? false,
                'cutoff_time' => $data['school_cutoff_time'] ?? '08:15:00',
                'recipients' => $data['school_recipients'] ?? [],
                'template_header' => $data['school_template_header'] ?? '',
                'template_row' => $data['school_template_row'] ?? '',
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

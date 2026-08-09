<?php

namespace App\Filament\Presensi\Pages;

use App\Models\PengaturanSekolah;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class PengaturanPresensiPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'filament.pages.school-settings';

    protected static ?string $navigationLabel = 'Pengaturan Presensi';

    protected static ?string $slug = 'pengaturan-presensi';

    protected static ?string $title = 'Pengaturan Presensi';

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';

    public ?array $data = [];

    /**
     * Hanya Super Admin yang bisa mengakses halaman pengaturan.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function mount(): void
    {
        // Load data singleton pengaturan sekolah
        $settings = PengaturanSekolah::current();
        $data = $settings ? $settings->toArray() : [];

        // Load data WhatsApp Settings (Koneksi API saja)
        $wa = \App\Models\WhatsAppSetting::current();
        $data['wa_is_active'] = $wa->is_active;
        $data['wa_base_url'] = $wa->base_url;
        $data['wa_api_key'] = $wa->api_key;
        $data['wa_instance_name'] = $wa->instance_name;
        $data['wa_sender_number'] = $wa->sender_number;
        $data['wa_delay_between_messages_seconds'] = $wa->delay_between_messages_seconds;
        $data['wa_send_window_start'] = $wa->send_window_start;
        $data['wa_send_window_end'] = $wa->send_window_end;

        $this->form->fill($data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Waktu Kehadiran')
                    ->schema([
                        TimePicker::make('checkin_time')
                            ->label('Jam Masuk (Check-in)')
                            ->required()
                            ->seconds(false)
                            ->helperText('Jam normal siswa masuk sekolah.'),

                        TextInput::make('late_threshold_minutes')
                            ->label('Batas Toleransi Terlambat (Menit)')
                            ->numeric()
                            ->default(15)
                            ->required()
                            ->helperText('Jumlah menit toleransi setelah jam masuk.'),

                        TimePicker::make('batas_scan_datang_time')
                            ->label('Batas Jam Absen Datang (Kiosk)')
                            ->required()
                            ->seconds(false)
                            ->helperText('Maksimal jam siswa bisa absen datang lewat mesin scanner. Lewat dari ini ditolak otomatis.'),

                        TimePicker::make('start_scan_out_time')
                            ->label('Jam Mulai Absen Pulang (Kiosk)')
                            ->required()
                            ->seconds(false)
                            ->helperText('Mesin scanner akan menganggap tap kartu setelah jam ini sebagai absen pulang.'),

                        Select::make('barcode_scan_mode')
                            ->label('Mode Kios Scanner Barcode')
                            ->options([
                                'nisn' => 'Gunakan NISN (Default)',
                                'nis' => 'Gunakan NIS',
                            ])
                            ->default('nisn')
                            ->required()
                            ->helperText('Menentukan jenis barcode yang akan dipindai oleh mesin presensi.'),
                    ])->columns(2),

                Section::make('Koneksi API WhatsApp (Evolution API)')
                    ->schema([
                        Toggle::make('wa_is_active')
                            ->label('Aktifkan Notifikasi WhatsApp')
                            ->reactive(),
                        TextInput::make('wa_base_url')
                            ->hidden()
                            ->dehydrated(false),
                        TextInput::make('wa_api_key')
                            ->hidden()
                            ->dehydrated(false),
                        \Filament\Forms\Components\Placeholder::make('wa_env_credentials_info')
                            ->hiddenLabel()
                            ->content(new \Illuminate\Support\HtmlString('
                                <div class="p-3 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-700 dark:text-emerald-300 flex items-center gap-2">
                                    <span class="text-base">🔒</span>
                                    <div><strong>Kredensial Terproteksi:</strong> Base URL & API Key Evolution API disimpan secara rahasia via file <code>.env</code> server untuk keamanan tingkat tinggi.</div>
                                </div>
                            '))
                            ->columnSpanFull(),
                        TextInput::make('wa_instance_name')
                            ->label('Nama Instance/Session'),
                        TextInput::make('wa_sender_number')
                            ->label('Nomor Pengirim')
                            ->disabled()
                            ->helperText('Nomor sender yang terkoneksi di Evolution API (readonly).'),
                        TextInput::make('wa_delay_between_messages_seconds')
                            ->label('Jeda antar pesan (detik)')
                            ->numeric()
                            ->default(4),
                        TimePicker::make('wa_send_window_start')
                            ->label('Jam mulai pengiriman (Send Window)'),
                        TimePicker::make('wa_send_window_end')
                            ->label('Jam batas pengiriman (Send Window)'),
                        \Filament\Schemas\Components\Actions::make([
                            \Filament\Actions\Action::make('test_connection')
                                ->label('Test Koneksi')
                                ->icon('heroicon-o-signal')
                                ->color('info')
                                ->action(function (\Filament\Schemas\Components\Utilities\Get $get, \Filament\Schemas\Components\Utilities\Set $set) {
                                    $waSetting = \App\Models\WhatsAppSetting::current();
                                    $baseUrl = $waSetting->base_url;
                                    $apiKey = $waSetting->api_key;
                                    $instanceName = $get('wa_instance_name') ?: $waSetting->instance_name;
                                    
                                    if (!$baseUrl || !$apiKey || !$instanceName) {
                                        Notification::make()
                                            ->title('Harap konfirmasi Base URL/API Key di .env dan Nama Instance terlebih dahulu.')
                                            ->danger()
                                            ->send();
                                        return;
                                    }

                                    try {
                                        $endpoint = rtrim($baseUrl, '/') . '/instance/connectionState/' . $instanceName;
                                        $response = \Illuminate\Support\Facades\Http::withHeaders([
                                            'apikey' => $apiKey
                                        ])->timeout(5)->get($endpoint);

                                        if ($response->successful()) {
                                            $data = $response->json();
                                            $state = $data['instance']['state'] ?? 'unknown';
                                            
                                            if ($state === 'open') {
                                                Notification::make()
                                                    ->title('Koneksi Berhasil! WhatsApp siap digunakan.')
                                                    ->success()
                                                    ->send();
                                            } else {
                                                Notification::make()
                                                    ->title('Instance ditemukan, tapi status: ' . strtoupper($state) . '. Silakan pastikan sudah Scan QR.')
                                                    ->warning()
                                                    ->send();
                                            }
                                        } else {
                                            $errorMsg = $response->json('message') ?? $response->body();
                                            Notification::make()
                                                ->title('Gagal terhubung (Status ' . $response->status() . ')')
                                                ->body(substr($errorMsg, 0, 100))
                                                ->danger()
                                                ->send();
                                        }
                                    } catch (\Exception $e) {
                                        Notification::make()
                                            ->title('Koneksi Error')
                                            ->body($e->getMessage())
                                            ->danger()
                                            ->send();
                                    }
                                })
                        ]),
                        \Filament\Forms\Components\Placeholder::make('evolution_api_disclaimer')
                                    ->hiddenLabel()
                                    ->content(new \Illuminate\Support\HtmlString('
                                        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400 space-y-1">
                                            <p class="font-semibold text-gray-700 dark:text-gray-300">ℹ️ Pemberitahuan Lisensi & Sanggahan (Attribution & Trademark Notice):</p>
                                            <p>• Infrastruktur gateway pengiriman pesan sistem ini didukung oleh layanan open-source <strong>Evolution API</strong> (Apache License 2.0 / Evolution Foundation).</p>
                                            <p>• <em>Disclaimer:</em> Sistem ERP ini merupakan perangkat lunak independen dan tidak berafiliasi, diawasi, atau didukung secara resmi oleh WhatsApp maupun Meta Platforms, Inc. WhatsApp® adalah merek dagang terdaftar milik Meta Platforms, Inc.</p>
                                        </div>
                                    '))
                                    ->columnSpanFull()
                            ])->columns(2),

            ])
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            // Simpan PengaturanSekolah
            $settings = PengaturanSekolah::first();
            if ($settings) {
                $settings->update($data);
            } else {
                PengaturanSekolah::create($data);
            }
            \Illuminate\Support\Facades\Cache::forget('public_pengaturan_sekolah');

            // Simpan WhatsAppSetting (Koneksi API)
            $wa = \App\Models\WhatsAppSetting::current();
            $wa->update([
                'is_active' => $data['wa_is_active'] ?? false,
                'base_url' => $data['wa_base_url'] ?? null,
                'api_key' => $data['wa_api_key'] ?? null,
                'instance_name' => $data['wa_instance_name'] ?? null,
                'delay_between_messages_seconds' => $data['wa_delay_between_messages_seconds'] ?? 4,
                'send_window_start' => $data['wa_send_window_start'] ?? null,
                'send_window_end' => $data['wa_send_window_end'] ?? null,
            ]);

            Notification::make()
                ->title('Pengaturan presensi berhasil disimpan')
                ->success()
                ->send();
        } catch (Halt $exception) {
            return;
        }
    }
}

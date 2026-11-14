<?php

namespace App\Filament\Sistem\Pages;

use App\Models\WhatsAppSetting;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\HtmlString;

class SistemKoneksiWaPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-signal';

    protected string $view = 'filament.pages.manajemen-notifikasi-wa';

    protected static ?string $navigationLabel = 'Pengaturan Koneksi WA';

    protected static ?string $slug = 'koneksi-wa';

    protected static ?string $title = 'Pengaturan Koneksi Gateway WhatsApp';

    protected ?string $subheading = 'Konfigurasi koneksi server Evolution API WhatsApp, jeda pengiriman, jam operasional (send window), dan status scheduler.';

    protected static string|\UnitEnum|null $navigationGroup = 'Gateway & Pengaturan Modul';

    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public bool $schedulerActive = false;
    public string $schedulerLastRun = '';
    public string $schedulerAgeLabel = '';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin()
            || $user->hasRole(['super_admin', 'admin_portal_sistem', 'admin_presensi_editor']);
    }

    public function mount(): void
    {
        $this->checkSchedulerStatus();

        $wa = WhatsAppSetting::current();

        $this->form->fill([
            'wa_is_active'                      => (bool) $wa->is_active,
            'wa_instance_name'                  => $wa->instance_name,
            'wa_sender_number'                  => $wa->sender_number,
            'wa_delay_between_messages_seconds' => $wa->delay_between_messages_seconds ?? 4,
            'wa_send_window_start'              => $wa->send_window_start,
            'wa_send_window_end'                => $wa->send_window_end,
        ]);
    }

    protected function checkSchedulerStatus(): void
    {
        $heartbeatPath = storage_path('framework/schedule-heartbeat');
        if (!file_exists($heartbeatPath)) {
            $this->schedulerActive   = false;
            $this->schedulerLastRun  = '-';
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

    protected function buildSchedulerBanner(): HtmlString
    {
        if ($this->schedulerActive) {
            return new HtmlString('
                <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-3 w-3 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                        </span>
                        <div class="text-xs sm:text-sm text-emerald-800 dark:text-emerald-200">
                            <strong>Scheduler &amp; Queue Worker Aktif</strong> — Detak terakhir: <code>' . e($this->schedulerLastRun) . '</code> (' . e($this->schedulerAgeLabel) . ').
                        </div>
                    </div>
                </div>
            ');
        }

        return new HtmlString('
            <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 flex items-center justify-between gap-4">
                <div class="text-xs sm:text-sm text-amber-800 dark:text-amber-200">
                    <strong>⚠️ Scheduler Belum Terdeteksi Aktif</strong> — Detak terakhir: <code>' . e($this->schedulerLastRun) . '</code> (' . e($this->schedulerAgeLabel) . '). Pastikan cron <code>schedule:run</code> dan <code>queue:work</code> berjalan di server.
                </div>
            </div>
        ');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('scheduler_status_banner')
                    ->hiddenLabel()
                    ->content(fn () => $this->buildSchedulerBanner())
                    ->columnSpanFull(),

                Section::make('Koneksi Gateway API WhatsApp (Evolution API)')
                    ->description('Konfigurasi utama gateway pengiriman pesan WhatsApp untuk seluruh modul sistem (Presensi, Jurnal KBM, SPIKAP, Guru Wali, dan Pelayanan Publik).')
                    ->icon('heroicon-o-signal')
                    ->schema([
                        Toggle::make('wa_is_active')
                            ->label('Aktifkan Gateway WhatsApp Global')
                            ->helperText('Jika dimatikan, seluruh pengiriman pesan WhatsApp otomatis di semua modul akan dihentikan sementara.')
                            ->columnSpanFull(),

                        Placeholder::make('wa_env_credentials_info')
                            ->hiddenLabel()
                            ->content(new HtmlString('
                                <div class="p-3 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-700 dark:text-emerald-300 flex items-center gap-2">
                                    <span class="text-base">🔒</span>
                                    <div><strong>Kredensial Base URL &amp; API Key Terproteksi</strong> — Dikelola melalui file konfigurasi server (<code>.env</code>).</div>
                                </div>
                            '))
                            ->columnSpanFull(),

                        TextInput::make('wa_instance_name')
                            ->label('Nama Instance / Session')
                            ->placeholder('contoh: smpn3-wa')
                            ->helperText('Nama instance yang terdaftar di dashboard Evolution API.'),

                        TextInput::make('wa_sender_number')
                            ->label('Nomor Pengirim Terkoneksi')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Nomor WhatsApp aktif yang terhubung pada sesi Evolution API.'),

                        TextInput::make('wa_delay_between_messages_seconds')
                            ->label('Jeda Antar Pesan (Detik)')
                            ->numeric()
                            ->minValue(1)
                            ->default(4)
                            ->helperText('Jeda aman pengiriman antar nomor untuk mencegah pemblokiran (disarankan 3–5 detik).'),

                        TimePicker::make('wa_send_window_start')
                            ->label('Jam Mulai Pengiriman (Send Window)')
                            ->seconds(false),

                        TimePicker::make('wa_send_window_end')
                            ->label('Jam Batas Pengiriman (Send Window)')
                            ->seconds(false),

                        Actions::make([
                            Action::make('test_connection')
                                ->label('Test Koneksi Evolution API')
                                ->icon('heroicon-o-signal')
                                ->color('info')
                                ->action(function (Get $get) {
                                    $waSetting    = WhatsAppSetting::current();
                                    $baseUrl      = $waSetting->base_url;
                                    $apiKey       = $waSetting->api_key;
                                    $instanceName = $get('wa_instance_name') ?: $waSetting->instance_name;

                                    if (!$baseUrl || !$apiKey || !$instanceName) {
                                        Notification::make()
                                            ->title('Konfigurasi Belum Lengkap')
                                            ->body('Pastikan Base URL / API Key di .env dan Nama Instance sudah diisi.')
                                            ->danger()
                                            ->send();
                                        return;
                                    }

                                    try {
                                        $endpoint = rtrim($baseUrl, '/') . '/instance/connectionState/' . $instanceName;
                                        $response = Http::withHeaders([
                                            'apikey' => $apiKey,
                                        ])->timeout(5)->get($endpoint);

                                        if ($response->successful()) {
                                            $state = $response->json('instance.state') ?? 'unknown';
                                            if ($state === 'open') {
                                                Notification::make()
                                                    ->title('Koneksi Berhasil! WhatsApp siap digunakan.')
                                                    ->success()
                                                    ->send();
                                            } else {
                                                Notification::make()
                                                    ->title('Instance ditemukan, status: ' . strtoupper((string) $state))
                                                    ->body('Pastikan perangkat WhatsApp sudah melakukan Scan QR.')
                                                    ->warning()
                                                    ->send();
                                            }
                                        } else {
                                            $errorMsg = $response->json('message') ?? $response->body();
                                            Notification::make()
                                                ->title('Gagal terhubung (Status ' . $response->status() . ')')
                                                ->body(substr((string) $errorMsg, 0, 120))
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
                                }),
                        ])->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Placeholder::make('evolution_api_disclaimer')
                    ->hiddenLabel()
                    ->content(new HtmlString('
                        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400 space-y-1">
                            <p class="font-semibold text-gray-700 dark:text-gray-300">ℹ️ Pemberitahuan Lisensi &amp; Sanggahan (Attribution &amp; Trademark Notice):</p>
                            <p>• Infrastruktur gateway pengiriman pesan sistem ini didukung oleh layanan open-source <strong>Evolution API</strong> (Apache License 2.0 / Evolution Foundation).</p>
                            <p>• <em>Disclaimer:</em> Sistem ERP ini merupakan perangkat lunak independen dan tidak berafiliasi, diawasi, atau didukung secara resmi oleh WhatsApp maupun Meta Platforms, Inc.</p>
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

            $wa = WhatsAppSetting::current();
            $wa->update([
                'is_active'                      => $data['wa_is_active'] ?? false,
                'instance_name'                  => $data['wa_instance_name'] ?? $wa->instance_name,
                'delay_between_messages_seconds' => $data['wa_delay_between_messages_seconds'] ?? 4,
                'send_window_start'              => $data['wa_send_window_start'] ?? $wa->send_window_start,
                'send_window_end'                => $data['wa_send_window_end'] ?? $wa->send_window_end,
            ]);

            Notification::make()
                ->title('Pengaturan koneksi WhatsApp berhasil disimpan')
                ->success()
                ->send();
        } catch (Halt $exception) {
            return;
        }
    }
}


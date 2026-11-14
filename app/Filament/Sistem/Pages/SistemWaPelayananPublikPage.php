<?php

namespace App\Filament\Sistem\Pages;

use App\Models\Jabatan;
use App\Models\PresensiNotificationSetting;
use App\Services\WhatsAppGatewayService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\HtmlString;

class SistemWaPelayananPublikPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox-stack';

    protected string $view = 'filament.pages.manajemen-notifikasi-wa';

    protected static ?string $navigationLabel = 'Notifikasi WA Pelayanan Publik';

    protected static ?string $slug = 'wa-pelayanan-publik';

    protected static ?string $title = 'Notifikasi WhatsApp Pelayanan Publik & Pengaduan (/pengaduan)';

    protected ?string $subheading = 'Atur pengiriman WhatsApp otomatis ke petugas sekolah dan pelapor saat ada aspirasi/pengaduan masyarakat di /pengaduan.';

    protected static string|\UnitEnum|null $navigationGroup = 'Notifikasi WhatsApp';

    protected static ?int $navigationSort = 5;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin()
            || $user->hasRole(['super_admin', 'admin_portal_sistem', 'admin_portal_web', 'admin_presensi_editor']);
    }

    public function mount(): void
    {
        $petugas       = PresensiNotificationSetting::where('status_presensi', 'pengaduan_publik_petugas')->first();
        $pelaporBaru   = PresensiNotificationSetting::where('status_presensi', 'pengaduan_publik_pelapor_baru')->first();
        $pelaporStatus = PresensiNotificationSetting::where('status_presensi', 'pengaduan_publik_pelapor_status')->first();

        $this->form->fill([
            'petugas_is_active'      => $petugas ? (bool) $petugas->is_active : true,
            'petugas_recipients'     => $petugas?->recipients ?: ['kepala_sekolah'],
            'petugas_template_pesan' => $petugas?->template_pesan
                ?: "📩 *PENGADUAN / ASPIRASI PUBLIK BARU*\n\nDari: *{nama_pelapor}*\nNo. HP: {no_hp}\nEmail: {email}\nKategori: *{kategori}*\nWaktu: {waktu}\n\n📝 *Isi Pesan:*\n\"{isi_pengaduan}\"\n\nSilakan buka Portal Web / Admin untuk meninjau dan menindaklanjuti.",

            'pelapor_baru_is_active'      => $pelaporBaru ? (bool) $pelaporBaru->is_active : true,
            'pelapor_baru_template_pesan' => $pelaporBaru?->template_pesan
                ?: "Halo Bapak/Ibu *{nama_pelapor}*,\n\nTerima kasih telah menyampaikan aspirasi / pengaduan melalui Layanan Publik *{nama_sekolah}*.\n\n📌 *Kategori:* {kategori}\n🗓️ *Waktu diterima:* {waktu}\n📌 *Status:* Menunggu Tindak Lanjut\n\nPesan Anda telah tercatat di sistem kami dan akan segera ditinjau oleh petugas terkait. Terima kasih.",

            'pelapor_status_is_active'      => $pelaporStatus ? (bool) $pelaporStatus->is_active : true,
            'pelapor_status_template_pesan' => $pelaporStatus?->template_pesan
                ?: "Halo Bapak/Ibu *{nama_pelapor}*,\n\nInformasi perkembangan terbaru atas aspirasi / pengaduan Anda di Layanan Publik *{nama_sekolah}*:\n\n📌 *Kategori:* {kategori}\n📌 *Status Terbaru:* *{status_baru}*\n\nTerima kasih atas masukan dan partisipasi Anda demi kemajuan layanan *{nama_sekolah}*.",
        ]);
    }

    protected function getRecipientOptions(): array
    {
        $fixed = [
            'kepala_sekolah' => 'Kepala Sekolah',
            'waka_kesiswaan' => 'Waka Kesiswaan',
            'waka_humas'     => 'Waka Humas',
            'guru_bk'        => 'Guru BK',
        ];

        try {
            $excludeNames = [
                'Kepala Sekolah', 'Guru BK', 'BK', 'Bimbingan Konseling', 'Bimbingan dan Konseling',
                'Waka Kesiswaan', 'Waka Humas',
            ];

            $dynamic = Jabatan::whereNotIn('nama_jabatan', $excludeNames)
                ->orderBy('nama_jabatan')
                ->pluck('nama_jabatan', 'nama_jabatan')
                ->mapWithKeys(fn ($name, $val) => ['jabatan:' . $val => 'Jabatan: ' . $name])
                ->toArray();

            $waGroups = WhatsAppGatewayService::getAvailableGroups();

            return array_merge($fixed, $dynamic, $waGroups);
        } catch (\Throwable $e) {
            return $fixed;
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('1. Notifikasi Pengaduan Masuk ke Petugas / Pejabat Sekolah')
                    ->description('Kirimkan WhatsApp otomatis ke pejabat sekolah atau grup WhatsApp ketika masyarakat mengirimkan aspirasi/pengaduan baru melalui halaman /pengaduan.')
                    ->schema([
                        Toggle::make('petugas_is_active')
                            ->label('Aktifkan Notifikasi WA ke Petugas Sekolah')
                            ->columnSpanFull(),

                        Select::make('petugas_recipients')
                            ->label('Jabatan / Grup Penerima Notifikasi')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->options(fn () => $this->getRecipientOptions())
                            ->helperText('Pilih satu atau beberapa jabatan sekolah / grup WhatsApp yang akan menerima pemberitahuan saat pengaduan baru masuk.')
                            ->columnSpanFull(),

                        Textarea::make('petugas_template_pesan')
                            ->label('Template Pesan WA ke Petugas Sekolah')
                            ->rows(7)
                            ->helperText('Placeholder: {nama_sekolah}, {nama_pelapor}, {no_hp}, {email}, {kategori}, {isi_pengaduan}, {waktu}')
                            ->columnSpanFull(),
                    ]),

                Section::make('2. Notifikasi Tanda Terima ke Nomor WA Pelapor')
                    ->description('Kirimkan pesan tanda terima otomatis ke nomor WhatsApp yang diisikan oleh pelapor pada formulir /pengaduan.')
                    ->schema([
                        Toggle::make('pelapor_baru_is_active')
                            ->label('Aktifkan WA Tanda Terima ke Pelapor')
                            ->columnSpanFull(),

                        Textarea::make('pelapor_baru_template_pesan')
                            ->label('Template Pesan WA Tanda Terima Pelapor')
                            ->rows(6)
                            ->helperText('Placeholder: {nama_sekolah}, {nama_pelapor}, {kategori}, {isi_pengaduan}, {waktu}')
                            ->columnSpanFull(),
                    ]),

                Section::make('3. Notifikasi Update Status Pengaduan ke Pelapor')
                    ->description('Kirimkan WhatsApp otomatis ke pelapor ketika petugas memperbarui status pengaduan menjadi "Sedang Diproses" atau "Selesai".')
                    ->schema([
                        Toggle::make('pelapor_status_is_active')
                            ->label('Aktifkan WA Update Status Pengaduan ke Pelapor')
                            ->columnSpanFull(),

                        Textarea::make('pelapor_status_template_pesan')
                            ->label('Template Pesan WA Update Status ke Pelapor')
                            ->rows(6)
                            ->helperText('Placeholder: {nama_sekolah}, {nama_pelapor}, {kategori}, {status_baru}, {isi_pengaduan}')
                            ->columnSpanFull(),
                    ]),

                Placeholder::make('info_layanan_publik')
                    ->hiddenLabel()
                    ->content(new HtmlString('
                        <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400">
                            💡 <strong>Catatan:</strong> Jika pelapor tidak mengisi kolom Nomor HP pada formulir <code>/pengaduan</code>, pengiriman ke pelapor akan dilewati secara otomatis tanpa menyebabkan error, sedangkan notifikasi ke petugas sekolah tetap dikirimkan.
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

            PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'pengaduan_publik_petugas'],
                [
                    'is_active'      => $data['petugas_is_active'] ?? false,
                    'recipients'     => $data['petugas_recipients'] ?? ['kepala_sekolah'],
                    'template_pesan' => $data['petugas_template_pesan'] ?? '',
                ]
            );

            PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'pengaduan_publik_pelapor_baru'],
                [
                    'is_active'      => $data['pelapor_baru_is_active'] ?? false,
                    'recipients'     => ['pelapor'],
                    'template_pesan' => $data['pelapor_baru_template_pesan'] ?? '',
                ]
            );

            PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'pengaduan_publik_pelapor_status'],
                [
                    'is_active'      => $data['pelapor_status_is_active'] ?? false,
                    'recipients'     => ['pelapor'],
                    'template_pesan' => $data['pelapor_status_template_pesan'] ?? '',
                ]
            );

            Notification::make()
                ->title('Pengaturan notifikasi WA Pelayanan Publik berhasil disimpan')
                ->success()
                ->send();
        } catch (Halt $exception) {
            return;
        }
    }
}


<?php

namespace App\Filament\Sistem\Pages;

use App\Models\PresensiNotificationSetting;
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

class SistemWaGuruWaliPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected string $view = 'filament.pages.manajemen-notifikasi-wa';

    protected static ?string $navigationLabel = 'Notifikasi WA Guru Wali';

    protected static ?string $slug = 'wa-guru-wali';

    protected static ?string $title = 'Notifikasi WhatsApp Janjian Guru Wali';

    protected ?string $subheading = 'Pengaturan WhatsApp khusus saat siswa mengajukan janjian tatap muka dan saat jadwal janjian diterima/ditetapkan oleh Guru Wali.';

    protected static string|\UnitEnum|null $navigationGroup = 'Notifikasi WhatsApp';

    protected static ?int $navigationSort = 4;

    public ?array $data = [];

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
        $gwPengajuan = PresensiNotificationSetting::where('status_presensi', 'guru_wali_pengajuan')->first();
        $gwJadwal    = PresensiNotificationSetting::where('status_presensi', 'guru_wali_jadwal')->first();

        $this->form->fill([
            'guru_wali_pengajuan_is_active'      => $gwPengajuan ? (bool) $gwPengajuan->is_active : true,
            'guru_wali_pengajuan_template_pesan' => $gwPengajuan?->template_pesan
                ?: "Halo Bapak/Ibu *{nama_guru_wali}*,\n\nSiswa bimbingan Anda, *{nama_siswa}* (Kelas {kelas}), mengajukan *Janjian Pertemuan Tatap Muka* melalui Ruang Aman SI-WALI:\n\n📌 *Kategori:* {kategori}\n📌 *Topik:* {topik}\n📝 *Uraian Singkat:* \"{detail}\"\n\nSilakan buka Portal Guru Wali untuk mengonfirmasi dan menetapkan jadwal pertemuan. Terima kasih.",

            'guru_wali_jadwal_is_active' => $gwJadwal ? (bool) $gwJadwal->is_active : true,
            'guru_wali_jadwal_targets'   => $gwJadwal?->recipients ?: ['siswa'],
            'guru_wali_jadwal_template'  => $gwJadwal?->template_pesan
                ?: "Halo *{nama_siswa}*,\n\nJanjian pertemuan tatap muka dengan Guru Wali (*{nama_guru_wali}*) untuk topik *{topik}* telah diterima & dijadwalkan:\n\n🗓️ *Waktu Pertemuan:* {jadwal_pasti}\n📝 *Catatan Guru Wali:* {catatan_guru}\n\nMohon hadir tepat waktu ya. Tetap semangat!",
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('info_curhat_online')
                    ->hiddenLabel()
                    ->content(new HtmlString('
                        <div class="p-3.5 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-xs sm:text-sm text-blue-800 dark:text-blue-200">
                            ℹ️ <strong>Fokus Janjian Tatap Muka:</strong> Untuk bimbingan biasa atau curhat online (mode <em>Pesan Portal</em>), percakapan dan notifikasinya sudah berjalan langsung di dalam aplikasi (Push Notification). WhatsApp hanya dikirimkan ketika ada <strong>pengajuan janjian tatap muka</strong> dan ketika <strong>janjian diterima/dijadwalkan</strong>.
                        </div>
                    '))
                    ->columnSpanFull(),

                Section::make('1. Saat Siswa Mengajukan Janjian Tatap Muka (WA ke Guru Wali)')
                    ->description('Sistem otomatis mengirimkan WhatsApp ke Guru Wali ketika siswa binaan mengajukan permohonan pertemuan tatap muka di /portal-siswa/konsultasi-guru-wali.')
                    ->schema([
                        Toggle::make('guru_wali_pengajuan_is_active')
                            ->label('Aktifkan WA Pengajuan Janjian Tatap Muka ke Guru Wali')
                            ->columnSpanFull(),

                        Textarea::make('guru_wali_pengajuan_template_pesan')
                            ->label('Template Pesan WA ke Guru Wali')
                            ->rows(7)
                            ->helperText('Placeholder: {nama_guru_wali}, {nama_siswa}, {kelas}, {kategori}, {mode}, {topik}, {detail}, {waktu}')
                            ->columnSpanFull(),
                    ]),

                Section::make('2. Saat Janjian Tatap Muka Diterima / Dijadwalkan (WA ke Siswa / Orang Tua)')
                    ->description('Sistem otomatis mengirimkan WhatsApp konfirmasi waktu & catatan pertemuan ketika Guru Wali menyetujui dan menetapkan jadwal tatap muka.')
                    ->schema([
                        Toggle::make('guru_wali_jadwal_is_active')
                            ->label('Aktifkan WA Konfirmasi Jadwal Janjian Diterima')
                            ->columnSpanFull(),

                        Select::make('guru_wali_jadwal_targets')
                            ->label('Target Penerima Konfirmasi Jadwal')
                            ->multiple()
                            ->options([
                                'siswa'     => 'Siswa Binaan',
                                'orang_tua' => 'Orang Tua / Wali Siswa',
                            ])
                            ->helperText('Pilih apakah konfirmasi jadwal dikirim ke nomor WA siswa saja, orang tua saja, atau keduanya.')
                            ->columnSpanFull(),

                        Textarea::make('guru_wali_jadwal_template')
                            ->label('Template Pesan WA Konfirmasi Jadwal')
                            ->rows(6)
                            ->helperText('Placeholder: {nama_siswa}, {nama_guru_wali}, {topik}, {jadwal_pasti}, {catatan_guru}')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'guru_wali_pengajuan'],
                [
                    'is_active'      => $data['guru_wali_pengajuan_is_active'] ?? false,
                    'recipients'     => ['guru_wali'],
                    'template_pesan' => $data['guru_wali_pengajuan_template_pesan'] ?? '',
                ]
            );

            PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'guru_wali_jadwal'],
                [
                    'is_active'      => $data['guru_wali_jadwal_is_active'] ?? false,
                    'recipients'     => $data['guru_wali_jadwal_targets'] ?? ['siswa'],
                    'template_pesan' => $data['guru_wali_jadwal_template'] ?? '',
                ]
            );

            // Pastikan balasan obrolan online dinonaktifkan di tabel setting
            PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'guru_wali_balasan'],
                [
                    'is_active'  => false,
                    'recipients' => ['siswa'],
                ]
            );

            Notification::make()
                ->title('Pengaturan notifikasi WA Guru Wali berhasil disimpan')
                ->success()
                ->send();
        } catch (Halt $exception) {
            return;
        }
    }
}


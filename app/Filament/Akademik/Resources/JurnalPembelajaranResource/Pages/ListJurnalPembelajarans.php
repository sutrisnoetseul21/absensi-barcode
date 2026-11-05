<?php

namespace App\Filament\Akademik\Resources\JurnalPembelajaranResource\Pages;

use App\Filament\Akademik\Resources\JurnalPembelajaranResource;
use App\Models\PengaturanSekolah;
use Filament\Actions;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListJurnalPembelajarans extends ListRecords
{
    protected static string $resource = JurnalPembelajaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('toggle_presensi_kbm_siswa')
                ->label('Presensi KBM Siswa')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color(fn () => (PengaturanSekolah::current()?->enable_presensi_kbm ?? true) ? 'success' : 'gray')
                ->badge(fn () => (PengaturanSekolah::current()?->enable_presensi_kbm ?? true) ? 'Aktif' : 'Non-aktif')
                ->badgeColor(fn () => (PengaturanSekolah::current()?->enable_presensi_kbm ?? true) ? 'success' : 'gray')
                ->modalHeading('Pengaturan Presensi KBM untuk Siswa')
                ->modalDescription('Atur keterlihatan rekapitulasi presensi mata pelajaran / jam mengajar di aplikasi Portal Siswa.')
                ->modalSubmitActionLabel('Simpan Pengaturan')
                ->fillForm(fn (): array => [
                    'enable_presensi_kbm' => (bool) (PengaturanSekolah::current()?->enable_presensi_kbm ?? true),
                ])
                ->form([
                    Toggle::make('enable_presensi_kbm')
                        ->label('Tampilkan Presensi KBM di Portal Siswa')
                        ->helperText('Jika diaktifkan, tab Presensi KBM akan muncul di Portal Siswa untuk memantau kehadiran per mata pelajaran yang diinput guru di Jurnal Mengajar. Jika dinonaktifkan, tab KBM otomatis disembunyikan.')
                        ->default(true),
                ])
                ->action(function (array $data): void {
                    $settings = PengaturanSekolah::current();
                    if ($settings) {
                        $settings->update([
                            'enable_presensi_kbm' => (bool) ($data['enable_presensi_kbm'] ?? true),
                        ]);
                    }
                    \Illuminate\Support\Facades\Cache::forget('public_pengaturan_sekolah');

                    Notification::make()
                        ->title('Pengaturan Presensi KBM berhasil disimpan')
                        ->body('Status saat ini: ' . (($data['enable_presensi_kbm'] ?? true) ? 'Aktif di Portal Siswa' : 'Dinonaktifkan dari Portal Siswa'))
                        ->success()
                        ->send();
                }),

            Actions\Action::make('buka_portal_guru')
                ->label('Buka Jurnal di Portal Guru')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('primary')
                ->url(fn () => route('portal-guru.jurnal-mengajar'))
                ->openUrlInNewTab(),
        ];
    }
}

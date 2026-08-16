<?php

namespace App\Filament\Perpustakaan\Resources\KunjunganPerpustakaanResource\Pages;

use App\Filament\Perpustakaan\Resources\KunjunganPerpustakaanResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageKunjunganPerpustakaans extends ManageRecords
{
    protected static string $resource = KunjunganPerpustakaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('buka_kiosk')
                ->label('Buka Kiosk Presensi Kunjungan')
                ->icon('heroicon-o-qr-code')
                ->color('success')
                ->url(route('perpustakaan.kunjungan'))
                ->openUrlInNewTab(),

            Actions\Action::make('unduhKunjungan')
                ->label('Unduh Riwayat')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->modalHeading('Unduh Riwayat Kunjungan')
                ->modalDescription('Pilih filter periode, tipe anggota, dan format dokumen yang ingin diunduh.')
                ->modalWidth('md')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('start_date')
                        ->label('Tanggal Mulai')
                        ->helperText('Kosongkan untuk semua waktu.'),
                    
                    \Filament\Forms\Components\DatePicker::make('end_date')
                        ->label('Tanggal Akhir')
                        ->helperText('Kosongkan untuk sampai saat ini.'),

                    \Filament\Forms\Components\CheckboxList::make('tipe_anggota')
                        ->label('Filter Tipe Anggota')
                        ->options([
                            'siswa' => 'Siswa',
                            'guru'  => 'Guru / Staff',
                        ])
                        ->bulkToggleable()
                        ->helperText('Kosongkan untuk semua tipe.')
                        ->columns(2),

                    \Filament\Forms\Components\Radio::make('format')
                        ->label('Format Unduhan')
                        ->options([
                            'pdf'   => '📄 PDF (A4 Portrait)',
                            'excel' => '📊 Excel (.xlsx)',
                        ])
                        ->default('pdf')
                        ->inline()
                        ->required(),
                ])
                ->action(function (array $data) {
                    $format     = $data['format'] ?? 'pdf';
                    $startDate  = $data['start_date'] ?? null;
                    $endDate    = $data['end_date'] ?? null;
                    $tipeFilter = $data['tipe_anggota'] ?? [];

                    $routeName = $format === 'excel'
                        ? 'perpustakaan.kunjungan.excel'
                        : 'perpustakaan.kunjungan.pdf';

                    $params = [];
                    if ($startDate) $params['start_date'] = $startDate;
                    if ($endDate)   $params['end_date']   = $endDate;
                    if (!empty($tipeFilter)) $params['tipe'] = $tipeFilter;

                    return redirect()->to(route($routeName, $params));
                }),

            Actions\CreateAction::make()
                ->label('Tambah Manual')
                ->icon('heroicon-o-plus')
                ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_perpustakaan_editor') || auth()->user()?->hasRole('admin_master_editor')),
        ];
    }
}

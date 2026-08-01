<?php

namespace App\Filament\Perpustakaan\Resources\PeminjamanAktifResource\Pages;

use App\Filament\Perpustakaan\Resources\PeminjamanAktifResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ManagePeminjamanAktifs extends ManageRecords
{
    protected static string $resource = PeminjamanAktifResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('unduhPeminjaman')
                ->label('Unduh Peminjaman')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->modalHeading('Unduh Data Peminjaman')
                ->modalDescription('Pilih filter status, tipe anggota, dan format dokumen yang ingin diunduh.')
                ->modalWidth('md')
                ->form([
                    \Filament\Forms\Components\CheckboxList::make('status')
                        ->label('Filter Status')
                        ->options([
                            'dipinjam'     => 'Dipinjam',
                            'dikembalikan' => 'Dikembalikan',
                            'hilang'       => 'Hilang',
                        ])
                        ->bulkToggleable()
                        ->helperText('Kosongkan untuk semua status.')
                        ->columns(2),

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
                            'pdf'   => '📄 PDF (A4 Landscape)',
                            'excel' => '📊 Excel (.xlsx)',
                        ])
                        ->default('pdf')
                        ->inline()
                        ->required(),
                ])
                ->action(function (array $data) {
                    $format       = $data['format'] ?? 'pdf';
                    $statusFilter = $data['status'] ?? [];
                    $tipeFilter   = $data['tipe_anggota'] ?? [];

                    $routeName = $format === 'excel'
                        ? 'perpustakaan.peminjaman-buku.excel'
                        : 'perpustakaan.peminjaman-buku.pdf';

                    $params = [];
                    if (!empty($statusFilter)) {
                        $params['status'] = $statusFilter;
                    }
                    if (!empty($tipeFilter)) {
                        $params['tipe'] = $tipeFilter;
                    }

                    return redirect()->to(route($routeName, $params));
                }),

            Actions\CreateAction::make()
                ->label('New Peminjaman')
                ->modalHeading('Tambah Transaksi Peminjaman')
                ->after(function ($record) {
                    if ($record->eksemplar_id) {
                        \App\Models\EksemplarBuku::where('id', $record->eksemplar_id)->update(['status' => 'dipinjam']);
                    }
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'aktif' => Tab::make('Peminjaman Aktif')
                ->badge(fn () => \App\Models\Peminjaman::whereIn('status', ['dipinjam', 'terlambat'])->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['dipinjam', 'terlambat'])),
            'dikembalikan' => Tab::make('Dikembalikan')
                ->badge(fn () => \App\Models\Peminjaman::where('status', 'dikembalikan')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'dikembalikan')),
            'semua' => Tab::make('Semua Transaksi')
                ->modifyQueryUsing(fn (Builder $query) => $query),
        ];
    }
}

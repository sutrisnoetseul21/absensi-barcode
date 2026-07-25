<?php

namespace App\Filament\Resources\SiswaMutasiResource\Pages;

use App\Filament\Resources\SiswaMutasiResource;
use Filament\Resources\Pages\ListRecords;

use App\Actions\Student\MutateStudentAction;
use App\Models\Siswa;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

class ListSiswaMutasi extends ListRecords
{
    protected static string $resource = SiswaMutasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('proses_mutasi')
                ->label('Proses Mutasi')
                ->icon('heroicon-o-arrow-right-on-rectangle')
                ->color('danger')
                ->modalHeading('Proses Mutasi Siswa')
                ->modalDescription('Siswa yang dipilih akan dikeluarkan dari kelas aktifnya dan kartunya akan dinonaktifkan. Data riwayat mereka akan tetap tersimpan.')
                ->modalWidth('md')
                ->form([
                    Select::make('siswa_id')
                        ->label('Pilih Siswa (Aktif)')
                        ->options(function () {
                            return Siswa::where('status', 'aktif')
                                ->get()
                                ->mapWithKeys(function ($siswa) {
                                    return [$siswa->id => $siswa->name . ' (' . ($siswa->nisn ?? '-') . ')'];
                                });
                        })
                        ->searchable()
                        ->required()
                ])
                ->action(function (array $data) {
                    $siswa = Siswa::find($data['siswa_id']);
                    if (!$siswa) return;

                    // Action ini sudah menangani DB::transaction dan event dispatching
                    (new MutateStudentAction)->execute($siswa);

                    Notification::make()
                        ->title('Berhasil Proses Mutasi')
                        ->body("Siswa **{$siswa->name}** telah berhasil dipindahkan ke daftar mutasi.")
                        ->success()
                        ->send();
                }),
        ];
    }
}

<?php

namespace App\Filament\Akademik\Resources\JurnalPembelajaranResource\Pages;

use App\Filament\Akademik\Resources\JurnalPembelajaranResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewJurnalPembelajaran extends ViewRecord
{
    protected static string $resource = JurnalPembelajaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cetak_pdf')
                ->label('Cetak Rekap PDF')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn () => route('portal-guru.jurnal-mengajar.cetak', [
                    'teacher_id'        => $this->record->teacher_id,
                    'class_id'          => $this->record->class_id,
                    'mata_pelajaran_id' => $this->record->mata_pelajaran_id,
                    'academic_year_id'  => $this->record->academic_year_id,
                    'date_from'         => $this->record->tanggal->format('Y-m-d'),
                    'date_to'           => $this->record->tanggal->format('Y-m-d'),
                ]))
                ->openUrlInNewTab(),
        ];
    }
}

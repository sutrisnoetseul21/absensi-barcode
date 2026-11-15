<?php

namespace App\Filament\Akademik\Resources\GuruWali\Pages;

use App\Filament\Akademik\Resources\GuruWali\JurnalGuruWaliResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewJurnalGuruWali extends ViewRecord
{
    protected static string $resource = JurnalGuruWaliResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cetak_individual_print')
                ->label('Print Preview Siswa')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(fn () => route('akademik.guru-wali.cetak.individual.print', [
                    'kelompok_id'      => $this->record->kelompok_id,
                    'student_id'       => $this->record->student_id,
                    'academic_year_id' => $this->record->academic_year_id,
                    'periode'          => 'tahunan',
                    'autoprint'        => '0',
                ]))
                ->openUrlInNewTab(),

            Action::make('cetak_individual_pdf')
                ->label('Unduh PDF Siswa')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn () => route('akademik.guru-wali.cetak.individual.pdf', [
                    'kelompok_id'      => $this->record->kelompok_id,
                    'student_id'       => $this->record->student_id,
                    'academic_year_id' => $this->record->academic_year_id,
                    'periode'          => 'tahunan',
                ]))
                ->openUrlInNewTab(),
        ];
    }
}


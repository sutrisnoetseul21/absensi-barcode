<?php

namespace App\Filament\Akademik\Resources\GuruWali\Pages;

use App\Filament\Akademik\Concerns\InteractsWithGuruWaliPeriod;
use App\Filament\Akademik\Resources\GuruWali\JurnalGuruWaliResource;
use App\Models\KelompokGuruWali;
use App\Models\TahunAjaran;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class ListJurnalGuruWalis extends ListRecords
{
    use InteractsWithGuruWaliPeriod;

    protected static string $resource = JurnalGuruWaliResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cetak_export_jurnal_wali')
                ->label('Cetak / Export Rekap')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->modalHeading('Cetak & Export Laporan Guru Wali')
                ->modalDescription('Pilih format laporan resmi SI-WALI (PDF / Print) atau Export Rekap Excel (.xlsx), Tahun Ajaran, periode, dan Kelompok Guru Wali.')
                ->modalWidth('lg')
                ->modalSubmitActionLabel('Proses & Buka')
                ->form([
                    Select::make('format')
                        ->label('Format & Jenis Laporan')
                        ->placeholder('— Pilih Format Laporan —')
                        ->options([
                            'kelompok_pdf'     => 'PDF — Laporan Kinerja Kelompok (Resmi SI-WALI)',
                            'kelompok_print'   => 'Print Preview — Laporan Kinerja Kelompok',
                            'massal_pdf'       => 'PDF — Laporan Individual Massal (1 Kelompok)',
                            'massal_print'     => 'Print Preview — Laporan Individual Massal',
                            'individual_pdf'   => 'PDF — Laporan Individual Siswa',
                            'individual_print' => 'Print Preview — Laporan Individual Siswa',
                            'excel'            => 'Excel (.xlsx) — Rekapitulasi Jurnal Guru Wali',
                        ])
                        ->required()
                        ->live()
                        ->native(false),

                    Select::make('academic_year_id')
                        ->label('Tahun Ajaran')
                        ->options(fn () => self::getAllGuruWaliAcademicYears())
                        ->default(fn () => self::getActiveGuruWaliAcademicYear()?->id)
                        ->required()
                        ->live()
                        ->searchable()
                        ->native(false),

                    Select::make('periode')
                        ->label('Periode Laporan')
                        ->options(function (Get $get): array {
                            $ay = $get('academic_year_id')
                                ? TahunAjaran::find($get('academic_year_id'))
                                : self::getActiveGuruWaliAcademicYear();

                            return self::buildGuruWaliPeriodOptions($ay, true, true);
                        })
                        ->default('tahunan')
                        ->required()
                        ->native(false),

                    Select::make('kelompok_id')
                        ->label('Kelompok / Guru Wali')
                        ->placeholder('— Pilih Kelompok Guru Wali —')
                        ->options(fn () => KelompokGuruWali::with('guru:id,name')
                            ->orderBy('nama_kelompok')
                            ->get()
                            ->mapWithKeys(fn ($k) => [$k->id => "{$k->nama_kelompok} — " . ($k->guru?->name ?? '-')])
                            ->toArray())
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('student_id', null)),

                    Select::make('student_id')
                        ->label('Siswa Binaan')
                        ->placeholder(fn (Get $get) => $get('kelompok_id') ? '— Pilih Siswa Binaan —' : 'Pilih Kelompok terlebih dahulu')
                        ->disabled(fn (Get $get) => empty($get('kelompok_id')))
                        ->visible(fn (Get $get) => in_array($get('format'), ['individual_pdf', 'individual_print'], true))
                        ->required(fn (Get $get) => in_array($get('format'), ['individual_pdf', 'individual_print'], true))
                        ->options(function (Get $get): array {
                            $kelompokId = $get('kelompok_id');
                            if (! $kelompokId) {
                                return [];
                            }

                            $kelompok = KelompokGuruWali::find($kelompokId);
                            if (! $kelompok) {
                                return [];
                            }

                            return $kelompok->anggota()
                                ->with('siswa:id,name,nisn')
                                ->get()
                                ->filter(fn ($a) => $a->siswa !== null)
                                ->sortBy(fn ($a) => $a->siswa->name)
                                ->mapWithKeys(fn ($a) => [
                                    $a->student_id => $a->siswa->name . ($a->siswa->nisn ? " ({$a->siswa->nisn})" : ''),
                                ])
                                ->toArray();
                        })
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    $ayId = $data['academic_year_id'] ?? self::getActiveGuruWaliAcademicYear()?->id;
                    $format = $data['format'] ?? 'kelompok_pdf';

                    $params = array_filter([
                        'academic_year_id' => $ayId,
                        'periode'          => $data['periode'] ?? 'tahunan',
                        'kelompok_id'      => $data['kelompok_id'] ?? null,
                        'student_id'       => $data['student_id'] ?? null,
                    ], fn ($v) => $v !== null && $v !== '');

                    $routeName = match ($format) {
                        'excel'            => 'akademik.guru-wali.export.excel',
                        'kelompok_print'   => 'akademik.guru-wali.cetak.kelompok.print',
                        'massal_pdf'       => 'akademik.guru-wali.cetak.individual.massal.pdf',
                        'massal_print'     => 'akademik.guru-wali.cetak.individual.massal.print',
                        'individual_pdf'   => 'akademik.guru-wali.cetak.individual.pdf',
                        'individual_print' => 'akademik.guru-wali.cetak.individual.print',
                        default            => 'akademik.guru-wali.cetak.kelompok.pdf',
                    };

                    $this->redirect(route($routeName, $params));
                }),

            Actions\Action::make('buka_portal_guru')
                ->label('Buka di Portal Guru')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('primary')
                ->url(fn () => route('portal-guru.guru-wali.jurnal'))
                ->openUrlInNewTab(),
        ];
    }
}


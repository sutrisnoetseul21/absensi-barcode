<?php

namespace App\Filament\Akademik\Resources\Kelas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KelasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Kelas')
                    ->placeholder('Contoh: 7A, X TKJ 1, 1A')
                    ->required()
                    ->maxLength(30),

                Select::make('grade_level')
                    ->label('Tingkat')
                    ->options(function () {
                        $level = \App\Models\PengaturanSekolah::current()?->school_level;

                        return match ($level) {
                            'SD' => [
                                1 => 'Kelas 1 (SD)',
                                2 => 'Kelas 2 (SD)',
                                3 => 'Kelas 3 (SD)',
                                4 => 'Kelas 4 (SD)',
                                5 => 'Kelas 5 (SD)',
                                6 => 'Kelas 6 (SD)',
                            ],
                            'SMP' => [
                                7 => 'Kelas 7 (SMP)',
                                8 => 'Kelas 8 (SMP)',
                                9 => 'Kelas 9 (SMP)',
                            ],
                            'SMA' => [
                                10 => 'Kelas 10 / X (SMA)',
                                11 => 'Kelas 11 / XI (SMA)',
                                12 => 'Kelas 12 / XII (SMA)',
                            ],
                            'SMK' => [
                                10 => 'Kelas 10 / X (SMK)',
                                11 => 'Kelas 11 / XI (SMK)',
                                12 => 'Kelas 12 / XII (SMK)',
                                13 => 'Kelas 13 / XIII (SMK 4 Tahun)',
                            ],
                            default => [
                                1 => 'Kelas 1 (SD)', 2 => 'Kelas 2 (SD)', 3 => 'Kelas 3 (SD)',
                                4 => 'Kelas 4 (SD)', 5 => 'Kelas 5 (SD)', 6 => 'Kelas 6 (SD)',
                                7 => 'Kelas 7 (SMP)', 8 => 'Kelas 8 (SMP)', 9 => 'Kelas 9 (SMP)',
                                10 => 'Kelas 10 / X (SMA/SMK)', 11 => 'Kelas 11 / XI (SMA/SMK)',
                                12 => 'Kelas 12 / XII (SMA/SMK)', 13 => 'Kelas 13 / XIII (SMK)',
                            ],
                        };
                    })
                    ->required(),

                TextInput::make('major')
                    ->label('Jurusan / Kompetensi Keahlian')
                    ->placeholder('Opsional. Contoh: TKJ, RPL, AKL, MIPA')
                    ->maxLength(100),

                Select::make('teacher_id')
                    ->label('Wali Kelas (Tahun Ajaran Aktif)')
                    ->options(\App\Models\Guru::pluck('name', 'id'))
                    ->searchable()
                    ->dehydrated(false)
                    ->helperText('Hanya berlaku untuk Tahun Ajaran yang sedang Aktif.')
                    ->afterStateHydrated(function (Select $component, ?\App\Models\Kelas $record) {
                        if (! $record) return;
                        $activeYear = \App\Models\PengaturanSekolah::current()?->academic_year_id_active;
                        if (! $activeYear) return;
                        $kelasAjaran = $record->kelasAjarans()->where('academic_year_id', $activeYear)->first();
                        if ($kelasAjaran) {
                            $component->state($kelasAjaran->teacher_id);
                        }
                    })
                    ->saveRelationshipsUsing(function (\App\Models\Kelas $record, $state) {
                        $activeYear = \App\Models\PengaturanSekolah::current()?->academic_year_id_active;
                        if (! $activeYear) return;
                        
                        \App\Models\KelasAjaran::updateOrCreate(
                            ['class_id' => $record->id, 'academic_year_id' => $activeYear],
                            ['teacher_id' => $state]
                        );
                    }),
            ]);
    }
}

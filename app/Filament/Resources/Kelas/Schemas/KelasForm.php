<?php

namespace App\Filament\Resources\Kelas\Schemas;

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
                    ->placeholder('Contoh: 7A, 8B')
                    ->required()
                    ->maxLength(20),

                Select::make('grade_level')
                    ->label('Tingkat')
                    ->options([
                        7 => 'Kelas 7 (SMP)',
                        8 => 'Kelas 8 (SMP)',
                        9 => 'Kelas 9 (SMP)',
                    ])
                    ->required(),

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

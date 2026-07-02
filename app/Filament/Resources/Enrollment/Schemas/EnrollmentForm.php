<?php

namespace App\Filament\Resources\Enrollment\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class EnrollmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('student_id')
                    ->label('Siswa')
                    ->relationship('siswa', 'name')
                    ->searchable(['name', 'nisn'])
                    ->preload()
                    ->required(),

                Select::make('class_id')
                    ->label('Kelas')
                    ->relationship('kelas', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->relationship('tahunAjaran', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'aktif'   => 'Aktif',
                        'naik'    => 'Naik Kelas',
                        'tinggal' => 'Tinggal Kelas',
                        'pindah'  => 'Pindah',
                        'lulus'   => 'Lulus',
                    ])
                    ->default('aktif')
                    ->required(),
            ]);
    }
}

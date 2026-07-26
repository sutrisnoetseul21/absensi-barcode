<?php

namespace App\Filament\Akademik\Resources\Kelas\RelationManagers;

use App\Models\KelasAjaran;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KelasAjaranRelationManager extends RelationManager
{
    protected static string $relationship = 'kelasAjarans';

    protected static ?string $title = 'Wali Kelas per Tahun Ajaran';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->relationship('tahunAjaran', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Pilih tahun ajaran secara eksplisit.'),

                Select::make('teacher_id')
                    ->label('Wali Kelas (Guru)')
                    ->relationship('guru', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('Kosongkan jika wali kelas belum ditentukan.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tahunAjaran.name')
                    ->label('Tahun Ajaran')
                    ->sortable(),

                TextColumn::make('guru.name')
                    ->label('Wali Kelas')
                    ->default('— Belum ditentukan —'),
            ])
            ->headerActions([
                // Dihapus sesuai permintaan: assign dilakukan dari List Kelas
            ])
            ->actions([
                // Edit & Delete hanya untuk Super Admin dan HANYA untuk tahun ajaran aktif
                EditAction::make()
                    ->visible(function (KelasAjaran $record): bool {
                        $isSuperAdmin = auth()->user()?->isSuperAdmin() ?? false;
                        if (!$isSuperAdmin) return false;
                        $activeTahunAjaranId = \App\Models\PengaturanSekolah::current()?->academic_year_id_active;
                        return $record->academic_year_id === $activeTahunAjaranId;
                    })
                    ->using(function (KelasAjaran $record, array $data): KelasAjaran {
                        $existing = KelasAjaran::updateOrCreate(
                            [
                                'class_id'         => $record->class_id,
                                'academic_year_id' => $data['academic_year_id'],
                            ],
                            [
                                'teacher_id' => $data['teacher_id'] ?? null,
                            ]
                        );

                        if ($existing->id !== $record->id) {
                            $record->delete();
                        }

                        return $existing;
                    }),

                DeleteAction::make()
                    ->visible(function (KelasAjaran $record): bool {
                        $isSuperAdmin = auth()->user()?->isSuperAdmin() ?? false;
                        if (!$isSuperAdmin) return false;
                        $activeTahunAjaranId = \App\Models\PengaturanSekolah::current()?->academic_year_id_active;
                        return $record->academic_year_id === $activeTahunAjaranId;
                    }),
            ]);
    }
}

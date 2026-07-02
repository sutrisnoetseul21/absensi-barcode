<?php

namespace App\Filament\Resources\Kelas\RelationManagers;

use App\Models\KelasAjaran;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
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
                // Tombol assign hanya untuk Super Admin
                Action::make('assignWaliKelas')
                    ->label('Assign Wali Kelas')
                    ->icon('heroicon-o-user-plus')
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false)
                    ->form([
                        Select::make('academic_year_id')
                            ->label('Tahun Ajaran')
                            ->relationship('tahunAjaran', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('teacher_id')
                            ->label('Wali Kelas (Guru)')
                            ->relationship('guru', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])
                    ->action(function (array $data): void {
                        // Upsert: updateOrCreate untuk hindari error duplikat
                        // UNIQUE [class_id, academic_year_id] sudah ada di DB
                        KelasAjaran::updateOrCreate(
                            [
                                'class_id'          => $this->ownerRecord->id,
                                'academic_year_id'  => $data['academic_year_id'],
                            ],
                            [
                                'teacher_id' => $data['teacher_id'] ?? null,
                            ]
                        );

                        Notification::make()
                            ->title('Wali kelas berhasil diassign')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                // Edit & Delete hanya untuk Super Admin
                EditAction::make()
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false)
                    ->using(function (KelasAjaran $record, array $data): KelasAjaran {
                        // Upsert: jika edit mengubah tahun ajaran ke yang sudah ada,
                        // updateOrCreate untuk hindari konflik unique constraint
                        $existing = KelasAjaran::updateOrCreate(
                            [
                                'class_id'         => $record->class_id,
                                'academic_year_id' => $data['academic_year_id'],
                            ],
                            [
                                'teacher_id' => $data['teacher_id'] ?? null,
                            ]
                        );

                        // Hapus record lama jika berubah ke combinasi berbeda
                        if ($existing->id !== $record->id) {
                            $record->delete();
                        }

                        return $existing;
                    }),

                DeleteAction::make()
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),
            ]);
    }
}

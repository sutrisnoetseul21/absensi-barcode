<?php

namespace App\Filament\Akademik\Resources\Kelas\RelationManagers;

use App\Models\KelasAjaran;
use App\Models\TahunAjaran;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PengajaranRelationManager extends RelationManager
{
    protected static string $relationship = 'pengajarans';

    protected static ?string $title = 'Guru & Mata Pelajaran';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->options(TahunAjaran::pluck('name', 'id'))
                    ->default(fn () => TahunAjaran::where('status', 'aktif')->value('id'))
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('teacher_id')
                    ->label('Guru')
                    ->relationship('guru', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('mata_pelajaran_id')
                    ->label('Mata Pelajaran')
                    ->relationship('mataPelajaran', 'nama_mapel')
                    ->required()
                    ->searchable()
                    ->preload(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kelasAjaran.tahunAjaran.name')
                    ->label('Tahun Ajaran')
                    ->sortable(),
                TextColumn::make('guru.name')
                    ->label('Guru')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('mataPelajaran.nama_mapel')
                    ->label('Mata Pelajaran')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Dihapus sesuai permintaan: assign dilakukan dari List Kelas
            ])
            ->actions([
                EditAction::make()
                    ->visible(function (\App\Models\Pengajaran $record): bool {
                        $isSuperAdmin = auth()->user()?->isSuperAdmin() ?? false;
                        if (!$isSuperAdmin) return false;
                        $activeTahunAjaranId = \App\Models\PengaturanSekolah::current()?->academic_year_id_active;
                        return $record->kelasAjaran->academic_year_id === $activeTahunAjaranId;
                    })
                    ->mutateRecordDataUsing(function (array $data, \Illuminate\Database\Eloquent\Model $record): array {
                        $data['academic_year_id'] = $record->kelasAjaran->academic_year_id;
                        return $data;
                    })
                    ->mutateFormDataUsing(function (array $data): array {
                        $kelasAjaran = KelasAjaran::firstOrCreate(
                            [
                                'class_id' => $this->ownerRecord->id,
                                'academic_year_id' => $data['academic_year_id'],
                            ],
                            [
                                'teacher_id' => null,
                            ]
                        );

                        $data['class_academic_year_id'] = $kelasAjaran->id;
                        unset($data['academic_year_id']);

                        return $data;
                    }),
                DeleteAction::make()
                    ->visible(function (\App\Models\Pengajaran $record): bool {
                        $isSuperAdmin = auth()->user()?->isSuperAdmin() ?? false;
                        if (!$isSuperAdmin) return false;
                        $activeTahunAjaranId = \App\Models\PengaturanSekolah::current()?->academic_year_id_active;
                        return $record->kelasAjaran->academic_year_id === $activeTahunAjaranId;
                    }),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

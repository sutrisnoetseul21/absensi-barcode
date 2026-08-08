<?php

namespace App\Filament\Akademik\Resources\RombonganBelajar\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use App\Models\KelasAjaran;
use Filament\Forms\Components\Select;
use App\Models\Guru;
use Filament\Notifications\Notification;

class RombonganBelajarsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Kelas')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('grade_level')
                    ->label('Tingkat')
                    ->sortable()
                    ->formatStateUsing(fn (int $state): string => "Kelas {$state}"),
                    
                TextColumn::make('wali_kelas')
                    ->label('Wali Kelas (Aktif)')
                    ->getStateUsing(function (Kelas $record) {
                        $activeTahunAjaranId = PengaturanSekolah::current()?->academic_year_id_active ?? \App\Models\TahunAjaran::aktif()->first()?->id;
                        if (!$activeTahunAjaranId) return 'Tidak ada tahun ajaran aktif';

                        $kelasAjaran = KelasAjaran::where('class_id', $record->id)
                            ->where('academic_year_id', $activeTahunAjaranId)
                            ->first();

                        return $kelasAjaran?->guru?->name ?? '—';
                    }),
            ])
            ->filters([
                SelectFilter::make('grade_level')
                    ->label('Filter Tingkat')
                    ->options([
                        7 => 'Kelas 7',
                        8 => 'Kelas 8',
                        9 => 'Kelas 9',
                    ])
                    ->native(false),
            ])
            ->recordActions([
                Action::make('assign_wali_kelas')
                    ->label('Wali Kelas')
                    ->icon('heroicon-o-user')
                    ->color('warning')
                    ->form([
                        Select::make('teacher_id')
                            ->label('Pilih Wali Kelas')
                            ->options(Guru::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->action(function (Kelas $record, array $data): void {
                        $activeTahunAjaranId = PengaturanSekolah::current()?->academic_year_id_active ?? \App\Models\TahunAjaran::aktif()->first()?->id;
                        if (!$activeTahunAjaranId) {
                            Notification::make()->title('Gagal')->body('Tidak ada tahun ajaran aktif.')->danger()->send();
                            return;
                        }
                        
                        KelasAjaran::updateOrCreate(
                            ['class_id' => $record->id, 'academic_year_id' => $activeTahunAjaranId],
                            ['teacher_id' => $data['teacher_id']]
                        );

                        Notification::make()->title('Wali kelas berhasil diubah')->success()->send();
                    })
                    ->disabled(fn (): bool => !(auth()->user()?->isSuperAdmin() ?? false)),

                Action::make('assign_pembelajaran')
                    ->label('Pembelajaran')
                    ->icon('heroicon-o-book-open')
                    ->color('info')
                    ->modalHeading(fn (Kelas $record): string => "Kelola Pembelajaran - Kelas {$record->name}")
                    ->modalDescription('Tentukan guru pengajar untuk setiap mata pelajaran pada kelas ini untuk tahun ajaran aktif.')
                    ->modalWidth('2xl')
                    ->form(function (Kelas $record): array {
                        $mapels = \App\Models\MataPelajaran::orderBy('nama_mapel')->get();
                        $gurus = Guru::pluck('name', 'id')->toArray();
                        
                        $fields = [];
                        foreach ($mapels as $mapel) {
                            $fields[] = \Filament\Forms\Components\Select::make("assignments.{$mapel->id}")
                                ->label($mapel->nama_mapel)
                                ->options($gurus)
                                ->searchable()
                                ->preload()
                                ->placeholder('— Belum Ditentukan —');
                        }
                        
                        return [
                            \Filament\Schemas\Components\Grid::make(2)
                                ->schema($fields),
                        ];
                    })
                    ->fillForm(function (Kelas $record): array {
                        $activeTahunAjaranId = PengaturanSekolah::current()?->academic_year_id_active ?? \App\Models\TahunAjaran::aktif()->first()?->id;
                        $kelasAjaran = $activeTahunAjaranId ? KelasAjaran::where('class_id', $record->id)->where('academic_year_id', $activeTahunAjaranId)->first() : null;

                        $existingAssignments = [];
                        if ($kelasAjaran) {
                            $existingAssignments = \App\Models\Pengajaran::where('class_academic_year_id', $kelasAjaran->id)
                                ->pluck('teacher_id', 'mata_pelajaran_id')
                                ->toArray();
                        }

                        return [
                            'assignments' => $existingAssignments,
                        ];
                    })
                    ->action(function (Kelas $record, array $data): void {
                        $activeTahunAjaranId = PengaturanSekolah::current()?->academic_year_id_active ?? \App\Models\TahunAjaran::aktif()->first()?->id;
                        if (!$activeTahunAjaranId) {
                            Notification::make()->title('Gagal')->body('Tidak ada tahun ajaran aktif.')->danger()->send();
                            return;
                        }

                        $kelasAjaran = KelasAjaran::firstOrCreate(
                            ['class_id' => $record->id, 'academic_year_id' => $activeTahunAjaranId],
                            ['teacher_id' => null]
                        );

                        $assignments = $data['assignments'] ?? [];
                        foreach ($assignments as $mapelId => $teacherId) {
                            if (empty($teacherId)) {
                                \App\Models\Pengajaran::where('class_academic_year_id', $kelasAjaran->id)
                                    ->where('mata_pelajaran_id', $mapelId)
                                    ->delete();
                            } else {
                                \App\Models\Pengajaran::updateOrCreate(
                                    [
                                        'class_academic_year_id' => $kelasAjaran->id,
                                        'mata_pelajaran_id' => $mapelId,
                                    ],
                                    [
                                        'teacher_id' => $teacherId,
                                    ]
                                );
                            }
                        }

                        Notification::make()->title('Pembelajaran kelas berhasil disimpan')->success()->send();
                    })
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),

                Action::make('assign_ekstrakurikuler')
                    ->label('Ekstrakurikuler')
                    ->icon('heroicon-o-star')
                    ->color('success')
                    ->action(function () {
                        Notification::make()->title('Info')->body('Fitur Ekstrakurikuler akan segera hadir.')->info()->send();
                    }),
            ])
            ->bulkActions([]);
    }
}

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
                    ->url(fn (Kelas $record): string => \App\Filament\Akademik\Resources\Kelas\Pages\ManagePembelajaranKelas::getUrl(['record' => $record->id]))
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

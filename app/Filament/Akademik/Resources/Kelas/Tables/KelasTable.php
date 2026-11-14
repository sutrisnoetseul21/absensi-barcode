<?php

namespace App\Filament\Akademik\Resources\Kelas\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class KelasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Kelas')
                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                    ->size('md')
                    ->icon('heroicon-o-building-office-2')
                    ->iconColor('primary')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('grade_level')
                    ->label('Tingkat / Jenjang')
                    ->badge()
                    ->color(fn (int $state): string => match ($state % 3) {
                        1 => 'info',
                        2 => 'success',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (int $state): string => "Kelas {$state}")
                    ->sortable(),

                TextColumn::make('wali_kelas')
                    ->label('Wali Kelas (Aktif)')
                    ->icon('heroicon-o-user-circle')
                    ->getStateUsing(function (\App\Models\Kelas $record) {
                        $activeTahunAjaranId = \App\Models\PengaturanSekolah::current()?->academic_year_id_active ?? \App\Models\TahunAjaran::aktif()->first()?->id;
                        if (!$activeTahunAjaranId) return 'Tahun Ajaran Belum Aktif';

                        $kelasAjaran = \App\Models\KelasAjaran::where('class_id', $record->id)
                            ->where('academic_year_id', $activeTahunAjaranId)
                            ->first();

                        return $kelasAjaran?->guru?->name ?? 'Belum Ditentukan';
                    })
                    ->badge(fn (string $state): bool => $state === 'Belum Ditentukan' || $state === 'Tahun Ajaran Belum Aktif')
                    ->color(fn (string $state): string => $state === 'Belum Ditentukan' || $state === 'Tahun Ajaran Belum Aktif' ? 'warning' : 'gray'),

                TextColumn::make('jumlah_siswa')
                    ->label('Jumlah Siswa')
                    ->icon('heroicon-o-user-group')
                    ->getStateUsing(function (\App\Models\Kelas $record) {
                        $activeTahunAjaranId = \App\Models\PengaturanSekolah::current()?->academic_year_id_active ?? \App\Models\TahunAjaran::aktif()->first()?->id;
                        if (!$activeTahunAjaranId) return '0 Siswa';

                        $count = \App\Models\EnrollmentSiswa::where('class_id', $record->id)
                            ->where('academic_year_id', $activeTahunAjaranId)
                            ->where('status', 'aktif')
                            ->count();

                        return "{$count} Siswa";
                    })
                    ->badge()
                    ->color('primary')
                    ->tooltip('Klik untuk melihat & kelola siswa di kelas ini')
                    ->action(
                        Action::make('lihat_siswa')
                            ->modalHeading(fn (\App\Models\Kelas $record): string => "Kelola Siswa Rombel - Kelas {$record->name}")
                            ->modalWidth('7xl')
                            ->modalSubmitAction(false)
                            ->modalCancelAction(false)
                            ->modalContent(function (\App\Models\Kelas $record, $livewire) {
                                $activeTahunAjaranId = \App\Models\PengaturanSekolah::current()?->academic_year_id_active
                                    ?? \App\Models\TahunAjaran::aktif()->first()?->id;

                                return \App\Filament\Akademik\Resources\Enrollment\Tables\EnrollmentTable::buildRombelModalContent(
                                    $record,
                                    $activeTahunAjaranId,
                                    $livewire
                                );
                            })
                    ),
            ])
            ->headerActions([])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('grade_level')
                    ->label('Filter Tingkat')
                    ->options(fn (): array => match (\App\Models\PengaturanSekolah::current()?->school_level) {
                        'SD' => [1 => 'Kelas 1', 2 => 'Kelas 2', 3 => 'Kelas 3', 4 => 'Kelas 4', 5 => 'Kelas 5', 6 => 'Kelas 6'],
                        'SMA', 'SMK' => [10 => 'Kelas 10', 11 => 'Kelas 11', 12 => 'Kelas 12'],
                        default => [7 => 'Kelas 7', 8 => 'Kelas 8', 9 => 'Kelas 9'],
                    })
                    ->native(false),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('assign_wali_kelas')
                    ->label('Wali Kelas')
                    ->icon('heroicon-o-user-plus')
                    ->color('warning')
                    ->button()
                    ->outlined()
                    ->size('sm')
                    ->form([
                        \Filament\Forms\Components\Select::make('teacher_id')
                            ->label('Pilih Wali Kelas')
                            ->options(\App\Models\Guru::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->fillForm(function (\App\Models\Kelas $record): array {
                        $activeTahunAjaranId = \App\Models\PengaturanSekolah::current()?->academic_year_id_active ?? \App\Models\TahunAjaran::aktif()->first()?->id;
                        $kelasAjaran = $activeTahunAjaranId ? \App\Models\KelasAjaran::where('class_id', $record->id)->where('academic_year_id', $activeTahunAjaranId)->first() : null;
                        return [
                            'teacher_id' => $kelasAjaran?->teacher_id,
                        ];
                    })
                    ->action(function (\App\Models\Kelas $record, array $data): void {
                        $activeTahunAjaranId = \App\Models\PengaturanSekolah::current()?->academic_year_id_active ?? \App\Models\TahunAjaran::aktif()->first()?->id;
                        if (!$activeTahunAjaranId) {
                            \Filament\Notifications\Notification::make()->title('Gagal')->body('Tidak ada tahun ajaran aktif.')->danger()->send();
                            return;
                        }
                        
                        \App\Models\KelasAjaran::updateOrCreate(
                            ['class_id' => $record->id, 'academic_year_id' => $activeTahunAjaranId],
                            ['teacher_id' => $data['teacher_id']]
                        );

                        \Filament\Notifications\Notification::make()->title('Wali kelas berhasil disimpan')->success()->send();
                    })
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_akademik_editor') || auth()->user()?->hasRole('admin_master_editor')),

                Action::make('assign_pembelajaran')
                    ->label('Pembelajaran')
                    ->icon('heroicon-o-book-open')
                    ->color('info')
                    ->button()
                    ->outlined()
                    ->size('sm')
                    ->modalHeading(fn (\App\Models\Kelas $record): string => "Kelola Pembelajaran - Kelas {$record->name}")
                    ->modalDescription('Tentukan guru pengajar untuk setiap mata pelajaran pada kelas ini untuk tahun ajaran aktif.')
                    ->modalWidth('2xl')
                    ->form(function (\App\Models\Kelas $record): array {
                        $mapels = \App\Models\MataPelajaran::orderBy('nama_mapel')->get();
                        $gurus = \App\Models\Guru::pluck('name', 'id')->toArray();
                        
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
                    ->fillForm(function (\App\Models\Kelas $record): array {
                        $activeTahunAjaranId = \App\Models\PengaturanSekolah::current()?->academic_year_id_active ?? \App\Models\TahunAjaran::aktif()->first()?->id;
                        $kelasAjaran = $activeTahunAjaranId ? \App\Models\KelasAjaran::where('class_id', $record->id)->where('academic_year_id', $activeTahunAjaranId)->first() : null;

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
                    ->action(function (\App\Models\Kelas $record, array $data): void {
                        $activeTahunAjaranId = \App\Models\PengaturanSekolah::current()?->academic_year_id_active ?? \App\Models\TahunAjaran::aktif()->first()?->id;
                        if (!$activeTahunAjaranId) {
                            \Filament\Notifications\Notification::make()->title('Gagal')->body('Tidak ada tahun ajaran aktif.')->danger()->send();
                            return;
                        }

                        $kelasAjaran = \App\Models\KelasAjaran::firstOrCreate(
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

                        \Filament\Notifications\Notification::make()->title('Pembelajaran kelas berhasil disimpan')->success()->send();
                    })
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_akademik_editor') || auth()->user()?->hasRole('admin_master_editor')),

                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit Kelas')
                    ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_akademik_editor') || auth()->user()?->hasRole('admin_master_editor'))
                    ->modalDescription(function (\App\Models\Kelas $record) {
                        $hasPengajaran = $record->pengajarans()->exists() ?? false;
                        $hasSiswa = $record->enrollments()->exists() ?? false;
                        
                        if ($hasPengajaran || $hasSiswa) {
                            return new \Illuminate\Support\HtmlString('<span style="color: #ef4444; font-weight: bold;">⚠️ Peringatan: Kelas ini sudah terisi (' . ($hasSiswa ? 'Siswa' : '') . ($hasPengajaran && $hasSiswa ? ' & ' : '') . ($hasPengajaran ? 'Pembelajaran' : '') . '). Mengubah data dapat merusak riwayat akademik!</span>');
                        }
                        return null;
                    })
                    ->before(function (\App\Models\Kelas $record, \Filament\Actions\EditAction $action) {
                        $hasPengajaran = $record->pengajarans()->exists() ?? false;
                        $hasSiswa = $record->enrollments()->exists() ?? false;

                        if ($hasSiswa || $hasPengajaran) {
                            \Filament\Notifications\Notification::make()
                                ->title('Perubahan Ditolak')
                                ->body('Kelas tidak dapat diubah karena sudah memiliki data Siswa atau Pembelajaran aktif.')
                                ->danger()
                                ->persistent()
                                ->send();
                            
                            $action->cancel();
                        }
                    }),

                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Hapus Kelas')
                    ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_akademik_editor') || auth()->user()?->hasRole('admin_master_editor'))
                    ->before(function (\App\Models\Kelas $record, DeleteAction $action) {
                        $hasPengajaran = $record->pengajarans()->exists() ?? false;
                        $hasSiswa = $record->enrollments()->exists() ?? false;

                        if ($hasSiswa || $hasPengajaran) {
                            \Filament\Notifications\Notification::make()
                                ->title('Akses Ditolak')
                                ->body('Kelas tidak dapat dihapus! Anda harus mengosongkan/menghapus data Siswa dan Pembelajaran yang terkait dengan kelas ini terlebih dahulu.')
                                ->danger()
                                ->persistent()
                                ->send();
                            
                            $action->cancel();
                        }
                    }),

                RestoreAction::make()
                    ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_akademik_editor') || auth()->user()?->hasRole('admin_master_editor'))
                    ->iconButton(),
            ])
            ->defaultGroup('grade_level')
            ->groups([
                Group::make('grade_level')
                    ->label('Jenjang')
                    ->getTitleFromRecordUsing(fn (\App\Models\Kelas $record): string => "Kelas {$record->grade_level}")
                    ->collapsible(),
            ])
            ->defaultSort('name', 'asc');
    }
}

<?php

namespace App\Filament\Akademik\Resources\UjianAkademik\RelationManagers;

use App\Models\KelasAjaran;
use App\Models\NilaiUjian;
use App\Models\Pengajaran;
use App\Services\Cbt\CbtServiceInterface;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NilaiUjianRelationManager extends RelationManager
{
    protected static string $relationship = 'nilaiUjians';

    protected static ?string $title = 'Rekapitulasi Nilai Peserta';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (\Illuminate\Database\Eloquent\Builder $query) {
                $user = auth()->user();
                
                // Jika user adalah super admin atau admin, tampilkan semua nilai
                if ($user && ($user->isSuperAdmin() || $user->hasRole('admin_akademik_editor') || $user->hasRole('admin_master_editor'))) {
                    return $query;
                }

                // Jika user adalah guru, batasi nilai hanya untuk kelas yang diajarnya pada mapel ini
                if ($user && $user->guru) {
                    $mataPelajaranId = $this->getOwnerRecord()->mata_pelajaran_id;
                    $classAcademicYearIds = \App\Models\Pengajaran::where('teacher_id', $user->guru->id)
                        ->where('mata_pelajaran_id', $mataPelajaranId)
                        ->pluck('class_academic_year_id')
                        ->toArray();
                    
                    $classIds = \App\Models\KelasAjaran::whereIn('id', $classAcademicYearIds)
                        ->pluck('class_id')
                        ->toArray();
                    
                    $query->whereIn('class_id', $classIds);
                }

                return $query;
            })
            ->recordTitleAttribute('siswa.name')
            ->columns([
                TextColumn::make('siswa.nisn')
                    ->label('NISN')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('siswa.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('kelas.name')
                    ->label('Kelas')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('guru_pengampu')
                    ->label('Guru Pengampu')
                    ->getStateUsing(function (NilaiUjian $record): string {
                        if (! $record->class_id || ! $record->ujianAkademik?->mata_pelajaran_id) {
                            return '—';
                        }

                        $kelasAjaranId = \App\Models\KelasAjaran::where('class_id', $record->class_id)
                            ->where('academic_year_id', $record->academic_year_id)
                            ->value('id');

                        if (! $kelasAjaranId) return '—';

                        $guru = \App\Models\Pengajaran::where('class_academic_year_id', $kelasAjaranId)
                            ->where('mata_pelajaran_id', $record->ujianAkademik->mata_pelajaran_id)
                            ->first()
                            ?->guru;

                        return $guru?->name ?? '—';
                    })
                    ->color('gray'),

                TextColumn::make('nilai_akhir')
                    ->label('Nilai Akhir')
                    ->sortable()
                    ->weight('bold')
                    ->color(fn (NilaiUjian $record) => $record->is_tuntas ? 'success' : 'danger')
                    ->description(fn (NilaiUjian $record) => $record->is_tuntas ? 'Tuntas KKM' : 'Belum Tuntas'),

                IconColumn::make('is_tuntas')
                    ->label('Status KKM')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('jumlah_benar')
                    ->label('Benar')
                    ->color('success'),

                TextColumn::make('jumlah_salah')
                    ->label('Salah')
                    ->color('danger'),

                TextColumn::make('jumlah_kosong')
                    ->label('Kosong')
                    ->color('gray'),

                TextColumn::make('violation_count')
                    ->label('Pelanggaran')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'gray'),

                TextColumn::make('submitted_at')
                    ->label('Waktu Submit')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('is_tuntas')
                    ->label('Ketuntasan KKM')
                    ->options([
                        '1' => 'Tuntas KKM',
                        '0' => 'Belum Tuntas',
                    ]),
            ])
            ->headerActions([
                Action::make('tarik_nilai_sekarang')
                    ->label('Tarik Nilai dari ZenCBT')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Tarik Nilai dari ZenCBT')
                    ->modalDescription('Mengambil skor dan butir jawaban terbaru dari engine ZenCBT.')
                    ->form([
                        TextInput::make('cbt_exam_id')
                            ->label('ID Ujian di ZenCBT')
                            ->numeric()
                            ->required()
                            ->default(fn ($livewire) => $livewire->ownerRecord->cbt_ujian_id)
                            ->helperText('ID numerik agenda ujian yang tercatat di ZenCBT.'),
                    ])
                    ->action(function ($livewire, array $data) {
                        try {
                            $ujian = $livewire->ownerRecord;
                            $cbtService = app(CbtServiceInterface::class);
                            $cbtExamId = (int) $data['cbt_exam_id'];

                            if ($ujian->cbt_ujian_id !== $cbtExamId) {
                                $ujian->update(['cbt_ujian_id' => $cbtExamId]);
                            }

                            $results = $cbtService->pullExamResults($cbtExamId, $ujian);
                            $total = count($results);

                            Notification::make()
                                ->title('Penarikan Nilai Sukses')
                                ->body("Berhasil memproses {$total} data nilai peserta.")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal Menarik Nilai')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->defaultSort('nilai_akhir', 'desc');
    }
}

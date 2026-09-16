<?php

namespace App\Filament\Akademik\Resources\UjianAkademik\Tables;

use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use App\Models\UjianAkademik;
use App\Services\Cbt\CbtServiceInterface;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UjianAkademikTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_ujian')
                    ->label('Nama Ujian')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (UjianAkademik $record) => $record->cbt_event_nama ? "Event CBT: {$record->cbt_event_nama}" : null),

                \Filament\Tables\Columns\SelectColumn::make('jenis_ujian_id')
                    ->label('Jenis')
                    ->options(fn () => \App\Models\JenisUjian::pluck('kode', 'id')->toArray())
                    ->placeholder('Pilih Jenis'),

                TextColumn::make('mataPelajaran.nama_mapel')
                    ->label('Mata Pelajaran')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('tahunAjaran.name')
                    ->label('T.A / Semester')
                    ->formatStateUsing(fn ($state, UjianAkademik $record) => ($state ?? '-') . ' (' . ucfirst($record->semester ?? '-') . ')')
                    ->sortable(),

                TextColumn::make('kkm')
                    ->label('KKM')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('classes.name')
                    ->label('Rombel Sasaran')
                    ->badge()
                    ->separator(', ')
                    ->default('—'),

                TextColumn::make('nilai_ujians_count')
                    ->counts('nilaiUjians')
                    ->label('Peserta Terdata')
                    ->badge()
                    ->color('success'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft'   => 'Draft',
                        'aktif'   => 'Aktif',
                        'selesai' => 'Selesai',
                        'arsip'   => 'Arsip',
                        default   => ucfirst((string) $state)
                    })
                    ->color(fn ($state) => match ($state) {
                        'draft'   => 'gray',
                        'aktif'   => 'success',
                        'selesai' => 'primary',
                        'arsip'   => 'danger',
                        default   => 'gray'
                    }),

                TextColumn::make('durasi_menit')
                    ->label('Durasi')
                    ->formatStateUsing(fn ($state) => $state ? "{$state} mnt" : '—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tanggal_mulai')
                    ->label('Waktu Mulai')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->options(TahunAjaran::pluck('name', 'id')),

                SelectFilter::make('semester')
                    ->label('Semester')
                    ->options([
                        'ganjil' => 'Ganjil',
                        'genap'  => 'Genap',
                    ]),

                SelectFilter::make('jenis_ujian')
                    ->label('Jenis Asesmen')
                    ->options([
                        'harian' => 'Ulangan Harian (UH)',
                        'sts'    => 'Sumatif Tengah Semester (ASTS)',
                        'sas'    => 'Sumatif Akhir Semester (ASAS)',
                        'tryout' => 'Try Out (TO)',
                    ]),

                SelectFilter::make('mata_pelajaran_id')
                    ->label('Mata Pelajaran')
                    ->options(MataPelajaran::pluck('nama_mapel', 'id')),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft'   => 'Draft',
                        'aktif'   => 'Aktif',
                        'selesai' => 'Selesai',
                        'arsip'   => 'Diarsipkan',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),

                // Aksi Tarik Nilai Langsung dari ZenCBT
                Action::make('tarik_nilai')
                    ->label('Tarik Nilai')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Tarik Nilai dari ZenCBT')
                    ->modalDescription('Sistem akan mengambil hasil ujian siswa dari engine ZenCBT untuk agenda ini secara otomatis.')
                    ->action(function (UjianAkademik $record) {
                        try {
                            $cbtService = app(CbtServiceInterface::class);
                            $cbtExamId = (int) $record->cbt_ujian_id;

                            if (!$cbtExamId) {
                                throw new \Exception('ID Ujian ZenCBT tidak ditemukan pada data ini.');
                            }

                            $results = $cbtService->pullExamResults($cbtExamId, $record);
                            $synced = $results['synced'] ?? 0;
                            $skipped = $results['skipped'] ?? 0;

                            Notification::make()
                                ->title('Penarikan Nilai Selesai')
                                ->body("Berhasil memperbarui data nilai untuk {$synced} peserta. (Dilewati: {$skipped})")
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
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

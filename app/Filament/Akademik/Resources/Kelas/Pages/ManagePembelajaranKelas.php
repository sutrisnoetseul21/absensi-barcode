<?php

namespace App\Filament\Akademik\Resources\Kelas\Pages;

use App\Filament\Akademik\Resources\Kelas\KelasResource;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\KelasAjaran;
use App\Models\MataPelajaran;
use App\Models\Pengajaran;
use App\Models\PengaturanSekolah;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ManagePembelajaranKelas extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;
    use InteractsWithRecord;

    protected static string $resource = \App\Filament\Akademik\Resources\RombonganBelajarResource::class;

    protected string $view = 'filament.akademik.resources.kelas.pages.manage-pembelajaran';

    public ?string $activeTahunAjaranName = null;
    public ?string $activeTahunAjaranId = null;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        
        $pengaturan = PengaturanSekolah::current();
        $activeTahunAjaran = $pengaturan?->tahunAjaranAktif ?? TahunAjaran::aktif()->first();
        
        $this->activeTahunAjaranId = $activeTahunAjaran?->id;
        $this->activeTahunAjaranName = $activeTahunAjaran?->name ?? 'Belum Diatur';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(MataPelajaran::query())
            ->columns([
                TextColumn::make('rowIndex')
                    ->label('No')
                    ->rowIndex(),
                
                TextColumn::make('nama_mapel')
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->sortable(),
                
                SelectColumn::make('guru')
                    ->label('Guru')
                    ->options(Guru::pluck('name', 'id')->toArray())
                    ->placeholder('— Belum Ditentukan —')
                    ->searchable()
                    ->getStateUsing(function (MataPelajaran $record) {
                        $activeTahunAjaranId = PengaturanSekolah::current()?->academic_year_id_active ?? \App\Models\TahunAjaran::aktif()->first()?->id;
                        if (!$activeTahunAjaranId) return null;

                        $kelasAjaran = KelasAjaran::where('class_id', $this->record->id)
                            ->where('academic_year_id', $activeTahunAjaranId)
                            ->first();
                        
                        if (!$kelasAjaran) return null;

                        $pengajaran = Pengajaran::where('class_academic_year_id', $kelasAjaran->id)
                            ->where('mata_pelajaran_id', $record->id)
                            ->first();

                        return $pengajaran?->teacher_id;
                    })
                    ->updateStateUsing(function (MataPelajaran $record, $state) {
                        $activeTahunAjaranId = PengaturanSekolah::current()?->academic_year_id_active ?? \App\Models\TahunAjaran::aktif()->first()?->id;
                        if (!$activeTahunAjaranId) {
                            Notification::make()->title('Gagal')->body('Tidak ada tahun ajaran aktif.')->danger()->send();
                            return;
                        }

                        // Pastikan KelasAjaran (rombel) ada
                        $kelasAjaran = KelasAjaran::firstOrCreate(
                            ['class_id' => $this->record->id, 'academic_year_id' => $activeTahunAjaranId],
                            ['teacher_id' => null] // Jangan timpa jika sudah ada
                        );

                        if (empty($state)) {
                            // Hapus penugasan jika dikosongkan
                            Pengajaran::where('class_academic_year_id', $kelasAjaran->id)
                                ->where('mata_pelajaran_id', $record->id)
                                ->delete();
                        } else {
                            // Update atau buat penugasan baru
                            Pengajaran::updateOrCreate(
                                [
                                    'class_academic_year_id' => $kelasAjaran->id,
                                    'mata_pelajaran_id' => $record->id,
                                ],
                                [
                                    'teacher_id' => $state,
                                ]
                            );
                        }
                        
                        Notification::make()->title('Guru berhasil disimpan')->success()->send();
                    })
                    ->disabled(fn (): bool => !(auth()->user()?->isSuperAdmin() ?? false))
                    ->extraAttributes(['style' => 'min-width: 200px;']),
            ])
            ->paginated(false);
    }
}

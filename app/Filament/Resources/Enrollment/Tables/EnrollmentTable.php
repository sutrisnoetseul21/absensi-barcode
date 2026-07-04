<?php

namespace App\Filament\Resources\Enrollment\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Models\TahunAjaran;
use App\Models\Kelas;
use Filament\Notifications\Notification;

class EnrollmentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),

                TextColumn::make('name')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('grade_level')
                    ->label('Angkatan')
                    ->sortable()
                    ->formatStateUsing(fn (int $state): string => "Kelas {$state}"),

                TextColumn::make('students_count')
                    ->label('Jumlah Siswa')
                    ->getStateUsing(function (\App\Models\Kelas $record, Table $table) {
                        $academicYearId = $table->getFilter('academic_year_id')->getState()['value'] 
                            ?? \App\Models\PengaturanSekolah::current()?->academic_year_id_active;
                        
                        if (!$academicYearId) return 0;
                        
                        return \App\Models\EnrollmentSiswa::where('class_id', $record->id)
                            ->where('academic_year_id', $academicYearId)
                            ->where('status', 'aktif')
                            ->count();
                    }),
            ])
            ->filters([
                SelectFilter::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->options(TahunAjaran::pluck('name', 'id')->toArray())
                    ->default(fn () => \App\Models\PengaturanSekolah::current()?->academic_year_id_active)
                    ->selectablePlaceholder(false)
                    ->query(fn ($query) => $query),
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->headerActions([
                \App\Filament\Resources\Enrollment\Actions\LuluskanKelas9Action::make(),
                \App\Filament\Resources\Enrollment\Actions\BatalkanKelulusanMassalAction::make(),
                
                // Download Template Naik Kelas (Siswa Lama)
                Action::make('download_template_naik_kelas')
                    ->visible(fn () => \App\Models\PengaturanSekolah::current()?->enable_promotion_features ?? false)
                    ->label('Template Naik Kelas')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->modalHeading('Unduh Template Naik Kelas')
                    ->modalDescription('Pilih Tahun Ajaran asal siswa saat ini dan Tahun Ajaran tujuan kenaikan kelas.')
                    ->form([
                        \Filament\Forms\Components\Select::make('source_academic_year_id')
                            ->label('Dari Tahun Ajaran')
                            ->options(TahunAjaran::orderedByYear()->pluck('name', 'id')->toArray())
                            ->default(fn () => \App\Models\PengaturanSekolah::current()?->academic_year_id_active)
                            ->required()
                            ->live(),

                        \Filament\Forms\Components\Select::make('target_academic_year_id')
                            ->label('Ke Tahun Ajaran (Tujuan)')
                            ->options(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                $sourceId = $get('source_academic_year_id');
                                if (!$sourceId) return [];
                                $source = TahunAjaran::find($sourceId);
                                if (!$source) return [];
                                // Hanya tampilkan TP berikutnya langsung (start_year = source end_year)
                                return TahunAjaran::where('start_year', $source->end_year)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->required()
                            ->helperText('Hanya Tahun Ajaran yang langsung berurutan yang bisa dipilih.'),
                    ])
                    ->action(function (array $data) {
                        $sourceId  = $data['source_academic_year_id'];
                        $targetId  = $data['target_academic_year_id'];

                        $source = TahunAjaran::find($sourceId);
                        $target = TahunAjaran::find($targetId);

                        // Guard: harus berurutan
                        if (!$source || !$target || $target->start_year !== $source->end_year) {
                            Notification::make()->title('Gagal')->body('Tahun ajaran tujuan harus berurutan langsung setelah tahun ajaran asal.')->danger()->send();
                            return;
                        }

                        // Guard: pastikan semua siswa kelas 9 di TP asal sudah lulus
                        $belumLulus = \App\Models\EnrollmentSiswa::where('academic_year_id', $sourceId)
                            ->where('status', 'aktif')
                            ->whereHas('kelas', fn($q) => $q->where('grade_level', 9))
                            ->count();

                        if ($belumLulus > 0) {
                            Notification::make()
                                ->title('Kelas 9 Belum Diluluskan')
                                ->body("Masih ada **{$belumLulus}** siswa kelas 9 yang belum diluluskan di Tahun Ajaran **{$source->name}**. Harap luluskan mereka terlebih dahulu sebelum menaikkan kelas.")
                                ->danger()
                                ->persistent()
                                ->send();
                            return;
                        }

                        $safeName = str_replace('/', '-', $source->name) . '_ke_' . str_replace('/', '-', $target->name);
                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\SiswaNaikKelasExport($sourceId, $targetId),
                            'template_naik_kelas_' . $safeName . '.xlsx'
                        );
                    }),

                \App\Filament\Resources\Enrollment\Actions\ImportNaikKelasAction::make(),
            ])
            ->actions([
                Action::make('manage_rombel')
                    ->label('Kelola Siswa')
                    ->icon('heroicon-o-users')
                    ->color('primary')
                    ->modalWidth('7xl')
                    ->modalHeading(fn (\App\Models\Kelas $record) => "Kelola Siswa Rombel - Kelas {$record->name}")
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->modalContent(function (\App\Models\Kelas $record, Table $table, $livewire) {
                        $academicYearId = $table->getFilter('academic_year_id')->getState()['value'] 
                            ?? \App\Models\PengaturanSekolah::current()?->academic_year_id_active;

                        // Set Livewire component state
                        $livewire->manageClassId = $record->id;
                        $livewire->manageAcademicYearId = $academicYearId;

                        // Query students enrolled
                        $leftStudents = \App\Models\Siswa::whereHas('enrollments', function ($q) use ($record, $academicYearId) {
                            $q->where('class_id', $record->id)
                              ->where('academic_year_id', $academicYearId)
                              ->where('status', 'aktif');
                        })
                        ->when($livewire->searchLeft, function ($q) use ($livewire) {
                            $q->where(fn($sub) => $sub->where('name', 'like', '%'.$livewire->searchLeft.'%')->orWhere('nisn', 'like', '%'.$livewire->searchLeft.'%'));
                        })
                        ->orderBy('name', 'asc')
                        ->get();

                        // Query students without class in the selected academic year
                        $rightStudents = \App\Models\Siswa::whereDoesntHave('enrollments', function ($q) use ($academicYearId) {
                            $q->where('academic_year_id', $academicYearId)
                              ->where('status', 'aktif');
                        })
                        ->when($livewire->searchRight, function ($q) use ($livewire) {
                            $q->where(fn($sub) => $sub->where('name', 'like', '%'.$livewire->searchRight.'%')->orWhere('nisn', 'like', '%'.$livewire->searchRight.'%'));
                        })
                        ->orderBy('name', 'asc')
                        ->limit(50)
                        ->get();

                        return view('filament.resources.enrollment.pages.rombel-manager-modal', [
                            'kelas' => $record,
                            'academicYear' => \App\Models\TahunAjaran::find($academicYearId),
                            'leftStudents' => $leftStudents,
                            'rightStudents' => $rightStudents,
                        ]);
                    })
            ])
            ->defaultSort('name', 'asc');
    }
}

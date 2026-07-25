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
                            
                        \Filament\Forms\Components\Placeholder::make('warning_belum_lulus')
                            ->hiddenLabel()
                            ->visible(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                $sourceId = $get('source_academic_year_id');
                                if (!$sourceId) return false;
                                
                                $belumLulus = \App\Models\EnrollmentSiswa::where('academic_year_id', $sourceId)
                                    ->where('status', 'aktif')
                                    ->whereHas('kelas', fn($q) => $q->where('grade_level', 9))
                                    ->count();
                                    
                                return $belumLulus > 0;
                            })
                            ->content(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                $sourceId = $get('source_academic_year_id');
                                $source = TahunAjaran::find($sourceId);
                                
                                $belumLulus = \App\Models\EnrollmentSiswa::where('academic_year_id', $sourceId)
                                    ->where('status', 'aktif')
                                    ->whereHas('kelas', fn($q) => $q->where('grade_level', 9))
                                    ->count();
                                    
                                return new \Illuminate\Support\HtmlString("
                                    <div class='p-3.5 bg-amber-50 text-amber-800 dark:bg-amber-500/10 dark:text-amber-400 rounded-lg border border-amber-200 dark:border-amber-500/20 text-sm flex gap-3 items-start mt-2 shadow-sm'>
                                        <svg class='w-5 h-5 mt-0.5 shrink-0 text-amber-500' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'>
                                            <path stroke-linecap='round' stroke-linejoin='round' d='M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'/>
                                        </svg>
                                        <div>
                                            <div class='font-bold mb-1'>Kelas 9 Belum Lulus</div>
                                            Masih ada <strong>{$belumLulus}</strong> siswa kelas 9 di TA <strong>" . ($source?->name) . "</strong> yang belum diluluskan. Jika dilanjutkan, siswa tersebut akan diabaikan dari template (dianggap tidak naik/lulus).
                                        </div>
                                    </div>
                                ");
                            }),
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
                        $livewire->manageClassId       = $record->id;
                        $livewire->manageAcademicYearId = $academicYearId;

                        // Cari TA sebelumnya: end_year = selectedYear.start_year
                        $selectedYear = \App\Models\TahunAjaran::find($academicYearId);
                        $previousYear = $selectedYear
                            ? \App\Models\TahunAjaran::where('end_year', $selectedYear->start_year)->first()
                            : null;

                        $targetGradeLevel = $record->grade_level;

                        // ── Panel Kiri: siswa yang sudah enrolled di kelas & TA ini ─────────
                        $leftStudents = \App\Models\Siswa::whereHas('enrollments', function ($q) use ($record, $academicYearId) {
                            $q->where('class_id', $record->id)
                              ->where('academic_year_id', $academicYearId)
                              ->where('status', 'aktif');
                        })
                        ->when($livewire->searchLeft, function ($q) use ($livewire) {
                            $q->where(fn($sub) => $sub
                                ->where('name', 'like', '%'.$livewire->searchLeft.'%')
                                ->orWhere('nisn', 'like', '%'.$livewire->searchLeft.'%')
                            );
                        })
                        ->orderBy('name')
                        ->get();

                        // ── Panel Kanan: kandidat siswa (smart filter) ────────────────────
                        $rightStudents = \App\Models\Siswa::where('status', 'aktif')
                            // Belum enrolled di TA yang dipilih
                            ->whereDoesntHave('enrollments', fn($q) =>
                                $q->where('academic_year_id', $academicYearId)->where('status', 'aktif')
                            )
                            ->where(function ($q) use ($previousYear, $targetGradeLevel) {
                                // Kasus 1: Belum pernah punya enrollment sama sekali (PPDB murni)
                                $q->orWhereDoesntHave('enrollments');

                                if ($previousYear) {
                                    // Kasus 2: Di TA sebelumnya punya kelas grade = G (tinggal kelas)
                                    $q->orWhereHas('enrollments', fn($eq) =>
                                        $eq->where('academic_year_id', $previousYear->id)
                                           ->whereHas('kelas', fn($k) =>
                                               $k->where('grade_level', $targetGradeLevel)
                                           )
                                    );

                                    // Kasus 3: Di TA sebelumnya punya kelas grade = G-1 (naik kelas)
                                    // Grade 7 tidak punya grade di bawahnya di SMP
                                    if ($targetGradeLevel > 7) {
                                        $q->orWhereHas('enrollments', fn($eq) =>
                                            $eq->where('academic_year_id', $previousYear->id)
                                               ->whereHas('kelas', fn($k) =>
                                                   $k->where('grade_level', $targetGradeLevel - 1)
                                               )
                                        );
                                    }
                                }
                            })
                            ->when($livewire->searchRight, fn($q) =>
                                $q->where(fn($sub) => $sub
                                    ->where('name', 'like', '%'.$livewire->searchRight.'%')
                                    ->orWhere('nisn', 'like', '%'.$livewire->searchRight.'%')
                                )
                            )
                            ->orderBy('name')
                            ->limit(100)
                            ->get();

                        // ── Map: nisn → nama kelas di TA sebelumnya (untuk kolom "Kelas Sebelumnya") ──
                        $previousClassMap = [];
                        if ($previousYear && $rightStudents->isNotEmpty()) {
                            $studentIds = $rightStudents->pluck('id')->toArray();
                            $prevEnrollments = \App\Models\EnrollmentSiswa::with('kelas')
                                ->where('academic_year_id', $previousYear->id)
                                ->whereIn('student_id', $studentIds)
                                ->get()
                                ->keyBy('student_id');

                            foreach ($prevEnrollments as $studentId => $enrollment) {
                                $previousClassMap[$studentId] = $enrollment->kelas?->name ?? '—';
                            }
                        }

                        return view('filament.resources.enrollment.pages.rombel-manager-modal', [
                            'kelas'              => $record,
                            'academicYear'       => \App\Models\TahunAjaran::find($academicYearId),
                            'previousYear'       => $previousYear,
                            'targetGradeLevel'   => $targetGradeLevel,
                            'leftStudents'       => $leftStudents,
                            'rightStudents'      => $rightStudents,
                            'previousClassMap'   => $previousClassMap,
                            // JSON untuk Alpine.js sort client-side
                            'leftStudentsJson'   => $leftStudents->map(fn($s) => [
                                'id'   => $s->id,
                                'name' => $s->name,
                                'nisn' => $s->nisn,
                            ])->values()->toJson(JSON_UNESCAPED_UNICODE),
                            'rightStudentsJson'  => $rightStudents->map(fn($s) => [
                                'id'              => $s->id,
                                'name'            => $s->name,
                                'nisn'            => $s->nisn,
                                'kelasSebelumnya' => $previousClassMap[$s->id] ?? null,
                            ])->values()->toJson(JSON_UNESCAPED_UNICODE),
                        ]);
                    })
            ])
            ->defaultSort('name', 'asc');
    }
}

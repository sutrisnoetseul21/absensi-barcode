<?php

namespace App\Filament\Presensi\Pages;

use Filament\Pages\Page;
use App\Filament\Traits\HasSimplePageRoleAccess;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Models\Presensi;
use App\Models\TahunAjaran;
use App\Models\Kelas;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanPresensiDetailExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class LaporanPresensi extends Page implements HasTable
{
    use HasSimplePageRoleAccess;

    protected static function getModuleRolePrefix(): string
    {
        return 'presensi';
    }

    use InteractsWithTable;

    public static function getNavigationIcon(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return 'heroicon-o-document-text';
    }

    public static function getNavigationLabel(): string
    {
        return 'Laporan Detail';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Presensi';
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Laporan Presensi Siswa';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    protected string $view = 'filament.pages.laporan-presensi';

    // Filters
    public $academicYears          = [];
    public $selectedAcademicYearId;
    public $classes                = [];
    public $selectedClassId        = null;
    public $inputDate              = null;
    public bool $hasSubmittedFilter = false;

    public function mount(): void
    {
        $this->selectedClassId = null;
        $this->inputDate       = null;
        $this->academicYears   = TahunAjaran::orderBy('start_year', 'desc')->get();

        $activeYear = TahunAjaran::where('status', 'aktif')->first() ?? $this->academicYears->first();
        if ($activeYear) {
            $this->selectedAcademicYearId = $activeYear->id;
        }

        $this->loadClasses();
    }

    public function loadClasses(): void
    {
        if (!$this->selectedAcademicYearId) {
            $this->classes        = collect();
            $this->selectedClassId = null;
            return;
        }

        $this->classes         = Kelas::orderBy('name', 'asc')->get();
        $this->selectedClassId = null;
    }

    public function updatedSelectedAcademicYearId(): void
    {
        $this->hasSubmittedFilter = false;
        $this->loadClasses();
    }

    public function updatedSelectedClassId(): void
    {
        $this->hasSubmittedFilter = false;
    }

    public function updatedInputDate(): void
    {
        $this->hasSubmittedFilter = false;
    }

    public function filterData(): void
    {
        if (!$this->selectedClassId || !$this->inputDate) {
            Notification::make()
                ->title('Pilih Filter Presensi')
                ->body('Silakan pilih Kelas dan Tanggal terlebih dahulu sebelum memproses.')
                ->warning()
                ->send();
            return;
        }

        $this->hasSubmittedFilter = true;
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                function () {
                    if (!$this->hasSubmittedFilter || !$this->selectedClassId || !$this->inputDate) {
                        return Presensi::query()->whereRaw('1 = 0');
                    }

                    return Presensi::query()
                        ->with(['siswa', 'kelas', 'inputManualOleh'])
                        ->where('academic_year_id', $this->selectedAcademicYearId)
                        ->where('class_id', $this->selectedClassId)
                        ->whereDate('date', $this->inputDate);
                }
            )
            ->columns([
                TextColumn::make('updated_at')
                    ->label('Tgl Edit/Input')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('siswa.nisn')
                    ->label('NISN')
                    ->searchable(),
                TextColumn::make('siswa.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kelas.name')
                    ->label('Kelas')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hadir' => 'success',
                        'telat' => 'warning',
                        'izin' => 'info',
                        'sakit' => 'info',
                        'alpa' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('input_method')
                    ->label('Metode Input')
                    ->getStateUsing(function (Presensi $record) {
                        return $record->is_manual_input ? 'Manual' : 'Scan Otomatis';
                    })
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Manual' ? 'warning' : 'success'),
                TextColumn::make('note')
                    ->label('Keterangan')
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return $state && strlen($state) > 40 ? $state : null;
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Presensi')
                    ->options([
                        'hadir' => 'Hadir',
                        'telat' => 'Terlambat',
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'alpa' => 'Alpa',
                    ]),
            ])
            ->defaultSort('date', 'desc')
            ->recordActions([
                DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Data Presensi')
                    ->modalDescription('Anda yakin ingin menghapus data presensi ini? Tindakan ini tidak dapat dibatalkan.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Presensi Terpilih')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Data Presensi')
                        ->modalDescription('Hapus semua data presensi yang dipilih? Tindakan ini tidak bisa dibatalkan.'),
                ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_excel')
                ->label('Export Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $query = $this->getFilteredTableQuery();
                    return Excel::download(new LaporanPresensiDetailExport($query), 'laporan_presensi_detail_' . date('Y-m-d') . '.xlsx');
                }),

            Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function () {
                    $query = $this->getFilteredTableQuery();
                    $count = $query->count();
                    
                    if ($count > 1000) {
                        Notification::make()
                            ->title('Export PDF Ditolak')
                            ->body("Data terlalu besar untuk diexport ke PDF ({$count} baris). Maksimal 1000 baris.")
                            ->danger()
                            ->send();
                        return;
                    }

                    $records = $query->orderBy('date')->get();
                    
                    $className = $this->selectedClassId ? Kelas::find($this->selectedClassId)?->name : 'Semua Kelas';
                    $ayName = $this->selectedAcademicYearId ? TahunAjaran::find($this->selectedAcademicYearId)?->name : 'Semua Tahun Ajaran';

                    $pdf = Pdf::loadView('exports.laporan-presensi-pdf', [
                        'records' => $records,
                        'monthName' => $this->inputDate ? \Carbon\Carbon::parse($this->inputDate)->isoFormat('D MMMM Y') : 'Semua Tanggal',
                        'year' => now('Asia/Jakarta')->year,
                        'className' => $className,
                        'academicYearName' => $ayName
                    ]);
                    
                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, 'laporan_presensi_' . date('Y-m-d') . '.pdf');
                }),
        ];
    }
}

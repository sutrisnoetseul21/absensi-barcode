<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
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
        return 'Absensi';
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Laporan Presensi Siswa';
    }

    public static function getNavigationSort(): ?int
    {
        return 4;
    }

    protected string $view = 'filament.pages.laporan-presensi';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Presensi::query()->with(['siswa', 'kelas'])
            )
            ->columns([
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
                TextColumn::make('note')
                    ->label('Keterangan')
                    ->limit(20)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return $state && strlen($state) > 20 ? $state : null;
                    }),
            ])
            ->filters([
                SelectFilter::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->options(TahunAjaran::pluck('name', 'id'))
                    ->default(fn () => TahunAjaran::where('status', 'aktif')->value('id')),
                SelectFilter::make('class_id')
                    ->label('Kelas')
                    ->options(Kelas::pluck('name', 'id')),
                SelectFilter::make('month')
                    ->label('Bulan')
                    ->options([
                        '1' => 'Januari',
                        '2' => 'Februari',
                        '3' => 'Maret',
                        '4' => 'April',
                        '5' => 'Mei',
                        '6' => 'Juni',
                        '7' => 'Juli',
                        '8' => 'Agustus',
                        '9' => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereMonth('date', $data['value']);
                        }
                    }),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'hadir' => 'Hadir',
                        'telat' => 'Terlambat',
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'alpa' => 'Alpa',
                    ])
            ])
            ->defaultSort('date', 'desc');
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
                            ->body("Data terlalu besar untuk diexport ke PDF ({$count} baris). Maksimal 1000 baris. Silakan gunakan Export Excel atau persempit filter Anda (misalnya pilih kelas tertentu).")
                            ->danger()
                            ->send();
                        return;
                    }

                    $records = $query->orderBy('date')->get();
                    
                    $tableFilters = $this->getTableFilterState();
                    
                    $month = $tableFilters['month']['value'] ?? null;
                    $classId = $tableFilters['class_id']['value'] ?? null;
                    $academicYearId = $tableFilters['academic_year_id']['value'] ?? null;
                    
                    $monthNames = [
                        '1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
                        '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
                        '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                    ];
                    
                    $monthName = $month ? ($monthNames[$month] ?? $month) : 'Semua Bulan';
                    $yearName = now('Asia/Jakarta')->year; 
                    $className = $classId ? Kelas::find($classId)?->name : 'Semua Kelas';
                    $ayName = $academicYearId ? TahunAjaran::find($academicYearId)?->name : 'Semua Tahun Ajaran';

                    $pdf = Pdf::loadView('exports.laporan-presensi-pdf', [
                        'records' => $records,
                        'monthName' => $monthName,
                        'year' => $yearName,
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

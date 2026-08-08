<?php

namespace App\Filament\Presensi\Pages;

use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ManajemenKartuPresensi extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon  = 'heroicon-o-identification';
    protected static ?string              $navigationLabel = 'Kartu Presensi Siswa';
    protected static ?string              $title           = 'Manajemen Kartu Presensi Siswa';
    protected static string|\UnitEnum|null $navigationGroup = 'Presensi';
    protected static ?int                 $navigationSort  = 90;
    protected string                      $view            = 'filament.pages.manajemen-kartu-presensi';

    // Filters
    public $academicYears          = [];
    public $selectedAcademicYearId;
    public $classes                = [];
    public $selectedClassId        = null;
    public bool $hasSubmittedFilter = false;

    /**
     * Hanya Superadmin yang boleh akses halaman ini.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->selectedClassId = null;
        $this->academicYears  = TahunAjaran::orderBy('start_year', 'desc')->get();

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

    public function filterData(): void
    {
        if (!$this->selectedClassId) {
            Notification::make()
                ->title('Pilih Kelas')
                ->body('Silakan pilih Kelas terlebih dahulu sebelum memproses.')
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
                    if (!$this->hasSubmittedFilter || !$this->selectedClassId) {
                        return Siswa::query()->whereRaw('1 = 0');
                    }

                    return Siswa::query()
                        ->where('status', 'aktif')
                        ->whereHas('enrollments', function (Builder $q) {
                            $q->where('class_id', $this->selectedClassId)
                              ->where('academic_year_id', $this->selectedAcademicYearId)
                              ->where('status', 'aktif');
                        });
                }
            )
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(url('https://ui-avatars.com/api/?name=Siswa&color=7F9CF5&background=EBF4FF')),

                TextColumn::make('nisn')
                    ->label('NISN')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('presensiProfile.barcode_code')
                    ->label('Barcode')
                    ->placeholder('Belum punya barcode')
                    ->copyable()
                    ->copyMessage('Barcode disalin'),

                TextColumn::make('presensiProfile.barcode_active')
                    ->label('Status Barcode')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Nonaktif')
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
            ])
            ->recordActions([
                // Cetak kartu presensi per siswa
                Action::make('cetak_kartu_presensi')
                    ->label('Cetak Kartu')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->action(function (Siswa $record, $livewire) {
                        $url = route('siswa.cetak-kartu-login', $record);
                        $livewire->js("window.open('{$url}', '_blank')");
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('cetak_kartu_presensi_massal')
                        ->label('Cetak Kartu Terpilih')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->action(function (Collection $records, $livewire) {
                            if ($records->isEmpty()) {
                                Notification::make()
                                    ->title('Tidak ada siswa dipilih')
                                    ->warning()
                                    ->send();
                                return;
                            }
                            $ids = $records->pluck('id')->implode(',');
                            $url = route('siswa.cetak-kartu-login-massal', ['ids' => $ids]);
                            $livewire->js("window.open('{$url}', '_blank')");
                        }),
                ]),
            ])
            ->headerActions([
                // Cetak SEMUA siswa di kelas terpilih
                Action::make('cetak_semua_kelas')
                    ->label('Cetak Semua Kartu Kelas Ini')
                    ->icon('heroicon-o-printer')
                    ->color('warning')
                    ->action(function ($livewire) {
                        $records = $livewire->getFilteredTableQuery()->get();

                        if ($records->isEmpty()) {
                            Notification::make()
                                ->title('Tidak ada data siswa')
                                ->warning()
                                ->send();
                            return;
                        }

                        $ids = $records->pluck('id')->implode(',');
                        $url = route('siswa.cetak-kartu-login-massal', ['ids' => $ids]);
                        $livewire->js("window.open('{$url}', '_blank')");
                    }),
            ])
            ->defaultSort('name');
    }
}

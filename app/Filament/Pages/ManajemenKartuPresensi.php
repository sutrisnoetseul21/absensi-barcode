<?php

namespace App\Filament\Pages;

use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
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

    /**
     * Hanya Superadmin yang boleh akses halaman ini.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Siswa::query()->where('status', 'aktif')
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
            ->filters([
                SelectFilter::make('academic_year')
                    ->label('Kelas (Tahun Ajaran Aktif)')
                    ->relationship('enrollmentAktif.kelas', 'name')
                    ->placeholder('Semua Kelas'),
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
                        ->label('Cetak Kartu Massal')
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
                // Cetak SEMUA siswa aktif yang sedang tampil (sesuai filter)
                Action::make('cetak_semua')
                    ->label('Cetak Semua (Terfilter)')
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

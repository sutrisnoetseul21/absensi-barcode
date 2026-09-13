<?php

namespace App\Filament\Akademik\Resources\GuruWali\RelationManagers;

use App\Models\KelompokGuruWaliSiswa;
use App\Models\Siswa;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AnggotaSiswaRelationManager extends RelationManager
{
    protected static string $relationship = 'anggota';

    protected static ?string $title = 'Anggota Siswa';

    public function getTabs(): array
    {
        $kelompok = $this->getOwnerRecord();
        $aktifCount = $kelompok ? $kelompok->anggotaAktif()->count() : 0;
        $arsipCount = $kelompok ? $kelompok->anggotaArsip()->count() : 0;
        $totalCount = $aktifCount + $arsipCount;

        return [
            'aktif' => Tab::make('Anggota Aktif')
                ->badge($aktifCount)
                ->badgeColor('success')
                ->modifyQueryUsing(fn ($query) => $query->where('status_aktif', true)),

            'arsip' => Tab::make('Riwayat / Arsip')
                ->badge($arsipCount)
                ->badgeColor($arsipCount > 0 ? 'warning' : 'gray')
                ->modifyQueryUsing(fn ($query) => $query->where('status_aktif', false)),

            'semua' => Tab::make('Semua')
                ->badge($totalCount)
                ->badgeColor('gray'),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'aktif';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('student_id')
                    ->label('Pilih Siswa')
                    ->required()
                    ->searchable()
                    ->options(function ($record) {
                        $currentStudentId = $record?->student_id;
                        $sudahDikelompok = KelompokGuruWaliSiswa::where('status_aktif', true)
                            ->when($currentStudentId, fn ($query) => $query->where('student_id', '!=', $currentStudentId))
                            ->pluck('student_id');

                        return Siswa::whereNotIn('id', $sudahDikelompok)
                            ->where('status', 'aktif')
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn ($s) => [$s->id => "{$s->name} ({$s->nisn})"])
                            ->toArray();
                    })
                    ->placeholder('Cari nama atau NISN siswa...'),

                Toggle::make('status_aktif')
                    ->label('Aktif di Kelompok')
                    ->default(true),

                TextInput::make('tahun_masuk')
                    ->label('Tahun Masuk ke Kelompok')
                    ->required()
                    ->numeric()
                    ->minValue(2000)
                    ->maxValue(2100)
                    ->default(date('Y'))
                    ->helperText('Tahun siswa ini bergabung ke kelompok dampingan ini'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['siswa.kelompokGuruWali.kelompok']))
            ->columns([
                TextColumn::make('siswa.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('siswa.nisn')
                    ->label('NISN')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('siswa.nis')
                    ->label('NIS')
                    ->placeholder('-'),

                TextColumn::make('tahun_masuk')
                    ->label('Tahun Masuk')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                IconColumn::make('status_aktif')
                    ->label('Status')
                    ->boolean(),

                TextColumn::make('keterangan_arsip')
                    ->label('Keterangan')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if ($record->status_aktif) {
                            return null;
                        }

                        $siswa = $record->siswa;
                        if (! $siswa) {
                            return null;
                        }

                        // 1. Cek apakah siswa saat ini terdaftar di kelompok Guru Wali aktif lain
                        $kelompokAktifLain = $siswa->kelompokGuruWali;
                        if ($kelompokAktifLain && $kelompokAktifLain->kelompok_id !== $record->kelompok_id) {
                            $namaKelompokBaru = $kelompokAktifLain->kelompok?->nama_kelompok ?? 'Kelompok Lain';
                            return "Pindah ke: {$namaKelompokBaru}";
                        }

                        // 2. Cek status siswa jika lulus
                        if ($siswa->status === 'lulus') {
                            return 'Sudah Lulus';
                        }

                        // 3. Cek status siswa jika mutasi
                        if ($siswa->status === 'mutasi') {
                            return 'Mutasi';
                        }

                        return null;
                    })
                    ->color(fn (?string $state): string => match (true) {
                        str_starts_with($state ?? '', 'Pindah ke:') => 'info',
                        $state === 'Sudah Lulus' => 'warning',
                        $state === 'Mutasi' => 'danger',
                        default => 'gray',
                    })
                    ->placeholder(''),
            ])
            ->filters([])
            ->headerActions([
                CreateAction::make()
                    ->label('+ Tambah Siswa')
                    ->before(function (array $data, $action) {
                        $sudahAktif = KelompokGuruWaliSiswa::where('student_id', $data['student_id'])
                            ->where('status_aktif', true)
                            ->exists();

                        if ($sudahAktif) {
                            $action->halt();

                            Notification::make()
                                ->title('Siswa Sudah dalam Kelompok Lain')
                                ->body('Siswa ini sudah terdaftar sebagai anggota kelompok dampingan aktif. Satu siswa hanya boleh punya satu Guru Wali aktif.')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                EditAction::make(),

                Action::make('keluarkan')
                    ->label('Keluarkan (Arsipkan)')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Keluarkan Siswa dari Kelompok?')
                    ->modalDescription('Siswa akan dikeluarkan dari kelompok ini, namun riwayat keanggotaan dan jurnal tetap tersimpan sebagai arsip (tidak dihapus permanen).')
                    ->modalSubmitActionLabel('Ya, Keluarkan')
                    ->visible(fn ($record) => (bool) $record->status_aktif)
                    ->action(function ($record) {
                        $record->update(['status_aktif' => false]);
                        Notification::make()
                            ->title('Siswa berhasil dikeluarkan dari kelompok')
                            ->body('Riwayat tetap tersimpan sebagai arsip.')
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('Belum ada siswa dalam kelompok ini')
            ->emptyStateDescription('Klik "+ Tambah Siswa" untuk menambahkan anggota.');
    }
}

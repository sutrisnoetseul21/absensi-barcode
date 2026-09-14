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
                        $ownerKelompokId = $this->getOwnerRecord()?->id;

                        return Siswa::where('status', 'aktif')
                            ->with(['kelompokGuruWali.kelompok'])
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(function ($s) use ($ownerKelompokId, $currentStudentId) {
                                $label = "{$s->name} ({$s->nisn})";
                                $kgw = $s->kelompokGuruWali;
                                if ($kgw && $kgw->status_aktif && $s->id !== $currentStudentId) {
                                    if ($kgw->kelompok_id === $ownerKelompokId) {
                                        $label .= " — [Sudah Aktif di Kelompok Ini]";
                                    } else {
                                        $namaKelompokLain = $kgw->kelompok?->nama_kelompok ?? 'Kelompok Lain';
                                        $label .= " — [Pindah dari: {$namaKelompokLain}]";
                                    }
                                }
                                return [$s->id => $label];
                            })
                            ->toArray();
                    })
                    ->placeholder('Cari nama atau NISN siswa...')
                    ->helperText('Jika siswa sudah berada di kelompok Guru Wali lain (misal karena pindah rombel/kelas), memilih siswa ini akan otomatis memindahkan & mengarsipkan riwayat dari kelompok lamanya.'),

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
                    ->using(function (array $data, string $model) {
                        $studentId = $data['student_id'];
                        $kelompokId = $this->getOwnerRecord()->id;
                        $tahunMasuk = $data['tahun_masuk'] ?? date('Y');
                        $statusAktif = $data['status_aktif'] ?? true;

                        // 1. Jika diaktifkan dan siswa saat ini aktif di kelompok lain, arsipkan kelompok lamanya
                        if ($statusAktif) {
                            $activeLain = KelompokGuruWaliSiswa::where('student_id', $studentId)
                                ->where('status_aktif', true)
                                ->where('kelompok_id', '!=', $kelompokId)
                                ->first();

                            if ($activeLain) {
                                $kelompokLama = $activeLain->kelompok?->nama_kelompok ?? 'Kelompok Lama';
                                $activeLain->update(['status_aktif' => false]);

                                Notification::make()
                                    ->title('Siswa Berhasil Dipindahkan')
                                    ->body("Keanggotaan siswa di {$kelompokLama} telah otomatis diarsipkan.")
                                    ->info()
                                    ->send();
                            }
                        }

                        // 2. Cek apakah siswa sudah pernah terdaftar di kelompok ini (riwayat arsip lama)
                        $existing = KelompokGuruWaliSiswa::where('kelompok_id', $kelompokId)
                            ->where('student_id', $studentId)
                            ->first();

                        if ($existing) {
                            $existing->update([
                                'status_aktif' => $statusAktif,
                                'tahun_masuk'  => $tahunMasuk,
                            ]);
                            return $existing;
                        }

                        // 3. Jika belum pernah terdaftar, buat baris baru
                        return KelompokGuruWaliSiswa::create([
                            'kelompok_id'  => $kelompokId,
                            'student_id'   => $studentId,
                            'tahun_masuk'  => $tahunMasuk,
                            'status_aktif' => $statusAktif,
                        ]);
                    }),
            ])
            ->actions([
                EditAction::make(),

                // 1. Keluarkan (Arsipkan) Siswa Aktif
                Action::make('keluarkan')
                    ->label('Keluarkan (Arsipkan)')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-exclamation-triangle')
                    ->modalHeading(fn ($record) => "Keluarkan " . ($record->siswa?->name ?? 'Siswa') . " dari Kelompok?")
                    ->modalDescription(fn ($record) => "Apakah Anda yakin ingin mengeluarkan siswa " . ($record->siswa?->name ?? 'ini') . " (NISN: " . ($record->siswa?->nisn ?? '—') . ") dari kelompok ini?\n\nSiswa akan dipindahkan ke tab 'Riwayat / Arsip'. Seluruh riwayat sesi pendampingan dan jurnal yang pernah dicatat akan tetap tersimpan aman.")
                    ->modalSubmitActionLabel('Ya, Keluarkan (Arsipkan)')
                    ->modalCancelActionLabel('Batal')
                    ->visible(fn ($record) => (bool) $record->status_aktif)
                    ->action(function ($record) {
                        $nama = $record->siswa?->name ?? 'Siswa';
                        $record->update(['status_aktif' => false]);
                        Notification::make()
                            ->title("{$nama} berhasil dikeluarkan")
                            ->body('Data keanggotaan telah dipindahkan ke tab Riwayat / Arsip.')
                            ->success()
                            ->send();
                    }),

                // 2. Aktifkan Kembali Siswa dari Tab Arsip
                Action::make('aktifkan_kembali')
                    ->label('Aktifkan Kembali')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-arrow-uturn-left')
                    ->modalHeading(fn ($record) => "Aktifkan Kembali " . ($record->siswa?->name ?? 'Siswa') . "?")
                    ->modalDescription(fn ($record) => "Siswa " . ($record->siswa?->name ?? 'ini') . " akan dikembalikan dari tab arsip menjadi anggota aktif kelompok ini.")
                    ->modalSubmitActionLabel('Ya, Aktifkan Kembali')
                    ->modalCancelActionLabel('Batal')
                    ->visible(fn ($record) => ! (bool) $record->status_aktif)
                    ->action(function ($record) {
                        $studentId = $record->student_id;
                        $kelompokId = $record->kelompok_id;

                        // Arsipkan jika saat ini aktif di kelompok lain
                        $activeLain = KelompokGuruWaliSiswa::where('student_id', $studentId)
                            ->where('status_aktif', true)
                            ->where('kelompok_id', '!=', $kelompokId)
                            ->first();

                        if ($activeLain) {
                            $activeLain->update(['status_aktif' => false]);
                        }

                        $record->update(['status_aktif' => true]);

                        Notification::make()
                            ->title('Siswa Berhasil Diaktifkan')
                            ->body(($record->siswa?->name ?? 'Siswa') . ' kini kembali berstatus anggota aktif kelompok ini.')
                            ->success()
                            ->send();
                    }),

                // 3. Hapus Permanen Baris Arsip (Hanya untuk Admin/Editor)
                Action::make('hapus_arsip')
                    ->label('Hapus Arsip')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-trash')
                    ->modalHeading(fn ($record) => "Hapus Data Arsip " . ($record->siswa?->name ?? 'Siswa') . "?")
                    ->modalDescription(fn ($record) => "PERINGATAN: Menghapus arsip ini akan menghapus riwayat keanggotaan siswa secara permanen dari kelompok ini. Tindakan ini tidak dapat dibatalkan.")
                    ->modalSubmitActionLabel('Ya, Hapus Permanen')
                    ->modalCancelActionLabel('Batal')
                    ->visible(fn ($record) => ! (bool) $record->status_aktif && (auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_akademik_editor') || auth()->user()?->hasRole('admin_master_editor')))
                    ->action(function ($record) {
                        $nama = $record->siswa?->name ?? 'Siswa';
                        $record->delete();
                        Notification::make()
                            ->title('Arsip Berhasil Dihapus')
                            ->body("Data riwayat keanggotaan {$nama} telah dihapus permanen.")
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('Belum ada siswa dalam kelompok ini')
            ->emptyStateDescription('Klik "+ Tambah Siswa" untuk menambahkan anggota.');
    }
}

<?php

namespace App\Filament\Akademik\Resources\Ekstrakurikuler\RelationManagers;

use App\Models\EkstrakurikulerSiswa;
use App\Models\Siswa;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AnggotaEkskulRelationManager extends RelationManager
{
    protected static string $relationship = 'anggota';

    protected static ?string $title = 'Anggota Ekstrakurikuler';

    public function getTabs(): array
    {
        $ekskul = $this->getOwnerRecord();
        $aktifCount = $ekskul ? $ekskul->anggotaAktif()->count() : 0;
        $arsipCount = $ekskul ? $ekskul->anggotaArsip()->count() : 0;
        $totalCount = $aktifCount + $arsipCount;

        return [
            'aktif' => Tab::make('Anggota Aktif')
                ->badge($aktifCount)
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status_aktif', true)),

            'arsip' => Tab::make('Riwayat / Arsip')
                ->badge($arsipCount)
                ->badgeColor($arsipCount > 0 ? 'warning' : 'gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status_aktif', false)),

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
                        $ekskulId = $this->getOwnerRecord()?->id;

                        return Siswa::where('status', 'aktif')
                            ->orderBy('name')
                            ->get()
                            ->reject(function ($s) use ($ekskulId, $currentStudentId) {
                                if ($s->id === $currentStudentId) {
                                    return false;
                                }
                                return EkstrakurikulerSiswa::where('ekstrakurikuler_id', $ekskulId)
                                    ->where('student_id', $s->id)
                                    ->where('status_aktif', true)
                                    ->exists();
                            })
                            ->mapWithKeys(fn ($s) => [$s->id => "{$s->name} ({$s->nisn})"])
                            ->toArray();
                    })
                    ->placeholder('Cari nama atau NISN siswa...'),

                DatePicker::make('tanggal_gabung')
                    ->label('Tanggal Bergabung')
                    ->default(now('Asia/Jakarta')->toDateString()),

                Toggle::make('status_aktif')
                    ->label('Aktif di Ekstrakurikuler')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        $yearId = $this->getOwnerRecord()?->academic_year_id;

        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with([
                'siswa.enrollments' => fn ($eq) => $eq->where('academic_year_id', $yearId)
                    ->where('status', 'aktif')
                    ->with('kelas'),
            ]))
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

                TextColumn::make('kelas_siswa')
                    ->label('Kelas')
                    ->getStateUsing(fn (EkstrakurikulerSiswa $record) => $record->siswa?->enrollments?->first()?->kelas?->name ?? 'Belum ada kelas')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('tanggal_gabung')
                    ->label('Tanggal Gabung')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->sortable(),

                IconColumn::make('status_aktif')
                    ->label('Status')
                    ->boolean(),
            ])
            ->filters([])
            ->headerActions([
                CreateAction::make()
                    ->label('+ Tambah Anggota')
                    ->using(function (array $data) {
                        $ekskulId = $this->getOwnerRecord()->id;
                        $studentId = $data['student_id'];
                        $tglGabung = $data['tanggal_gabung'] ?? now('Asia/Jakarta')->toDateString();
                        $statusAktif = $data['status_aktif'] ?? true;

                        $existing = EkstrakurikulerSiswa::where('ekstrakurikuler_id', $ekskulId)
                            ->where('student_id', $studentId)
                            ->first();

                        if ($existing) {
                            $existing->update([
                                'tanggal_gabung' => $tglGabung,
                                'status_aktif'   => $statusAktif,
                            ]);
                            Notification::make()
                                ->title('Anggota diaktifkan kembali')
                                ->success()
                                ->send();
                            return $existing;
                        }

                        return EkstrakurikulerSiswa::create([
                            'ekstrakurikuler_id' => $ekskulId,
                            'student_id'         => $studentId,
                            'tanggal_gabung'     => $tglGabung,
                            'status_aktif'       => $statusAktif,
                        ]);
                    }),
            ])
            ->actions([
                EditAction::make(),
                Action::make('keluarkan')
                    ->label('Keluarkan')
                    ->icon('heroicon-o-arrow-right-start-on-rectangle')
                    ->color('danger')
                    ->visible(fn (EkstrakurikulerSiswa $record) => $record->status_aktif)
                    ->requiresConfirmation()
                    ->modalHeading('Keluarkan Anggota dari Ekstrakurikuler')
                    ->modalDescription('Siswa akan diarsipkan (status_aktif = false) agar riwayat keanggotaan tetap terjaga.')
                    ->action(function (EkstrakurikulerSiswa $record) {
                        $record->update(['status_aktif' => false]);
                        Notification::make()
                            ->title('Anggota dikeluarkan (diarsipkan)')
                            ->success()
                            ->send();
                    }),
                Action::make('aktifkan_kembali')
                    ->label('Aktifkan')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->visible(fn (EkstrakurikulerSiswa $record) => !$record->status_aktif)
                    ->action(function (EkstrakurikulerSiswa $record) {
                        $record->update([
                            'status_aktif'   => true,
                            'tanggal_gabung' => now('Asia/Jakarta')->toDateString(),
                        ]);
                        Notification::make()
                            ->title('Anggota diaktifkan kembali')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }
}


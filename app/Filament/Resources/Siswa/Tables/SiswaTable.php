<?php

namespace App\Filament\Resources\Siswa\Tables;

use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SiswaTable
{
    public static function configure(Table $table): Table
    {
        // Ambil tahun ajaran aktif dari pengaturan sekolah
        $activeYearId = PengaturanSekolah::current()?->academic_year_id_active;

        return $table
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

                TextColumn::make('name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kelas_aktif')
                    ->label('Kelas')
                    ->getStateUsing(function (Siswa $record, $livewire) {
                        $targetYearId = $livewire->tableFilters['academic_year_id']['value'] ?? null;
                        $activeYearId = $targetYearId ?: (PengaturanSekolah::current()?->academic_year_id_active);
                        if (!$activeYearId) return '—';
                        
                        $enrollment = $record->enrollments()->where('academic_year_id', $activeYearId)->first();
                        if (!$enrollment) return '—';

                        $className = $enrollment->kelas?->name ?? '—';
                        if ($enrollment->status !== 'aktif') {
                            $statusLabel = match ($enrollment->status) {
                                'naik' => 'Naik',
                                'lulus' => 'Lulus',
                                'tinggal' => 'Tinggal',
                                'pindah' => 'Pindah',
                                default => $enrollment->status,
                            };
                            return $className . ' (' . ucfirst($statusLabel) . ')';
                        }

                        return $className;
                    }),

                TextColumn::make('barcode_code')
                    ->label('Barcode')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Barcode disalin'),

                TextColumn::make('username')
                    ->label('Username Login')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Username disalin'),

                IconColumn::make('must_change_password')
                    ->label('Ganti Password?')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('warning')
                    ->falseColor('success'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                \App\Filament\Resources\Siswa\Actions\LuluskanKelas9Action::make(),
                \App\Filament\Resources\Siswa\Actions\BatalkanKelulusanMassalAction::make(),
                \App\Filament\Resources\Siswa\Actions\ImportFotoMassalAction::make(),
                
                // 1. Download Template Siswa Baru
                Action::make('download_template_siswa_baru')
                    ->label('Template Siswa Baru')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(fn () => \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\SiswaBaruTemplateExport, 'template_siswa_baru.xlsx')),

                \App\Filament\Resources\Siswa\Actions\ImportSiswaBaruAction::make(),

                // 3. Download Template Naik Kelas (Siswa Lama)
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

                \App\Filament\Resources\Siswa\Actions\ImportNaikKelasAction::make(),
            ])
            ->filters([
                TrashedFilter::make(),

                // Filter Status Pendaftaran
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'aktif' => 'Aktif',
                        'naik' => 'Naik Kelas',
                        'lulus' => 'Lulus',
                        'tinggal' => 'Tinggal Kelas',
                        'pindah' => 'Pindah Sekolah',
                    ])
                    ->query(function (Builder $query, array $data, $livewire) {
                        if (!empty($data['value'])) {
                            $targetYearId = $livewire->tableFilters['academic_year_id']['value'] ?? null;
                            $activeYearId = $targetYearId ?: (\App\Models\PengaturanSekolah::current()?->academic_year_id_active);
                            
                            $query->whereHas('enrollments', function (Builder $query) use ($data, $activeYearId) {
                                $query->where('academic_year_id', $activeYearId)
                                      ->where('status', $data['value']);
                            });
                        }
                    }),

                // Filter Tahun Ajaran
                SelectFilter::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->options(TahunAjaran::pluck('name', 'id')->toArray())
                    ->default($activeYearId)
                    ->query(function (Builder $query, array $data) {
                        // Query hanya menerapkan filter jika ada nilainya, 
                        // tapi kita tangani ini secara gabungan di filter Kelas jika diperlukan.
                        // Di sini kita biarkan kosong jika kita ingin menggabungkannya.
                        // Namun lebih baik ditangani melalui relasi whereHas
                        if (!empty($data['value'])) {
                            $query->whereHas('enrollments', function (Builder $query) use ($data) {
                                $query->where('academic_year_id', $data['value']);
                            });
                        }
                    }),

                // Filter Kelas
                SelectFilter::make('class_id')
                    ->label('Kelas')
                    ->options(Kelas::pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('enrollments', function (Builder $query) use ($data) {
                                $query->where('class_id', $data['value']);
                            });
                        }
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),

                // Custom Action: Reset Password
                Action::make('resetPassword')
                    ->label('Reset Password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reset Password Siswa')
                    ->modalDescription(fn (Siswa $record): string => "Password baru akan di-generate untuk {$record->name} ({$record->nisn}). Pastikan catat password sebelum menutup dialog ini.")
                    ->action(function (Siswa $record): void {
                        $newPassword = Str::random(8);

                        $record->update([
                            'password'             => $newPassword, // otomatis di-hash via cast 'hashed'
                            'must_change_password' => true,
                        ]);

                        Notification::make()
                            ->title('Password berhasil direset')
                            ->body("Username: **{$record->username}**\nPassword baru: **{$newPassword}**\n\nSiswa wajib ganti password saat login berikutnya.")
                            ->success()
                            ->persistent()
                            ->send();
                    }),

                // Custom Action: Batalkan Kelulusan
                Action::make('batalkan_kelulusan')
                    ->label('Batalkan Kelulusan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Kelulusan Siswa')
                    ->modalDescription('Apakah Anda yakin ingin membatalkan kelulusan siswa ini dan mengembalikan statusnya menjadi "Aktif"?')
                    ->visible(function (Siswa $record, $livewire) {
                        $targetYearId = $livewire->tableFilters['academic_year_id']['value'] ?? null;
                        $activeYearId = $targetYearId ?: (\App\Models\PengaturanSekolah::current()?->academic_year_id_active);
                        if (!$activeYearId) return false;
                        
                        $enrollment = $record->enrollments()->where('academic_year_id', $activeYearId)->first();
                        return $enrollment && $enrollment->status === 'lulus';
                    })
                    ->action(function (Siswa $record, $livewire) {
                        $targetYearId = $livewire->tableFilters['academic_year_id']['value'] ?? null;
                        $activeYearId = $targetYearId ?: (\App\Models\PengaturanSekolah::current()?->academic_year_id_active);
                        
                        $enrollment = $record->enrollments()->where('academic_year_id', $activeYearId)->first();
                        if ($enrollment) {
                            $enrollment->update(['status' => 'aktif']);
                            Notification::make()
                                ->title('Kelulusan Dibatalkan')
                                ->body("Kelulusan siswa **{$record->name}** telah dibatalkan. Status pendaftaran dikembalikan menjadi **Aktif**.")
                                ->success()
                                ->send();
                        }
                    }),

                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}

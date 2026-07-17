<?php

namespace App\Filament\Resources\Siswa\Tables;

use App\Actions\Student\MutateStudentAction;
use App\Actions\Student\ReactivateStudentAction;
use Filament\Forms\Components\TextInput;
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

                TextColumn::make('nis')
                    ->label('NIS')
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
                \App\Filament\Resources\Siswa\Actions\ImportFotoMassalAction::make(),

                // 1. Download Template Siswa Baru
                Action::make('download_template_siswa_baru')
                    ->label('Template Siswa Baru')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(fn () => \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\SiswaBaruTemplateExport, 'template_siswa_baru.xlsx')),

                \App\Filament\Resources\Siswa\Actions\ImportSiswaBaruAction::make(),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'aktif'))
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                // Hapus diblokir jika siswa masih terdaftar di kelas (enrollment)
                DeleteAction::make()
                    ->before(function (Siswa $record, DeleteAction $action) {
                        if ($record->enrollments()->exists()) {
                            Notification::make()
                                ->title('Tidak Bisa Dihapus')
                                ->body('Siswa ini masih terdaftar di kelas. Keluarkan siswa dari menu Pendaftaran Kelas terlebih dahulu.')
                                ->danger()
                                ->send();
                            $action->cancel();
                        }
                    }),

                // Action: Tandai Mutasi (pindah/keluar)
                Action::make('tandai_mutasi')
                    ->label('Tandai Mutasi')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Tandai Siswa Mutasi')
                    ->modalDescription('Siswa ini akan ditandai sebagai Mutasi (pindah/keluar sekolah). Jika siswa masih terdaftar di sebuah kelas, status pendaftarannya di kelas tersebut akan otomatis diubah menjadi "Pindah".')
                    ->visible(fn (Siswa $record) => $record->status === 'aktif')
                    ->action(function (Siswa $record) {
                        (new MutateStudentAction)->execute($record);

                        Notification::make()
                            ->title('Siswa Ditandai Mutasi')
                            ->body("Siswa **{$record->name}** telah dipindahkan ke daftar Siswa Mutasi.")
                            ->success()
                            ->send();
                    }),

                // Action: Aktifkan Kembali (dari arsip mutasi)
                Action::make('aktifkan_kembali')
                    ->label('Aktifkan Kembali')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Siswa $record) => $record->status === 'mutasi')
                    ->action(function (Siswa $record) {
                        (new ReactivateStudentAction)->execute($record);

                        Notification::make()
                            ->title('Siswa Diaktifkan Kembali')
                            ->body("Siswa **{$record->name}** telah dikembalikan ke daftar Siswa Aktif.")
                            ->success()
                            ->send();
                    }),


                // Custom Action: Reset Password
                Action::make('resetPassword')
                    ->label('Reset Password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->form([
                        TextInput::make('password')
                            ->password()
                            ->label('Password Baru')
                            ->required()
                            ->minLength(6)
                            ->same('password_confirmation'),
                        TextInput::make('password_confirmation')
                            ->password()
                            ->label('Konfirmasi Password Baru')
                            ->required()
                            ->minLength(6),
                    ])
                    ->modalHeading('Reset Password Siswa')
                    ->modalSubmitActionLabel('Ganti Password')
                    ->action(function (Siswa $record, array $data): void {
                        $record->update([
                            'password'             => bcrypt($data['password']),
                            'must_change_password' => false,
                        ]);

                        Notification::make()
                            ->title('Password berhasil diubah')
                            ->body("Password untuk siswa **{$record->name}** ({$record->nisn}) telah berhasil diperbarui.")
                            ->success()
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
                    DeleteBulkAction::make()
                        ->before(function (\Illuminate\Database\Eloquent\Collection $records, DeleteBulkAction $action) {
                            $blocked = $records->filter(fn ($r) => $r->enrollments()->exists());
                            if ($blocked->isNotEmpty()) {
                                $names = $blocked->pluck('name')->join(', ');
                                Notification::make()
                                    ->title('Beberapa Siswa Tidak Bisa Dihapus')
                                    ->body("Siswa berikut masih terdaftar di kelas: {$names}. Hapus pendaftaran kelasnya terlebih dahulu.")
                                    ->danger()
                                    ->send();
                                $action->cancel();
                            }
                        }),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}

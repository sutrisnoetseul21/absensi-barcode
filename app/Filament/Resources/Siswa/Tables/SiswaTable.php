<?php

namespace App\Filament\Resources\Siswa\Tables;

use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
            ->filters([
                TrashedFilter::make(),

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
                EditAction::make(),

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

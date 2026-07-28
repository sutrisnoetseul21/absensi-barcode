<?php

namespace App\Filament\Perpustakaan\Pages;

use App\Models\StudentPresensiProfile;
use App\Models\TeacherPresensiProfile;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class AnggotaResource extends Page implements HasTable
{
    use InteractsWithTable;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Anggota';
    protected static ?string $title = 'Anggota Perpustakaan';
    protected static \UnitEnum|string|null $navigationGroup = null;
    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.perpustakaan.pages.anggota-resource';

    public function table(Table $table): Table
    {
        $students = DB::table('students')
            ->join('student_enrollments', 'students.id', '=', 'student_enrollments.student_id')
            ->join('academic_years', 'student_enrollments.academic_year_id', '=', 'academic_years.id')
            ->leftJoin('student_presensi_profiles', 'students.id', '=', 'student_presensi_profiles.student_id')
            ->where('academic_years.status', 'aktif')
            ->where('student_enrollments.status', 'aktif')
            ->whereNull('students.deleted_at')
            ->select([
                DB::raw("CONCAT('siswa_', students.id) as id"),
                'students.name as nama',
                'students.nisn as identifier',
                DB::raw("'Siswa' as tipe"),
                'student_presensi_profiles.barcode_code',
                'student_presensi_profiles.barcode_active',
                'students.id as original_id'
            ]);

        $teachers = DB::table('teachers')
            ->whereNull('teachers.deleted_at')
            ->leftJoin('teacher_presensi_profiles', 'teachers.id', '=', 'teacher_presensi_profiles.teacher_id')
            ->select([
                DB::raw("CONCAT('guru_', teachers.id) as id"),
                'teachers.name as nama',
                'teachers.nip as identifier',
                DB::raw("'Guru' as tipe"),
                'teacher_presensi_profiles.barcode_code',
                'teacher_presensi_profiles.barcode_active',
                'teachers.id as original_id'
            ]);

        $subquery = $students->union($teachers);
        $query = \App\Models\StudentPresensiProfile::query()->fromSub($subquery, 'student_presensi_profiles');

        return $table
            ->query($query)
            ->defaultSort('nama', 'asc')
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('identifier')
                    ->label('NISN / NIP')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipe')
                    ->label('Tipe Anggota')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Siswa' => 'info',
                        'Guru' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('barcode_code')
                    ->label('Kode Barcode')
                    ->searchable()
                    ->placeholder('Belum ada barcode'),
                IconColumn::make('barcode_active')
                    ->label('Status Barcode')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
            ])
            ->actions([
                Action::make('toggle_status')
                    ->label(fn ($record) => $record->barcode_active ? 'Nonaktifkan' : 'Aktifkan')
                    ->icon(fn ($record) => $record->barcode_active ? 'heroicon-o-no-symbol' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->barcode_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        if ($record->tipe === 'Siswa') {
                            $profile = StudentPresensiProfile::where('student_id', $record->original_id)->first();
                            if ($profile) {
                                $profile->update(['barcode_active' => !$profile->barcode_active]);
                            }
                        } else {
                            $profile = TeacherPresensiProfile::where('teacher_id', $record->original_id)->first();
                            if ($profile) {
                                $profile->update(['barcode_active' => !$profile->barcode_active]);
                            }
                        }
                        Notification::make()
                            ->title('Status barcode berhasil diubah')
                            ->success()
                            ->send();
                    })
                    ->hidden(fn ($record) => empty($record->barcode_code)),
            ]);
    }
}

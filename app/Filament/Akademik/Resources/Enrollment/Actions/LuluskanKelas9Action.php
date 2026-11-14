<?php

namespace App\Filament\Akademik\Resources\Enrollment\Actions;

use App\Filament\Akademik\Resources\Enrollment\Tables\EnrollmentTable;
use Filament\Actions\Action;
use App\Models\TahunAjaran;
use App\Models\PengaturanSekolah;

class LuluskanKelas9Action extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'luluskan_kelas_9';
    }

    protected function setUp(): void
    {
        parent::setUp();

        [, $maxGrade] = EnrollmentTable::getGradeBounds();

        $this->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_akademik_editor') || auth()->user()?->hasRole('admin_master_editor'))
            ->label("Luluskan Kelas {$maxGrade}")
            ->icon('heroicon-o-academic-cap')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading("Meluluskan Siswa Kelas {$maxGrade}")
            ->modalDescription("Tindakan ini akan meluluskan seluruh siswa kelas tingkat {$maxGrade} secara massal pada tahun ajaran yang dipilih.")
            ->form([
                \Filament\Forms\Components\Select::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->options(TahunAjaran::pluck('name', 'id')->toArray())
                    ->default(fn () => PengaturanSekolah::current()?->academic_year_id_active)
                    ->required(),
            ])
            ->action(function (array $data) {
                $yearId = $data['academic_year_id'];
                $tahunAjaran = TahunAjaran::find($yearId);
                [, $maxGrade] = EnrollmentTable::getGradeBounds();

                $enrollments = \App\Models\EnrollmentSiswa::where('academic_year_id', $yearId)
                    ->where('status', 'aktif')
                    ->whereHas('kelas', function ($q) use ($maxGrade) {
                        $q->where('grade_level', $maxGrade);
                    })
                    ->get();

                $count = 0;
                foreach ($enrollments as $enrollment) {
                    $enrollment->update(['status' => 'lulus']);
                    // Tandai siswa sebagai lulus di tabel students
                    if ($enrollment->siswa) {
                        $enrollment->siswa->update(['status' => 'lulus']);
                    }
                    $count++;
                }

                $yearName = $tahunAjaran?->name ?? '';
                \Filament\Notifications\Notification::make()
                    ->title('Kelulusan Massal Berhasil')
                    ->body("Berhasil meluluskan **{$count}** siswa kelas {$maxGrade} untuk Tahun Ajaran **{$yearName}**. Mereka kini muncul di menu **Siswa Lulus**.")
                    ->success()
                    ->send();

            });
    }
}

<?php

namespace App\Filament\Resources\Enrollment\Actions;

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

        $this->visible(fn () => PengaturanSekolah::current()?->enable_promotion_features ?? false)
            ->label('Luluskan Kelas 9')
            ->icon('heroicon-o-academic-cap')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Meluluskan Siswa Kelas 9')
            ->modalDescription('Tindakan ini akan meluluskan seluruh siswa kelas tingkat 9 secara massal pada tahun ajaran yang dipilih.')
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
                
                $enrollments = \App\Models\EnrollmentSiswa::where('academic_year_id', $yearId)
                    ->where('status', 'aktif')
                    ->whereHas('kelas', function ($q) {
                        $q->where('grade_level', 9);
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
                    ->body("Berhasil meluluskan **{$count}** siswa kelas 9 untuk Tahun Ajaran **{$yearName}**. Mereka kini muncul di menu **Siswa Lulus**.")
                    ->success()
                    ->send();

            });
    }
}

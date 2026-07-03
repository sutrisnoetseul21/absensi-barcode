<?php

namespace App\Filament\Resources\Siswa\Actions;

use Filament\Actions\Action;
use App\Models\TahunAjaran;
use App\Models\PengaturanSekolah;

class BatalkanKelulusanMassalAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'batalkan_kelulusan_massal';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->visible(fn () => PengaturanSekolah::current()?->enable_promotion_features ?? false)
            ->label('Batalkan Kelulusan')
            ->icon('heroicon-o-arrow-path')
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading('Batalkan Kelulusan')
            ->modalDescription('Tindakan ini akan memulihkan status kelulusan seluruh siswa di tahun ajaran yang dipilih kembali menjadi Aktif.')
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
                    ->where('status', 'lulus')
                    ->get();

                $count = 0;
                foreach ($enrollments as $enrollment) {
                    $enrollment->update(['status' => 'aktif']);
                    $count++;
                }

                $yearName = $tahunAjaran?->name ?? '';
                \Filament\Notifications\Notification::make()
                    ->title('Pembatalan Kelulusan Berhasil')
                    ->body("Berhasil membatalkan kelulusan **{$count}** siswa untuk Tahun Ajaran **{$yearName}** kembali menjadi Aktif.")
                    ->success()
                    ->send();
            });
    }
}

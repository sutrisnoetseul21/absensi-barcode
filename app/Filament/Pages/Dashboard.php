<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Actions\Action;
use App\Services\KalenderSekolahService;
use App\Models\EnrollmentSiswa;
use App\Models\Presensi;
use App\Models\PengaturanSekolah;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

class Dashboard extends BaseDashboard
{
    protected function getHeaderActions(): array
    {
        $today = now('Asia/Jakarta');
        $kalenderService = app(KalenderSekolahService::class);
        $isHariSekolahGlobal = $kalenderService->isHariSekolah($today);

        $dateStr = $today->translatedFormat('l, d F Y');

        return [
            Action::make('proses_alpa_massal')
                ->label('Tandai Alpa (Hari Ini)')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($isHariSekolahGlobal ? 'warning' : 'danger')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Proses Alpa Massal')
                ->modalDescription(new HtmlString(
                    $isHariSekolahGlobal
                        ? "Anda akan mencatat status <b>Alpa</b> untuk semua siswa aktif yang belum absen hari ini ({$dateStr}). Lanjutkan?"
                        : "<div class='text-danger-600 font-bold mb-2'>⚠️ PERINGATAN: Hari ini direkomendasikan sebagai HARI LIBUR / AKHIR PEKAN.</div>Anda yakin ingin tetap memproses Alpa untuk hari ini ({$dateStr})?"
                ))
                ->modalSubmitActionLabel('Ya, Tetap Lanjutkan')
                ->action(function () use ($today, $kalenderService) {
                    $settings = PengaturanSekolah::current();
                    $activeYearId = $settings->academic_year_id_active ?? null;

                    if (!$activeYearId) {
                        Notification::make()->title('Gagal')->body('Tidak ada Tahun Ajaran aktif.')->danger()->send();
                        return;
                    }

                    // Ambil seluruh enrollment aktif di tahun ajaran ini
                    $enrollments = EnrollmentSiswa::where('academic_year_id', $activeYearId)
                        ->where('status', 'aktif')
                        ->get();

                    if ($enrollments->isEmpty()) {
                        Notification::make()->title('Info')->body('Tidak ada siswa aktif.')->info()->send();
                        return;
                    }

                    $dateString = $today->toDateString();
                    $count = 0;

                    foreach ($enrollments as $enrollment) {
                        // Cek apakah libur khusus untuk kelas siswa ini
                        if (!$kalenderService->isHariSekolah($today, $enrollment->class_id)) {
                            continue;
                        }

                        // Cek apakah sudah absen
                        $sudahAbsen = Presensi::where('student_id', $enrollment->student_id)
                            ->where('date', $dateString)
                            ->exists();

                        if (!$sudahAbsen) {
                            Presensi::create([
                                'student_id' => $enrollment->student_id,
                                'class_id' => $enrollment->class_id,
                                'academic_year_id' => $activeYearId,
                                'date' => $dateString,
                                'status' => 'alpa',
                                'scan_time' => null,
                                'note' => 'Otomatis Alpa oleh Sistem (Manual Trigger)',
                            ]);
                            $count++;
                        }
                    }

                    if ($count > 0) {
                        Notification::make()
                            ->title('Sukses')
                            ->body("Berhasil menandai {$count} siswa sebagai Alpa untuk hari ini.")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Selesai')
                            ->body('Tidak ada siswa baru yang ditandai Alpa. (Semua sudah absen atau libur)')
                            ->info()
                            ->send();
                    }
                }),
        ];
    }
}

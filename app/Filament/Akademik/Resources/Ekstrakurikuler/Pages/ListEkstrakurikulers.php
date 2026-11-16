<?php

namespace App\Filament\Akademik\Resources\Ekstrakurikuler\Pages;

use App\Filament\Akademik\Resources\Ekstrakurikuler\EkstrakurikulerResource;
use App\Models\EkstrakurikulerSiswa;
use App\Models\Siswa;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListEkstrakurikulers extends ListRecords
{
    protected static string $resource = EkstrakurikulerResource::class;

    public ?string $currentEkskulId = null;
    public ?string $filterKelasId = null;
    public ?string $searchKandidat = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Ekstrakurikuler')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function tambahAnggotaBulk(array $studentIds, ?string $tanggalGabung, string $ekskulId): array
    {
        $berhasil = 0;
        $gagal = [];
        $tglGabung = !empty($tanggalGabung) ? $tanggalGabung : now('Asia/Jakarta')->toDateString();

        foreach ($studentIds as $studentId) {
            try {
                // Reaktivasi jika siswa pernah terarsip di ekskul ini, atau buat record baru
                $existing = EkstrakurikulerSiswa::where('ekstrakurikuler_id', $ekskulId)
                    ->where('student_id', $studentId)
                    ->first();

                if ($existing) {
                    if ($existing->status_aktif) {
                        continue;
                    }
                    $existing->update([
                        'status_aktif'   => true,
                        'tanggal_gabung' => $tglGabung,
                    ]);
                } else {
                    EkstrakurikulerSiswa::create([
                        'ekstrakurikuler_id' => $ekskulId,
                        'student_id'         => $studentId,
                        'tanggal_gabung'     => $tglGabung,
                        'status_aktif'       => true,
                    ]);
                }

                $berhasil++;
            } catch (\Exception $e) {
                $siswa = Siswa::find($studentId);
                $gagal[] = $siswa?->name ?? $studentId;
            }
        }

        if ($berhasil > 0) {
            Notification::make()
                ->title("{$berhasil} anggota berhasil ditambahkan ke ekstrakurikuler")
                ->success()
                ->send();
        }

        if (count($gagal) > 0) {
            Notification::make()
                ->title('Sebagian gagal ditambahkan')
                ->body(implode(', ', $gagal))
                ->warning()
                ->send();
        }

        return [
            'berhasil' => $berhasil,
            'gagal'    => $gagal,
        ];
    }

    public function keluarkanAnggota(string $anggotaId): bool
    {
        $anggota = EkstrakurikulerSiswa::find($anggotaId);
        if (!$anggota) {
            return false;
        }

        $anggota->update(['status_aktif' => false]);

        Notification::make()
            ->title('Anggota dikeluarkan dari ekstrakurikuler')
            ->body('Riwayat keanggotaan disimpan sebagai arsip.')
            ->success()
            ->send();

        return true;
    }
}


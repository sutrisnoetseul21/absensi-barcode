<?php

namespace App\Filament\Akademik\Resources\GuruWali\Pages;

use App\Filament\Akademik\Resources\GuruWali\KelompokGuruWaliResource;
use App\Models\KelompokGuruWaliSiswa;
use App\Models\Siswa;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListKelompokGuruWali extends ListRecords
{
    protected static string $resource = KelompokGuruWaliResource::class;

    public ?string $currentKelompokId = null;
    public ?string $filterKelasId = null;
    public ?string $searchKandidat = null;

    public function mount(): void
    {
        parent::mount();
        \App\Models\KelompokGuruWali::syncAllTeachers();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function tambahAnggotaBulk(array $studentIds, int $tahunMasuk, string $kelompokId): array
    {
        $berhasil = 0;
        $gagal = [];
        $added = [];

        foreach ($studentIds as $studentId) {
            try {
                // 1. Jika siswa aktif di kelompok lain, arsipkan keanggotaan kelompok lamanya
                $activeLain = KelompokGuruWaliSiswa::where('student_id', $studentId)
                    ->where('status_aktif', true)
                    ->where('kelompok_id', '!=', $kelompokId)
                    ->first();

                if ($activeLain) {
                    $activeLain->update(['status_aktif' => false]);
                }

                // 2. Cek apakah sudah pernah ada di kelompok ini (reaktivasi)
                $existing = KelompokGuruWaliSiswa::where('kelompok_id', $kelompokId)
                    ->where('student_id', $studentId)
                    ->first();

                if ($existing) {
                    $existing->update([
                        'status_aktif' => true,
                        'tahun_masuk'  => $tahunMasuk,
                    ]);
                    $record = $existing;
                } else {
                    $record = KelompokGuruWaliSiswa::create([
                        'kelompok_id'  => $kelompokId,
                        'student_id'   => $studentId,
                        'tahun_masuk'  => $tahunMasuk,
                        'status_aktif' => true,
                    ]);
                }

                $record->load('siswa');
                $added[] = [
                    'id'          => $record->id,
                    'student_id'  => $record->student_id,
                    'name'        => $record->siswa?->name ?? '—',
                    'nisn'        => $record->siswa?->nisn ?? '—',
                    'nis'         => $record->siswa?->nis ?? '—',
                    'tahun_masuk' => $record->tahun_masuk,
                ];
                $berhasil++;
            } catch (\Exception $e) {
                $siswa = Siswa::find($studentId);
                $gagal[] = $siswa->name ?? $studentId;
            }
        }

        if ($berhasil > 0) {
            Notification::make()
                ->title("{$berhasil} siswa berhasil ditambahkan")
                ->success()
                ->send();
        }

        if (count($gagal) > 0) {
            Notification::make()
                ->title('Sebagian gagal ditambahkan')
                ->body('Sudah punya Guru Wali lain: ' . implode(', ', $gagal))
                ->warning()
                ->send();
        }

        return [
            'berhasil' => $berhasil,
            'gagal'    => $gagal,
            'added'    => $added,
        ];
    }

    public function keluarkanAnggota(string $anggotaId): bool
    {
        $anggota = KelompokGuruWaliSiswa::find($anggotaId);
        if (!$anggota) {
            return false;
        }

        $anggota->update(['status_aktif' => false]);

        Notification::make()
            ->title('Siswa dikeluarkan dari kelompok')
            ->body('Riwayat tetap tersimpan sebagai arsip.')
            ->success()
            ->send();

        return true;
    }
}

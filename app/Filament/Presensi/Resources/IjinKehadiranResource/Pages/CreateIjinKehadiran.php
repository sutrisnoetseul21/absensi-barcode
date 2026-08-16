<?php

namespace App\Filament\Presensi\Resources\IjinKehadiranResource\Pages;

use App\Filament\Presensi\Resources\IjinKehadiranResource;
use App\Models\LeaveRequest;
use App\Models\TahunAjaran;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateIjinKehadiran extends CreateRecord
{
    protected static string $resource = IjinKehadiranResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 1. Tentukan Academic Year aktif
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        if (!$activeYear) {
            Notification::make()
                ->title('Gagal')
                ->body('Tahun ajaran aktif tidak ditemukan.')
                ->danger()
                ->send();
            $this->halt();
        }
        
        $data['academic_year_id'] = $activeYear->id;
        
        // 2. Validasi Overlap
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];
        $studentId = $data['student_id'];
        
        $overlapRecord = LeaveRequest::where('student_id', $studentId)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function ($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->first();
            
        if ($overlapRecord) {
            $statusLabel = $overlapRecord->status === 'approved' ? 'Approved' : 'Pending';
            Notification::make()
                ->title('Validasi Gagal')
                ->body("Siswa ini sudah memiliki pengajuan ijin/sakit yang berstatus [{$statusLabel}] pada rentang tanggal tersebut.")
                ->danger()
                ->send();
            $this->halt();
        }

        // 3. Validasi Hari Lampau yang Sudah Hadir/Telat
        $existingAttendance = \App\Models\Presensi::where('student_id', $studentId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereIn('status', ['hadir', 'telat'])
            ->first();

        if ($existingAttendance) {
            $formattedDate = \Illuminate\Support\Carbon::parse($existingAttendance->date)->translatedFormat('d F Y');
            Notification::make()
                ->title('Validasi Gagal')
                ->body("Gagal: Siswa ini sudah memiliki catatan presensi (Hadir/Telat) pada tanggal {$formattedDate}. Silakan periksa kembali tanggal pengajuan.")
                ->danger()
                ->send();
            $this->halt();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var LeaveRequest $record */
        $record = $this->record;
        $record->recordLog('created', 'Pengajuan ijin/sakit dibuat');
    }
}

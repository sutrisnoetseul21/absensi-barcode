<?php

namespace App\Filament\Resources\PindahKelasResource\Pages;

use App\Filament\Resources\PindahKelasResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

use App\Models\EnrollmentSiswa;
use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use App\Models\RiwayatPindahKelas;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ManagePindahKelas extends ManageRecords
{
    protected static string $resource = PindahKelasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('proses_pindah_kelas')
                ->label('Proses Pindah Kelas')
                ->icon('heroicon-o-arrows-right-left')
                ->color('warning')
                ->modalHeading('Pindah Kelas Siswa')
                ->modalDescription('Pilih siswa yang sedang aktif untuk dipindahkan ke kelas lain pada tahun ajaran ini.')
                ->modalWidth('md')
                ->form([
                    Select::make('enrollment_id')
                        ->label('Pilih Siswa')
                        ->options(function () {
                            $activeYearId = PengaturanSekolah::current()?->academic_year_id_active;
                            return EnrollmentSiswa::with(['siswa', 'kelas'])
                                ->where('academic_year_id', $activeYearId)
                                ->where('status', 'aktif')
                                ->get()
                                ->mapWithKeys(function ($enrollment) {
                                    return [
                                        $enrollment->id => ($enrollment->siswa?->name ?? 'Unknown') . ' (' . ($enrollment->siswa?->nisn ?? '-') . ') - ' . ($enrollment->kelas?->name ?? 'Tanpa Kelas')
                                    ];
                                });
                        })
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, ?string $state) {
                            if (!$state) {
                                $set('from_class_id', null);
                                return;
                            }
                            $enrollment = EnrollmentSiswa::find($state);
                            $set('from_class_id', $enrollment?->class_id);
                        }),

                    Select::make('from_class_id')
                        ->label('Kelas Saat Ini')
                        ->options(Kelas::pluck('name', 'id'))
                        ->disabled()
                        ->dehydrated(),

                    Select::make('to_class_id')
                        ->label('Pindah Ke Kelas')
                        ->options(Kelas::pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->rules([
                            fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                if ($value === $get('from_class_id')) {
                                    $fail('Kelas tujuan tidak boleh sama dengan kelas saat ini.');
                                }
                            },
                        ]),
                ])
                ->action(function (array $data) {
                    $enrollment = EnrollmentSiswa::find($data['enrollment_id']);
                    if (!$enrollment) return;

                    $oldClassId = $enrollment->class_id;
                    $newClassId = $data['to_class_id'];

                    DB::transaction(function () use ($enrollment, $oldClassId, $newClassId) {
                        // 1. Log Riwayat
                        RiwayatPindahKelas::create([
                            'enrollment_id' => $enrollment->id,
                            'student_id' => $enrollment->student_id,
                            'academic_year_id' => $enrollment->academic_year_id,
                            'from_class_id' => $oldClassId,
                            'to_class_id' => $newClassId,
                            'reason' => 'Dipindah Manual (Koreksi/Mutasi Internal)'
                        ]);

                        // 2. Update Enrollment
                        $enrollment->update([
                            'class_id' => $newClassId
                        ]);
                    });

                    Notification::make()
                        ->title('Berhasil Pindah Kelas')
                        ->success()
                        ->send();
                })
        ];
    }
}

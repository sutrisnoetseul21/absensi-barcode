<?php

namespace App\Filament\Resources\Enrollment\Actions;

use App\Models\EnrollmentSiswa;
use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

/**
 * Action Bulk Enrollment Siswa ke Kelas.
 *
 * Menggantikan fitur auto-enroll yang sebelumnya ada di ImportSiswaBaruAction.
 * Admin dapat memilih banyak siswa yang belum memiliki kelas di tahun ajaran
 * tertentu, lalu menetapkan mereka ke kelas yang sama dalam satu proses.
 *
 * Lokasi: EnrollmentResource (Modul Akademik) — sesuai arsitektur pisah total.
 */
class BulkEnrollStudentsAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'bulk_enroll_students';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Daftarkan Siswa ke Kelas')
            ->icon('heroicon-o-user-plus')
            ->color('primary')
            ->modalHeading('Daftarkan Siswa ke Kelas (Bulk Enrollment)')
            ->modalDescription('Pilih Tahun Ajaran dan Kelas tujuan, lalu pilih siswa yang akan didaftarkan. Hanya siswa yang belum memiliki enrollment aktif di tahun ajaran yang dipilih yang dapat didaftarkan.')
            ->modalWidth('3xl')
            ->form([
                \Filament\Forms\Components\Select::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->options(TahunAjaran::orderedByYear()->pluck('name', 'id')->toArray())
                    ->default(fn () => PengaturanSekolah::current()?->academic_year_id_active)
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($set) => $set('student_ids', [])),

                \Filament\Forms\Components\Select::make('class_id')
                    ->label('Kelas Tujuan')
                    ->options(Kelas::orderBy('grade_level')->orderBy('name')->pluck('name', 'id')->toArray())
                    ->required()
                    ->searchable(),

                \Filament\Forms\Components\Select::make('student_ids')
                    ->label('Pilih Siswa')
                    ->helperText('Hanya siswa aktif yang belum terdaftar di tahun ajaran yang dipilih.')
                    ->multiple()
                    ->searchable()
                    ->options(function (\Filament\Schemas\Components\Utilities\Get $get) {
                        $yearId = $get('academic_year_id');

                        // Ambil semua siswa aktif
                        $query = Siswa::where('status', 'aktif')->orderBy('name');

                        // Filter: hanya siswa yang BELUM punya enrollment aktif di tahun ini
                        if ($yearId) {
                            $query->whereDoesntHave('enrollments', function ($q) use ($yearId) {
                                $q->where('academic_year_id', $yearId)
                                  ->where('status', 'aktif');
                            });
                        }

                        return $query->pluck('name', 'id')->toArray();
                    })
                    ->required()
                    ->minItems(1),
            ])
            ->action(function (array $data) {
                $yearId     = $data['academic_year_id'];
                $classId    = $data['class_id'];
                $studentIds = $data['student_ids'];

                if (empty($studentIds)) {
                    Notification::make()->title('Tidak ada siswa dipilih')->warning()->send();
                    return;
                }

                // Validasi: tahun ajaran dan kelas harus ada
                $tahunAjaran = TahunAjaran::find($yearId);
                $kelas       = Kelas::find($classId);

                if (!$tahunAjaran || !$kelas) {
                    Notification::make()->title('Data tidak valid')->body('Tahun Ajaran atau Kelas tidak ditemukan.')->danger()->send();
                    return;
                }

                $berhasil = 0;
                $dilewati = 0;

                DB::transaction(function () use ($studentIds, $yearId, $classId, &$berhasil, &$dilewati) {
                    foreach ($studentIds as $studentId) {
                        // Double-check: skip jika siswa ternyata sudah punya enrollment aktif
                        $alreadyEnrolled = EnrollmentSiswa::where('student_id', $studentId)
                            ->where('academic_year_id', $yearId)
                            ->where('status', 'aktif')
                            ->exists();

                        if ($alreadyEnrolled) {
                            $dilewati++;
                            continue;
                        }

                        EnrollmentSiswa::create([
                            'student_id'       => $studentId,
                            'academic_year_id' => $yearId,
                            'class_id'         => $classId,
                            'status'           => 'aktif',
                        ]);

                        $berhasil++;
                    }
                });

                $msg = "**{$berhasil}** siswa berhasil didaftarkan ke kelas **{$kelas->name}** (TA {$tahunAjaran->name}).";
                if ($dilewati > 0) {
                    $msg .= " **{$dilewati}** siswa dilewati karena sudah terdaftar.";
                }

                Notification::make()
                    ->title('Bulk Enrollment Selesai')
                    ->body($msg)
                    ->success()
                    ->send();
            });
    }
}

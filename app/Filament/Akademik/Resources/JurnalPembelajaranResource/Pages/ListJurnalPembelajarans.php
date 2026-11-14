<?php

namespace App\Filament\Akademik\Resources\JurnalPembelajaranResource\Pages;

use App\Filament\Akademik\Resources\JurnalPembelajaranResource;
use App\Models\Guru;
use App\Models\JurnalPembelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Pengajaran;
use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class ListJurnalPembelajarans extends ListRecords
{
    protected static string $resource = JurnalPembelajaranResource::class;

    protected static function getActiveAcademicYear(): ?TahunAjaran
    {
        $activeId = PengaturanSekolah::current()?->academic_year_id_active;
        if ($activeId) {
            $ay = TahunAjaran::find($activeId);
            if ($ay) {
                return $ay;
            }
        }

        return TahunAjaran::where('status', 'aktif')->first()
            ?? TahunAjaran::orderBy('start_year', 'desc')->first();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cetak_rekap_jurnal')
                ->label('Cetak / Export Rekap')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->modalHeading(fn () => 'Cetak & Export Rekap Jurnal — TA ' . (self::getActiveAcademicYear()?->name ?? 'Aktif'))
                ->modalDescription('Pilih format dokumen, periode (Bulanan atau Semester), serta Guru, Kelas, dan Mata Pelajaran yang diampu.')
                ->modalWidth('lg')
                ->modalSubmitActionLabel('Proses & Unduh')
                ->form([
                    Select::make('format')
                        ->label('Format Dokumen')
                        ->placeholder('— Pilih Format Dokumen (PDF / Excel) —')
                        ->options([
                            'pdf'   => 'PDF (Siap Cetak A4 Landscape)',
                            'excel' => 'Excel (.xlsx)',
                        ])
                        ->required()
                        ->native(false),

                    Select::make('periode_type')
                        ->label('Jenis Periode')
                        ->placeholder('— Pilih Jenis Periode —')
                        ->options([
                            'bulanan'  => 'Per Bulan',
                            'semester' => 'Per Semester (Semester 1 / Semester 2)',
                        ])
                        ->required()
                        ->live()
                        ->native(false),

                    Select::make('month_year')
                        ->label('Pilih Bulan')
                        ->placeholder('— Pilih Bulan —')
                        ->options(function (): array {
                            $ay = self::getActiveAcademicYear();
                            $sy = $ay?->start_year ?: (int) date('Y');
                            $ey = $ay?->end_year ?: ($sy + 1);
                            $monthNames = [
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                            ];
                            $opts = [];
                            for ($m = 7; $m <= 12; $m++) {
                                $opts[sprintf('%02d-%d', $m, $sy)] = $monthNames[$m] . ' ' . $sy;
                            }
                            for ($m = 1; $m <= 6; $m++) {
                                $opts[sprintf('%02d-%d', $m, $ey)] = $monthNames[$m] . ' ' . $ey;
                            }

                            return $opts;
                        })
                        ->default(fn () => date('m-Y'))
                        ->visible(fn (Get $get) => $get('periode_type') === 'bulanan')
                        ->required(fn (Get $get) => $get('periode_type') === 'bulanan')
                        ->native(false),

                    Select::make('semester')
                        ->label('Pilih Semester')
                        ->placeholder('— Pilih Semester —')
                        ->options([
                            'ganjil' => 'Semester 1 / Ganjil (Juli – Desember)',
                            'genap'  => 'Semester 2 / Genap (Januari – Juni)',
                        ])
                        ->default(fn () => (int) date('n') >= 7 ? 'ganjil' : 'genap')
                        ->visible(fn (Get $get) => $get('periode_type') === 'semester')
                        ->required(fn (Get $get) => $get('periode_type') === 'semester')
                        ->native(false),

                    Select::make('teacher_id')
                        ->label('Guru Pengampu')
                        ->placeholder('— Pilih Guru Pengampu —')
                        ->options(function (): array {
                            $ayId = self::getActiveAcademicYear()?->id;
                            $teacherIds = Pengajaran::query()
                                ->whereHas('kelasAjaran', fn ($q) => $q->when($ayId, fn ($sq) => $sq->where('academic_year_id', $ayId)))
                                ->pluck('teacher_id')
                                ->merge(
                                    JurnalPembelajaran::query()
                                        ->when($ayId, fn ($q) => $q->where('academic_year_id', $ayId))
                                        ->pluck('teacher_id')
                                )
                                ->filter()
                                ->unique()
                                ->values();

                            if ($teacherIds->isNotEmpty()) {
                                return Guru::whereIn('id', $teacherIds)->orderBy('name')->pluck('name', 'id')->toArray();
                            }

                            return Guru::orderBy('name')->pluck('name', 'id')->toArray();
                        })
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set) {
                            $set('class_id', null);
                            $set('mata_pelajaran_id', null);
                        }),

                    Select::make('class_id')
                        ->label('Kelas yang Diampu')
                        ->placeholder(fn (Get $get) => $get('teacher_id') ? '— Pilih Kelas —' : 'Pilih Guru terlebih dahulu')
                        ->disabled(fn (Get $get) => empty($get('teacher_id')))
                        ->options(function (Get $get): array {
                            $teacherId = $get('teacher_id');
                            if (!$teacherId) {
                                return [];
                            }

                            $ayId = self::getActiveAcademicYear()?->id;

                            $classIdsFromPengajaran = Pengajaran::query()
                                ->where('teacher_id', $teacherId)
                                ->whereHas('kelasAjaran', fn ($q) => $q->when($ayId, fn ($sq) => $sq->where('academic_year_id', $ayId)))
                                ->with('kelasAjaran:id,class_id')
                                ->get()
                                ->pluck('kelasAjaran.class_id');

                            $classIdsFromJurnal = JurnalPembelajaran::query()
                                ->where('teacher_id', $teacherId)
                                ->when($ayId, fn ($q) => $q->where('academic_year_id', $ayId))
                                ->pluck('class_id');

                            $classIds = $classIdsFromPengajaran->merge($classIdsFromJurnal)->filter()->unique()->values();

                            return Kelas::whereIn('id', $classIds)
                                ->orderBy('grade_level')
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray();
                        })
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                            $set('mata_pelajaran_id', null);
                            $teacherId = $get('teacher_id');
                            if (!$teacherId || !$state) {
                                return;
                            }
                            $ayId = self::getActiveAcademicYear()?->id;
                            $mapelIds = Pengajaran::query()
                                ->where('teacher_id', $teacherId)
                                ->whereHas('kelasAjaran', fn ($q) => $q->where('class_id', $state)->when($ayId, fn ($sq) => $sq->where('academic_year_id', $ayId)))
                                ->pluck('mata_pelajaran_id')
                                ->merge(
                                    JurnalPembelajaran::query()
                                        ->where('teacher_id', $teacherId)
                                        ->where('class_id', $state)
                                        ->when($ayId, fn ($q) => $q->where('academic_year_id', $ayId))
                                        ->pluck('mata_pelajaran_id')
                                )
                                ->filter()
                                ->unique()
                                ->values();

                            if ($mapelIds->count() === 1) {
                                $set('mata_pelajaran_id', $mapelIds->first());
                            }
                        }),

                    Select::make('mata_pelajaran_id')
                        ->label('Mata Pelajaran yang Diampu')
                        ->placeholder(fn (Get $get) => $get('class_id') ? '— Pilih Mata Pelajaran —' : 'Pilih Kelas terlebih dahulu')
                        ->disabled(fn (Get $get) => empty($get('class_id')))
                        ->options(function (Get $get): array {
                            $teacherId = $get('teacher_id');
                            $classId = $get('class_id');
                            if (!$teacherId || !$classId) {
                                return [];
                            }

                            $ayId = self::getActiveAcademicYear()?->id;

                            $mapelIdsFromPengajaran = Pengajaran::query()
                                ->where('teacher_id', $teacherId)
                                ->whereHas('kelasAjaran', fn ($q) => $q->where('class_id', $classId)->when($ayId, fn ($sq) => $sq->where('academic_year_id', $ayId)))
                                ->pluck('mata_pelajaran_id');

                            $mapelIdsFromJurnal = JurnalPembelajaran::query()
                                ->where('teacher_id', $teacherId)
                                ->where('class_id', $classId)
                                ->when($ayId, fn ($q) => $q->where('academic_year_id', $ayId))
                                ->pluck('mata_pelajaran_id');

                            $mapelIds = $mapelIdsFromPengajaran->merge($mapelIdsFromJurnal)->filter()->unique()->values();

                            return MataPelajaran::whereIn('id', $mapelIds)
                                ->orderBy('nama_mapel')
                                ->pluck('nama_mapel', 'id')
                                ->toArray();
                        })
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data) {
                    $ayId = self::getActiveAcademicYear()?->id;

                    $params = array_filter([
                        'academic_year_id'  => $ayId,
                        'periode_type'      => $data['periode_type'] ?? 'bulanan',
                        'month_year'        => ($data['periode_type'] ?? '') === 'bulanan' ? ($data['month_year'] ?? null) : null,
                        'semester'          => ($data['periode_type'] ?? '') === 'semester' ? ($data['semester'] ?? null) : null,
                        'teacher_id'        => $data['teacher_id'] ?? null,
                        'class_id'          => $data['class_id'] ?? null,
                        'mata_pelajaran_id' => $data['mata_pelajaran_id'] ?? null,
                    ], fn ($v) => $v !== null && $v !== '');

                    $routeName = ($data['format'] ?? 'pdf') === 'excel'
                        ? 'portal-guru.jurnal-mengajar.excel'
                        : 'portal-guru.jurnal-mengajar.cetak';

                    $this->redirect(route($routeName, $params));
                }),

            Actions\Action::make('toggle_presensi_kbm_siswa')
                ->label('Presensi KBM Siswa')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color(fn () => (PengaturanSekolah::current()?->enable_presensi_kbm ?? true) ? 'success' : 'gray')
                ->badge(fn () => (PengaturanSekolah::current()?->enable_presensi_kbm ?? true) ? 'Aktif' : 'Non-aktif')
                ->badgeColor(fn () => (PengaturanSekolah::current()?->enable_presensi_kbm ?? true) ? 'success' : 'gray')
                ->modalHeading('Pengaturan Presensi KBM untuk Siswa')
                ->modalDescription('Atur keterlihatan rekapitulasi presensi mata pelajaran / jam mengajar di aplikasi Portal Siswa.')
                ->modalSubmitActionLabel('Simpan Pengaturan')
                ->fillForm(fn (): array => [
                    'enable_presensi_kbm' => (bool) (PengaturanSekolah::current()?->enable_presensi_kbm ?? true),
                ])
                ->form([
                    Toggle::make('enable_presensi_kbm')
                        ->label('Tampilkan Presensi KBM di Portal Siswa')
                        ->helperText('Jika diaktifkan, tab Presensi KBM akan muncul di Portal Siswa untuk memantau kehadiran per mata pelajaran yang diinput guru di Jurnal Mengajar. Jika dinonaktifkan, tab KBM otomatis disembunyikan.')
                        ->default(true),
                ])
                ->action(function (array $data): void {
                    $settings = PengaturanSekolah::current();
                    if ($settings) {
                        $settings->update([
                            'enable_presensi_kbm' => (bool) ($data['enable_presensi_kbm'] ?? true),
                        ]);
                    }
                    \Illuminate\Support\Facades\Cache::forget('public_pengaturan_sekolah');

                    Notification::make()
                        ->title('Pengaturan Presensi KBM berhasil disimpan')
                        ->body('Status saat ini: ' . (($data['enable_presensi_kbm'] ?? true) ? 'Aktif di Portal Siswa' : 'Dinonaktifkan dari Portal Siswa'))
                        ->success()
                        ->send();
                }),

            Actions\Action::make('buka_portal_guru')
                ->label('Buka di Portal Guru')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('primary')
                ->url(fn () => route('portal-guru.jurnal-mengajar'))
                ->openUrlInNewTab(),
        ];
    }
}

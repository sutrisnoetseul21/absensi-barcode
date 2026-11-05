<?php

namespace App\Livewire\PortalSiswa;

use App\Models\JurnalPembelajaran;
use App\Models\KelasAjaran;
use App\Models\Pengajaran;
use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class SiswaJurnalKbm extends Component
{
    public $student;
    public $enrollment;
    public $kelasName = '-';
    public $tahunAjaranName = '-';
    public $enablePresensiKbm = true;

    // Filter Periode (default 'all' = seluruh semester)
    public $selectedMonthYear = 'all';
    public $availableMonths = [];

    // Data Rekapitulasi Mapel & Ringkasan Total
    public $subjects = [];
    public $summary = [
        'total_pertemuan_terdata' => 0,
        'total_hadir' => 0,
        'total_sakit' => 0,
        'total_izin' => 0,
        'total_alpa' => 0,
        'total_tidak_mengikuti' => 0,
        'persentase_kehadiran' => 100.0,
    ];

    // Detail Modal Mata Pelajaran yang Dipilih
    public $selectedSubjectDetail = null;

    public function mount()
    {
        $sekolah = PengaturanSekolah::current();
        $this->enablePresensiKbm = (bool) ($sekolah?->enable_presensi_kbm ?? true);

        abort_if(! $this->enablePresensiKbm, 403, 'Fitur Jurnal KBM sedang dinonaktifkan oleh sekolah.');

        $this->student = Auth::user()->student;
        if (! $this->student) {
            abort(403, 'Akses ditolak: Data siswa tidak ditemukan.');
        }

        $this->enrollment = $this->student->enrollmentAktif()->with(['kelas', 'tahunAjaran'])->first()
            ?: $this->student->enrollments()->with(['kelas', 'tahunAjaran'])->latest()->first();

        $this->kelasName = $this->enrollment?->kelas?->name ?? 'Kelas Siswa';
        $this->tahunAjaranName = $this->enrollment?->tahunAjaran?->name ?? '-';

        $this->generateAvailableMonths();
        $this->loadKbmData();
    }

    private function generateAvailableMonths()
    {
        $this->availableMonths = [
            'all' => 'Semua Bulan (Semester Ini)',
        ];

        $tahunAjaran = $this->enrollment?->tahunAjaran ?? TahunAjaran::where('is_active', true)->first();

        if ($tahunAjaran) {
            $sy = $tahunAjaran->start_year;
            $ey = $tahunAjaran->end_year;
            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ];

            // Juli - Desember (Start Year)
            for ($m = 7; $m <= 12; $m++) {
                $key = sprintf('%02d-%d', $m, $sy);
                $this->availableMonths[$key] = $monthNames[$m] . ' ' . $sy;
            }

            // Januari - Juni (End Year)
            for ($m = 1; $m <= 6; $m++) {
                $key = sprintf('%02d-%d', $m, $ey);
                $this->availableMonths[$key] = $monthNames[$m] . ' ' . $ey;
            }
        }
    }

    public function updatedSelectedMonthYear()
    {
        $this->loadKbmData();
    }

    public function loadKbmData()
    {
        if (! $this->student) return;

        $classId = $this->enrollment?->class_id;
        $academicYearId = $this->enrollment?->academic_year_id ?? TahunAjaran::where('is_active', true)->value('id');

        // Tentukan Filter Bulan
        $isAllMonths = ($this->selectedMonthYear === 'all');
        $month = null;
        $year = null;

        if (! $isAllMonths && str_contains($this->selectedMonthYear, '-')) {
            $parts = explode('-', $this->selectedMonthYear);
            $month = (int) $parts[0];
            $year = (int) $parts[1];
        }

        // Ambil Daftar Mapel dari Pengajaran Rombel Siswa
        $kelasAjaran = KelasAjaran::where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->first();

        $pengajarans = collect();
        if ($kelasAjaran) {
            $pengajarans = Pengajaran::where('class_academic_year_id', $kelasAjaran->id)
                ->with(['mataPelajaran', 'guru'])
                ->get();
        }

        // Ambil Seluruh Jurnal Mengajar untuk Kelas Ini
        $jurnalQuery = JurnalPembelajaran::where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->with(['guru', 'mataPelajaran', 'presensis' => function ($q) {
                $q->where('student_id', $this->student->id);
            }]);

        if (! $isAllMonths && $month && $year) {
            $jurnalQuery->whereYear('tanggal', $year)->whereMonth('tanggal', $month);
        }

        $allJurnals = $jurnalQuery->orderBy('tanggal', 'desc')
            ->orderBy('jam_mulai', 'desc')
            ->get();

        $jurnalsByMapel = $allJurnals->groupBy('mata_pelajaran_id');

        $subjectRows = [];
        $grandTotalTerdata = 0;
        $grandTotalHadir = 0;
        $grandTotalSakit = 0;
        $grandTotalIzin = 0;
        $grandTotalAlpa = 0;
        $grandTotalTidakMengikuti = 0;
        $processedMapelIds = [];

        foreach ($pengajarans as $pengajaran) {
            $mapel = $pengajaran->mataPelajaran;
            if (! $mapel) continue;

            $mapelId = $mapel->id;
            $processedMapelIds[] = $mapelId;
            $jurnalsForMapel = $jurnalsByMapel->get($mapelId, collect());

            $totalTerdata = 0;
            $hadir = 0;
            $sakit = 0;
            $izin = 0;
            $alpa = 0;
            $tidakMengikuti = 0;
            $pertemuanList = [];

            // Urutkan tanggal menaik untuk penomoran "Pertemuan 1, 2, ..."
            $sortedMeetings = $jurnalsForMapel->sortBy('tanggal')->values();

            foreach ($sortedMeetings as $index => $jurnal) {
                $studentPresensi = $jurnal->presensis->first();
                $totalTerdata++;

                $statusJurnal = $studentPresensi?->status_jurnal ?? 'hadir';
                $statusGerbang = strtolower($studentPresensi?->status_gerbang_snapshot ?? 'hadir');
                $keterangan = $studentPresensi?->keterangan ?? '';

                $normalizedStatus = 'hadir';
                $statusLabel = 'Hadir';

                if ($statusJurnal === 'tidak_mengikuti') {
                    if ($statusGerbang === 'sakit') {
                        $normalizedStatus = 'sakit';
                        $statusLabel = 'Sakit';
                        $sakit++;
                    } elseif ($statusGerbang === 'izin') {
                        $normalizedStatus = 'izin';
                        $statusLabel = 'Izin';
                        $izin++;
                    } elseif ($statusGerbang === 'alpa') {
                        $normalizedStatus = 'alpa';
                        $statusLabel = 'Alpa';
                        $alpa++;
                    } else {
                        $normalizedStatus = 'tidak_mengikuti';
                        $statusLabel = 'Tidak Mengikuti KBM';
                        $tidakMengikuti++;
                    }
                } else {
                    $normalizedStatus = 'hadir';
                    $statusLabel = 'Hadir';
                    $hadir++;
                }

                $jamKeMulai = $jurnal->jam_mulai ? (int) $jurnal->jam_mulai : 1;
                $durasiJp = (int) ($jurnal->durasi_jp ?? 2);
                $jamKeSelesai = $jamKeMulai + $durasiJp - 1;
                $jamKeLabel = ($durasiJp > 1)
                    ? "Jam Ke-{$jamKeMulai} s.d {$jamKeSelesai}"
                    : "Jam Ke-{$jamKeMulai}";

                $dateObj = Carbon::parse($jurnal->tanggal);

                $pertemuanList[] = [
                    'jurnal_id' => $jurnal->id,
                    'pertemuan_ke' => $index + 1,
                    'tanggal' => $dateObj->toDateString(),
                    'tanggal_formatted' => $dateObj->translatedFormat('d M Y'),
                    'hari' => $dateObj->translatedFormat('l'),
                    'jam_ke_label' => $jamKeLabel,
                    'durasi_jp' => $durasiJp,
                    'materi' => $jurnal->judul_materi ?: 'Materi KBM',
                    'uraian_materi' => $jurnal->uraian_kegiatan ?: '',
                    'status' => $normalizedStatus,
                    'status_label' => $statusLabel,
                    'status_gerbang' => $statusGerbang,
                    'keterangan' => $keterangan,
                    'guru_nama' => $jurnal->guru?->name ?: ($pengajaran->guru?->name ?? '-'),
                ];
            }

            // Balik kembali ke tanggal terbaru di atas untuk display riwayat
            $pertemuanList = array_reverse($pertemuanList);

            $persentase = $totalTerdata > 0
                ? round(($hadir / $totalTerdata) * 100, 1)
                : 100.0;

            $subjectRows[] = [
                'mata_pelajaran_id' => $mapelId,
                'nama_mapel' => $mapel->name ?? $mapel->nama_mapel ?? 'Mata Pelajaran',
                'kode_mapel' => $mapel->code ?? $mapel->kode_mapel ?? 'MP',
                'kategori' => $mapel->kategori ?? 'Wajib',
                'guru_pengampu' => [
                    'id' => $pengajaran->guru?->id,
                    'name' => $pengajaran->guru?->name ?? 'Belum Ditentukan',
                    'nip' => $pengajaran->guru?->nip ?? '-',
                    'photo_url' => $pengajaran->guru?->photo_path ? asset('storage/' . $pengajaran->guru->photo_path) : null,
                ],
                'stats' => [
                    'total_pertemuan_terdata' => $totalTerdata,
                    'hadir' => $hadir,
                    'sakit' => $sakit,
                    'izin' => $izin,
                    'alpa' => $alpa,
                    'tidak_mengikuti' => $tidakMengikuti,
                    'persentase_kehadiran' => $persentase,
                ],
                'has_data' => ($totalTerdata > 0),
                'pertemuan_list' => $pertemuanList,
            ];

            $grandTotalTerdata += $totalTerdata;
            $grandTotalHadir += $hadir;
            $grandTotalSakit += $sakit;
            $grandTotalIzin += $izin;
            $grandTotalAlpa += $alpa;
            $grandTotalTidakMengikuti += $tidakMengikuti;
        }

        $grandPersentase = $grandTotalTerdata > 0
            ? round(($grandTotalHadir / $grandTotalTerdata) * 100, 1)
            : 100.0;

        $this->subjects = $subjectRows;
        $this->summary = [
            'total_pertemuan_terdata' => $grandTotalTerdata,
            'total_hadir' => $grandTotalHadir,
            'total_sakit' => $grandTotalSakit,
            'total_izin' => $grandTotalIzin,
            'total_alpa' => $grandTotalAlpa,
            'total_tidak_mengikuti' => $grandTotalTidakMengikuti,
            'persentase_kehadiran' => $grandPersentase,
        ];
    }

    public function showDetail($mataPelajaranId)
    {
        foreach ($this->subjects as $subj) {
            if ($subj['mata_pelajaran_id'] == $mataPelajaranId) {
                $this->selectedSubjectDetail = $subj;
                break;
            }
        }
    }

    public function closeDetail()
    {
        $this->selectedSubjectDetail = null;
    }

    public function render()
    {
        return view('livewire.portal-siswa.siswa-jurnal-kbm');
    }
}

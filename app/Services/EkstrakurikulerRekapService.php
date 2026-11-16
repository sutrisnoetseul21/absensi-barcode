<?php

namespace App\Services;

use App\Models\Ekstrakurikuler;
use App\Models\EkstrakurikulerPertemuan;
use App\Models\EkstrakurikulerPresensi;
use Carbon\Carbon;

class EkstrakurikulerRekapService
{
    /**
     * Susun data lengkap rekap kehadiran + jurnal pertemuan untuk 1 ekstrakurikuler.
     */
    public function buildEkskulReportData(
        Ekstrakurikuler $ekskul,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $ekskul->loadMissing(['pembinas', 'tahunAjaran']);
        $yearId = $ekskul->academic_year_id;

        $meetingsQuery = EkstrakurikulerPertemuan::where('ekstrakurikuler_id', $ekskul->id)
            ->when($startDate, fn ($q) => $q->whereDate('tanggal', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('tanggal', '<=', $endDate))
            ->orderBy('tanggal', 'asc')
            ->orderBy('pertemuan_ke', 'asc');

        $meetingModels = $meetingsQuery->get();
        $meetingIds = $meetingModels->pluck('id')->all();

        $members = $ekskul->anggotaAktif()
            ->with([
                'siswa.enrollments' => fn ($q) => $q->where('academic_year_id', $yearId)
                    ->where('status', 'aktif')
                    ->with('kelas'),
            ])
            ->get()
            ->sortBy(fn ($a) => mb_strtolower($a->siswa?->name ?? ''))
            ->values();

        $presensis = empty($meetingIds)
            ? collect()
            : EkstrakurikulerPresensi::whereIn('pertemuan_id', $meetingIds)->get();

        $presensiByMeetingAndStudent = [];
        foreach ($presensis as $p) {
            $presensiByMeetingAndStudent[$p->pertemuan_id][$p->student_id] = $p;
        }

        $meetings = [];
        foreach ($meetingModels as $m) {
            $mPres = $presensis->where('pertemuan_id', $m->id);
            $meetings[] = [
                'id'              => $m->id,
                'pertemuan_ke'    => $m->pertemuan_ke,
                'tanggal'         => $m->tanggal?->toDateString(),
                'tanggal_short'   => $m->tanggal?->format('d/m') ?? '-',
                'tanggal_label'   => $m->tanggal?->locale('id')->translatedFormat('l, d F Y') ?? '-',
                'judul_kegiatan'  => $m->judul_kegiatan ?: 'Latihan Ekstrakurikuler',
                'catatan_pembina' => $m->catatan_pembina ?: '-',
                'status_sesi'     => $m->status_sesi ?? 'aktif',
                'hadir_count'     => $mPres->where('status', 'hadir')->count(),
                'izin_count'      => $mPres->where('status', 'izin')->count(),
                'sakit_count'     => $mPres->where('status', 'sakit')->count(),
                'alpa_count'      => $mPres->where('status', 'alpa')->count(),
            ];
        }

        $memberRows = [];
        $totalMeetings = count($meetings);
        $sumPct = 0;

        foreach ($members as $idx => $member) {
            $st = $member->siswa;
            if (!$st) continue;

            $daily = [];
            $hadir = 0;
            $izin = 0;
            $sakit = 0;
            $alpa = 0;

            foreach ($meetings as $m) {
                $att = $presensiByMeetingAndStudent[$m['id']][$st->id] ?? null;
                $code = match ($att?->status) {
                    'hadir' => 'H',
                    'izin'  => 'I',
                    'sakit' => 'S',
                    'alpa'  => 'A',
                    default => '-',
                };

                if ($code === 'H') $hadir++;
                elseif ($code === 'I') $izin++;
                elseif ($code === 'S') $sakit++;
                elseif ($code === 'A') $alpa++;

                $daily[$m['id']] = $code;
            }

            $pct = $totalMeetings > 0 ? (int) round(($hadir / $totalMeetings) * 100) : 0;
            $sumPct += $pct;

            $memberRows[] = [
                'no'             => count($memberRows) + 1,
                'student_id'     => $st->id,
                'nisn'           => $st->nisn ?? '-',
                'name'           => $st->name,
                'kelas'          => $st->enrollments->first()?->kelas?->name ?? '-',
                'tanggal_gabung' => $member->tanggal_gabung ? Carbon::parse($member->tanggal_gabung)->format('d/m/Y') : '-',
                'daily'          => $daily,
                'hadir'          => $hadir,
                'izin'           => $izin,
                'sakit'          => $sakit,
                'alpa'           => $alpa,
                'pct'            => $pct,
            ];
        }

        $avgPct = count($memberRows) > 0 && $totalMeetings > 0
            ? (int) round($sumPct / count($memberRows))
            : 0;

        return [
            'ekskul'   => $ekskul,
            'meetings' => $meetings,
            'members'  => $memberRows,
            'summary'  => [
                'total_anggota'      => count($memberRows),
                'total_pertemuan'    => $totalMeetings,
                'avg_attendance_pct' => $avgPct,
            ],
        ];
    }

    /**
     * Ringkasan monitoring seluruh ekstrakurikuler pada 1 tahun ajaran (untuk Portal Akademik).
     */
    public function getMonitoringOverview(
        string $academicYearId,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $ekskuls = Ekstrakurikuler::with(['pembinas', 'tahunAjaran'])
            ->withCount('anggotaAktif')
            ->where('academic_year_id', $academicYearId)
            ->orderBy('nama_ekskul')
            ->get();

        $rows = [];
        $totalMembers = 0;
        $totalMeetings = 0;
        $sumPct = 0;
        $pctCount = 0;

        foreach ($ekskuls as $eks) {
            $meetings = EkstrakurikulerPertemuan::where('ekstrakurikuler_id', $eks->id)
                ->when($startDate, fn ($q) => $q->whereDate('tanggal', '>=', $startDate))
                ->when($endDate, fn ($q) => $q->whereDate('tanggal', '<=', $endDate))
                ->orderBy('tanggal', 'desc')
                ->orderBy('pertemuan_ke', 'desc')
                ->get();

            $meetingIds = $meetings->pluck('id')->all();
            $meetingCount = count($meetingIds);
            $memberCount = (int) $eks->anggota_aktif_count;

            $hadir = 0;
            $izin = 0;
            $sakit = 0;
            $alpa = 0;

            if (!empty($meetingIds)) {
                $counts = EkstrakurikulerPresensi::whereIn('pertemuan_id', $meetingIds)
                    ->selectRaw('status, count(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status');

                $hadir = (int) ($counts['hadir'] ?? 0);
                $izin = (int) ($counts['izin'] ?? 0);
                $sakit = (int) ($counts['sakit'] ?? 0);
                $alpa = (int) ($counts['alpa'] ?? 0);
            }

            $expectedSlots = $memberCount * $meetingCount;
            $avgPct = $expectedSlots > 0 ? (int) round(($hadir / $expectedSlots) * 100) : 0;

            $latest = $meetings->first();

            $rows[] = [
                'id'               => $eks->id,
                'nama_ekskul'      => $eks->nama_ekskul,
                'slug'             => $eks->slug,
                'status_aktif'     => (bool) $eks->status_aktif,
                'pembinas'         => $eks->pembinas->pluck('name')->implode(', ') ?: '-',
                'anggota_count'    => $memberCount,
                'pertemuan_count'  => $meetingCount,
                'hadir'            => $hadir,
                'izin'             => $izin,
                'sakit'            => $sakit,
                'alpa'             => $alpa,
                'avg_pct'          => $avgPct,
                'latest_tanggal'   => $latest?->tanggal?->locale('id')->translatedFormat('d M Y') ?? '-',
                'latest_judul'     => $latest?->judul_kegiatan ?: ($latest ? 'Pertemuan ke-' . $latest->pertemuan_ke : '-'),
            ];

            $totalMembers += $memberCount;
            $totalMeetings += $meetingCount;
            if ($expectedSlots > 0) {
                $sumPct += $avgPct;
                $pctCount++;
            }
        }

        return [
            'rows' => $rows,
            'totals' => [
                'total_ekskul'    => count($rows),
                'total_anggota'   => $totalMembers,
                'total_pertemuan' => $totalMeetings,
                'avg_pct'         => $pctCount > 0 ? (int) round($sumPct / $pctCount) : 0,
            ],
        ];
    }
}


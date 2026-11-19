<?php

namespace App\Livewire\PortalSiswa;

use App\Models\Ekstrakurikuler;
use App\Models\EkstrakurikulerPresensi;
use App\Models\EkstrakurikulerSiswa;
use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class SiswaEkstrakurikuler extends Component
{
    public $student;
    public $academicYears = [];
    public ?string $selectedAcademicYearId = null;
    public ?string $selectedEkskulId = null;

    public array $ekskulList = [];
    public ?array $selectedItem = null;

    public function mount(?string $slug = null): void
    {
        abort_if(
            !(PengaturanSekolah::current()?->enable_ekstrakurikuler ?? true),
            403,
            'Fitur Ekstrakurikuler sedang dinonaktifkan.'
        );

        $this->student = Auth::user()?->student;
        if (!$this->student) {
            abort(403, 'Akses ditolak: Data siswa tidak ditemukan.');
        }

        $this->academicYears = TahunAjaran::orderBy('start_year', 'desc')->get();
        $this->selectedAcademicYearId = PengaturanSekolah::current()?->academic_year_id_active
            ?? TahunAjaran::where('status', 'aktif')->value('id')
            ?? $this->academicYears->first()?->id;

        if ($slug) {
            $target = Ekstrakurikuler::where('slug', $slug)->firstOrFail();
            $isMember = $target->anggotaAktif()->where('student_id', $this->student->id)->exists();
            abort_unless($isMember, 403, 'Anda bukan anggota aktif ekstrakurikuler ini.');

            $this->selectedAcademicYearId = $target->academic_year_id;
            $this->selectedEkskulId = $target->id;
        }

        $this->loadData();
    }

    public function updatedSelectedAcademicYearId(): void
    {
        $this->selectedEkskulId = null;
        $this->loadData();
    }

    public function selectEkskul(string $ekskulId): void
    {
        $found = collect($this->ekskulList)->firstWhere('id', $ekskulId);
        if ($found) {
            $this->selectedEkskulId = $found['id'];
            $this->selectedItem = $found;
        }
    }

    public function loadData(): void
    {
        if (!$this->selectedAcademicYearId || !$this->student) {
            $this->ekskulList = [];
            $this->selectedItem = null;
            return;
        }

        $memberships = EkstrakurikulerSiswa::with([
            'ekstrakurikuler' => fn ($q) => $q->with([
                'pembinas',
                'tahunAjaran',
                'pertemuans' => fn ($pq) => $pq->orderBy('tanggal', 'desc')->orderBy('pertemuan_ke', 'desc'),
            ]),
        ])
            ->where('student_id', $this->student->id)
            ->where('status_aktif', true)
            ->whereHas('ekstrakurikuler', fn ($q) => $q
                ->where('academic_year_id', $this->selectedAcademicYearId)
                ->where('status_aktif', true)
            )
            ->get()
            ->sortBy(fn ($m) => strtolower($m->ekstrakurikuler?->nama_ekskul ?? ''))
            ->values();

        $myPresensis = EkstrakurikulerPresensi::where('student_id', $this->student->id)
            ->whereIn('ekstrakurikuler_id', $memberships->pluck('ekstrakurikuler_id'))
            ->get()
            ->keyBy('pertemuan_id');

        $list = [];
        foreach ($memberships as $m) {
            $ekskul = $m->ekstrakurikuler;
            if (!$ekskul) continue;

            $pertemuans = [];
            $hadir = 0;
            $izin = 0;
            $sakit = 0;
            $alpa = 0;
            $belum = 0;

            foreach ($ekskul->pertemuans as $p) {
                $att = $myPresensis->get($p->id);
                $st = $att?->status ?? 'belum';
                if ($st === 'hadir') $hadir++;
                elseif ($st === 'izin') $izin++;
                elseif ($st === 'sakit') $sakit++;
                elseif ($st === 'alpa') $alpa++;
                else $belum++;

                $pertemuans[] = [
                    'pertemuan_ke'    => $p->pertemuan_ke,
                    'tanggal_label'   => $p->tanggal ? $p->tanggal->locale('id')->translatedFormat('l, d F Y') : '-',
                    'judul_kegiatan'  => $p->judul_kegiatan ?: 'Pertemuan Latihan Rutin',
                    'catatan_pembina' => $p->catatan_pembina,
                    'status_sesi'     => $p->status_sesi,
                    'status'          => $st,
                    'scan_time'       => ($att && $att->scan_time) ? substr((string) $att->scan_time, 0, 5) : null,
                    'is_manual'       => $att ? (bool) $att->is_manual_input : false,
                    'keterangan'      => $att?->keterangan,
                ];
            }

            $totalPertemuan = count($pertemuans);
            $pct = $totalPertemuan > 0 ? round(($hadir / $totalPertemuan) * 100) : 0;

            $list[] = [
                'id'              => $ekskul->id,
                'slug'            => $ekskul->slug,
                'nama_ekskul'     => $ekskul->nama_ekskul,
                'tahun_ajaran'    => $ekskul->tahunAjaran?->name ?? '-',
                'pembinas'        => $ekskul->pembinas->pluck('name')->implode(', ') ?: '-',
                'keterangan'      => $ekskul->keterangan,
                'tanggal_gabung'  => $m->tanggal_gabung ? $m->tanggal_gabung->locale('id')->translatedFormat('d F Y') : '-',
                'total_pertemuan' => $totalPertemuan,
                'hadir'           => $hadir,
                'izin'            => $izin,
                'sakit'           => $sakit,
                'alpa'            => $alpa,
                'belum'           => $belum,
                'pct'             => $pct,
                'pertemuans'      => $pertemuans,
            ];
        }

        $this->ekskulList = $list;

        if (count($list) > 0) {
            $selected = $this->selectedEkskulId
                ? collect($list)->firstWhere('id', $this->selectedEkskulId)
                : null;
            $selected = $selected ?? $list[0];
            $this->selectedEkskulId = $selected['id'];
            $this->selectedItem = $selected;
        } else {
            $this->selectedEkskulId = null;
            $this->selectedItem = null;
        }
    }

    public function render()
    {
        $title = $this->selectedItem
            ? "Ekstrakurikuler {$this->selectedItem['nama_ekskul']} - Portal Siswa"
            : 'Ekstrakurikuler Saya - Portal Siswa';

        return view('livewire.portal-siswa.siswa-ekstrakurikuler')
            ->title($title);
    }
}


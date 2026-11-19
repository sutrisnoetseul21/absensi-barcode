<?php

namespace App\Livewire\PortalPresensi\Ekstrakurikuler;

use App\Models\Ekstrakurikuler;
use App\Models\EkstrakurikulerPertemuan;
use App\Models\EkstrakurikulerPresensi;
use App\Models\EkstrakurikulerSiswa;
use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class InputManualEkstrakurikuler extends Component
{
    public $academicYears = [];
    public ?string $selectedAcademicYearId = null;

    public $ekskuls = [];
    public ?string $selectedEkskulId = null;

    public string $inputDate = '';
    public string $judulKegiatan = '';
    public string $statusSesi = 'aktif';

    public ?string $existingPertemuanId = null;
    public ?int $existingPertemuanKe = null;

    public array $presensiRows = [];

    public function mount(): void
    {
        abort_if(
            !(PengaturanSekolah::current()?->enable_ekstrakurikuler ?? true),
            403,
            'Fitur Ekstrakurikuler sedang dinonaktifkan.'
        );

        $this->academicYears = TahunAjaran::orderBy('start_year', 'desc')->get();
        $this->inputDate = request()->query('date') ?: Carbon::today('Asia/Jakarta')->toDateString();

        $activeYear = TahunAjaran::where('status', 'aktif')->first()
            ?? (PengaturanSekolah::current()?->academic_year_id_active
                ? TahunAjaran::find(PengaturanSekolah::current()->academic_year_id_active)
                : null)
            ?? $this->academicYears->first();

        $this->selectedAcademicYearId = $activeYear?->id;

        $requestedEkskulId = request()->query('ekskul_id');
        if ($requestedEkskulId) {
            $reqEks = Ekstrakurikuler::find($requestedEkskulId);
            if ($reqEks) {
                $this->selectedAcademicYearId = $reqEks->academic_year_id;
                $this->selectedEkskulId = $reqEks->id;
            }
        }

        $this->loadEkskuls();
    }

    public function updatedSelectedAcademicYearId(): void
    {
        $this->selectedEkskulId = null;
        $this->loadEkskuls();
    }

    public function updatedSelectedEkskulId(): void
    {
        $this->loadFormRows();
    }

    public function updatedInputDate(): void
    {
        $this->loadFormRows();
    }

    protected function loadEkskuls(): void
    {
        if (!$this->selectedAcademicYearId) {
            $this->ekskuls = collect();
            $this->presensiRows = [];
            return;
        }

        $this->ekskuls = Ekstrakurikuler::where('academic_year_id', $this->selectedAcademicYearId)
            ->where('status_aktif', true)
            ->orderBy('nama_ekskul')
            ->get();

        if ($this->ekskuls->isNotEmpty()) {
            $exists = $this->selectedEkskulId && $this->ekskuls->contains('id', $this->selectedEkskulId);
            if (!$exists) {
                $this->selectedEkskulId = $this->ekskuls->first()->id;
            }
            $this->loadFormRows();
        } else {
            $this->selectedEkskulId = null;
            $this->existingPertemuanId = null;
            $this->existingPertemuanKe = null;
            $this->presensiRows = [];
        }
    }

    public function loadFormRows(): void
    {
        if (!$this->selectedEkskulId || !$this->inputDate) {
            $this->presensiRows = [];
            return;
        }

        $ekskul = collect($this->ekskuls)->firstWhere('id', $this->selectedEkskulId)
            ?? Ekstrakurikuler::find($this->selectedEkskulId);

        if (!$ekskul) {
            $this->presensiRows = [];
            return;
        }

        $pertemuan = EkstrakurikulerPertemuan::where('ekstrakurikuler_id', $ekskul->id)
            ->whereDate('tanggal', $this->inputDate)
            ->first();

        $this->existingPertemuanId = $pertemuan?->id;
        $this->existingPertemuanKe = $pertemuan?->pertemuan_ke;
        $this->judulKegiatan = $pertemuan?->judul_kegiatan ?? '';
        $this->statusSesi = $pertemuan?->status_sesi ?? 'aktif';

        $members = EkstrakurikulerSiswa::with([
            'siswa' => fn ($q) => $q->with([
                'enrollments' => fn ($eq) => $eq->where('academic_year_id', $ekskul->academic_year_id)
                    ->where('status', 'aktif')
                    ->with('kelas'),
            ]),
        ])
            ->where('ekstrakurikuler_id', $ekskul->id)
            ->where('status_aktif', true)
            ->get()
            ->sortBy(fn ($m) => strtolower($m->siswa?->name ?? ''))
            ->values();

        $existing = $pertemuan
            ? EkstrakurikulerPresensi::where('pertemuan_id', $pertemuan->id)->get()->keyBy('student_id')
            : collect();

        $rows = [];
        foreach ($members as $m) {
            $st = $m->siswa;
            if (!$st) continue;

            $att = $existing->get($st->id);
            $rows[] = [
                'student_id'      => $st->id,
                'name'            => $st->name,
                'nisn'            => $st->nisn ?? '-',
                'kelas'           => $st->enrollments->first()?->kelas?->name ?? '-',
                'status'          => $att?->status ?? '',
                'scan_time'       => ($att && $att->scan_time) ? substr((string) $att->scan_time, 0, 5) : null,
                'is_manual_input' => $att ? (bool) $att->is_manual_input : false,
                'keterangan'      => $att?->keterangan ?? '',
            ];
        }

        $this->presensiRows = $rows;
    }

    public function markUnfilledAs(string $status): void
    {
        if (!in_array($status, ['hadir', 'izin', 'sakit', 'alpa'], true)) return;

        foreach ($this->presensiRows as $i => $row) {
            if (empty($row['status'])) {
                $this->presensiRows[$i]['status'] = $status;
            }
        }
    }

    public function savePresensi(): void
    {
        if (!$this->selectedEkskulId || !$this->inputDate) return;

        $ekskul = Ekstrakurikuler::find($this->selectedEkskulId);
        if (!$ekskul) return;

        $user = Auth::user();
        $isGuru = $user?->teacher !== null;
        $actor = $isGuru ? $user->teacher : $user;
        $now = Carbon::now('Asia/Jakarta');

        $pertemuan = EkstrakurikulerPertemuan::findOrCreateForDate(
            $ekskul,
            $this->inputDate,
            $user,
            trim($this->judulKegiatan) ?: null
        );

        DB::transaction(function () use ($pertemuan, $ekskul, $user, $isGuru, $actor, $now) {
            $pertemuan->update([
                'judul_kegiatan' => trim($this->judulKegiatan) ?: null,
                'status_sesi'    => in_array($this->statusSesi, ['aktif', 'selesai'], true) ? $this->statusSesi : 'aktif',
            ]);

            foreach ($this->presensiRows as $row) {
                $status = $row['status'] ?? '';
                if (!in_array($status, ['hadir', 'izin', 'sakit', 'alpa'], true)) {
                    continue;
                }

                $existing = EkstrakurikulerPresensi::where('pertemuan_id', $pertemuan->id)
                    ->where('student_id', $row['student_id'])
                    ->first();

                // Pertahankan hasil scan kiosk jika petugas tidak mengubah status maupun keterangannya
                $isKioskKeep = (
                    $existing
                    && $existing->is_manual_input === false
                    && $existing->status === $status
                    && ($existing->keterangan ?? '') === ($row['keterangan'] ?? '')
                );
                if ($isKioskKeep) {
                    continue;
                }

                $ket = trim((string) ($row['keterangan'] ?? ''));

                EkstrakurikulerPresensi::updateOrCreate(
                    [
                        'pertemuan_id' => $pertemuan->id,
                        'student_id'   => $row['student_id'],
                    ],
                    [
                        'ekstrakurikuler_id'   => $ekskul->id,
                        'status'               => $status,
                        'scan_time'            => ($status === 'hadir')
                            ? ($existing?->scan_time ?: $now->format('H:i:s'))
                            : null,
                        'is_manual_input'      => true,
                        'manual_input_by_id'   => $actor?->id,
                        'manual_input_by_type' => $isGuru ? \App\Models\Guru::class : \App\Models\User::class,
                        'manual_input_at'      => $now,
                        'manual_input_user_id' => $user?->id,
                        'keterangan'           => $ket !== '' ? $ket : 'Input Manual Petugas Presensi',
                    ]
                );
            }
        });

        $this->loadFormRows();
        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => "Presensi {$ekskul->nama_ekskul} Pertemuan ke-{$pertemuan->pertemuan_ke} berhasil disimpan!",
        ]);
    }

    public function render()
    {
        $selectedEkskul = collect($this->ekskuls)->firstWhere('id', $this->selectedEkskulId);

        return view('livewire.portal-presensi.ekstrakurikuler.input-manual-ekstrakurikuler', [
            'selectedEkskul' => $selectedEkskul,
        ])->title('Input Manual Presensi Ekstrakurikuler');
    }
}


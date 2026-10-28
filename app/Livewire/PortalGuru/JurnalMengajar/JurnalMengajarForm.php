<?php

namespace App\Livewire\PortalGuru\JurnalMengajar;

use App\Models\EnrollmentSiswa;
use App\Models\Guru;
use App\Models\JurnalPembelajaran;
use App\Models\JurnalPembelajaranPresensi;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Pengajaran;
use App\Models\PengaturanSekolah;
use App\Models\Presensi;
use App\Models\PresensiNotificationSetting;
use App\Models\TahunAjaran;
use App\Services\WhatsAppGatewayService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class JurnalMengajarForm extends Component
{
    public ?string $jurnalId = null;
    public bool $isEdit = false;
    public bool $isSupervisor = false;

    // Master Info
    public ?string $academic_year_id = null;
    public ?string $teacher_id = null;
    public string $teacher_name = '';
    public string $tanggal = '';

    // Waktu KBM
    public int $jam_mulai = 1;
    public int $durasi_jp = 2;
    public int $jam_selesai = 2;
    public string $jam_ke_label = 'Jam ke 1 - 2 (2 JP)';

    // Pemilihan Kelas & Mapel
    public ?string $class_id = null;
    public ?int $mata_pelajaran_id = null;
    public bool $singleMapel = false;

    public $availableClasses = [];
    public $availableMapels = [];

    // Form Jurnal
    public string $materi = '';
    public string $uraian_materi = '';
    public string $refleksi = '';

    // Matriks Presensi Siswa: key by student_id
    // [student_id => ['id', 'nis', 'name', 'status_gerbang', 'status_jurnal', 'is_locked', 'keterangan', 'diabsenkan_telat']]
    public array $studentList = [];

    protected function rules(): array
    {
        return [
            'tanggal'           => 'required|date',
            'class_id'          => 'required|exists:classes,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'jam_mulai'         => 'required|integer|min:1|max:15',
            'durasi_jp'         => 'required|integer|min:1|max:10',
            'materi'            => 'required|string|max:255',
            'uraian_materi'     => 'required|string|min:5',
            'refleksi'          => 'nullable|string',
        ];
    }

    protected function messages(): array
    {
        return [
            'tanggal.required'           => 'Tanggal pembelajaran wajib diisi.',
            'class_id.required'          => 'Pilih kelas rombongan belajar.',
            'mata_pelajaran_id.required' => 'Pilih mata pelajaran.',
            'jam_mulai.required'         => 'Jam mulai wajib ditentukan.',
            'durasi_jp.required'         => 'Durasi JP wajib ditentukan.',
            'materi.required'            => 'Judul materi pokok pembelajaran wajib diisi.',
            'uraian_materi.required'     => 'Uraian aktivitas KBM wajib diisi.',
        ];
    }

    public function mount(?string $id = null)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Akses ditolak: Silakan login.');
        }

        $this->isSupervisor = $user->isSuperAdmin() || $user->hasPermissionTo('portal_guru:supervisi_jurnal');

        $activeYear = PengaturanSekolah::current()?->academic_year_id_active 
            ?? TahunAjaran::where('status', 'aktif')->first()?->id;
        $this->academic_year_id = $activeYear;

        if ($id) {
            $this->isEdit = true;
            $this->jurnalId = $id;
            $this->loadExistingJurnal($id);
        } else {
            $this->tanggal = now()->format('Y-m-d');
            
            // Guru pengampu default: user login
            $guru = $user->teacher;
            if (!$guru && !$this->isSupervisor) {
                abort(403, 'Akses ditolak: Akun Anda tidak memiliki profil guru.');
            }

            $this->teacher_id = $guru?->id;
            $this->teacher_name = $guru?->name ?? $user->name;

            $this->calculateJamSelesai();
            $this->loadAvailableClasses();
        }
    }

    public function calculateJamSelesai(): void
    {
        $mulai = max(1, (int)$this->jam_mulai);
        $durasi = max(1, (int)$this->durasi_jp);
        $this->jam_selesai = $mulai + $durasi - 1;

        if ($durasi === 1) {
            $this->jam_ke_label = "Jam ke {$mulai} (1 JP)";
        } else {
            $this->jam_ke_label = "Jam ke {$mulai} - {$this->jam_selesai} ({$durasi} JP)";
        }
    }

    public function updatedJamMulai(): void
    {
        $this->calculateJamSelesai();
    }

    public function updatedDurasiJp(): void
    {
        $this->calculateJamSelesai();
    }

    public function loadAvailableClasses(): void
    {
        if (!$this->teacher_id || !$this->academic_year_id) {
            $this->availableClasses = [];
            return;
        }

        $pengajarans = Pengajaran::where('teacher_id', $this->teacher_id)
            ->whereHas('kelasAjaran', fn($q) => $q->where('academic_year_id', $this->academic_year_id))
            ->with('kelasAjaran.kelas')
            ->get();

        $this->availableClasses = $pengajarans->pluck('kelasAjaran.kelas')->unique('id')->filter()->values();

        // Jika hanya 1 kelas, langsung pilih
        if ($this->availableClasses->count() === 1 && empty($this->class_id)) {
            $this->class_id = $this->availableClasses->first()->id;
            $this->updatedClassId();
        }
    }

    public function updatedClassId(): void
    {
        $this->loadAvailableMapels();
        $this->loadStudentsAndGateAttendance();
    }

    public function loadAvailableMapels(): void
    {
        if (!$this->teacher_id || !$this->class_id || !$this->academic_year_id) {
            $this->availableMapels = [];
            $this->mata_pelajaran_id = null;
            $this->singleMapel = false;
            return;
        }

        $pengajarans = Pengajaran::where('teacher_id', $this->teacher_id)
            ->whereHas('kelasAjaran', function ($q) {
                $q->where('class_id', $this->class_id)
                  ->where('academic_year_id', $this->academic_year_id);
            })
            ->with('mataPelajaran')
            ->get();

        $this->availableMapels = $pengajarans->pluck('mataPelajaran')->unique('id')->filter()->values();

        // Jika guru hanya mengajar 1 mapel di kelas ini, otomatis pilih dan kunci
        if ($this->availableMapels->count() === 1) {
            $this->mata_pelajaran_id = $this->availableMapels->first()->id;
            $this->singleMapel = true;
        } else {
            $this->singleMapel = false;
        }
    }

    public function updatedTanggal(): void
    {
        if ($this->class_id) {
            $this->loadStudentsAndGateAttendance();
        }
    }

    public function loadStudentsAndGateAttendance(): void
    {
        if (!$this->class_id || !$this->academic_year_id || !$this->tanggal) {
            $this->studentList = [];
            return;
        }

        // Ambil seluruh siswa aktif di kelas ini pada tahun ajaran aktif
        $enrollments = EnrollmentSiswa::where('class_id', $this->class_id)
            ->where('academic_year_id', $this->academic_year_id)
            ->where('status', 'aktif')
            ->with('student')
            ->get();

        $students = $enrollments->pluck('student')->filter()->sortBy('name')->values();

        // Ambil data presensi gerbang hari itu dari tabel attendances
        $studentIds = $students->pluck('id')->toArray();
        $attendances = Presensi::whereIn('student_id', $studentIds)
            ->whereDate('date', $this->tanggal)
            ->get()
            ->keyBy('student_id');

        $list = [];
        foreach ($students as $siswa) {
            $att = $attendances->get($siswa->id);
            $statusGerbang = $att ? $att->status : 'belum_absen';

            // Kunci jika status gerbang adalah izin, sakit, alpa
            $isLocked = in_array($statusGerbang, ['izin', 'sakit', 'alpa'], true);

            // Default status jurnal:
            // Jika sakit/izin/alpa -> tidak_mengikuti (dan terkunci)
            // Jika hadir/telat -> hadir
            // Jika belum_absen -> default hadir tapi dengan penanda
            $defaultStatusJurnal = $isLocked ? 'tidak_mengikuti' : 'hadir';

            $list[$siswa->id] = [
                'student_id'            => $siswa->id,
                'nis'                   => $siswa->nis ?? $siswa->nisn ?? '-',
                'name'                  => $siswa->name,
                'status_gerbang'        => $statusGerbang,
                'status_jurnal'         => $defaultStatusJurnal,
                'is_locked'             => $isLocked,
                'keterangan'            => '',
                'diabsenkan_telat'      => false,
            ];
        }

        $this->studentList = $list;
    }

    public function toggleAbsenkanTelat(string $studentId): void
    {
        if (!isset($this->studentList[$studentId])) return;

        // Hanya untuk siswa yang belum absen gerbang
        if ($this->studentList[$studentId]['status_gerbang'] === 'belum_absen') {
            $current = $this->studentList[$studentId]['diabsenkan_telat'];
            $this->studentList[$studentId]['diabsenkan_telat'] = !$current;
            $this->studentList[$studentId]['status_jurnal'] = !$current ? 'hadir' : 'tidak_mengikuti';
        }
    }

    public function setStatusJurnal(string $studentId, string $status): void
    {
        if (!isset($this->studentList[$studentId])) return;

        // Dilarang ubah jika terkunci (sakit/izin/alpa)
        if ($this->studentList[$studentId]['is_locked']) {
            return;
        }

        $this->studentList[$studentId]['status_jurnal'] = $status;
    }

    public function markAllHadir(): void
    {
        foreach ($this->studentList as $studentId => $item) {
            if (!$item['is_locked']) {
                $this->studentList[$studentId]['status_jurnal'] = 'hadir';
                if ($item['status_gerbang'] === 'belum_absen') {
                    $this->studentList[$studentId]['diabsenkan_telat'] = true;
                }
            }
        }
    }

    public function markAllTidakMengikuti(): void
    {
        foreach ($this->studentList as $studentId => $item) {
            if (!$item['is_locked']) {
                $this->studentList[$studentId]['status_jurnal'] = 'tidak_mengikuti';
                if ($item['status_gerbang'] === 'belum_absen') {
                    $this->studentList[$studentId]['diabsenkan_telat'] = false;
                }
            }
        }
    }

    public function setDurasi(int $jp): void
    {
        $this->durasi_jp = max(1, min(10, $jp));
        $this->calculateJamSelesai();
    }

    public function setJamMulai(int $jam): void
    {
        $this->jam_mulai = max(1, min(15, $jam));
        $this->calculateJamSelesai();
    }

    public function loadExistingJurnal(string $id): void
    {
        $jurnal = JurnalPembelajaran::with(['guru', 'kelas', 'mataPelajaran', 'presensis.siswa'])->findOrFail($id);

        $this->academic_year_id = $jurnal->academic_year_id;
        $this->teacher_id       = $jurnal->teacher_id;
        $this->teacher_name     = $jurnal->guru?->name ?? 'Guru Pengampu';
        $this->tanggal          = $jurnal->tanggal->format('Y-m-d');
        $this->class_id         = $jurnal->class_id;
        $this->mata_pelajaran_id= $jurnal->mata_pelajaran_id;
        $this->jam_mulai        = $jurnal->jam_mulai;
        $this->durasi_jp        = $jurnal->durasi_jp;
        $this->jam_selesai      = $jurnal->jam_selesai;
        $this->jam_ke_label     = $jurnal->jam_ke_label;
        $this->materi           = $jurnal->materi;
        $this->uraian_materi    = $jurnal->uraian_materi;
        $this->refleksi         = $jurnal->refleksi ?? '';

        $this->loadAvailableClasses();
        $this->loadAvailableMapels();

        // Load presensi yang sudah tersimpan
        $list = [];
        foreach ($jurnal->presensis as $presensi) {
            $siswa = $presensi->siswa;
            if (!$siswa) continue;

            $statusGerbang = $presensi->status_gerbang_snapshot;
            $isLocked = in_array($statusGerbang, ['izin', 'sakit', 'alpa'], true);

            $list[$siswa->id] = [
                'student_id'            => $siswa->id,
                'nis'                   => $siswa->nis ?? $siswa->nisn ?? '-',
                'name'                  => $siswa->name,
                'status_gerbang'        => $statusGerbang,
                'status_jurnal'         => $presensi->status_jurnal,
                'is_locked'             => $isLocked,
                'keterangan'            => $presensi->keterangan ?? '',
                'diabsenkan_telat'      => $presensi->diabsenkan_telat_di_gerbang,
            ];
        }

        $this->studentList = $list;
    }

    public function save()
    {
        $this->validate();
        $this->calculateJamSelesai();

        $user = Auth::user();

        // Validasi kepemilikan jika bukan supervisor
        if (!$this->isSupervisor && $this->teacher_id !== $user->teacher?->id) {
            session()->flash('error', 'Anda tidak berwenang menyimpan jurnal untuk guru lain.');
            return;
        }

        DB::beginTransaction();
        try {
            // Hitung ringkasan kehadiran
            $totalSiswa = count($this->studentList);
            $totalHadir = 0;
            $totalTidakMengikuti = 0;

            foreach ($this->studentList as $item) {
                if ($item['status_jurnal'] === 'hadir') {
                    $totalHadir++;
                } else {
                    $totalTidakMengikuti++;
                }
            }

            $jurnalData = [
                'academic_year_id'       => $this->academic_year_id,
                'teacher_id'             => $this->teacher_id,
                'class_id'               => $this->class_id,
                'mata_pelajaran_id'      => $this->mata_pelajaran_id,
                'tanggal'                => $this->tanggal,
                'jam_mulai'              => $this->jam_mulai,
                'durasi_jp'              => $this->durasi_jp,
                'jam_selesai'            => $this->jam_selesai,
                'jam_ke_label'           => $this->jam_ke_label,
                'materi'                 => trim($this->materi),
                'uraian_materi'          => trim($this->uraian_materi),
                'refleksi'               => trim($this->refleksi) ?: null,
                'total_siswa'            => $totalSiswa,
                'total_hadir'            => $totalHadir,
                'total_tidak_mengikuti'  => $totalTidakMengikuti,
                'created_by'             => $user->id,
            ];

            if ($this->isEdit && $this->jurnalId) {
                $jurnal = JurnalPembelajaran::findOrFail($this->jurnalId);
                $jurnal->update($jurnalData);
                // Hapus detail lama untuk diganti baru
                $jurnal->presensis()->delete();
            } else {
                $jurnal = JurnalPembelajaran::create($jurnalData);
            }

            // Simpan detail presensi sesi & sinkronisasi siswa yang diabsenkan telat
            $studentsToNotifyTelat = [];

            foreach ($this->studentList as $studentId => $item) {
                $statusJurnal = $item['status_jurnal'];
                $diabsenkanTelat = (bool)($item['diabsenkan_telat'] ?? false);

                // Jika siswa belum absen di gerbang dan guru meng-absenkannya hadir:
                // Catat ke attendances dengan status WAJIB TELAT
                if ($item['status_gerbang'] === 'belum_absen' && ($diabsenkanTelat || $statusJurnal === 'hadir')) {
                    $diabsenkanTelat = true;

                    // Cari enrollment id siswa
                    $enrollment = EnrollmentSiswa::where('student_id', $studentId)
                        ->where('academic_year_id', $this->academic_year_id)
                        ->first();

                    Presensi::updateOrCreate(
                        [
                            'student_id'       => $studentId,
                            'date'             => $this->tanggal,
                        ],
                        [
                            'enrollment_id'    => $enrollment?->id,
                            'class_id'         => $this->class_id,
                            'academic_year_id' => $this->academic_year_id,
                            'status'           => 'telat',
                            'late_minutes'     => 1,
                            'is_manual_input'  => true,
                            'manual_input_by_id'   => $this->teacher_id,
                            'manual_input_by_type' => Guru::class,
                            'source'           => 'jurnal_guru',
                            'note'             => 'Diabsenkan oleh guru mata pelajaran di Jurnal Pembelajaran',
                            'scan_time'        => now()->toTimeString(),
                        ]
                    );

                    $studentsToNotifyTelat[] = $studentId;
                }

                // Simpan ke tabel detail jurnal
                JurnalPembelajaranPresensi::create([
                    'jurnal_pembelajaran_id'     => $jurnal->id,
                    'student_id'                 => $studentId,
                    'status_jurnal'              => $statusJurnal,
                    'status_gerbang_snapshot'    => $item['status_gerbang'],
                    'keterangan'                 => trim($item['keterangan'] ?? '') ?: null,
                    'diabsenkan_telat_di_gerbang'=> $diabsenkanTelat,
                ]);
            }

            DB::commit();

            session()->flash('success', 'Jurnal mengajar berhasil disimpan!');
            return redirect()->route('portal-guru.jurnal-mengajar');

        } catch (\Throwable $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menyimpan jurnal: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.portal-guru.jurnal-mengajar.jurnal-mengajar-form');
    }
}

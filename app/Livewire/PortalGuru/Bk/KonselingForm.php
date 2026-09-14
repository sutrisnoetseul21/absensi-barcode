<?php

namespace App\Livewire\PortalGuru\Bk;

use App\Models\EnrollmentSiswa;
use App\Models\JurnalGuruWali;
use App\Models\Kelas;
use App\Models\KonselingBk;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class KonselingForm extends Component
{
    public $konselingId = null;
    public $isEdit = false;

    // Form fields
    public $student_id = '';
    public $tanggal_waktu = '';
    public $bidang_bimbingan = 'Pribadi';
    public $jenis_layanan = 'Konseling Individu';
    public $topik_masalah = '';
    public $uraian_kasus = '';
    public $pendekatan_teknik = '';
    public $hasil_konseling = '';
    public $rencana_tindak_lanjut = '';
    public $status_kasus = 'Dalam Penanganan';
    public $rekomendasi_untuk_guru_wali = '';
    public $is_rahasia = true;
    public $jurnal_guru_wali_id = null;

    // Referral object if linked
    public $referralJurnal = null;
    public $isStudentLocked = false;

    // Active state & scoped classes
    public $teacher;
    public $activeYear;
    public $accessibleClassIds = [];
    public $accessibleClasses = [];
    public $canAccessAll = false;

    // List of eligible students
    public $studentsList = [];

    protected function rules()
    {
        return [
            'student_id'                  => 'required|exists:students,id',
            'tanggal_waktu'               => 'required|date',
            'bidang_bimbingan'            => 'required|in:Pribadi,Sosial,Belajar,Karir',
            'jenis_layanan'               => 'required|in:Konseling Individu,Konseling Kelompok,Mediasi,Bimbingan Klasikal,Home Visit / Kunjungan Rumah,Konferensi Kasus',
            'topik_masalah'               => 'required|string|min:3|max:255',
            'uraian_kasus'                => 'required|string|min:5',
            'pendekatan_teknik'           => 'nullable|string',
            'hasil_konseling'             => 'nullable|string',
            'rencana_tindak_lanjut'       => 'nullable|string',
            'status_kasus'                => 'required|in:Dalam Penanganan,Bimbingan Lanjutan,Dirujuk ke Ahli Luar,Tuntas / Selesai',
            'rekomendasi_untuk_guru_wali' => 'nullable|string',
            'is_rahasia'                  => 'boolean',
            'jurnal_guru_wali_id'         => 'nullable|exists:jurnal_guru_wali,id',
        ];
    }

    protected $messages = [
        'student_id.required'       => 'Pilih siswa binaan terlebih dahulu.',
        'student_id.exists'         => 'Siswa yang dipilih tidak valid.',
        'tanggal_waktu.required'    => 'Waktu sesi konseling wajib diisi.',
        'bidang_bimbingan.required' => 'Pilih bidang bimbingan.',
        'jenis_layanan.required'    => 'Pilih format / jenis layanan konseling.',
        'topik_masalah.required'    => 'Topik permasalahan wajib diisi.',
        'topik_masalah.min'         => 'Topik permasalahan minimal 3 karakter.',
        'uraian_kasus.required'     => 'Uraian kasus wajib diisi.',
        'uraian_kasus.min'          => 'Uraian kasus minimal 5 karakter.',
        'status_kasus.required'     => 'Pilih status kasus saat ini.',
    ];

    public function mount($id = null)
    {
        $user = Auth::user();
        if (! $user || ! $user->canAccessPortalBk()) {
            abort(403, 'Akses ditolak: Anda tidak memiliki hak akses sebagai Guru BK.');
        }

        $this->teacher = $user->teacher;
        $this->activeYear = TahunAjaran::where('status', 'aktif')->first();

        if ($this->teacher) {
            $this->canAccessAll = $this->teacher->canAccessAllClasses();
            if ($this->canAccessAll) {
                $this->accessibleClasses = Kelas::orderBy('name')->get();
                $this->accessibleClassIds = $this->accessibleClasses->pluck('id')->toArray();
            } else {
                $this->accessibleClassIds = $this->teacher->getKelasBinaanBkIds($this->activeYear?->id);
                $this->accessibleClasses = Kelas::whereIn('id', $this->accessibleClassIds)->orderBy('name')->get();
            }
        }

        $this->loadStudentsList();

        if ($id) {
            $this->isEdit = true;
            $this->konselingId = $id;
            $this->loadExistingKonseling($id);
        } else {
            $this->tanggal_waktu = Carbon::now()->format('Y-m-d\TH:i');

            // Cek jika berasal dari rujukan Guru Wali
            if (request()->has('rujukan_id')) {
                $rujukan = JurnalGuruWali::with(['siswa', 'guru'])->find(request('rujukan_id'));
                if ($rujukan) {
                    $this->referralJurnal = $rujukan;
                    $this->jurnal_guru_wali_id = $rujukan->id;
                    $this->student_id = $rujukan->student_id;
                    $this->isStudentLocked = true;
                    $this->topik_masalah = 'Tindak Lanjut Rujukan Guru Wali: ' . $rujukan->kategori_pendampingan;

                    // Petakan kategori Guru Wali ke bidang bimbingan BK
                    $mapping = [
                        'Akademik'                => 'Belajar',
                        'Sosial & Psikologis'     => 'Sosial',
                        'Minat & Bakat / Ekskul'  => 'Karir',
                        'Karakter & Kedisiplinan' => 'Pribadi',
                    ];
                    if (isset($mapping[$rujukan->kategori_pendampingan])) {
                        $this->bidang_bimbingan = $mapping[$rujukan->kategori_pendampingan];
                    }

                    $this->uraian_kasus = "Catatan Guru Wali ({$rujukan->guru?->name}):\n" . $rujukan->uraian_pembahasan;
                }
            } elseif (request()->has('student_id')) {
                $this->student_id = request('student_id');
                $this->isStudentLocked = true;
            }
        }
    }

    protected function loadStudentsList()
    {
        $query = EnrollmentSiswa::with(['siswa', 'kelas'])
            ->where('status', 'aktif')
            ->when($this->activeYear, fn ($q) => $q->where('academic_year_id', $this->activeYear->id));

        if (! $this->canAccessAll && ! empty($this->accessibleClassIds)) {
            $query->whereIn('class_id', $this->accessibleClassIds);
        } elseif (! $this->canAccessAll && empty($this->accessibleClassIds)) {
            $this->studentsList = collect();
            return;
        }

        $this->studentsList = $query->get()->map(function ($enrollment) {
            return [
                'id'         => $enrollment->siswa->id,
                'name'       => $enrollment->siswa->name,
                'nisn'       => $enrollment->siswa->nisn,
                'kelas_name' => $enrollment->kelas->name ?? '—',
                'avatar'     => $enrollment->siswa->avatar_url,
            ];
        })->sortBy('name')->values()->toArray();
    }

    protected function loadExistingKonseling($id)
    {
        $konseling = KonselingBk::with(['siswa', 'jurnalGuruWali.guru'])->findOrFail($id);

        if (! $this->canAccessAll && $konseling->teacher_id !== $this->teacher?->id) {
            // Cek jika kelas siswa termasuk dalam kelas binaannya
            $studentClassId = EnrollmentSiswa::where('student_id', $konseling->student_id)
                ->when($this->activeYear, fn ($q) => $q->where('academic_year_id', $this->activeYear->id))
                ->value('class_id');

            if (! in_array($studentClassId, $this->accessibleClassIds)) {
                abort(403, 'Anda tidak berhak mengedit sesi konseling di luar kelas binaan Anda.');
            }
        }

        $this->student_id = $konseling->student_id;
        $this->tanggal_waktu = $konseling->tanggal_waktu->format('Y-m-d\TH:i');
        $this->bidang_bimbingan = $konseling->bidang_bimbingan;
        $this->jenis_layanan = $konseling->jenis_layanan;
        $this->topik_masalah = $konseling->topik_masalah;
        $this->uraian_kasus = $konseling->uraian_kasus;
        $this->pendekatan_teknik = $konseling->pendekatan_teknik;
        $this->hasil_konseling = $konseling->hasil_konseling;
        $this->rencana_tindak_lanjut = $konseling->rencana_tindak_lanjut;
        $this->status_kasus = $konseling->status_kasus;
        $this->rekomendasi_untuk_guru_wali = $konseling->rekomendasi_untuk_guru_wali;
        $this->is_rahasia = (bool) $konseling->is_rahasia;
        $this->jurnal_guru_wali_id = $konseling->jurnal_guru_wali_id;
        $this->referralJurnal = $konseling->jurnalGuruWali;
        $this->isStudentLocked = true;
    }

    public function selectTeknik($teknik)
    {
        if (empty($this->pendekatan_teknik)) {
            $this->pendekatan_teknik = $teknik;
        } else {
            $this->pendekatan_teknik .= ', ' . $teknik;
        }
    }

    public function save()
    {
        $validated = $this->validate();

        $data = [
            'academic_year_id'            => $this->activeYear?->id,
            'teacher_id'                  => $this->teacher->id,
            'student_id'                  => $validated['student_id'],
            'jurnal_guru_wali_id'         => $validated['jurnal_guru_wali_id'] ?: null,
            'tanggal_waktu'               => $validated['tanggal_waktu'],
            'jenis_layanan'               => $validated['jenis_layanan'],
            'bidang_bimbingan'            => $validated['bidang_bimbingan'],
            'topik_masalah'               => $validated['topik_masalah'],
            'uraian_kasus'                => $validated['uraian_kasus'],
            'pendekatan_teknik'           => $validated['pendekatan_teknik'] ?: null,
            'hasil_konseling'             => $validated['hasil_konseling'] ?: null,
            'rencana_tindak_lanjut'       => $validated['rencana_tindak_lanjut'] ?: null,
            'status_kasus'                => $validated['status_kasus'],
            'rekomendasi_untuk_guru_wali' => $validated['rekomendasi_untuk_guru_wali'] ?: null,
            'is_rahasia'                  => (bool) $this->is_rahasia,
        ];

        if ($this->isEdit) {
            $konseling = KonselingBk::findOrFail($this->konselingId);
            $konseling->update($data);
            session()->flash('success', 'Sesi konseling berhasil diperbarui.');
        } else {
            KonselingBk::create($data);
            session()->flash('success', 'Sesi konseling baru berhasil disimpan.');
        }

        return redirect()->route('portal-guru.bk.konseling');
    }

    public function render()
    {
        $selectedStudent = null;
        if ($this->student_id) {
            $selectedStudent = Siswa::find($this->student_id);
        }

        return view('livewire.portal-guru.bk.konseling-form', [
            'selectedStudent' => $selectedStudent,
        ]);
    }
}

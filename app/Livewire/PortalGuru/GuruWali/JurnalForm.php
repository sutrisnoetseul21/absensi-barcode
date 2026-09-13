<?php

namespace App\Livewire\PortalGuru\GuruWali;

use App\Models\JurnalGuruWali;
use App\Models\KelompokGuruWali;
use App\Models\KonsultasiGuruWali;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class JurnalForm extends Component
{
    public $teacher;
    public $kelompok;
    public $jurnalId = null;

    // Form fields
    public $student_id = '';
    public $tanggal_waktu;
    public $jenis_pendampingan = 'Individu';
    public $kategori_pendampingan = 'Akademik';
    public $uraian_pembahasan = '';
    public $status_sesi = 'Tuntas / Selesai';
    public $rujukan_kolaborasi = 'Mandiri';
    public $rencana_tindak_lanjut = '';
    public $is_public_note = false;

    // Relasi konsultasi jika konversi
    public $konsultasi_id = null;

    // Status terkunci jika dibuka spesifik dari kartu siswa / edit
    public $isStudentLocked = false;

    protected function rules()
    {
        return [
            'student_id'            => 'required|exists:students,id',
            'tanggal_waktu'         => 'required|date',
            'jenis_pendampingan'    => 'required|in:Individu,Kelompok Kecil,Klasikal',
            'kategori_pendampingan' => 'required|in:Akademik,Karakter & Kedisiplinan,Minat & Bakat / Ekskul,Sosial & Psikologis',
            'uraian_pembahasan'     => 'required|string|min:5',
            'status_sesi'           => 'required|in:Tuntas / Selesai,Dalam Pemantauan,Bimbingan Lanjutan',
            'rujukan_kolaborasi'    => 'required|in:Mandiri,Wali Kelas,Guru BK,Orang Tua / Wali',
            'rencana_tindak_lanjut' => 'nullable|string',
            'is_public_note'        => 'boolean',
        ];
    }

    protected $messages = [
        'student_id.required'            => 'Pilih siswa binaan terlebih dahulu.',
        'student_id.exists'              => 'Siswa yang dipilih tidak valid.',
        'tanggal_waktu.required'         => 'Waktu pendampingan wajib diisi.',
        'uraian_pembahasan.required'     => 'Uraian pembahasan wajib diisi.',
        'uraian_pembahasan.min'          => 'Uraian pembahasan minimal 5 karakter.',
        'kategori_pendampingan.required' => 'Pilih kategori pendampingan.',
        'jenis_pendampingan.required'    => 'Pilih jenis pendampingan.',
        'status_sesi.required'           => 'Pilih status sesi bimbingan.',
        'rujukan_kolaborasi.required'    => 'Pilih rujukan kolaborasi.',
    ];

    public function mount($id = null)
    {
        $user = Auth::user();
        $this->teacher = $user->teacher;

        if (! $this->teacher) {
            abort(403, 'Anda tidak terdaftar sebagai guru.');
        }

        $this->kelompok = KelompokGuruWali::where('teacher_id', $this->teacher->id)->first();

        if (! $this->kelompok || ! $this->kelompok->status_aktif) {
            abort(403, 'Anda belum memiliki tugas penugasan Guru Wali aktif.');
        }

        $this->tanggal_waktu = Carbon::now()->format('Y-m-d\TH:i');

        // Mode Edit
        if ($id) {
            $jurnal = JurnalGuruWali::where('teacher_id', $this->teacher->id)->findOrFail($id);
            $this->jurnalId = $jurnal->id;
            $this->student_id = $jurnal->student_id;
            $this->isStudentLocked = true;
            $this->tanggal_waktu = Carbon::parse($jurnal->tanggal_waktu)->format('Y-m-d\TH:i');
            $this->jenis_pendampingan = $jurnal->jenis_pendampingan;
            $this->kategori_pendampingan = $jurnal->kategori_pendampingan;
            $this->uraian_pembahasan = $jurnal->uraian_pembahasan;
            $this->status_sesi = $jurnal->status_sesi;
            $this->rujukan_kolaborasi = $jurnal->rujukan_kolaborasi;
            $this->rencana_tindak_lanjut = $jurnal->rencana_tindak_lanjut ?? '';
            $this->is_public_note = (bool) $jurnal->is_public_note;
        } else {
            // Pre-fill dari parameter URL ?student_id=xxx
            if (request()->has('student_id')) {
                $reqStudentId = request('student_id');
                // Pastikan siswa ada di anggota aktif kelompok
                $isAnggota = $this->kelompok->anggotaAktif()->where('student_id', $reqStudentId)->exists();
                if ($isAnggota) {
                    $this->student_id = $reqStudentId;
                    $this->isStudentLocked = true;
                }
            }

            // Pre-fill dari konversi konsultasi ?konsultasi_id=xxx
            if (request()->has('konsultasi_id')) {
                $konsultasi = KonsultasiGuruWali::where('teacher_id', $this->teacher->id)
                    ->find(request('konsultasi_id'));

                if ($konsultasi) {
                    $this->konsultasi_id = $konsultasi->id;
                    $this->student_id = $konsultasi->student_id;
                    $this->isStudentLocked = true;
                    $this->kategori_pendampingan = $konsultasi->kategori_pendampingan;
                    $this->uraian_pembahasan = "Topik: " . $konsultasi->topik_konsultasi . "\n\nPermasalahan Siswa:\n" . $konsultasi->detail_permasalahan . ($konsultasi->tanggapan_guru ? "\n\nTanggapan Guru:\n" . $konsultasi->tanggapan_guru : '');
                }
            }
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'teacher_id'            => $this->teacher->id,
            'kelompok_id'           => $this->kelompok->id,
            'student_id'            => $this->student_id,
            'tanggal_waktu'         => $this->tanggal_waktu,
            'jenis_pendampingan'    => $this->jenis_pendampingan,
            'kategori_pendampingan' => $this->kategori_pendampingan,
            'uraian_pembahasan'     => $this->uraian_pembahasan,
            'status_sesi'           => $this->status_sesi,
            'rujukan_kolaborasi'    => $this->rujukan_kolaborasi,
            'rencana_tindak_lanjut' => $this->rencana_tindak_lanjut ?: null,
            'is_public_note'        => $this->is_public_note,
        ];

        if ($this->jurnalId) {
            $jurnal = JurnalGuruWali::where('teacher_id', $this->teacher->id)->findOrFail($this->jurnalId);
            $jurnal->update($data);
            session()->flash('success', 'Catatan jurnal pendampingan berhasil diperbarui.');
        } else {
            $jurnal = JurnalGuruWali::create($data);

            // Jika dibuat dari konversi konsultasi, hubungkan
            if ($this->konsultasi_id) {
                $konsultasi = KonsultasiGuruWali::where('teacher_id', $this->teacher->id)->find($this->konsultasi_id);
                if ($konsultasi) {
                    $konsultasi->update([
                        'status_pengajuan' => 'Dikonversi ke Jurnal',
                        'jurnal_id'        => $jurnal->id,
                    ]);
                }
            }

            session()->flash('success', 'Catatan jurnal pendampingan berhasil disimpan.');
        }

        return redirect()->route('portal-guru.guru-wali.jurnal');
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $anggotaList = $this->kelompok->anggotaAktif()
            ->with(['siswa.enrollmentAktif.kelas'])
            ->get();

        $selectedStudent = null;
        if ($this->student_id) {
            $selectedStudent = Siswa::with('enrollmentAktif.kelas')->find($this->student_id);
        }

        return view('livewire.portal-guru.guru-wali.jurnal-form', [
            'anggotaList'     => $anggotaList,
            'selectedStudent' => $selectedStudent,
        ]);
    }
}

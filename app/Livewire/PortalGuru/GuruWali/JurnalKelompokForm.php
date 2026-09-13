<?php

namespace App\Livewire\PortalGuru\GuruWali;

use App\Models\JurnalGuruWali;
use App\Models\KelompokGuruWali;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class JurnalKelompokForm extends Component
{
    public $teacher;
    public $kelompok;

    // Sesi Bersama (Global)
    public $tanggal_waktu;
    public $jenis_pendampingan = 'Kelompok Kecil';
    public $kategori_pendampingan = 'Akademik';
    public $uraian_pembahasan = '';
    public $rujukan_kolaborasi = 'Mandiri';
    public $rencana_tindak_lanjut = '';
    public $is_public_note = false;

    // Kontrol Massal
    public $selectedAll = true;
    public $bulkStatus = 'Tuntas / Selesai';

    // Daftar Siswa Dampingan
    public $studentsData = [];

    protected function rules()
    {
        return [
            'tanggal_waktu'         => 'required|date',
            'jenis_pendampingan'    => 'required|in:Kelompok Kecil,Klasikal',
            'kategori_pendampingan' => 'required|in:Akademik,Karakter & Kedisiplinan,Minat & Bakat / Ekskul,Sosial & Psikologis',
            'uraian_pembahasan'     => 'required|string|min:5',
            'rujukan_kolaborasi'    => 'required|in:Mandiri,Wali Kelas,Guru BK,Orang Tua / Wali',
            'rencana_tindak_lanjut' => 'nullable|string',
            'is_public_note'        => 'boolean',
            'studentsData'          => 'required|array|min:1',
            'studentsData.*.status_sesi' => 'required|in:Tuntas / Selesai,Dalam Pemantauan,Bimbingan Lanjutan',
        ];
    }

    protected $messages = [
        'tanggal_waktu.required'         => 'Tanggal & waktu sesi wajib diisi.',
        'uraian_pembahasan.required'     => 'Topik / uraian pembahasan kelompok wajib diisi.',
        'uraian_pembahasan.min'          => 'Topik / uraian pembahasan minimal 5 karakter.',
        'kategori_pendampingan.required' => 'Pilih kategori pembahasan.',
        'jenis_pendampingan.required'    => 'Pilih jenis pendampingan kelompok.',
        'rujukan_kolaborasi.required'    => 'Pilih rujukan kolaborasi.',
    ];

    public function mount()
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

        $anggotaList = $this->kelompok->anggotaAktif()
            ->with(['siswa.enrollmentAktif.kelas'])
            ->get();

        $this->studentsData = [];
        foreach ($anggotaList as $anggota) {
            $siswa = $anggota->siswa;
            if ($siswa) {
                $this->studentsData[] = [
                    'student_id'     => $siswa->id,
                    'name'           => $siswa->name,
                    'nisn'           => $siswa->nisn ?? '-',
                    'kelas'          => $siswa->enrollmentAktif?->kelas?->name ?? 'Belum ada kelas',
                    'avatar'         => $siswa->avatar_url,
                    'selected'       => true,
                    'status_sesi'    => 'Tuntas / Selesai',
                    'catatan_khusus' => '',
                    'rtl_khusus'     => '',
                ];
            }
        }
    }

    public function toggleSelectAll()
    {
        $this->selectedAll = ! $this->selectedAll;
        foreach ($this->studentsData as $index => $item) {
            $this->studentsData[$index]['selected'] = $this->selectedAll;
        }
    }

    public function updatedSelectedAll($value)
    {
        foreach ($this->studentsData as $index => $item) {
            $this->studentsData[$index]['selected'] = (bool) $value;
        }
    }

    public function applyBulkStatus($status)
    {
        if (empty($status)) return;
        $this->bulkStatus = $status;
        foreach ($this->studentsData as $index => $item) {
            if ($item['selected']) {
                $this->studentsData[$index]['status_sesi'] = $status;
            }
        }
    }

    public function getSelectedCountProperty()
    {
        return collect($this->studentsData)->where('selected', true)->count();
    }

    public function save()
    {
        $this->validate();

        $selectedStudents = collect($this->studentsData)->where('selected', true);

        if ($selectedStudents->isEmpty()) {
            $this->addError('studentsData', 'Pilih minimal 1 siswa yang mengikuti sesi bimbingan kelompok ini.');
            return;
        }

        DB::transaction(function () use ($selectedStudents) {
            foreach ($selectedStudents as $item) {
                $catatan = trim($item['catatan_khusus'] ?? '');
                $finalUraian = $this->uraian_pembahasan;
                if (!empty($catatan)) {
                    $finalUraian .= "\n\n[Catatan Khusus Siswa: {$catatan}]";
                }

                $rtl = trim($item['rtl_khusus'] ?? '');
                $finalRtl = !empty($rtl) ? $rtl : ($this->rencana_tindak_lanjut ?: null);

                JurnalGuruWali::create([
                    'teacher_id'            => $this->teacher->id,
                    'kelompok_id'           => $this->kelompok->id,
                    'student_id'            => $item['student_id'],
                    'tanggal_waktu'         => $this->tanggal_waktu,
                    'jenis_pendampingan'    => $this->jenis_pendampingan,
                    'kategori_pendampingan' => $this->kategori_pendampingan,
                    'uraian_pembahasan'     => $finalUraian,
                    'status_sesi'           => $item['status_sesi'] ?? 'Tuntas / Selesai',
                    'rujukan_kolaborasi'    => $this->rujukan_kolaborasi,
                    'rencana_tindak_lanjut' => $finalRtl,
                    'is_public_note'        => $this->is_public_note,
                ]);
            }
        });

        $count = $selectedStudents->count();
        session()->flash('success', "Berhasil mencatat jurnal bimbingan kelompok untuk {$count} siswa binaan.");

        return redirect()->route('portal-guru.guru-wali.jurnal');
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        return view('livewire.portal-guru.guru-wali.jurnal-kelompok-form');
    }
}

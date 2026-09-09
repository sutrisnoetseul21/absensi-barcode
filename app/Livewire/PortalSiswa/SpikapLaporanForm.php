<?php

namespace App\Livewire\PortalSiswa;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\SpikapLaporan;
use App\Models\SpikapLampiran;
use App\Models\SpikapLogStatus;

#[Layout('components.layouts.portal')]
class SpikapLaporanForm extends Component
{
    use WithFileUploads;

    public $student;

    // ─── Field Form ──────────────────────────────────────────────────────────
    public string $sifat_laporan     = 'biasa';
    public string $jenis_perundungan = '';
    public string $uraian_kejadian   = '';
    public string $lokasi_kejadian   = '';
    public string $waktu_kejadian    = '';
    public string $tujuan_penerima   = ''; // hanya untuk sifat=biasa

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile[] */
    public array $foto_bukti  = [];

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile[] */
    public array $video_bukti = [];

    // State UI
    public bool $submitted = false;
    public ?int $laporanIdBaru = null;

    // ─── Mount ───────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->student = Auth::user()->student;

        if (!$this->student || !in_array($this->student->status, ['aktif', 'active'])) {
            abort(403, 'Hanya siswa aktif yang dapat membuat laporan SPIKAP.');
        }
    }

    // ─── Watchers ────────────────────────────────────────────────────────────

    /**
     * Reset tujuan_penerima ketika sifat laporan diubah ke 'darurat'.
     */
    public function updatedSifatLaporan(string $value): void
    {
        if ($value === 'darurat') {
            $this->tujuan_penerima = '';
        }
    }

    // ─── Validasi ────────────────────────────────────────────────────────────

    protected function rules(): array
    {
        $rules = [
            'sifat_laporan'      => 'required|in:biasa,darurat',
            'jenis_perundungan'  => 'required|in:fisik,verbal,sosial,digital,lainnya',
            'uraian_kejadian'    => 'required|string|min:20|max:5000',
            'lokasi_kejadian'    => 'nullable|string|max:255',
            'waktu_kejadian'     => 'nullable|date',

            // Foto: maks 5 file, masing-masing max 5 MB, format jpg/jpeg/png/webp
            'foto_bukti'         => 'nullable|array|max:5',
            'foto_bukti.*'       => 'file|mimes:jpg,jpeg,png,webp|max:5120',

            // Video: maks 2 file, masing-masing max 50 MB, format mp4/mov/avi
            'video_bukti'        => 'nullable|array|max:2',
            'video_bukti.*'      => 'file|mimes:mp4,mov,avi|max:51200',
        ];

        // tujuan_penerima wajib hanya untuk laporan biasa
        if ($this->sifat_laporan === 'biasa') {
            $rules['tujuan_penerima'] = 'required|in:guru_bk,wali_kelas';
        } else {
            $rules['tujuan_penerima'] = 'nullable';
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'jenis_perundungan.required'  => 'Pilih jenis perundungan.',
            'uraian_kejadian.required'    => 'Uraian kejadian wajib diisi.',
            'uraian_kejadian.min'         => 'Uraian kejadian minimal 20 karakter.',
            'tujuan_penerima.required'    => 'Pilih tujuan laporan untuk laporan biasa.',
            'foto_bukti.max'              => 'Maksimal 5 foto.',
            'foto_bukti.*.mimes'          => 'Foto harus berformat JPG, PNG, atau WebP.',
            'foto_bukti.*.max'            => 'Ukuran foto maksimal 5 MB.',
            'video_bukti.max'             => 'Maksimal 2 video.',
            'video_bukti.*.mimes'         => 'Video harus berformat MP4, MOV, atau AVI.',
            'video_bukti.*.max'           => 'Ukuran video maksimal 50 MB.',
        ];
    }

    // ─── Submit ──────────────────────────────────────────────────────────────

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            // 1. Simpan laporan utama
            $laporan = SpikapLaporan::create([
                'student_id'         => $this->student->id,
                'sifat_laporan'      => $this->sifat_laporan,
                'jenis_perundungan'  => $this->jenis_perundungan,
                'uraian_kejadian'    => $this->uraian_kejadian,
                'lokasi_kejadian'    => $this->lokasi_kejadian ?: null,
                'waktu_kejadian'     => $this->waktu_kejadian ?: null,
                'tujuan_penerima'    => $this->sifat_laporan === 'biasa'
                                            ? $this->tujuan_penerima
                                            : null,
                'status'             => 'diterima',
            ]);

            // 2. Simpan lampiran foto
            foreach ($this->foto_bukti as $foto) {
                $path = $foto->storeAs(
                    'spikap/' . $laporan->id,
                    'foto_' . uniqid() . '.' . $foto->getClientOriginalExtension(),
                    'local'
                );
                SpikapLampiran::create([
                    'laporan_id' => $laporan->id,
                    'tipe_file'  => 'foto',
                    'path_file'  => $path,
                    'nama_asli'  => $foto->getClientOriginalName(),
                    'ukuran_bytes' => $foto->getSize(),
                ]);
            }

            // 3. Simpan lampiran video
            foreach ($this->video_bukti as $video) {
                $path = $video->storeAs(
                    'spikap/' . $laporan->id,
                    'video_' . uniqid() . '.' . $video->getClientOriginalExtension(),
                    'local'
                );
                SpikapLampiran::create([
                    'laporan_id' => $laporan->id,
                    'tipe_file'  => 'video',
                    'path_file'  => $path,
                    'nama_asli'  => $video->getClientOriginalName(),
                    'ukuran_bytes' => $video->getSize(),
                ]);
            }

            // 4. Catat log status awal (sistem)
            SpikapLogStatus::catatSistem(
                $laporan->id,
                'diterima',
                'Laporan diterima dan disimpan ke sistem.'
            );

            $this->laporanIdBaru = $laporan->id;

            Log::info("SPIKAP: laporan #{$laporan->id} ({$this->sifat_laporan}) dibuat oleh siswa #{$this->student->id}");
        });

        // 5. Trigger notifikasi WhatsApp jika laporan darurat
        if ($this->laporanIdBaru) {
            $laporanBaru = SpikapLaporan::find($this->laporanIdBaru);
            if ($laporanBaru && $laporanBaru->sifat_laporan === 'darurat') {
                try {
                    app(\App\Services\SpikapNotificationService::class)->sendEmergencyNotification($laporanBaru);
                } catch (\Exception $e) {
                    Log::error("SPIKAP: gagal mengirim notifikasi WA darurat untuk laporan #{$laporanBaru->id}: " . $e->getMessage());
                }
            }
        }

        $this->submitted = true;
        $this->reset(['sifat_laporan', 'jenis_perundungan', 'uraian_kejadian',
                       'lokasi_kejadian', 'waktu_kejadian', 'tujuan_penerima',
                       'foto_bukti', 'video_bukti']);
    }

    // ─── Render ──────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.portal-siswa.spikap-laporan-form');
    }
}

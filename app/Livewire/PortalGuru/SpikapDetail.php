<?php

namespace App\Livewire\PortalGuru;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SpikapLaporan;
use App\Models\SpikapLogStatus;

#[Layout('components.layouts.portal')]
class SpikapDetail extends Component
{
    public int $laporanId;
    public string $statusBaru = '';
    public string $catatan = '';
    public bool $canUpdate = false;

    public function mount(int $id): void
    {
        $user = Auth::user();

        // Otorisasi: cek permission view atau role SPIKAP / Wali Kelas (via role atau jabatan)
        $hasAccess = $user->can('spikap.view') ||
            $user->can('spikap.view_any') ||
            $user->hasAnyRole(['spikap_guru_bk', 'spikap_wali_kelas', 'spikap_kepala_sekolah', 'spikap_admin', 'super_admin']) ||
            $user->isGuruBk() ||
            $user->isKepalaSekolah() ||
            ($user->hasRole('wali_kelas') && $user->teacher !== null);

        if (!$hasAccess) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk melihat detail laporan SPIKAP.');
        }

        // Ambil laporan dalam scope user
        $laporan = SpikapLaporan::forUser($user)
            ->with(['siswa.enrollmentAktif.kelas', 'lampiran', 'logStatus.changedBy', 'lastHandledBy'])
            ->findOrFail($id);

        $this->laporanId  = $laporan->id;
        $this->statusBaru = $laporan->status;
        $this->canUpdate  = $laporan->canUpdateStatusBy($user);
    }

    public function updateStatus(): void
    {
        $user = Auth::user();

        // Verifikasi kembali izin update status
        $laporan = SpikapLaporan::forUser($user)->findOrFail($this->laporanId);

        if (!$laporan->canUpdateStatusBy($user)) {
            abort(403, 'Anda tidak memiliki hak untuk mengubah status laporan ini.');
        }

        $this->validate([
            'statusBaru' => 'required|in:diterima,dalam_investigasi,selesai',
            'catatan'    => 'nullable|string|max:1000',
        ], [
            'statusBaru.required' => 'Pilih status baru.',
            'statusBaru.in'       => 'Pilihan status tidak valid.',
            'catatan.max'         => 'Catatan tidak boleh melebihi 1000 karakter.',
        ]);

        $statusLama = $laporan->status;
        $catatanTrim = trim($this->catatan);

        // Jika tidak ada perubahan status dan tidak ada catatan baru
        if ($this->statusBaru === $statusLama && empty($catatanTrim)) {
            session()->flash('info', 'Tidak ada perubahan status atau catatan baru yang disimpan.');
            return;
        }

        DB::transaction(function () use ($laporan, $statusLama, $catatanTrim, $user) {
            $teacher = $user->teacher;

            // 1. Update status laporan & guru penangan terakhir
            $laporan->update([
                'status'          => $this->statusBaru,
                'last_handled_by' => $teacher?->id,
            ]);

            // 2. Catat audit trail di spikap_log_status
            SpikapLogStatus::catatGuru(
                $laporan->id,
                $statusLama,
                $this->statusBaru,
                $catatanTrim ?: null,
                $user->id
            );
        });

        // 3. Kirim notifikasi WA perkembangan status ke siswa dan/atau orang tua
        try {
            app(\App\Services\SpikapNotificationService::class)->sendStatusUpdateNotificationToSiswaDanOrtu(
                $laporan,
                $this->statusBaru,
                $catatanTrim ?: null
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("SPIKAP: gagal kirim WA status update ke siswa/ortu: " . $e->getMessage());
        }

        $this->catatan = '';
        session()->flash('success', 'Status laporan berhasil diperbarui dan dicatat ke dalam riwayat penanganan.');
    }

    public function render()
    {
        $user = Auth::user();

        $laporan = SpikapLaporan::forUser($user)
            ->with([
                'siswa.enrollmentAktif.kelas',
                'lampiran',
                'logStatus.changedBy',
                'lastHandledBy'
            ])
            ->findOrFail($this->laporanId);

        // Update status canUpdate jika kondisi berubah
        $this->canUpdate = $laporan->canUpdateStatusBy($user);

        return view('livewire.portal-guru.spikap-detail', compact('laporan'));
    }
}

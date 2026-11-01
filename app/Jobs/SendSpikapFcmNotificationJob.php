<?php

namespace App\Jobs;

use App\Models\Guru;
use App\Models\KelasAjaran;
use App\Models\SpikapLaporan;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\FcmPushService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Job pengiriman Push Notification (FCM HTTP v1) ke perangkat Android:
 * 1. Guru Wali Kelas / Guru BK / Kepala Sekolah saat ada laporan SPIKAP baru.
 * 2. Siswa / Orang Tua saat status aduan ditanggapi atau diperbarui.
 */
class SendSpikapFcmNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly int $laporanId,
        public readonly string $eventType = 'laporan_baru', // 'laporan_baru' | 'update_status'
        public readonly ?string $statusBaru = null,
        public readonly ?string $catatan = null,
        public readonly ?string $changedByUserId = null
    ) {}

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(FcmPushService $fcm): void
    {
        $laporan = SpikapLaporan::with(['siswa.enrollmentAktif.kelas', 'siswa.user'])->find($this->laporanId);
        if (!$laporan) {
            Log::warning("SendSpikapFcmNotificationJob: Laporan #{$this->laporanId} tidak ditemukan.");
            return;
        }

        $siswa = $laporan->siswa;
        $namaSiswa = $siswa?->name ?? 'Siswa';
        $namaKelas = $siswa?->enrollmentAktif?->kelas?->name ?? '-';
        $jenisLaporan = $laporan->label_jenis;

        if ($this->eventType === 'laporan_baru') {
            $this->handleLaporanBaru($fcm, $laporan, $siswa, $namaSiswa, $namaKelas, $jenisLaporan);
        } elseif ($this->eventType === 'update_status') {
            $this->handleUpdateStatus($fcm, $laporan, $siswa, $namaSiswa, $namaKelas);
        }
    }

    protected function handleLaporanBaru(
        FcmPushService $fcm,
        SpikapLaporan $laporan,
        $siswa,
        string $namaSiswa,
        string $namaKelas,
        string $jenisLaporan
    ): void {
        $targetUsers = collect();

        // 1. Cari Wali Kelas
        $currentYear = TahunAjaran::aktif()->first();
        $enrollment = $siswa?->enrollmentAktif;
        $waliUser = null;

        if ($enrollment && $currentYear) {
            $kelasAjaran = KelasAjaran::where('class_id', $enrollment->class_id)
                ->where('academic_year_id', $currentYear->id)
                ->with('guru.user')
                ->first();
            $waliUser = $kelasAjaran?->guru?->user;
        }

        if ($laporan->sifat_laporan === 'darurat') {
            // Laporan DARURAT: Kirim ke Wali Kelas, Kepala Sekolah, dan Semua Guru BK
            $title = "🚨 DARURAT SPIKAP: {$namaSiswa} ({$namaKelas})";
            $body = "Laporan darurat: {$jenisLaporan}. Segera buka aplikasi untuk penanganan!";

            if ($waliUser) {
                $targetUsers->push($waliUser);
            }

            // Kepala Sekolah
            $ksUsers = User::role('spikap_kepala_sekolah')->get();
            foreach ($ksUsers as $ks) {
                $targetUsers->push($ks);
            }

            // Guru BK
            $bkUsers = Guru::whereHas('jabatans', function ($q) {
                $q->where('nama_jabatan', 'like', '%BK%')->orWhere('nama_jabatan', 'like', '%Konseling%');
            })->with('user')->get()->pluck('user')->filter();
            foreach ($bkUsers as $bk) {
                $targetUsers->push($bk);
            }

            // Admin SPIKAP
            $adminSpikap = User::role('spikap_admin')->get();
            foreach ($adminSpikap as $adm) {
                $targetUsers->push($adm);
            }
        } else {
            // Laporan BIASA
            if ($laporan->tujuan_penerima === 'wali_kelas') {
                $title = "📋 Laporan SPIKAP Baru (Wali Kelas)";
                $body = "Dari: {$namaSiswa} ({$namaKelas}) - {$jenisLaporan}. Silakan tinjau di aplikasi.";
                if ($waliUser) {
                    $targetUsers->push($waliUser);
                }
            } else {
                // Tujuan Guru BK
                $title = "📋 Laporan SPIKAP Baru (Guru BK)";
                $body = "Dari: {$namaSiswa} ({$namaKelas}) - {$jenisLaporan}. Silakan tinjau di aplikasi.";

                $bkUsers = Guru::whereHas('jabatans', function ($q) {
                    $q->where('nama_jabatan', 'like', '%BK%')->orWhere('nama_jabatan', 'like', '%Konseling%');
                })->with('user')->get()->pluck('user')->filter();

                if ($bkUsers->isEmpty()) {
                    $bkUsers = User::role('spikap_guru_bk')->get();
                }

                foreach ($bkUsers as $bk) {
                    $targetUsers->push($bk);
                }
            }
        }

        $payload = [
            'type' => 'spikap_laporan',
            'laporan_id' => (string) $laporan->id,
            'sifat' => (string) $laporan->sifat_laporan,
            'tujuan' => (string) $laporan->tujuan_penerima,
            'student_name' => (string) $namaSiswa,
            'class_name' => (string) $namaKelas,
            'title' => $title,
            'body' => $body,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ];

        $uniqueUsers = $targetUsers->unique('id');
        Log::info("SendSpikapFcmNotificationJob: Mengirim notifikasi laporan #{$laporan->id} ke {$uniqueUsers->count()} user guru/pejabat.");

        foreach ($uniqueUsers as $user) {
            try {
                $res = $fcm->sendToUser($user, $title, $body, $payload);
                Log::info("SendSpikapFcmNotificationJob: Hasil kirim ke {$user->name} (#{$user->id}): " . json_encode($res));
            } catch (Throwable $e) {
                Log::error("SendSpikapFcmNotificationJob: Gagal kirim ke user #{$user->id}: " . $e->getMessage());
            }
        }
    }

    protected function handleUpdateStatus(
        FcmPushService $fcm,
        SpikapLaporan $laporan,
        $siswa,
        string $namaSiswa,
        string $namaKelas
    ): void {
        $statusLabel = match ($this->statusBaru) {
            'dalam_investigasi' => 'Dalam Penanganan / Investigasi',
            'selesai'           => 'Selesai / Ditutup',
            'ditolak'           => 'Ditolak',
            'diterima'          => 'Laporan Diterima',
            default             => ucfirst($this->statusBaru ?? 'Diproses'),
        };

        $catatanInfo = !empty($this->catatan) ? " Catatan: {$this->catatan}" : "";
        $title = "💬 Update Tindak Lanjut SPIKAP";
        $body = "Status laporanmu: [{$statusLabel}].{$catatanInfo}";

        $payload = [
            'type' => 'spikap_status_update',
            'laporan_id' => (string) $laporan->id,
            'status' => (string) ($this->statusBaru ?? $laporan->status),
            'title' => $title,
            'body' => $body,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ];

        // 1. Kirim ke Siswa Pelapor
        $siswaUser = $siswa?->user;
        if ($siswaUser) {
            try {
                $res = $fcm->sendToUser($siswaUser, $title, $body, $payload);
                Log::info("SendSpikapFcmNotificationJob: Update status laporan #{$laporan->id} dikirim ke Siswa {$siswaUser->name}: " . json_encode($res));
            } catch (Throwable $e) {
                Log::error("SendSpikapFcmNotificationJob: Gagal kirim status update ke Siswa #{$siswaUser->id}: " . $e->getMessage());
            }
        }

        // 2. Jika yang update bukan Wali Kelas, beritahu Wali Kelas juga
        $currentYear = TahunAjaran::aktif()->first();
        $enrollment = $siswa?->enrollmentAktif;
        if ($enrollment && $currentYear) {
            $kelasAjaran = KelasAjaran::where('class_id', $enrollment->class_id)
                ->where('academic_year_id', $currentYear->id)
                ->with('guru.user')
                ->first();
            $waliUser = $kelasAjaran?->guru?->user;

            if ($waliUser && $waliUser->id !== $this->changedByUserId) {
                $titleWali = "ℹ️ Perkembangan Kasus SPIKAP: {$namaSiswa}";
                $bodyWali = "Status aduan siswa {$namaSiswa} ({$namaKelas}) kini: [{$statusLabel}].";
                $payloadWali = array_merge($payload, [
                    'title' => $titleWali,
                    'body' => $bodyWali,
                ]);

                try {
                    $fcm->sendToUser($waliUser, $titleWali, $bodyWali, $payloadWali);
                } catch (Throwable $e) {
                    Log::error("SendSpikapFcmNotificationJob: Gagal kirim update ke Wali Kelas #{$waliUser->id}: " . $e->getMessage());
                }
            }
        }
    }
}

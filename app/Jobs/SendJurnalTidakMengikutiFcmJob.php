<?php

namespace App\Jobs;

use App\Models\JurnalPembelajaran;
use App\Models\PresensiNotificationSetting;
use App\Models\Siswa;
use App\Services\FcmPushService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendJurnalTidakMengikutiFcmJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly string $jurnalId,
        public readonly string $studentId,
        public readonly string $targetType, // siswa | wali_kelas
        public readonly ?string $keterangan = null
    ) {}

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(FcmPushService $fcm): void
    {
        $jurnal = JurnalPembelajaran::with(["guru.user", "kelas", "mataPelajaran"])->find($this->jurnalId);
        if (!$jurnal) {
            Log::warning("SendJurnalTidakMengikutiFcmJob: Jurnal #{$this->jurnalId} tidak ditemukan.");
            return;
        }

        $siswa = Siswa::with(["user", "enrollmentAktif.kelas"])->find($this->studentId);
        if (!$siswa) {
            Log::warning("SendJurnalTidakMengikutiFcmJob: Siswa #{$this->studentId} tidak ditemukan.");
            return;
        }

        $namaSiswa = $siswa->name;
        $nisn = $siswa->nisn ?: ($siswa->nis ?: "-");
        $namaKelas = $jurnal->kelas?->name ?? $siswa->enrollmentAktif?->kelas?->name ?? "-";
        $namaGuru = $jurnal->guru?->nama_lengkap ?? $jurnal->guru?->name ?? "Guru Pengajar";
        $mataPelajaran = $jurnal->mataPelajaran?->nama_pelajaran ?? $jurnal->mataPelajaran?->nama ?? "Mata Pelajaran";
        $jamKe = $jurnal->jam_ke_label ?? "Jam ke {$jurnal->jam_mulai} - {$jurnal->jam_selesai}";
        $tanggal = $jurnal->tanggal ? Carbon::parse($jurnal->tanggal)->translatedFormat("d F Y") : now()->translatedFormat("d F Y");
        $keteranganText = $this->keterangan ?: "Tidak ada keterangan khusus";

        if ($this->targetType === "siswa") {
            $targetUser = $siswa->user;
            if (!$targetUser) {
                Log::info("SendJurnalTidakMengikutiFcmJob: Siswa {$namaSiswa} belum memiliki akun User terdaftar.");
                return;
            }

            $setting = PresensiNotificationSetting::where("status_presensi", "jurnal_tidak_mengikuti_app_siswa")->first();
            $titleTemplate = $setting?->recipients["title"] ?? "⚠️ Peringatan: Tidak Mengikuti Pelajaran";
            $bodyTemplate = ($setting && !empty($setting->template_pesan))
                ? $setting->template_pesan
                : "Anda tercatat hadir tetapi tidak mengikuti KBM {mata_pelajaran} (Jam {jam_ke}). Segera temui guru pengajar atau wali kelas.";

            $title = strtr($titleTemplate, [
                "{nama_siswa}"     => $namaSiswa,
                "{kelas}"          => $namaKelas,
                "{mata_pelajaran}" => $mataPelajaran,
                "{jam_ke}"         => $jamKe,
            ]);

            $body = strtr($bodyTemplate, [
                "{nama_siswa}"     => $namaSiswa,
                "{nisn}"           => $nisn,
                "{kelas}"          => $namaKelas,
                "{nama_guru}"      => $namaGuru,
                "{mata_pelajaran}" => $mataPelajaran,
                "{jam_ke}"         => $jamKe,
                "{tanggal}"        => $tanggal,
                "{keterangan}"     => $keteranganText,
            ]);

            $payload = [
                "type"           => "jurnal_tidak_mengikuti",
                "target_role"    => "siswa",
                "jurnal_id"      => (string) $jurnal->id,
                "student_id"     => (string) $siswa->id,
                "class_name"     => (string) $namaKelas,
                "mata_pelajaran" => (string) $mataPelajaran,
                "action_url"     => "/portal-siswa/presensi",
                "click_action"   => "FLUTTER_NOTIFICATION_CLICK",
                "title"          => $title,
                "body"           => $body,
            ];

            try {
                $res = $fcm->sendToUser($targetUser, $title, $body, $payload);
                Log::info("SendJurnalTidakMengikutiFcmJob: Notifikasi FCM terkirim ke Siswa {$namaSiswa}: " . json_encode($res));

                // Simpan ke database notifications agar tampil di lonceng notifikasi aplikasi
                $targetUser->notifications()->create([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'type' => 'App\Notifications\JurnalTidakMengikutiNotification',
                    'data' => [
                        'title' => $title,
                        'body' => $body,
                        'type' => 'jurnal_tidak_mengikuti',
                        'id' => (string) $jurnal->id,
                        'jurnal_id' => (string) $jurnal->id,
                        'student_id' => (string) $siswa->id,
                        'mata_pelajaran' => (string) $mataPelajaran,
                        'action_url' => $payload['action_url'],
                    ],
                    'read_at' => null,
                ]);
            } catch (Throwable $e) {
                Log::error("SendJurnalTidakMengikutiFcmJob: Gagal kirim FCM ke Siswa {$namaSiswa}: " . $e->getMessage());
            }

        } elseif ($this->targetType === "wali_kelas") {
            $waliKelas = $jurnal->kelas?->waliKelas($jurnal->academic_year_id);
            $targetUser = $waliKelas?->user;
            if (!$targetUser) {
                Log::info("SendJurnalTidakMengikutiFcmJob: Wali Kelas {$namaKelas} belum memiliki akun User terdaftar.");
                return;
            }

            $setting = PresensiNotificationSetting::where("status_presensi", "jurnal_tidak_mengikuti_app_wali")->first();
            $titleTemplate = $setting?->recipients["title"] ?? "⚠️ Siswa Meninggalkan Kelas: {nama_siswa}";
            $bodyTemplate = ($setting && !empty($setting->template_pesan))
                ? $setting->template_pesan
                : "{nama_siswa} ({kelas}) tercatat hadir namun tidak mengikuti KBM {mata_pelajaran} bersama {nama_guru}.";

            $title = strtr($titleTemplate, [
                "{nama_siswa}"     => $namaSiswa,
                "{kelas}"          => $namaKelas,
                "{mata_pelajaran}" => $mataPelajaran,
                "{jam_ke}"         => $jamKe,
            ]);

            $body = strtr($bodyTemplate, [
                "{nama_siswa}"     => $namaSiswa,
                "{nisn}"           => $nisn,
                "{kelas}"          => $namaKelas,
                "{nama_guru}"      => $namaGuru,
                "{mata_pelajaran}" => $mataPelajaran,
                "{jam_ke}"         => $jamKe,
                "{tanggal}"        => $tanggal,
                "{keterangan}"     => $keteranganText,
            ]);

            $payload = [
                "type"           => "jurnal_tidak_mengikuti",
                "target_role"    => "wali_kelas",
                "jurnal_id"      => (string) $jurnal->id,
                "student_id"     => (string) $siswa->id,
                "student_name"   => (string) $namaSiswa,
                "class_name"     => (string) $namaKelas,
                "mata_pelajaran" => (string) $mataPelajaran,
                "action_url"     => "/portal-guru/jurnal-mengajar",
                "click_action"   => "FLUTTER_NOTIFICATION_CLICK",
                "title"          => $title,
                "body"           => $body,
            ];

            try {
                $res = $fcm->sendToUser($targetUser, $title, $body, $payload);
                Log::info("SendJurnalTidakMengikutiFcmJob: Notifikasi FCM terkirim ke Wali Kelas ({$targetUser->name}): " . json_encode($res));

                // Simpan ke database notifications agar tampil di lonceng notifikasi aplikasi
                $targetUser->notifications()->create([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'type' => 'App\Notifications\JurnalTidakMengikutiNotification',
                    'data' => [
                        'title' => $title,
                        'body' => $body,
                        'type' => 'jurnal_tidak_mengikuti',
                        'id' => (string) $jurnal->id,
                        'jurnal_id' => (string) $jurnal->id,
                        'student_id' => (string) $siswa->id,
                        'mata_pelajaran' => (string) $mataPelajaran,
                        'action_url' => $payload['action_url'],
                    ],
                    'read_at' => null,
                ]);
            } catch (Throwable $e) {
                Log::error("SendJurnalTidakMengikutiFcmJob: Gagal kirim FCM ke Wali Kelas: " . $e->getMessage());
            }
        }
    }
}

<?php

namespace App\Services;

use App\Jobs\SendJurnalTidakMengikutiFcmJob;
use App\Jobs\SendJurnalTidakMengikutiWaJob;
use App\Models\JurnalPembelajaran;
use App\Models\PresensiNotificationSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class JurnalMengajarNotificationService
{
    /**
     * Memproses data presensi jurnal pembelajaran dan mengirimkan notifikasi multi-kanal
     * (WhatsApp & FCM Android) untuk siswa yang tercatat hadir di gerbang tetapi tidak mengikuti KBM.
     *
     * @param JurnalPembelajaran $jurnal
     * @param array|Collection $studentPresensis
     * @return array
     */
    public function processPresensiJurnal(JurnalPembelajaran $jurnal, array|Collection $studentPresensis): array
    {
        $waSetting = PresensiNotificationSetting::where("status_presensi", "jurnal_tidak_mengikuti_wa")->first();
        $appSetting = PresensiNotificationSetting::where("status_presensi", "jurnal_tidak_mengikuti_app")->first();

        $waActive = $waSetting ? (bool) $waSetting->is_active : false;
        $appActive = $appSetting ? (bool) $appSetting->is_active : false;

        if (!$waActive && !$appActive) {
            return [
                "status"  => "skipped",
                "message" => "Notifikasi WA dan Notifikasi Aplikasi untuk Jurnal Mengajar keduanya dinonaktifkan.",
            ];
        }

        $waTargets = ($waSetting && !empty($waSetting->recipients)) ? (array) $waSetting->recipients : ["wali_kelas", "orang_tua"];
        $appTargets = ($appSetting && !empty($appSetting->recipients)) ? (array) $appSetting->recipients : ["siswa", "wali_kelas"];

        $processed = 0;
        $skipped = 0;

        foreach ($studentPresensis as $key => $item) {
            $studentId = is_array($item) ? ($item["student_id"] ?? $item["id"] ?? $key) : ($item->student_id ?? $item->id);
            $statusGerbang = is_array($item) ? ($item["status_gerbang"] ?? $item["status_gerbang_snapshot"] ?? "") : ($item->status_gerbang_snapshot ?? "");
            $statusJurnal = is_array($item) ? ($item["status_jurnal"] ?? "") : ($item->status_jurnal ?? "");
            $keterangan = is_array($item) ? ($item["keterangan"] ?? null) : ($item->keterangan ?? null);

            $statusGerbangClean = strtolower(trim((string) $statusGerbang));
            $isHadirGerbang = in_array($statusGerbangClean, ["hadir", "terlambat", "telat"], true);
            $isTidakMengikuti = ($statusJurnal === "tidak_mengikuti");

            // Deteksi anomali: Hadir di gerbang TETAPI tidak mengikuti KBM di kelas
            if (!$isHadirGerbang || !$isTidakMengikuti) {
                continue;
            }

            // Idempotency: Jangan kirim berulang-ulang untuk jurnal dan siswa yang sama dalam kurun 1 hari
            $cacheKey = "jurnal_notif_{$jurnal->id}_{$studentId}";
            if (Cache::has($cacheKey)) {
                $skipped++;
                continue;
            }
            Cache::put($cacheKey, true, now()->addHours(24));

            // 1. Eksekusi Notifikasi WhatsApp (Jika aktif)
            if ($waActive) {
                if (in_array("wali_kelas", $waTargets, true)) {
                    SendJurnalTidakMengikutiWaJob::dispatch($jurnal->id, $studentId, "wali_kelas", $keterangan);
                }
                if (in_array("orang_tua", $waTargets, true)) {
                    SendJurnalTidakMengikutiWaJob::dispatch($jurnal->id, $studentId, "orang_tua", $keterangan);
                }
            }

            // 2. Eksekusi Notifikasi Aplikasi Android FCM (Jika aktif)
            if ($appActive) {
                if (in_array("siswa", $appTargets, true)) {
                    SendJurnalTidakMengikutiFcmJob::dispatch($jurnal->id, $studentId, "siswa", $keterangan);
                }
                if (in_array("wali_kelas", $appTargets, true)) {
                    SendJurnalTidakMengikutiFcmJob::dispatch($jurnal->id, $studentId, "wali_kelas", $keterangan);
                }
            }

            $processed++;
        }

        Log::info("JurnalMengajarNotificationService: Selesai memproses Jurnal #{$jurnal->id}. Terkirim untuk {$processed} siswa (di-skip {$skipped}).");

        return [
            "status"    => "success",
            "processed" => $processed,
            "skipped"   => $skipped,
        ];
    }
}

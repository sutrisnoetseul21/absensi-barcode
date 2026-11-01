<?php

namespace App\Jobs;

use App\Models\JurnalPembelajaran;
use App\Models\PresensiNotificationSetting;
use App\Models\Siswa;
use App\Models\WhatsAppNotificationLog;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendJurnalTidakMengikutiWaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly string $jurnalId,
        public readonly string $studentId,
        public readonly string $targetType, // wali_kelas | orang_tua
        public readonly ?string $keterangan = null
    ) {}

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(): void
    {
        $jurnal = JurnalPembelajaran::with(["guru", "kelas", "mataPelajaran"])->find($this->jurnalId);
        if (!$jurnal) {
            Log::warning("SendJurnalTidakMengikutiWaJob: Jurnal #{$this->jurnalId} tidak ditemukan.");
            return;
        }

        $siswa = Siswa::with(["enrollmentAktif.kelas"])->find($this->studentId);
        if (!$siswa) {
            Log::warning("SendJurnalTidakMengikutiWaJob: Siswa #{$this->studentId} tidak ditemukan.");
            return;
        }

        $toNumber = null;
        $template = null;
        $recipientRole = $this->targetType;

        $namaSiswa = $siswa->name;
        $nisn = $siswa->nisn ?: ($siswa->nis ?: "-");
        $namaKelas = $jurnal->kelas?->name ?? $siswa->enrollmentAktif?->kelas?->name ?? "-";
        $namaGuru = $jurnal->guru?->nama_lengkap ?? $jurnal->guru?->name ?? "Guru Pengajar";
        $mataPelajaran = $jurnal->mataPelajaran?->nama_pelajaran ?? $jurnal->mataPelajaran?->nama ?? "Mata Pelajaran";
        $jamKe = $jurnal->jam_ke_label ?? "Jam ke {$jurnal->jam_mulai} - {$jurnal->jam_selesai}";
        $tanggal = $jurnal->tanggal ? Carbon::parse($jurnal->tanggal)->translatedFormat("d F Y") : now()->translatedFormat("d F Y");
        $keteranganText = $this->keterangan ?: "Tidak ada keterangan khusus";

        if ($this->targetType === "wali_kelas") {
            $waliKelas = $jurnal->kelas?->waliKelas($jurnal->academic_year_id);
            $toNumber = $waliKelas?->no_hp ?: $waliKelas?->user?->phone;
            if (!$toNumber) {
                Log::info("SendJurnalTidakMengikutiWaJob: Wali Kelas untuk {$namaKelas} tidak memiliki nomor WhatsApp.");
                return;
            }

            $setting = PresensiNotificationSetting::where("status_presensi", "jurnal_tidak_mengikuti_wa_wali")->first();
            $template = ($setting && !empty($setting->template_pesan))
                ? $setting->template_pesan
                : "⚠️ *PEMBERITAHUAN KBM: SISWA MENINGGALKAN KELAS*\n\nYth. Wali Kelas {kelas},\nMenginformasikan bahwa siswa asuh Anda:\n- Nama: *{nama_siswa}*\n- NISN: {nisn}\n- Kelas: {kelas}\n\nTercatat *HADIR* di gerbang sekolah, namun dinyatakan *TIDAK MENGIKUTI* pembelajaran:\n- Mata Pelajaran: *{mata_pelajaran}*\n- Guru Pengajar: {nama_guru}\n- Jam ke: {jam_ke}\n- Tanggal: {tanggal}\n- Catatan Guru: {keterangan}\n\nMohon untuk dapat ditindaklanjuti. Terima kasih.";
        } elseif ($this->targetType === "orang_tua") {
            $toNumber = $siswa->no_hp_orang_tua ?: $siswa->no_hp;
            if (!$toNumber) {
                Log::info("SendJurnalTidakMengikutiWaJob: Orang tua / Siswa #{$siswa->id} tidak memiliki nomor WhatsApp.");
                return;
            }

            $setting = PresensiNotificationSetting::where("status_presensi", "jurnal_tidak_mengikuti_wa")->first();
            $template = ($setting && !empty($setting->template_pesan))
                ? $setting->template_pesan
                : "⚠️ *PEMBERITAHUAN SEKOLAH: INFORMASI KEHADIRAN KELAS*\n\nYth. Bapak/Ibu Orang Tua/Wali dari *{nama_siswa}*,\n\nKami menginformasikan bahwa ananda tercatat telah tiba/hadir di gerbang sekolah pagi ini, namun saat Kegiatan Belajar Mengajar (KBM) berlangsung, ananda tidak berada di kelas / tidak mengikuti pelajaran:\n- Mata Pelajaran: *{mata_pelajaran}*\n- Guru Pengajar: {nama_guru}\n- Jam ke: {jam_ke}\n- Tanggal: {tanggal}\n- Catatan Guru: {keterangan}\n\nPihak sekolah dan wali kelas sedang memastikan kondisi ananda di lingkungan sekolah. Terima kasih.";
        }

        if (!$template || !$toNumber) {
            return;
        }

        $pesan = strtr($template, [
            "{nama_siswa}"     => $namaSiswa,
            "{nisn}"           => $nisn,
            "{kelas}"          => $namaKelas,
            "{nama_guru}"      => $namaGuru,
            "{mata_pelajaran}" => $mataPelajaran,
            "{jam_ke}"         => $jamKe,
            "{tanggal}"        => $tanggal,
            "{keterangan}"     => $keteranganText,
        ]);

        try {
            $log = WhatsAppNotificationLog::create([
                "module"           => "jurnal_mengajar",
                "recipient_type"   => $recipientRole,
                "recipient_number" => $toNumber,
                "message"          => $pesan,
                "status"           => "pending",
                "response_payload" => json_encode(["info" => "Jurnal Tidak Mengikuti ({$this->targetType}) Queued"]),
                "related_type"     => "jurnal_pembelajaran",
                "related_id"       => (string) $jurnal->id,
            ]);

            SendWhatsAppNotificationJob::dispatch(
                $toNumber,
                $pesan,
                "jurnal_pembelajaran",
                (string) $jurnal->id,
                $recipientRole,
                $log->id
            );

            Log::info("SendJurnalTidakMengikutiWaJob: WA berhasil diantrekan ke {$recipientRole} ({$toNumber}) untuk siswa {$namaSiswa}.");
        } catch (Throwable $e) {
            Log::error("SendJurnalTidakMengikutiWaJob error: " . $e->getMessage());
        }
    }
}

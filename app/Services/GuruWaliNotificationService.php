<?php

namespace App\Services;

use App\Jobs\SendWhatsAppNotificationJob;
use App\Models\KonsultasiGuruWali;
use App\Models\PengaturanSekolah;
use App\Models\PresensiNotificationSetting;
use App\Models\WhatsAppNotificationLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GuruWaliNotificationService
{
    public function __construct(
        protected RecipientResolverService $resolver
    ) {}

    /**
     * Kirim notifikasi WA ke Guru Wali saat siswa mengajukan janjian tatap muka baru.
     * (Untuk bimbingan biasa / curhat online via Pesan Portal hanya menggunakan notifikasi di aplikasi).
     */
    public function sendPengajuanNotificationToGuru(KonsultasiGuruWali $konsultasi): array
    {
        if (($konsultasi->mode_konsultasi ?? '') !== 'Tatap Muka') {
            return ['status' => 'skipped', 'reason' => 'Bimbingan online cukup melalui aplikasi'];
        }

        $setting = PresensiNotificationSetting::where('status_presensi', 'guru_wali_pengajuan')->first();
        if ($setting && !$setting->is_active) {
            return ['status' => 'skipped', 'reason' => 'Notifikasi WA pengajuan guru wali dinonaktifkan'];
        }

        $konsultasi->loadMissing(['siswa.enrollmentAktif.kelas', 'guru']);
        $siswa = $konsultasi->siswa;
        $guru  = $konsultasi->guru;

        if (!$guru) {
            return ['status' => 'failed', 'reason' => 'Guru wali tidak ditemukan'];
        }

        $toNumber = $guru->no_hp ?: $guru->user?->phone;
        if (!$toNumber) {
            Log::info("GuruWaliNotificationService: Guru Wali #{$guru->id} tidak memiliki nomor HP.");
            return ['status' => 'skipped', 'reason' => 'Nomor HP Guru Wali tidak ditemukan'];
        }

        $namaSiswa    = $siswa?->name ?? 'Siswa';
        $namaKelas    = $siswa?->enrollmentAktif?->kelas?->name ?? '-';
        $namaGuruWali = $guru->name ?? 'Guru Wali';
        $kategori     = $konsultasi->kategori_pendampingan ?? '-';
        $mode         = $konsultasi->mode_konsultasi ?? '-';
        $topik        = $konsultasi->topik_konsultasi ?? '-';
        $detail       = $konsultasi->detail_permasalahan ?? '-';
        $waktuObj     = $konsultasi->created_at ?? now();
        $waktu        = $waktuObj->translatedFormat('d F Y, H:i') . ' WIB';

        $template = ($setting && !empty($setting->template_pesan))
            ? $setting->template_pesan
            : "Halo Bapak/Ibu *{nama_guru_wali}*,\n\nSiswa bimbingan Anda, *{nama_siswa}* (Kelas {kelas}), baru saja menyampaikan cerita / permohonan bimbingan melalui Ruang Aman SI-WALI:\n\n📌 *Kategori:* {kategori}\n📌 *Mode:* {mode}\n📌 *Topik:* {topik}\n📝 *Pesan Siswa:* \"{detail}\"\n\nSilakan buka Portal Guru Wali pada aplikasi untuk membaca detail atau merespons permohonan bimbingan ananda. Terima kasih.";

        $pesan = strtr($template, [
            '{nama_guru_wali}' => $namaGuruWali,
            '{nama_siswa}'     => $namaSiswa,
            '{kelas}'          => $namaKelas,
            '{kategori}'       => $kategori,
            '{mode}'           => $mode,
            '{topik}'          => $topik,
            '{detail}'         => $detail,
            '{waktu}'          => $waktu,
        ]);

        $log = WhatsAppNotificationLog::create([
            'module'           => 'guru_wali',
            'recipient_type'   => 'guru_wali',
            'recipient_number' => $toNumber,
            'message'          => $pesan,
            'status'           => 'pending',
            'response_payload' => json_encode(['info' => 'Guru Wali Pengajuan Baru Notification Queued']),
            'related_type'     => 'konsultasi_guru_wali',
            'related_id'       => (string) $konsultasi->id,
        ]);

        SendWhatsAppNotificationJob::dispatch(
            $toNumber,
            $pesan,
            'konsultasi_guru_wali',
            (string) $konsultasi->id,
            'guru_wali',
            $log->id
        );

        return ['status' => 'dispatched', 'log_id' => $log->id];
    }

    /**
     * Kirim notifikasi WA balasan / tanggapan guru wali ke siswa dan/atau orang tua.
     * Dinonaktifkan karena obrolan / curhat online sudah ditangani melalui notifikasi aplikasi.
     */
    public function sendBalasanNotificationToSiswa(KonsultasiGuruWali $konsultasi, string $balasanGuru): array
    {
        return ['status' => 'skipped', 'reason' => 'Balasan obrolan online cukup melalui aplikasi'];
    }

    /**
     * Kirim notifikasi WA penetapan jadwal bimbingan tatap muka ke siswa dan/atau orang tua.
     */
    public function sendJadwalNotificationToSiswa(KonsultasiGuruWali $konsultasi): array
    {
        $setting = PresensiNotificationSetting::where('status_presensi', 'guru_wali_jadwal')->first();
        if ($setting && !$setting->is_active) {
            return ['status' => 'skipped', 'reason' => 'Notifikasi WA jadwal bimbingan dinonaktifkan'];
        }

        $konsultasi->loadMissing(['siswa.enrollmentAktif.kelas', 'guru']);
        $siswa = $konsultasi->siswa;
        $guru  = $konsultasi->guru;

        if (!$siswa) {
            return ['status' => 'failed', 'reason' => 'Siswa tidak ditemukan'];
        }

        $targetTypes = ($setting && !empty($setting->recipients)) ? $setting->recipients : ['siswa'];
        $contacts    = $this->resolver->resolveKontakSiswa($siswa, $targetTypes);

        if (empty($contacts)) {
            return ['status' => 'skipped', 'reason' => 'Kontak siswa/orang tua tidak ditemukan'];
        }

        $namaSiswa    = $siswa->name;
        $namaGuruWali = $guru?->name ?? 'Guru Wali';
        $topik        = $konsultasi->topik_konsultasi ?? '-';
        $jadwalPasti  = $konsultasi->jadwal_pasti
            ? Carbon::parse($konsultasi->jadwal_pasti)->translatedFormat('l, d F Y pukul H:i') . ' WIB'
            : ($konsultasi->usulan_tanggal_waktu ? Carbon::parse($konsultasi->usulan_tanggal_waktu)->translatedFormat('l, d F Y pukul H:i') . ' WIB' : 'Segera');
        $catatanGuru  = $konsultasi->tanggapan_guru ?: '-';

        $template = ($setting && !empty($setting->template_pesan))
            ? $setting->template_pesan
            : "Halo *{nama_siswa}*,\n\nJadwal pertemuan tatap muka bimbingan dengan Guru Wali (*{nama_guru_wali}*) untuk topik *{topik}* telah ditetapkan:\n\n🗓️ *Waktu Pertemuan:* {jadwal_pasti}\n📝 *Catatan:* {catatan_guru}\n\nMohon hadir tepat waktu ya. Tetap semangat!";

        $dispatched = [];

        foreach ($contacts as $contact) {
            $toNumber = $contact['number'];
            $type     = $contact['type'];

            $pesan = strtr($template, [
                '{nama_siswa}'     => $namaSiswa,
                '{nama_guru_wali}' => $namaGuruWali,
                '{topik}'          => $topik,
                '{jadwal_pasti}'   => $jadwalPasti,
                '{catatan_guru}'   => $catatanGuru,
            ]);

            $log = WhatsAppNotificationLog::create([
                'module'           => 'guru_wali',
                'recipient_type'   => $type,
                'recipient_number' => $toNumber,
                'message'          => $pesan,
                'status'           => 'pending',
                'response_payload' => json_encode(['info' => 'Guru Wali Jadwal Notification Queued']),
                'related_type'     => 'konsultasi_guru_wali',
                'related_id'       => (string) $konsultasi->id,
            ]);

            SendWhatsAppNotificationJob::dispatch(
                $toNumber,
                $pesan,
                'konsultasi_guru_wali',
                (string) $konsultasi->id,
                $type,
                $log->id
            );

            $dispatched[] = ['number' => $toNumber, 'type' => $type, 'log_id' => $log->id];
        }

        return ['status' => 'dispatched', 'count' => count($dispatched), 'recipients' => $dispatched];
    }
}

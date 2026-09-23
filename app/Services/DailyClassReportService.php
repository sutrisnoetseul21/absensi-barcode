<?php

namespace App\Services;

use App\Models\PresensiDailyReportSetting;
use App\Models\TahunAjaran;
use App\Models\KelasAjaran;
use App\Models\EnrollmentSiswa;
use App\Models\Presensi;
use App\Models\WhatsAppNotificationLog;
use App\Jobs\SendWhatsAppNotificationJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DailyClassReportService
{
    public function __construct(
        protected RecipientResolverService $resolver
    ) {}

    /**
     * Dispatch laporan harian per kelas.
     *
     * @param  bool  $isManual  Jika true, dedup guard tetap dicek tapi cutoff diabaikan
     * @return array{dispatched: int, skipped: int, errors: string[]}
     */
    public function dispatch(bool $isManual = false): array
    {
        $setting = PresensiDailyReportSetting::current();

        if (!$setting->is_active) {
            return ['dispatched' => 0, 'skipped' => 0, 'errors' => ['Laporan harian tidak aktif.']];
        }

        $currentYear = TahunAjaran::aktif()->first();
        if (!$currentYear) {
            return ['dispatched' => 0, 'skipped' => 0, 'errors' => ['Tidak ada tahun ajaran aktif.']];
        }

        $now = Carbon::now();
        $tanggal = $now->format('d-m-Y');

        $kelasAjarans = KelasAjaran::with(['kelas', 'guru'])
            ->where('academic_year_id', $currentYear->id)
            ->get();

        $dispatched = 0;
        $skipped    = 0;
        $errors     = [];

        foreach ($kelasAjarans as $kelasAjaran) {
            $result = $this->processKelas($kelasAjaran, $currentYear, $setting, $tanggal, $now);
            $dispatched += $result['dispatched'];
            $skipped    += $result['skipped'];
            $errors      = array_merge($errors, $result['errors']);
        }

        return compact('dispatched', 'skipped', 'errors');
    }

    private function processKelas($kelasAjaran, $currentYear, $setting, $tanggal, $now): array
    {
        $dispatched = 0;
        $skipped    = 0;
        $errors     = [];

        $enrollments = EnrollmentSiswa::with('siswa')
            ->where('academic_year_id', $currentYear->id)
            ->where('class_id', $kelasAjaran->class_id)
            ->where('status', 'aktif')
            ->get();

        $totalSiswa = $enrollments->count();
        if ($totalSiswa === 0) {
            $skipped++;
            return compact('dispatched', 'skipped', 'errors');
        }

        $studentIds  = $enrollments->pluck('student_id')->toArray();
        $attendances = Presensi::whereIn('student_id', $studentIds)
            ->where('date', $now->toDateString())
            ->get();

        $hadirList = $attendances->whereIn('status', ['hadir', 'pulang']);
        $telatList = $attendances->where('status', 'telat');
        $alpaList  = $attendances->where('status', 'alpa');
        $sakitList = $attendances->where('status', 'sakit');
        $izinList  = $attendances->where('status', 'izin');

        $hadir = $hadirList->count();
        $telat = $telatList->count();
        $alpa  = $alpaList->count();
        $sakit = $sakitList->count();
        $izin  = $izinList->count();

        // Helper untuk ekstrak nama siswa dari collection presensi
        $getStudentNames = function ($attendanceCol) use ($enrollments) {
            $ids = $attendanceCol->pluck('student_id')->toArray();
            $names = [];
            foreach ($enrollments as $en) {
                if (in_array($en->student_id, $ids)) {
                    $names[] = $en->siswa->name ?? 'Siswa Tanpa Nama';
                }
            }
            return $names;
        };

        $sakitNames = $getStudentNames($sakitList);
        $izinNames  = $getStudentNames($izinList);
        $alpaNames  = $getStudentNames($alpaList);
        $telatNames = $getStudentNames($telatList);

        $attendanceStudentIds = $attendances->pluck('student_id')->toArray();
        $belumPresensiNames   = [];
        foreach ($enrollments as $enrollment) {
            if (!in_array($enrollment->student_id, $attendanceStudentIds)) {
                $belumPresensiNames[] = $enrollment->siswa->name ?? 'Siswa Tanpa Nama';
            }
        }

        // Format bullet daftar baris
        $formatDaftar = function (array $names, string $emptyText = 'Tidak ada') {
            if (empty($names)) {
                return $emptyText;
            }
            return implode("\n", array_map(fn($n) => "- $n", $names));
        };

        // Format inline koma
        $formatKoma = function (array $names) {
            return empty($names) ? '-' : implode(', ', $names);
        };

        $daftarBelumPresensi = empty($belumPresensiNames)
            ? 'Tidak ada (Semua sudah mengisi presensi)'
            : implode("\n", array_map(fn($n) => "- $n", $belumPresensiNames));

        $namaBelumPresensi = $formatKoma($belumPresensiNames);
        $daftarSakit       = $formatDaftar($sakitNames);
        $namaSakit         = $formatKoma($sakitNames);
        $daftarIzin        = $formatDaftar($izinNames);
        $namaIzin          = $formatKoma($izinNames);
        $daftarAlpa        = $formatDaftar($alpaNames);
        $namaAlpa          = $formatKoma($alpaNames);
        $daftarTelat       = $formatDaftar($telatNames);
        $namaTelat         = $formatKoma($telatNames);

        $namaKelas = $kelasAjaran->kelas ? $kelasAjaran->kelas->name : 'Kelas Tidak Diketahui';

        $pesan = str_replace(
            [
                '{nama_kelas}', 
                '{tanggal}', 
                '{total_siswa}', 
                '{jumlah_hadir}', 
                '{jumlah_terlambat}', 
                '{jumlah_alpa}', 
                '{jumlah_alfa}', 
                '{jumlah_sakit}', 
                '{jumlah_izin}', 
                '{daftar_belum_presensi}',
                '{nama_belum_presensi}',
                '{daftar_sakit}',
                '{nama_sakit}',
                '{daftar_izin}',
                '{nama_izin}',
                '{daftar_alpa}',
                '{nama_alpa}',
                '{daftar_alfa}',
                '{nama_alfa}',
                '{daftar_terlambat}',
                '{nama_terlambat}',
            ],
            [
                $namaKelas, 
                $tanggal, 
                $totalSiswa, 
                ($hadir + $telat), 
                $telat, 
                $alpa, 
                $alpa, 
                $sakit, 
                $izin, 
                $daftarBelumPresensi,
                $namaBelumPresensi,
                $daftarSakit,
                $namaSakit,
                $daftarIzin,
                $namaIzin,
                $daftarAlpa,
                $namaAlpa,
                $daftarAlpa,
                $namaAlpa,
                $daftarTelat,
                $namaTelat,
            ],
            $setting->template_pesan
        );

        // Dedup Guard
        $relatedType = 'daily_report_kelas';
        $relatedId   = $kelasAjaran->id;
        $todayStr    = $now->toDateString();

        $alreadyDispatched = WhatsAppNotificationLog::where('related_type', $relatedType)
            ->where('related_id', (string) $relatedId)
            ->whereDate('created_at', $todayStr)
            ->whereIn('status', ['sent', 'pending'])
            ->exists();

        if ($alreadyDispatched) {
            $skipped++;
            return compact('dispatched', 'skipped', 'errors');
        }

        // Resolve Penerima
        $resolvedRecipients = [];
        $seenNumbers        = [];
        $recipientKeys      = $setting->recipients ?? [];

        foreach ($recipientKeys as $key) {
            if ($key === 'wali_kelas') {
                $hp = $kelasAjaran->guru ? $kelasAjaran->guru->no_hp : null;
                if ($hp && !isset($seenNumbers[$hp])) {
                    $resolvedRecipients[] = ['number' => $hp, 'type' => 'wali_kelas'];
                    $seenNumbers[$hp]     = true;
                }
            } elseif (str_starts_with($key, 'GROUP:')) {
                $groupId = substr($key, 6);
                if (!empty($groupId) && str_ends_with($groupId, '@g.us') && !isset($seenNumbers[$groupId])) {
                    $resolvedRecipients[] = ['number' => $groupId, 'type' => 'whatsapp_group'];
                    $seenNumbers[$groupId] = true;
                }
            } elseif ($key !== 'ortu') {
                $jabatansHp = $this->resolver->resolveByJabatan($key);
                foreach ($jabatansHp as $hp) {
                    if ($hp && !isset($seenNumbers[$hp])) {
                        $resolvedRecipients[] = ['number' => $hp, 'type' => $key];
                        $seenNumbers[$hp]     = true;
                    }
                }
            }
        }

        if (empty($resolvedRecipients)) {
            $errors[] = "Tidak ada penerima valid untuk $namaKelas.";
            return compact('dispatched', 'skipped', 'errors');
        }

        // Dispatch Jobs
        foreach ($resolvedRecipients as $recipient) {
            $toNumber      = $recipient['number'];
            $recipientType = $recipient['type'];

            $log = WhatsAppNotificationLog::create([
                'module'           => 'presensi',
                'recipient_type'   => $recipientType,
                'recipient_number' => $toNumber,
                'message'          => $pesan,
                'status'           => 'pending',
                'response_payload' => json_encode(['info' => "Daily Report Dispatched for $namaKelas"]),
                'related_type'     => $relatedType,
                'related_id'       => (string) $relatedId,
            ]);

            SendWhatsAppNotificationJob::dispatch($toNumber, $pesan, $relatedType, (string) $relatedId, $recipientType, $log->id);
            $dispatched++;
        }

        return compact('dispatched', 'skipped', 'errors');
    }
}

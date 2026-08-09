<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PresensiDailyReportSetting;
use App\Models\TahunAjaran;
use App\Models\KelasAjaran;
use App\Models\EnrollmentSiswa;
use App\Models\Presensi;
use App\Models\WhatsAppNotificationLog;
use App\Models\PengaturanSekolah;
use App\Jobs\SendWhatsAppNotificationJob;
use App\Services\RecipientResolverService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendDailyClassReportCommand extends Command
{
    protected $signature = 'presensi:send-daily-class-report {--force : Abaikan jam cutoff untuk testing}';
    protected $description = 'Kirim laporan harian presensi per kelas ke Wali Kelas';

    public function handle(RecipientResolverService $resolver)
    {
        $setting = PresensiDailyReportSetting::current();

        if (!$setting->is_active) {
            $this->info('Laporan harian tidak aktif.');
            return;
        }

        $now = Carbon::now();
        $currentTime = $now->format('H:i');
        $cutoffTime = substr($setting->cutoff_time, 0, 5); // Ambil jam:menit

        if (!$this->option('force') && $currentTime !== $cutoffTime) {
            $this->info("Bukan waktunya kirim laporan. Current: $currentTime, Cutoff: $cutoffTime");
            return;
        }

        $currentYear = TahunAjaran::aktif()->first();
        if (!$currentYear) {
            $this->error('Tidak ada tahun ajaran aktif.');
            return;
        }

        $this->info("Memulai pengiriman laporan harian (Cutoff: $cutoffTime)");

        // Loop semua kelas di tahun ajaran aktif
        $kelasAjarans = KelasAjaran::with(['kelas', 'guru'])
            ->where('academic_year_id', $currentYear->id)
            ->get();

        $pengaturan = PengaturanSekolah::current();
        $tanggal = $now->format('d-m-Y');

        foreach ($kelasAjarans as $kelasAjaran) {
            $this->processKelas($kelasAjaran, $currentYear, $setting, $tanggal, $now, $resolver);
        }

        $this->info('Selesai memproses laporan harian.');
    }

    private function processKelas($kelasAjaran, $currentYear, $setting, $tanggal, $now, $resolver)
    {
        // 1. Dapatkan semua siswa aktif di kelas ini
        $enrollments = EnrollmentSiswa::with('siswa')
            ->where('academic_year_id', $currentYear->id)
            ->where('class_id', $kelasAjaran->class_id)
            ->where('status', 'aktif')
            ->get();

        $totalSiswa = $enrollments->count();
        if ($totalSiswa === 0) {
            return; // Kelas kosong
        }

        // 2. Dapatkan record presensi hari ini untuk siswa-siswa di kelas ini
        $studentIds = $enrollments->pluck('student_id')->toArray();
        $attendances = Presensi::whereIn('student_id', $studentIds)
            ->where('date', $now->toDateString())
            ->get();

        $hadir = $attendances->whereIn('status', ['hadir', 'pulang'])->count(); // Pulang asumsikan pagi hadir
        $telat = $attendances->where('status', 'telat')->count();
        $alpa = $attendances->where('status', 'alpa')->count();
        $sakit = $attendances->where('status', 'sakit')->count();
        $izin = $attendances->where('status', 'izin')->count();

        // 3. Identifikasi siapa yang belum presensi
        $attendanceStudentIds = $attendances->pluck('student_id')->toArray();
        $belumPresensiNames = [];
        
        foreach ($enrollments as $enrollment) {
            if (!in_array($enrollment->student_id, $attendanceStudentIds)) {
                $belumPresensiNames[] = '- ' . ($enrollment->siswa->name ?? 'Siswa Tanpa Nama');
            }
        }

        $daftarBelumPresensi = empty($belumPresensiNames) ? 'Tidak ada (Semua sudah mengisi presensi)' : implode("\n", $belumPresensiNames);
        $namaKelas = $kelasAjaran->kelas ? $kelasAjaran->kelas->name : 'Kelas Tidak Diketahui';

        // 4. Render Template
        $pesan = str_replace(
            ['{nama_kelas}', '{tanggal}', '{total_siswa}', '{jumlah_hadir}', '{jumlah_terlambat}', '{jumlah_alpa}', '{jumlah_sakit}', '{jumlah_izin}', '{daftar_belum_presensi}'],
            [$namaKelas, $tanggal, $totalSiswa, ($hadir + $telat), $telat, $alpa, $sakit, $izin, $daftarBelumPresensi], // Hadir + telat sebagai total yang ada di sekolah, sesuaikan dengan logic bisnis
            $setting->template_pesan
        );

        // 5. Dedup Guard
        $relatedType = 'daily_report_kelas';
        $relatedId = $kelasAjaran->id; // ID unik kelas ajaran (mewakili 1 kelas di 1 tahun ajaran)
        $todayStr = $now->toDateString();

        $alreadyDispatched = WhatsAppNotificationLog::where('related_type', $relatedType)
            ->where('related_id', (string)$relatedId)
            ->whereDate('created_at', $todayStr)
            ->whereIn('status', ['sent', 'pending'])
            ->exists();

        if ($alreadyDispatched) {
            $this->line("Laporan untuk $namaKelas sudah dikirim hari ini. Skip.");
            return;
        }

        // 6. Resolve Penerima
        $resolvedRecipients = [];
        $seenNumbers = [];
        $recipientKeys = $setting->recipients ?? [];

        foreach ($recipientKeys as $key) {
            if ($key === 'wali_kelas') {
                $hp = $kelasAjaran->guru ? $kelasAjaran->guru->no_hp : null;
                if ($hp && !isset($seenNumbers[$hp])) {
                    $resolvedRecipients[] = ['number' => $hp, 'type' => 'wali_kelas'];
                    $seenNumbers[$hp] = true;
                }
            } elseif ($key !== 'ortu') {
                // Jabatan
                $jabatansHp = $resolver->resolveByJabatan($key);
                foreach ($jabatansHp as $hp) {
                    if ($hp && !isset($seenNumbers[$hp])) {
                        $resolvedRecipients[] = ['number' => $hp, 'type' => $key];
                        $seenNumbers[$hp] = true;
                    }
                }
            }
        }

        if (empty($resolvedRecipients)) {
            $this->warn("Tidak ada penerima valid untuk $namaKelas. Skip.");
            return;
        }

        // 7. Dispatch Job
        foreach ($resolvedRecipients as $recipient) {
            $toNumber = $recipient['number'];
            $recipientType = $recipient['type'];

            $log = WhatsAppNotificationLog::create([
                'module' => 'presensi',
                'recipient_type' => $recipientType,
                'recipient_number' => $toNumber,
                'message' => $pesan,
                'status' => 'pending',
                'response_payload' => json_encode(['info' => "Daily Report Dispatched for $namaKelas"]),
                'related_type' => $relatedType,
                'related_id' => (string)$relatedId,
            ]);

            SendWhatsAppNotificationJob::dispatch(
                $toNumber,
                $pesan,
                $relatedType,
                (string)$relatedId,
                $recipientType,
                $log->id
            );
            $this->info("Job dispatched untuk $namaKelas ke $toNumber ($recipientType)");
        }
    }
}

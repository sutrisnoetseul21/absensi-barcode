<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Presensi extends Model
{
    use HasUuids;

    protected $table = 'attendances';

    public const BLOCKED_OUT_STATUSES = ['alpa', 'izin', 'sakit'];

    protected $fillable = [
        'student_id',
        'enrollment_id',
        'class_id',
        'academic_year_id',
        'date',
        'scan_time',
        'scan_out_time',
        'status',
        'status_pulang',
        'late_minutes',
        'note',
        'is_manual_input',
        'manual_input_by_id',
        'manual_input_by_type',
        'manual_input_at',
        'manual_input_user_id',
        'scanned_by',
        'source',
        'leave_request_id',
    ];

    protected $casts = [
        'date'            => 'date',
        'late_minutes'    => 'integer',
        'is_manual_input' => 'boolean',
        'manual_input_at' => 'datetime',
    ];

    // Siswa yang diabsen
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    public function student(): BelongsTo
    {
        return $this->siswa();
    }

    // Enrollment siswa (kelas + tahun ajaran)
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(EnrollmentSiswa::class, 'enrollment_id');
    }

    // Kelas (denormalized — langsung tanpa join)
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'class_id');
    }

    // Tahun ajaran (denormalized — langsung tanpa join)
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'academic_year_id');
    }

    // Admin yang scan (guard Filament = tabel users)
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    // Polymorphic: siapa yang input manual (Guru atau User/Admin)
    public function inputManualOleh(): MorphTo
    {
        return $this->morphTo('manual_input_by');
    }

    // Akun user (tabel users) yang melakukan input/edit manual terakhir
    public function manualInputUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manual_input_user_id');
    }

    /**
     * Ringkasan audit pelaku & waktu input manual untuk kebutuhan keamanan/monitoring.
     *
     * @return array{is_manual: bool, role: ?string, actor_name: ?string, account: ?string, at_label: ?string, summary: ?string}
     */
    public function getManualAuditInfo(): array
    {
        $hasManualActor = (bool) ($this->is_manual_input || $this->manual_input_by_id || $this->manual_input_user_id || $this->manual_input_at);
        if (!$hasManualActor) {
            return [
                'is_manual' => false,
                'role' => null,
                'actor_name' => null,
                'account' => null,
                'at_label' => null,
                'summary' => null,
            ];
        }

        $morphType = (string) ($this->manual_input_by_type ?? '');
        $isGuru = in_array($morphType, [\App\Models\Guru::class, 'guru', 'wali_kelas'], true);
        $baseRole = $isGuru ? 'Guru' : ($morphType !== '' || $this->manual_input_user_id ? 'Admin' : 'Manual');
        $role = (!$this->is_manual_input && $baseRole !== 'Manual')
            ? "Scan Kiosk + Edit {$baseRole}"
            : $baseRole;

        $morphActor = $this->inputManualOleh;
        $userAccount = $this->manualInputUser ?? ($morphActor instanceof User ? $morphActor : null);

        $actorName = $morphActor?->name ?? $userAccount?->name;
        $account = $userAccount?->email ?? ($morphActor instanceof Guru ? $morphActor->user?->email : null);

        $timestamp = $this->manual_input_at ?? $this->updated_at ?? $this->created_at;
        $atLabel = $timestamp
            ? \Carbon\Carbon::parse($timestamp)->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y H:i') . ' WIB'
            : null;

        $parts = array_filter([
            $role . ($actorName ? ": {$actorName}" : ''),
            $account ? "({$account})" : null,
            $atLabel ? "• {$atLabel}" : null,
        ]);

        return [
            'is_manual' => true,
            'role' => $role,
            'actor_name' => $actorName,
            'account' => $account,
            'at_label' => $atLabel,
            'summary' => implode(' ', $parts),
        ];
    }

    /**
     * Normalisasi input waktu HH:mm (24 jam) ke format DB HH:mm:ss.
     */
    public static function normalizeTimeForStorage(?string $time): ?string
    {
        if (empty($time)) {
            return null;
        }
        if (preg_match('/^([01]?\d|2[0-3]):([0-5]\d)(?::([0-5]\d))?$/', trim($time), $m)) {
            return sprintf('%02d:%02d:%02d', (int) $m[1], (int) $m[2], (int) ($m[3] ?? 0));
        }
        return null;
    }

    /**
     * Susun log audit catatan perubahan manual lengkap dengan peran, nama, akun login, dan tanggal-jam WIB.
     */
    public static function buildManualAuditNote(
        ?self $existing,
        string $actorType,
        string $actorName,
        ?string $accountEmail,
        string $newStatus,
        int $newLate,
        ?string $newStatusPulang,
        ?string $newScanTime,
        ?string $newScanOutTime
    ): string {
        $nowWib = \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('d M Y H:i') . ' WIB';
        $accountPart = $accountEmail ? " [akun: {$accountEmail}]" : '';
        $jamDatangStr = $newScanTime ? substr($newScanTime, 0, 5) : '-';
        $jamPulangStr = $newScanOutTime ? substr($newScanOutTime, 0, 5) : '-';

        if ($existing) {
            $strLama = $existing->status === 'telat' ? "Telat ({$existing->late_minutes} mnt)" : ucfirst($existing->status);
            $strBaru = $newStatus === 'telat' ? "Telat ({$newLate} mnt)" : ucfirst($newStatus);
            $strPulangLama = $existing->status_pulang ? ucfirst($existing->status_pulang) : 'Kosong';
            $strPulangBaru = $newStatusPulang ? ucfirst($newStatusPulang) : 'Kosong';
            $appendNote = "Diedit oleh {$actorType}: {$actorName}{$accountPart} pada {$nowWib} (Datang: {$strLama}->{$strBaru} [{$jamDatangStr}], Pulang: {$strPulangLama}->{$strPulangBaru} [{$jamPulangStr}])";
            return $existing->note ? $existing->note . ' | ' . $appendNote : $appendNote;
        }

        return "Diinput Manual oleh {$actorType}: {$actorName}{$accountPart} pada {$nowWib} (Datang: " . ucfirst($newStatus) . " [{$jamDatangStr}], Pulang: " . ($newStatusPulang ? ucfirst($newStatusPulang) . " [{$jamPulangStr}]" : 'Kosong') . ')';
    }

    /**
     * Hitung keterlambatan otomatis & periksa kesesuaian jam datang terhadap Pengaturan Sekolah.
     *
     * @return array{checkin_hm: string, threshold: int, cutoff_hm: string, late_minutes: int, alarm: ?string, alarm_type: ?string}
     */
    public static function evaluateArrivalTime(?string $status, ?string $scanTime, ?PengaturanSekolah $settings = null, ?string $date = null): array
    {
        $settings ??= PengaturanSekolah::current();
        $refDate = $date ?: \Carbon\Carbon::now('Asia/Jakarta')->toDateString();
        $checkinTimeStr = $settings?->checkin_time ?: '07:00:00';
        $threshold = (int) ($settings?->late_threshold_minutes ?? 15);

        $checkinCarbon = \Carbon\Carbon::parse($refDate . ' ' . $checkinTimeStr, 'Asia/Jakarta');
        $cutoffCarbon = $checkinCarbon->copy()->addMinutes($threshold);

        $result = [
            'checkin_hm'   => $checkinCarbon->format('H:i'),
            'threshold'    => $threshold,
            'cutoff_hm'    => $cutoffCarbon->format('H:i'),
            'late_minutes' => 0,
            'alarm'        => null,
            'alarm_type'   => null,
        ];

        if (!in_array($status, ['hadir', 'telat'], true) || empty($scanTime)) {
            return $result;
        }

        $normalized = self::normalizeTimeForStorage($scanTime);
        if (!$normalized) {
            return $result;
        }

        $scanCarbon = \Carbon\Carbon::parse($refDate . ' ' . $normalized, 'Asia/Jakarta');
        $diffFromCheckin = $scanCarbon->greaterThan($checkinCarbon)
            ? (int) $checkinCarbon->diffInMinutes($scanCarbon)
            : 0;
        $isLateByRule = $diffFromCheckin > $threshold;

        if ($status === 'telat') {
            $result['late_minutes'] = $diffFromCheckin;
            if (!$isLateByRule) {
                $result['alarm_type'] = 'telat_too_early';
                $result['alarm'] = "Input telat tidak boleh kurang dari jam {$result['cutoff_hm']}";
            }
        } elseif ($status === 'hadir' && $isLateByRule) {
            $result['alarm_type'] = 'hadir_too_late';
            $result['alarm'] = "Input hadir tidak boleh lebih dari jam {$result['cutoff_hm']}";
        }

        return $result;
    }

    /**
     * Saran default jam datang 24h WIB saat status dipilih manual agar tidak memicu false-alarm.
     */
    public static function defaultManualScanTime(string $status, ?PengaturanSekolah $settings = null): string
    {
        $settings ??= PengaturanSekolah::current();
        $now = \Carbon\Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        $checkinTimeStr = $settings?->checkin_time ?: '07:00:00';
        $threshold = (int) ($settings?->late_threshold_minutes ?? 15);

        $checkinCarbon = \Carbon\Carbon::parse($today . ' ' . $checkinTimeStr, 'Asia/Jakarta');
        $cutoffCarbon = $checkinCarbon->copy()->addMinutes($threshold);

        if ($status === 'hadir') {
            return $now->greaterThan($cutoffCarbon) ? $checkinCarbon->format('H:i') : $now->format('H:i');
        }

        if ($status === 'telat') {
            return $now->greaterThan($cutoffCarbon)
                ? $now->format('H:i')
                : $cutoffCarbon->copy()->addMinutes(5)->format('H:i');
        }

        return $now->format('H:i');
    }
}

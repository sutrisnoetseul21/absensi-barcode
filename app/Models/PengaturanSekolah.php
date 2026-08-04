<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengaturanSekolah extends Model
{
    use HasUuids;

    protected $table = 'school_settings';

    protected $fillable = [
        'school_name',
        'school_address',
        'school_logo_path',
        'district_logo_path',
        'hero_image_path',
        'login_background_path',
        'principal_name',
        'principal_signature_path',
        'checkin_time',
        'batas_scan_datang_time',
        'start_scan_out_time',
        'work_days_type',
        'work_days_history',
        'late_threshold_minutes',
        'academic_year_id_active',
        'enable_promotion_features',
        'barcode_scan_mode',
        'lama_pinjam_buku_hari',
        'theme_primary',
        'theme_secondary',
        'theme_accent',
        'theme_warning',
        'theme_danger',
        'theme_info',
        // Portal Management
        'maintenance_portal_siswa',
        'maintenance_portal_guru',
        'maintenance_portal_perpustakaan',
        'welcome_message_siswa',
        'welcome_message_guru',
        'welcome_message_perpustakaan',
        'global_announcement_active',
        'global_announcement',
    ];

    protected $casts = [
        'checkin_time'                    => 'string',
        'late_threshold_minutes'          => 'integer',
        'enable_promotion_features'       => 'boolean',
        'work_days_history'               => 'array',
        'lama_pinjam_buku_hari'           => 'integer',
        'maintenance_portal_siswa'        => 'boolean',
        'maintenance_portal_guru'         => 'boolean',
        'maintenance_portal_perpustakaan' => 'boolean',
        'global_announcement_active'      => 'boolean',
    ];

    // Tahun ajaran yang sedang aktif
    public function tahunAjaranAktif(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'academic_year_id_active');
    }

    /**
     * Ambil pengaturan sekolah (tabel hanya berisi 1 baris).
     */
    public static function current(): ?static
    {
        return static::first();
    }
}

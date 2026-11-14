<?php

namespace App\Filament\Resources\ManajemenAksesPortalResource\Pages;

use App\Filament\Resources\ManajemenAksesPortalResource;
use App\Models\KelasAjaran;
use App\Models\TahunAjaran;
use Filament\Resources\Pages\EditRecord;

class EditManajemenAksesPortal extends EditRecord
{
    protected static string $resource = ManajemenAksesPortalResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = $this->record;
        $isSuperAdmin = $user->isSuperAdmin();
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        $activeYearId = $activeYear?->id;

        $data['akses_portal_guru'] = $isSuperAdmin || $user->hasRole('wali_kelas');
        $data['akses_portal_perpustakaan'] = $isSuperAdmin || $user->hasRole(['petugas_perpustakaan', 'admin_perpustakaan']);
        $data['akses_portal_presensi'] = $isSuperAdmin || $user->hasRole('petugas_presensi');
        $data['akses_dashboard_presensi'] = $isSuperAdmin || $user->hasRole('admin_portal_presensi');
        $data['akses_portal_web'] = $isSuperAdmin || $user->hasRole('admin_portal_web');
        $data['akses_portal_setup'] = $isSuperAdmin || $user->hasRole('admin_portal_setup');
        $data['akses_portal_akademik'] = $isSuperAdmin
            || $user->hasRole('admin_portal_akademik')
            || $user->hasAnyRole(['admin_akademik_editor', 'admin_akademik_viewer']);
        $data['hak_akses_akademik'] = (!$isSuperAdmin && $user->hasRole('admin_akademik_viewer') && !$user->hasRole('admin_akademik_editor'))
            ? 'viewer'
            : 'editor';
        $data['akses_portal_sistem'] = $isSuperAdmin || $user->hasRole('admin_portal_sistem');
        $data['bypass_semua_kelas'] = $isSuperAdmin || $user->can('portal_guru:akses_semua_kelas');
        $data['supervisi_jurnal_guru'] = $isSuperAdmin || $user->can('portal_guru:supervisi_jurnal');

        $pantauClassIds = [];
        if ($user->teacher && $activeYearId) {
            $pantauClassIds = $user->teacher->kelasPantau()
                ->where('academic_year_id', $activeYearId)
                ->pluck('class_id')
                ->toArray();
        }
        $data['kelas_binaan_bk_ids'] = $pantauClassIds;

        return $data;
    }

    protected function afterSave(): void
    {
        $user = $this->record;

        // Super Admin otomatis memiliki semua akses portal & bypass, tidak perlu dimodifikasi per-role
        if ($user->isSuperAdmin()) {
            return;
        }

        $formData = $this->form->getState();
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        $activeYearId = $activeYear?->id;

        // 1. Kelola Portal Guru
        $hasAksesGuru = !empty($formData['akses_portal_guru']);
        if ($hasAksesGuru) {
            if (!$user->hasRole('wali_kelas')) {
                $user->assignRole('wali_kelas');
            }
        } else {
            if ($user->hasRole('wali_kelas')) {
                $user->removeRole('wali_kelas');
            }
        }

        // Bypass Semua Kelas
        $bypassSemua = !empty($formData['bypass_semua_kelas']);
        if ($bypassSemua) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'portal_guru:akses_semua_kelas', 'guard_name' => 'web']);
            $user->givePermissionTo('portal_guru:akses_semua_kelas');
        } else {
            if ($user->can('portal_guru:akses_semua_kelas')) {
                $user->revokePermissionTo('portal_guru:akses_semua_kelas');
            }
        }

        // Supervisi Jurnal Guru (Akses Semua Guru & Mapel)
        $supervisiJurnal = !empty($formData['supervisi_jurnal_guru']);
        if ($supervisiJurnal) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'portal_guru:supervisi_jurnal', 'guard_name' => 'web']);
            $user->givePermissionTo('portal_guru:supervisi_jurnal');
        } else {
            if ($user->can('portal_guru:supervisi_jurnal')) {
                $user->revokePermissionTo('portal_guru:supervisi_jurnal');
            }
        }

        // Sync kelas binaan BK (jika user adalah Guru BK)
        if ($user->teacher && $activeYearId) {
            $isBk = $user->isGuruBk() || $user->teacher->hasJabatan(['Guru BK', 'BK']);
            if ($hasAksesGuru && $isBk) {
                $selectedClassIds = $formData['kelas_binaan_bk_ids'] ?? [];

                // Hapus yang lama di tahun ini
                $user->teacher->kelasPantau()->where('academic_year_id', $activeYearId)->delete();

                // Insert yang baru
                foreach ($selectedClassIds as $classId) {
                    \App\Models\TeacherClassAccess::create([
                        'teacher_id' => $user->teacher->id,
                        'class_id' => $classId,
                        'academic_year_id' => $activeYearId,
                    ]);
                }
            } elseif (!$isBk) {
                // Jika bukan Guru BK, bersihkan data kelas pantau jika ada
                $user->teacher->kelasPantau()->where('academic_year_id', $activeYearId)->delete();
            }
        }

        // 2. Kelola Portal Perpustakaan
        $hasAksesPerpus = !empty($formData['akses_portal_perpustakaan']);
        if ($hasAksesPerpus) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'petugas_perpustakaan', 'guard_name' => 'web']);
            if (!$user->hasRole('petugas_perpustakaan') && !$user->hasRole('admin_perpustakaan')) {
                $user->assignRole('petugas_perpustakaan');
            }
        } else {
            if ($user->hasRole('petugas_perpustakaan')) {
                $user->removeRole('petugas_perpustakaan');
            }
        }

        // 3. Kelola Portal Presensi (Admin & Kiosk)
        $hasAksesDashboardPresensi = !empty($formData['akses_dashboard_presensi']);
        
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'petugas_presensi', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_portal_presensi', 'guard_name' => 'web']);

        if ($hasAksesDashboardPresensi) {
            if (!$user->hasRole('admin_portal_presensi')) $user->assignRole('admin_portal_presensi');
            if (!$user->hasRole('petugas_presensi')) $user->assignRole('petugas_presensi');
        } else {
            if ($user->hasRole('admin_portal_presensi')) $user->removeRole('admin_portal_presensi');
            if ($user->hasRole('petugas_presensi')) $user->removeRole('petugas_presensi');
        }

        // 5. Kelola Portal Web Sekolah
        $hasAksesWeb = !empty($formData['akses_portal_web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_portal_web', 'guard_name' => 'web']);

        if ($hasAksesWeb) {
            if (!$user->hasRole('admin_portal_web')) $user->assignRole('admin_portal_web');
        } else {
            if ($user->hasRole('admin_portal_web')) $user->removeRole('admin_portal_web');
        }

        // 6. Kelola Portal Setup & Instalasi
        $hasAksesSetup = !empty($formData['akses_portal_setup']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_portal_setup', 'guard_name' => 'web']);

        if ($hasAksesSetup) {
            if (!$user->hasRole('admin_portal_setup')) $user->assignRole('admin_portal_setup');
        } else {
            if ($user->hasRole('admin_portal_setup')) $user->removeRole('admin_portal_setup');
        }

        // 7. Kelola Portal Akademik
        $hasAksesAkademik = !empty($formData['akses_portal_akademik']);
        $levelAkademik = $formData['hak_akses_akademik'] ?? 'editor';
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_portal_akademik', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_akademik_editor', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_akademik_viewer', 'guard_name' => 'web']);

        if ($hasAksesAkademik) {
            if (!$user->hasRole('admin_portal_akademik')) {
                $user->assignRole('admin_portal_akademik');
            }
            if ($levelAkademik === 'viewer') {
                if (!$user->hasRole('admin_akademik_viewer')) $user->assignRole('admin_akademik_viewer');
                if ($user->hasRole('admin_akademik_editor')) $user->removeRole('admin_akademik_editor');
            } else {
                if (!$user->hasRole('admin_akademik_editor')) $user->assignRole('admin_akademik_editor');
                if (!$user->hasRole('admin_akademik_viewer')) $user->assignRole('admin_akademik_viewer');
            }
        } else {
            if ($user->hasRole('admin_portal_akademik')) $user->removeRole('admin_portal_akademik');
            if ($user->hasRole('admin_akademik_editor')) $user->removeRole('admin_akademik_editor');
            if ($user->hasRole('admin_akademik_viewer')) $user->removeRole('admin_akademik_viewer');
        }

        // 8. Kelola Portal Sistem
        $hasAksesSistem = !empty($formData['akses_portal_sistem']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_portal_sistem', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_akses_editor', 'guard_name' => 'web']);

        if ($hasAksesSistem) {
            if (!$user->hasRole('admin_portal_sistem')) $user->assignRole('admin_portal_sistem');
            if (!$user->hasRole('admin_akses_editor')) $user->assignRole('admin_akses_editor');
        } else {
            if ($user->hasRole('admin_portal_sistem')) $user->removeRole('admin_portal_sistem');
            if ($user->hasRole('admin_akses_editor')) $user->removeRole('admin_akses_editor');
        }

    }
}

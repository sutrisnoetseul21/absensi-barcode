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
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        $activeYearId = $activeYear?->id;

        $data['akses_portal_guru'] = $user->hasRole('wali_kelas');
        $data['akses_portal_perpustakaan'] = $user->hasRole(['petugas_perpustakaan', 'admin_perpustakaan']);
        $data['akses_portal_presensi'] = $user->hasRole('petugas_presensi');
        $data['akses_dashboard_presensi'] = $user->hasRole('admin_portal_presensi');
        $data['akses_portal_web'] = $user->hasRole('admin_portal_web');
        $data['bypass_semua_kelas'] = $user->hasPermissionTo('portal_guru:akses_semua_kelas');

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
        if ($hasAksesGuru && $bypassSemua) {
            $user->givePermissionTo('portal_guru:akses_semua_kelas');
        } else {
            $user->revokePermissionTo('portal_guru:akses_semua_kelas');
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

    }
}

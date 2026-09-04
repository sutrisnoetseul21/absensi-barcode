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
        $data['akses_ijin_kehadiran'] = $user->hasRole('admin_ijin_kehadiran');
        $data['akses_portal_web'] = $user->hasRole('admin_portal_web');

        if ($user->hasPermissionTo('portal_guru:akses_semua_kelas')) {
            $data['mode_akses_kelas'] = 'semua_kelas';
            $data['kelas_pilihan_ids'] = [];
        } else {
            $pantauClassIds = [];
            if ($user->teacher && $activeYearId) {
                $pantauClassIds = $user->teacher->kelasPantau()
                    ->where('academic_year_id', $activeYearId)
                    ->pluck('class_id')
                    ->toArray();
            }

            if (count($pantauClassIds) > 0) {
                $data['mode_akses_kelas'] = 'kelas_tertentu';
                $data['kelas_pilihan_ids'] = $pantauClassIds;
            } else {
                $data['mode_akses_kelas'] = 'wali_kelas_saja';
                $data['kelas_pilihan_ids'] = [];
            }
        }

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

        $modeAkses = $formData['mode_akses_kelas'] ?? 'wali_kelas_saja';
        if ($hasAksesGuru && $modeAkses === 'semua_kelas') {
            $user->givePermissionTo('portal_guru:akses_semua_kelas');
        } else {
            $user->revokePermissionTo('portal_guru:akses_semua_kelas');
        }

        // Sync mode kelas pantau
        if ($user->teacher && $activeYearId) {
            if ($hasAksesGuru && $modeAkses === 'kelas_tertentu') {
                $selectedClassIds = $formData['kelas_pilihan_ids'] ?? [];
                
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
            } else {
                // Jika ganti ke mode lain, bersihkan data kelas pantau
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

        // 3 & 4. Kelola Portal Presensi (Admin & Kiosk)
        $hasAksesDashboardPresensi = !empty($formData['akses_dashboard_presensi']);
        $hasAksesIjinKehadiran = !empty($formData['akses_ijin_kehadiran']);
        
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'petugas_presensi', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_portal_presensi', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_ijin_kehadiran', 'guard_name' => 'web']);

        if ($hasAksesDashboardPresensi) {
            if (!$user->hasRole('admin_portal_presensi')) $user->assignRole('admin_portal_presensi');
            if (!$user->hasRole('petugas_presensi')) $user->assignRole('petugas_presensi');
        } else {
            if ($user->hasRole('admin_portal_presensi')) $user->removeRole('admin_portal_presensi');
            if ($user->hasRole('petugas_presensi')) $user->removeRole('petugas_presensi');
        }

        if ($hasAksesIjinKehadiran) {
            if (!$user->hasRole('admin_ijin_kehadiran')) $user->assignRole('admin_ijin_kehadiran');
        } else {
            if ($user->hasRole('admin_ijin_kehadiran')) $user->removeRole('admin_ijin_kehadiran');
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

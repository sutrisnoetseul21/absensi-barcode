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

        if ($user->hasPermissionTo('portal_guru:akses_semua_kelas')) {
            $data['mode_akses_kelas'] = 'semua_kelas';
            $data['kelas_pilihan_ids'] = [];
        } else {
            $existingClassIds = [];
            if ($user->teacher && $activeYearId) {
                $existingClassIds = $user->teacher->kelasAjarans()
                    ->where('academic_year_id', $activeYearId)
                    ->pluck('class_id')
                    ->toArray();
            }

            if (count($existingClassIds) > 1) {
                $data['mode_akses_kelas'] = 'kelas_tertentu';
                $data['kelas_pilihan_ids'] = $existingClassIds;
            } else {
                $data['mode_akses_kelas'] = 'wali_kelas_saja';
                $data['kelas_pilihan_ids'] = $existingClassIds;
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

        // Jika mode kelas tertentu & user punya profil teacher
        if ($hasAksesGuru && $modeAkses === 'kelas_tertentu' && $user->teacher && $activeYearId) {
            $selectedClassIds = $formData['kelas_pilihan_ids'] ?? [];
            
            foreach ($selectedClassIds as $classId) {
                KelasAjaran::firstOrCreate([
                    'academic_year_id' => $activeYearId,
                    'class_id'         => $classId,
                    'teacher_id'       => $user->teacher->id,
                ]);
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

        // 3. Kelola Portal Presensi
        $hasAksesPresensi = !empty($formData['akses_portal_presensi']);
        if ($hasAksesPresensi) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'petugas_presensi', 'guard_name' => 'web']);
            if (!$user->hasRole('petugas_presensi')) {
                $user->assignRole('petugas_presensi');
            }
        } else {
            if ($user->hasRole('petugas_presensi')) {
                $user->removeRole('petugas_presensi');
            }
        }

        // 4. Kelola Dashboard Presensi
        $hasAksesDashboardPresensi = !empty($formData['akses_dashboard_presensi']);
        if ($hasAksesDashboardPresensi) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_portal_presensi', 'guard_name' => 'web']);
            if (!$user->hasRole('admin_portal_presensi')) {
                $user->assignRole('admin_portal_presensi');
            }
        } else {
            if ($user->hasRole('admin_portal_presensi')) {
                $user->removeRole('admin_portal_presensi');
            }
        }
    }
}

<?php

namespace App\Filament\Resources\ManajemenAksesPortalResource\Pages;

use App\Filament\Resources\ManajemenAksesPortalResource;
use App\Filament\Resources\UserResource;
use App\Models\KelasAjaran;
use App\Models\TahunAjaran;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Role;

class EditManajemenAksesPortal extends EditRecord
{
    protected static string $resource = ManajemenAksesPortalResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            UserResource::getUrl('index') => 'Manajemen User dan Akses',
            $this->getRecordTitle(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return UserResource::getUrl('index');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->url(UserResource::getUrl('index'));
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = $this->record;
        $isSuperAdmin = $user->isSuperAdmin();
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        $activeYearId = $activeYear?->id;

        $data['is_super_admin_toggle'] = $isSuperAdmin;

        $data['akses_portal_guru'] = $isSuperAdmin || $user->isGuruUser() || $user->hasRole('wali_kelas');
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
        $guru = $user->teacher ?? $user->guru;
        $isBk = $user->isGuruBk() || ($guru !== null && $guru->hasJabatan(['Guru BK', 'BK']));

        $pantauClassIds = [];
        if ($guru && $activeYearId) {
            $pantauClassIds = $guru->kelasPantau()
                ->where('academic_year_id', $activeYearId)
                ->pluck('class_id')
                ->toArray();
        }

        $data['bypass_semua_kelas'] = $isSuperAdmin
            || (! $isBk && ! empty($pantauClassIds))
            || (! $user->isGuruUser() && $user->can('portal_guru:akses_semua_kelas'));
        $data['supervisi_jurnal_guru'] = $isSuperAdmin || $user->can('portal_guru:supervisi_jurnal');
        $data['bypass_class_ids'] = $pantauClassIds;
        $data['kelas_binaan_bk_ids'] = $pantauClassIds;

        return $data;
    }

    protected function afterSave(): void
    {
        $user = $this->record;
        $formData = $this->form->getState();

        // Akun yang sedang login tidak boleh mencabut status Super Admin dirinya sendiri
        if ($user->id === auth()->id() && $user->isSuperAdmin()) {
            return;
        }

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $wantsSuperAdmin = ! empty($formData['is_super_admin_toggle']);

        if ($wantsSuperAdmin) {
            if (! $user->hasRole('super_admin')) {
                $user->assignRole('super_admin');
            }
            if (! $user->is_super_admin) {
                $user->forceFill(['is_super_admin' => true])->saveQuietly();
            }

            return;
        }

        if ($user->hasRole('super_admin')) {
            $user->removeRole('super_admin');
        }
        if ($user->is_super_admin) {
            $user->forceFill(['is_super_admin' => false])->saveQuietly();
        }

        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        $activeYearId = $activeYear?->id;

        // 1. Kelola Portal Guru (akun Guru selalu otomatis memiliki akses Portal Guru / role wali_kelas)
        Role::firstOrCreate(['name' => 'wali_kelas', 'guard_name' => 'web']);
        $hasAksesGuru = $user->isGuruUser() || ! empty($formData['akses_portal_guru']);
        if ($hasAksesGuru) {
            if (!$user->hasRole('wali_kelas')) {
                $user->assignRole('wali_kelas');
            }
        } else {
            if ($user->hasRole('wali_kelas')) {
                $user->removeRole('wali_kelas');
            }
        }

        $guru = $user->teacher ?? $user->guru;
        $isBk = $user->isGuruBk() || ($guru !== null && $guru->hasJabatan(['Guru BK', 'BK']));

        // Akses Kelas Tambahan: untuk akun Guru dibatasi pada kelas pilihan (tanpa permission bypass semua kelas)
        $bypassSemua = !empty($formData['bypass_semua_kelas']);
        if (! $user->isGuruUser() && $bypassSemua) {
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

        // Sync kelas pantau (untuk Guru BK dari kelas_binaan_bk_ids, untuk Guru non-BK dari bypass_class_ids)
        if ($guru && $activeYearId) {
            $selectedClassIds = [];
            if ($hasAksesGuru && $isBk) {
                $selectedClassIds = $formData['kelas_binaan_bk_ids'] ?? [];
            } elseif ($hasAksesGuru && ! $isBk && $bypassSemua) {
                $selectedClassIds = $formData['bypass_class_ids'] ?? [];
            }

            $guru->kelasPantau()->where('academic_year_id', $activeYearId)->delete();

            foreach (array_unique($selectedClassIds) as $classId) {
                \App\Models\TeacherClassAccess::create([
                    'teacher_id' => $guru->id,
                    'class_id' => $classId,
                    'academic_year_id' => $activeYearId,
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

<?php

namespace App\Providers;

use App\Events\Student\StudentGraduated;
use App\Events\Student\StudentGraduationCancelled;
use App\Events\Student\StudentMutated;
use App\Events\Student\StudentReactivated;
use App\Listeners\Enrollment\HandleStudentGraduated;
use App\Listeners\Enrollment\HandleStudentGraduationCancelled;
use App\Listeners\Enrollment\HandleStudentMutated;
use App\Listeners\Enrollment\HandleStudentReactivated;
use App\Listeners\Presensi\HandleStudentDeactivatedForPresensi;
use App\Listeners\Presensi\HandleStudentReactivatedForPresensi;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\Filament\Auth\Http\Responses\Contracts\LogoutResponse::class, \App\Http\Responses\LogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Morph Map untuk polymorphic manual_input_by di tabel attendances.
        // 'admin'      → User (admin Filament, tabel users)
        // 'wali_kelas' → Guru (wali kelas, tabel teachers)
        Relation::morphMap([
            'admin'      => \App\Models\User::class,
            'wali_kelas' => \App\Models\Guru::class,
        ]);

        // ─────────────────────────────────────────────────────────────
        // Event-Driven Architecture — Modul Siswa (Tahap 2 Refactoring)
        //
        // Pola: Modul Siswa hanya men-dispatch Event. Modul lain
        // (Enrollment, Presensi) bereaksi lewat Listener masing-masing
        // tanpa Modul Siswa perlu mengetahui detail implementasinya.
        // ─────────────────────────────────────────────────────────────

        // StudentMutated → Enrollment: tandai enrollment aktif menjadi 'pindah'
        Event::listen(StudentMutated::class, HandleStudentMutated::class);

        // StudentMutated → Presensi: nonaktifkan barcode siswa
        Event::listen(StudentMutated::class, HandleStudentDeactivatedForPresensi::class);

        // StudentGraduated → Enrollment: tandai enrollment menjadi 'lulus'
        Event::listen(StudentGraduated::class, HandleStudentGraduated::class);

        // StudentGraduated → Presensi: nonaktifkan barcode siswa
        Event::listen(StudentGraduated::class, HandleStudentDeactivatedForPresensi::class);

        // StudentReactivated → Enrollment: kembalikan enrollment 'pindah' → 'aktif'
        Event::listen(StudentReactivated::class, HandleStudentReactivated::class);

        // StudentReactivated → Presensi: aktifkan kembali barcode siswa
        Event::listen(StudentReactivated::class, HandleStudentReactivatedForPresensi::class);

        // StudentGraduationCancelled → Enrollment: kembalikan enrollment 'lulus' → 'aktif'
        Event::listen(StudentGraduationCancelled::class, HandleStudentGraduationCancelled::class);

        // StudentGraduationCancelled → Presensi: aktifkan kembali barcode siswa
        Event::listen(StudentGraduationCancelled::class, HandleStudentReactivatedForPresensi::class);
    }
}

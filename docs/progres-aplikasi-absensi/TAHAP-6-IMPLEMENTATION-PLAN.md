# TAHAP 6 - IMPLEMENTATION PLAN: Portal Wali Kelas & Admin Manual Input

## 1. Ringkasan Scope
Implementasi fitur pengelolaan absensi tingkat kelas yang memiliki *dual-mode* (bisa diakses oleh Wali Kelas dan Admin). Komponen utama yang akan dibuat adalah `WaliKelasDashboard` dan `ManualAttendanceInput`, yang dirancang agar *reusable*. 
- **Mode Wali Kelas**: Secara otomatis membatasi (scope) akses hanya ke kelas yang diampu (berdasarkan relasi `class_academic_year.teacher_id`) menggunakan guard `wali_kelas`.
- **Mode Admin**: Memungkinkan pemilihan kelas manapun tanpa batasan *assignment* (menggunakan guard `web` / bawaan Filament) melalui custom page Filament.

## 2. Investigasi Skema Aktual

Berdasarkan pengecekan migrasi database:

### Tabel `teachers` (`2026_07_01_144310_create_teachers_table.php`)
```php
Schema::create('teachers', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('name');
    $table->string('nip')->unique()->nullable();
    $table->string('username')->unique();
    $table->string('password');
    $table->boolean('must_change_password')->default(true);
    // ... timestamps & softDeletes
});
```

### Tabel `class_academic_year` (`2026_07_01_144516_create_class_academic_year_table.php`)
Tabel pivot ini memetakan kelas, tahun ajaran, dan wali kelas:
```php
Schema::create('class_academic_year', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('class_id')->constrained('classes')->cascadeOnDelete();
    $table->foreignUuid('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
    $table->foreignUuid('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
    // ... unique(['class_id', 'academic_year_id'])
});
```

### Tabel `attendances` (`2026_07_01_144517_create_attendances_table.php`)
Telah dikonfirmasi memiliki kolom-kolom untuk input manual:
```php
$table->boolean('is_manual_input')->default(false);
// Polymorphic: bisa Teacher (wali_kelas) atau User (admin)
$table->uuid('manual_input_by_id')->nullable();
$table->string('manual_input_by_type')->nullable();
// ... unique(['student_id', 'date'])
```

## 3. Struktur Auth Multi-Guard
Guard `wali_kelas` sudah terkonfigurasi di `config/auth.php`:
```php
'wali_kelas' => [
    'driver'   => 'session',
    'provider' => 'gurus',
],
'gurus' => [
    'driver' => 'eloquent',
    'model'  => App\Models\Guru::class, // Mengarah ke tabel teachers
],
```
Rute portal wali kelas (`/wali-kelas/*`) akan diproteksi dengan middleware `auth:wali_kelas`.

## 4. Struktur Component & Reuse Strategy
- **`WaliKelasDashboard` (Livewire Component)**:
  - Component akan mendeteksi mode aksesnya melalui pengecekan guard: `Auth::guard('wali_kelas')->check()` vs `Auth::guard('web')->check()`.
  - Jika `wali_kelas`, opsi dropdown kelas hanya diisi dengan kelas dari `class_academic_year` yang `teacher_id`-nya adalah auth user saat ini DAN `academic_year_id` adalah tahun ajaran aktif, dan otomatis me-load kelas pertama.
    Contoh:
    ```php
    $myClasses = \DB::table('class_academic_year')
        ->where('teacher_id', $actor->id)
        ->where('academic_year_id', $activeAcademicYearId)
        ->get();
    ```
  - Jika `web` (Admin), opsi dropdown diisi semua kelas, dan user bisa bebas memilih.
- **`RekapAbsensiKelas` (Filament Custom Page)**:
  - Page khusus untuk Admin/Super Admin yang secara internal akan me-render Livewire Component `WaliKelasDashboard` di dalamnya.
- **`ManualAttendanceInput` (Livewire Component / Modal)**:
  - Pencarian siswa di-scope: Jika `wali_kelas`, query `Student` di-filter berdasarkan `class_id` yang sedang aktif (milik wali kelas). Jika admin, bisa cari semua.

## 5. Validasi Server-Side
- **Autorisasi Kelas**: 
  Di dalam method `mount()` di `WaliKelasDashboard`, jika guard adalah `wali_kelas`, pastikan `$selectedClassId` ada di dalam array/koleksi kelas yang diampu. Jika tidak valid, `abort(403)` atau fallback ke kelas miliknya.
- **Validasi Input Manual**: 
  Sebelum melakukan `\App\Models\Presensi::updateOrCreate()`, lakukan query:
  ```php
  $existing = \App\Models\Presensi::where('student_id', $studentId)->where('date', $date)->first();
  if ($existing && $existing->is_manual_input === false) {
      // Tolak, karena ini data valid dari Kios Scan
      throw ValidationException::withMessages(['student' => 'Siswa ini sudah tercatat Hadir/Telat hari ini via scan, tidak bisa diubah manual.']);
  }
  ```

## 6. Instalasi & Konfigurasi `spatie/laravel-activitylog`
- Menjalankan `composer require spatie/laravel-activitylog`
- Publish config & migration: `php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"`
- `php artisan migrate`
- Implementasi Log di `ManualAttendanceInput`:
  ```php
  $actor = Auth::guard('wali_kelas')->check() 
      ? Auth::guard('wali_kelas')->user() 
      : Auth::guard('web')->user();

  // Pastikan manual_input_by_id dan manual_input_by_type di tabel attendances juga diisi dari $actor ini.

  activity()
     ->performedOn($attendance)
     ->causedBy($actor)
     ->withProperties(['old_status' => $oldStatus, 'new_status' => $newStatus, 'note' => $note])
     ->log('Updated manual attendance');
  ```

## 7. Query Alert Pelanggaran
Dihitung secara *real-time* per bulan berjalan, reset otomatis di bulan baru:
```php
$startOfMonth = now()->startOfMonth()->toDateString();
$endOfMonth = now()->endOfMonth()->toDateString();

// Siswa dengan total Alpa >= 3 di bulan berjalan
$alpaTerlaluBanyak = \App\Models\Presensi::where('academic_year_id', $academicYearId)
    ->where('class_id', $classId)
    ->whereBetween('date', [$startOfMonth, $endOfMonth])
    ->where('status', 'alpa')
    ->groupBy('student_id')
    ->havingRaw('COUNT(*) >= 3')
    ->pluck('student_id');

// Siswa dengan total keterlambatan >= 100 menit di bulan berjalan
$telatTerlaluBanyak = \App\Models\Presensi::where('academic_year_id', $academicYearId)
    ->where('class_id', $classId)
    ->whereBetween('date', [$startOfMonth, $endOfMonth])
    ->where('status', 'telat')
    ->groupBy('student_id')
    ->havingRaw('SUM(late_minutes) >= 100')
    ->pluck('student_id');
```
Label merah akan dimunculkan di UI baris tabel siswa jika ID-nya masuk di salah satu/kedua list tersebut.

## 8. Urutan Eksekusi (Step-by-Step)
1. Setup & Install `spatie/laravel-activitylog`.
2. Buat `WaliKelasLogin` Component & Routing.
3. Buat `WaliKelasDashboard` Component dasar (UI Tabel & Filter Bulan).
4. Implementasikan *Server-Side Validation* di Dashboard (scope kelas per guard).
5. Buat fitur Filament Page `RekapAbsensiKelas` dan integrasikan dashboard untuk mode admin.
6. Buat `ManualAttendanceInput` component dengan validasi bentrok Kios Scan.
7. Integrasikan Activity Log pada aksi simpan.
8. Buat query dan render UI untuk **Alert Pelanggaran**.
9. Lakukan Testing Manual sesuai skenario.

## 9. Test Manual / Verifikasi
- [ ] Login wali kelas berhasil dan redirect ke dashboard kelasnya
- [ ] Wali kelas tidak bisa akses kelas lain (dicoba manipulasi class_id via URL/DevTools tetap ditolak server)
- [ ] Admin/Super Admin bisa akses & input manual absensi untuk kelas manapun lewat Filament Page
- [ ] Input manual absensi tersimpan dengan flag benar (`is_manual_input`, `manual_input_by_id/type`)
- [ ] Input manual DITOLAK kalau siswa sudah punya record Hadir/Telat asli hari itu
- [ ] Wali kelas bisa edit input manual miliknya sendiri, dan perubahan tercatat di activity log
- [ ] Alert pelanggaran (>=3x Alpa atau >=100 menit telat) dihitung per bulan berjalan, reset tiap bulan baru
- [ ] Pencarian siswa bisa pakai nama dan NISN, ter-scope sesuai mode akses (wali kelas vs admin)

# Plan (Revisi v3.1): Notifikasi & Persetujuan Ijin Siswa via WhatsApp

## Hasil Verifikasi Codebase (v3.1)
1. **Alur Approve/Reject Lama:** Filament memanggil `syncAttendances()`/`removeAttendances()` dan `recordLog()` lalu memperbarui `status`, `approved_by`, `approved_at` secara diam-diam tanpa menggunakan observer otomatis. Portal Guru (`IjinKehadiranDetail`) melakukan rutinitas yang sama ditambah memanggil method manual `sendNotificationToStudent()`. Jadi, tidak ada efek berganda dari event/observer model.
2. **Approver (Null safety):** Komponen UI di Admin Panel/Filament merender data dengan syntax *null-safe* (`$record?->approvedBy ? $record->approvedBy->name : '-'`). Portal guru juga aman. Tipe morph approver adalah class model authentikasi (`App\Models\User`). Mengingat akses melalui token publik tidak terikat pada User ID spesifik yang pasti terdeteksi, mengisi `approved_by` dengan `null` adalah aman secara arsitektur.
3. **Kontrak WhatsApp Gateway:** `WhatsAppGatewayService::sendMessage()` secara internal sudah menangani _error_ via _try-catch_, dan mengembalikan `bool` (`true` jika berhasil, `false` jika API gagal/luar jam/timeout HTTP), sembari otomatis menyimpan state akhirnya ke `WhatsAppNotificationLog`. Tidak melempar Exception langsung.
4. **Penyimpanan Lampiran:** File disimpan ke disk `public` (direktori `leave-requests/`) menggunakan fungsi `store()`, sehingga secara default memanfaatkan penamaan *hashName* yang acak.
5. **Config Queue:** Driver memakai koneksi `database` (default), dengan konfigurasi default `retry_after` = 90 detik.

## Asumsi
- Fitur ini menggunakan standar PHPUnit/Laravel Feature testing (berdasarkan verifikasi file test codebase).
- Domain aplikasi dapat dijangkau oleh jaringan server Evolution API untuk mengunduh media dari _temporary signed URL_.

---

## Konsep Final
Ketika siswa mengajukan ijin di `portal-siswa/ijin-kehadiran/form`, sistem akan:
1. Menyimpan data ijin (sudah ada).
2. Generate token unik ke database tanpa menghentikan _flow_ utamanya.
3. Dispatch Job ke Queue untuk kirim WA ke Wali Kelas.
4. Job mengirim pesan TEKS berisi detail ijin + **satu link konfirmasi pendek**.
5. Job mengirim pesan LAMPIRAN media secara terpisah (tanpa caption) menggunakan _temporary signed URL_ khusus.
6. Wali kelas mengeklik link → tampil **halaman konfirmasi** (nama siswa, alasan, tombol Setujui/Tolak).
7. Guru klik tombol setuju/tolak → POST dengan proteksi CSRF → eksekusi perubahan atomik (mencegah klik dobel) di dalam _Database Transaction_.
8. Siswa otomatis mendapat notifikasi WA balasan mengenai hasilnya (menggunakan Job terpisah pasca transaksi ter-commit).

---

## Keputusan Desain (v3.1)

| Aspek | Keputusan | Alasan |
|---|---|---|
| (BARU) Transaksi DB | `DB::transaction` digunakan pada aksi POST, mencakup update status, sync absensi, & catat log. | Menjamin integritas data. Rollback dijamin jika `syncAttendances` melempar _error_ (token tidak mubazir). |
| (BARU) Urutan WA | Pesan TEKS dikirim DULU, disusul LAMPIRAN per file. | Batas _caption_ media WA ±1024 karakter; mencegah isi penting terpotong. |
| (BARU) Idempotensi Media | Tracking _granular_: `wa_text_sent_at` (Timestamp) dan `wa_sent_files` (JSON). | Jika job di-retry, pesan/file yang sudah sukses akan di-skip (tidak spamming). |
| (v3.1) Proteksi Token & Fillable | Token dimasukkan ke array `$hidden` di model. **Tidak dimasukkan** ke `$fillable`. Diisi lewat `forceFill()`. | Mencegah celah keamanan _mass-assignment vulnerability_ & kebocoran JSON. |
| (v3.1) Accessor Lampiran | Accessor kustom `attachments` (menggabung `file_path` & `file_paths`, _deduplikasi_, cek fisik _storage_). | Menjamin Job WA & Route membaca dan me-resolve metadata media terpusat & steril. |
| (v3.1) Re-use Alur Lama | Replikasi efek samping Portal Guru (panggil `syncAttendances` & _dispatch Job_ notif ke siswa) secara manual di `action()`. | Menyamakan sifat aplikasi tanpa notifikasi ganda. |
| (v3.1) Akses Lampiran Publik | File tetap di-hosting pada disk `public` namun URL file memakai hash (risiko bocor rendah). | Relokasi masal ke disk _private_ berpotensi merusak kompatibilitas fitur lama, risiko _public hash_ dapat diterima. |
| (v3.1) State Halaman & Error | Sentralisasi penentuan halaman di `resolveState()`. 419 (CSRF) & 422 di-_handle_ _gracefully_. | Menjaga kenyamanan UX bagi User saat me-refresh browser. |
| Pengiriman WA | Queue Job dengan `$tries = 3`, `backoff = [60, 300]`. Job akan melempar Exception bila API WA me-return False. | Memicu fasilitas _retry_ Laravel secara wajar sebelum digugurkan. |

---

## Langkah Implementasi (Step-by-Step)

### STEP 1: Migration — Tambah kolom ke `leave_requests`
**File:** Buat migration baru (`..._add_approval_fields_to_leave_requests_table.php`)
```php
public function up() {
    Schema::table('leave_requests', function (Blueprint $table) {
        $table->string('approval_token', 8)->nullable()->unique();
        $table->timestamp('token_used_at')->nullable();
        $table->timestamp('token_expires_at')->nullable();
        $table->timestamp('wa_text_sent_at')->nullable();
        $table->json('wa_sent_files')->nullable();
    });
}
public function down() {
    Schema::table('leave_requests', function (Blueprint $table) {
        $table->dropColumn(['approval_token', 'token_used_at', 'token_expires_at', 'wa_text_sent_at', 'wa_sent_files']);
    });
}
```

> ✅ **Hasil Implementasi:** File `database/migrations/2026_09_28_194645_add_approval_fields_to_leave_requests_table.php` dibuat. `php artisan migrate` berhasil dieksekusi (476ms). 5 kolom baru terverifikasi masuk ke tabel `leave_requests`.

### STEP 2: Update Model `LeaveRequest` (v3.1)
**File:** `app/Models/LeaveRequest.php`
- JANGAN tambahkan kolom baru (token dll) ke properti `$fillable`.
- Tetap masukkan `'approval_token'` ke array `$hidden`.
- Tambahkan `$casts`: `token_used_at` (datetime), `token_expires_at` (datetime), `wa_text_sent_at` (datetime), `wa_sent_files` (array).
- **`getAttachmentsAttribute()` (Accessor):** Perbarui method ini untuk: menggabungkan `file_path` dan `file_paths`, membuang duplikat, dan **hanya memuat file yang benar-benar eksis** di `Storage::disk('public')`. Kembalikan array berisi associative object dengan struktur: `index` (0-based integer stabil), `path`, `name` (basename), `mime`, dan `mediatype` (`image` untuk jpg/jpeg/png/webp; `document` untuk pdf; skip/log jenis lainnya).
- **`generateApprovalToken()`**: Jika `$this->approval_token` masih null, lakukan _looping_ (maks 5x percobaan generate `Str::random(8)` Base62) yang dibungkus _try/catch_ penangkap `UniqueConstraintViolationException`. Set `token_expires_at` (now + 7 hari). Gunakan operasi simpan bypass mass-assignment `$this->forceFill([...])->saveQuietly()`. Lempar Exception jika 5x perulangan habis.
- **`isTokenValid()`**: Cek `token_used_at === null` && `token_expires_at > now()` && `status === 'pending'`.

> ✅ **Hasil Implementasi:** `app/Models/LeaveRequest.php` diperbarui. Perubahan utama:
> - `$hidden = ['approval_token']` ditambahkan (token tidak bocor ke `toArray()`/JSON).
> - `$casts` diperluas dengan 4 cast baru.
> - `getAttachmentsAttribute()` ditulis ulang: deduplikasi path, cek `Storage::disk('public')->exists()`, filter hanya `image`/`document`, return array berstruktur `[index, path, name, mime, mediatype]`.
> - `generateApprovalToken()`: loop 5x catch `UniqueConstraintViolationException`, simpan via `forceFill()->saveQuietly()`, idempoten (tidak timpa token yang sudah ada).
> - `isTokenValid()`: helper boolean cek token valid.
> - Syntax terverifikasi via `php -l` dan `php artisan tinker`.

### STEP 3: Tambah method `sendMedia()` ke `WhatsAppGatewayService` (v3.1)
**File:** `app/Services/WhatsAppGatewayService.php`
- Buat method `sendMedia(string $toNumber, string $mediaUrl, string $mediaType, string $fileName, ?string $caption = null, ...)` yang mengembalikan nilai _boolean_.
- Tiru behavior `sendMessage()`: lakukan _try-catch_, panggil Endpoint `/message/sendMedia/{instance_name}`.
- Payload body request: `number`, `mediatype`, `mimetype`, `media` (signed URL string), `fileName`, dan `caption`.
- Jika request gagal (status non-2xx) atau terjadi Exception, kembalikan `false` dan rekam ke `WhatsAppNotificationLog`. Jangan melempar (throw) Exception langsung di level Service.

> ✅ **Hasil Implementasi:** Method `sendMedia()` ditambahkan ke `app/Services/WhatsAppGatewayService.php`. Kontrak identik `sendMessage()`: return `bool`, try-catch internal, catat ke `WhatsAppNotificationLog`. Endpoint `/message/sendMedia/{instance}`, timeout 30s. Payload: `number`, `mediatype`, `mimetype`, `media` (signed URL), `fileName`, `caption` (opsional). Send Window check tetap diterapkan. Syntax terverifikasi via `php -l`.

### STEP 4: Route Publik `/ijin-approval/{token}` & Route Media (v3.1)
**File:** `routes/web.php` dan `bootstrap/app.php`
- Tambahkan pendefinisian Route Token approval berikut:
```php
Route::middleware('throttle:20,1')->group(function () {
    Route::get('/ijin-approval/{token}', [LeaveRequestApprovalController::class, 'show'])->name('ijin.approval.show');
    Route::post('/ijin-approval/{token}/action', [LeaveRequestApprovalController::class, 'action'])->name('ijin.approval.action');
})->where('token', '[A-Za-z0-9]{8}');

Route::get('/ijin-media/{leaveRequest}/{index}', [LeaveRequestApprovalController::class, 'media'])
    ->name('ijin.media')
    ->middleware('signed');
```
- Modifikasi _Exception Handler_ di `bootstrap/app.php` via method `withExceptions()`. Daftarkan _closure_ untuk menangkap `TokenMismatchException` (419 Error) secara eksklusif bagi _request URL_ berawalan `ijin-approval/*`. Kembalikan response Blade view khusus yang ramah (informasikan pesan "Sesi habis, muat ulang halaman lalu coba lagi") dengan link kembali.

> ✅ **Hasil Implementasi:**
> - `routes/web.php`: Ditambahkan 3 route di akhir file. Route token pakai `->where('token', '[A-Za-z0-9]{8}')`. Route media pakai `middleware('signed')` + constraint UUID dan index numerik.
> - `bootstrap/app.php`: Handler `TokenMismatchException` ditambahkan di `withExceptions()` khusus path `ijin-approval/*`, me-return view `leave-request-approval` dengan state `csrf_expired` dan link kembali ke halaman konfirmasi.
> - Syntax terverifikasi via `php -l`. Route list gagal karena controller belum dibuat (STEP 5) — ini normal.

### STEP 5: Controller `LeaveRequestApprovalController` (v3.1)
**File:** `app/Http/Controllers/LeaveRequestApprovalController.php` (Baru)
- Definisikan private method **`resolveState(?LeaveRequest $r): string`**: Jika `$r` null → kembalikan `'invalid'`; Jika kedaluwarsa (`token_expires_at <= now()`) → `'expired'`; Jika `token_used_at` isi ATAU status bukan pending → `'used'`; Selain ketiga kriteria tersebut, valid → `'confirm'`.
- **`show($token)` (GET):** 
  - Cari model `LeaveRequest` pertama berdasarkan `where('approval_token', $token)`. Panggil `resolveState()`. Render *view blade* sambil melampirkan state bersangkutan.
- **`action($token, Request $request)` (POST):**
  - Terapkan validasi `action` wajb terisi nilai enum `approve`/`reject`. (Jika tidak lulus validasi, framework me-return error 422 dan ini adalah perilaku normal).
  - Cari model LeaveRequest `$r`. Jika `resolveState($r) !== 'confirm'`, langsung *early-return* ke blade beserta nilai state-nya.
  - Inisiasi `DB::transaction()`:
    ```php
    $affected = LeaveRequest::where('approval_token', $token)
        ->whereNull('token_used_at')->where('status', 'pending')
        ->where('token_expires_at', '>', now())
        ->update([
            'status' => $action === 'approve' ? 'approved' : 'rejected',
            'token_used_at' => now(),
            'approved_at' => now(),
            'approved_by' => null, 
            'approved_by_type' => null,
        ]);
    
    // Check update idempoten & kondisi Race
    if ($affected === 0) {
        $record = LeaveRequest::where('approval_token', $token)->first();
        return view(..., ['state' => $this->resolveState($record)]);
    }
    
    // Jika 1 baris sukses terpengaruh, kerjakan efek samping operasional
    $record = LeaveRequest::where('approval_token', $token)->first();
    
    if ($action === 'approve') {
        app(LeaveRequestService::class)->syncAttendances($record);
    } else {
        app(LeaveRequestService::class)->removeAttendances($record);
    }
    
    $record->recordLog($action === 'approve' ? 'approved' : 'rejected', 'Via token link WhatsApp', [
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent()
    ]);
    
    // Kirim konfirmasi WA balasan ke siswa (Step 9) di luar database lock
    DB::afterCommit(fn() => SendLeaveRequestResultNotificationJob::dispatch($record->id));
    ```
    - Jika `syncAttendances` melempar Exception, `transaction` gagal dan view error generic bisa dirender pada Controller Catch / Handler umum.
- **`media(LeaveRequest $leaveRequest, $index)`:**
  - Telusuri item accessor `$leaveRequest->attachments` di mana iterasi kunci sejajar dengan indeks param URL `$index`. Jika lolos:
  - Kembalikan `Storage::disk('public')->response($path)` atau `response()->file()` dibarengi array opsi Header tambahan `X-Content-Type-Options: nosniff`. (404 bila miss).

> ✅ **Hasil Implementasi:** `app/Http/Controllers/LeaveRequestApprovalController.php` dibuat. Fitur:
> - `resolveState()`: 5 state (invalid/expired/used/confirm/error).
> - `show()`: query dengan eager load `student.enrollmentAktif.kelas`, tidak mengubah data.
> - `action()`: validasi `in:approve,reject`, early-return jika state bukan `confirm`, `DB::transaction` berisi update atomik (5 kondisi WHERE), `syncAttendances`/`removeAttendances`, `recordLog` dengan IP+UserAgent, `DB::afterCommit` dispatch Job notif siswa. Exception ditangkap → state `error` → log ke channel error.
> - `media()`: cari attachment via `$record->attachments` accessor, `response()->file()` dengan header `X-Content-Type-Options: nosniff`, `Cache-Control: private, no-store`.
> - Route list terverifikasi: 3 route `ijin.*` terdaftar aktif.

### STEP 6: View Halaman Approval
**File:** `resources/views/leave-request-approval.blade.php` (Baru)
- Meta tags: Sisipkan `<meta name="robots" content="noindex">` dan HTTP Header / Meta Referrer-Policy strict.
- Tampilkan konten layout berdasarkan isi variabel string `$state` (5 kondisi tampilan).
- Form POST mengandalkan tag `@csrf`. Beri peringatan modal dialog / event JavaScript ringan `confirm()` khusus saat Guru mengklik tombol "Tolak". Disabel atribut form setelah interaksi `submit` berjalan di UI.

### STEP 7: Update `IjinKehadiranForm` (Generate Token & Job) (v3.1)
**File:** `app/Livewire/PortalSiswa/IjinKehadiranForm.php`
Di bagian ekor eksekusi method `save()`, selimut perintah pasca-simpan absensi utama dengan try-catch agar tidak menyebabkan interupsi form UI siswa:
```php
try {
    $record->generateApprovalToken();
    DB::afterCommit(fn() => SendLeaveRequestWaNotificationJob::dispatch($record->id));
} catch (\Throwable $e) {
    report($e); 
    // Kegagalan sekunder API token/job ini dikompromikan diam & dicatat Log. 
    // Ijinnya tetap valid tersimpan dan dapat diapprove manual di Portal Admin.
}
```

### STEP 8: Job `SendLeaveRequestWaNotificationJob` (Pengiriman ke Guru) (v3.1)
**File:** `app/Jobs/SendLeaveRequestWaNotificationJob.php`
- Set `public int $timeout = 120;` untuk menyediakan slot waktu (maksimal 3 file dikalikan dengan 30s + toleransi) per eksekusi worker.
- Jika wali kelas tidak ditemukan (null HP), lakukan `Log::warning` dan matikan job (`return;`).
- **Teks Utama**: Jika nilai database `$record->wa_text_sent_at` masih `null`, persiapkan text message. (Potong panjang text alasan di database hingga sisa 500 karakter berakhiran "..."). Cek template; bila tidak mendapati `{link_konfirmasi}`, *append* `/ijin-approval/{token}` di akhir baris pesan. Panggil `sendMessage()`. _Penting:_ Bila return boolean Gateway bernilai `false`, _lempar_ Exception manual: `throw new \Exception('Gateway text fail');` agar memancing iterasi _retry_. Jika `true`, tandai database dengan `forceFill(['wa_text_sent_at' => now()])->saveQuietly()`.
- **Media Tambahan**: Akses array `attachments` (dibatasi maksimum iterasi 3 entri file teratas. Sisanya sampaikan via pesan teks ("+N lampiran lain, periksa panel")).
  - Cek: bila `$index` urutan media belum terdaftar di kolom database `wa_sent_files` (JSON).
  - Jika belum, hasilkan `URL::temporarySignedRoute('ijin.media', now()->addHours(6), [...])`.
  - Panggil gateway `sendMedia()` tanpa _caption_ berlebih.
  - Bila mereturn `false`, lemparkan (throw) Exception API fail.
  - Jika `true`, _push_ int `$index` bersangkutan ke JSON data, lalukan method eksekusi `$record->update([...])` / `forceFill()->saveQuietly()`.
- Berikan method bawaan antrian job: `public function failed(Throwable $e)` yang menampung rekaman Error pamungkas Job yang berhenti retry di `WhatsAppNotificationLog`.

### STEP 9: Job `SendLeaveRequestResultNotificationJob` (Notifikasi Balik) (v3.1)
**File:** `app/Jobs/SendLeaveRequestResultNotificationJob.php`
- Job ini mem-fotocopy logika relasional target tujuan template yang aslinya eksis di fungsi `IjinKehadiranDetail::sendNotificationToStudent()`.
- Membangun payload berdasar _status disetujui/ditolak_, memanggil service pengirim `sendMessage()`.
- Implementasi _Throwable fail fallback_ `failed(Throwable $e)` yang mencatat ke _error console_ & Notification Log bila 3 upaya gagal total.

### STEP 10: Placeholder Template di Admin
**File:** `app/Filament/Presensi/Pages/ManajemenNotifikasiWaPage.php`
- Tambahkan bantuan _Hint_ UI yang menyatakan Placeholder `{link_konfirmasi}` kini dapat dimanfaatkan di kolom Editor template ijin terkait.

---

## Alur Data & Transaksi
```
Siswa Submit Form Ijin
  --> DB LeaveRequest Created (pending status)
  --> Try/Catch: Generate Token API. Dispatch Job (afterCommit hook)

Job Antrian Pengirim WA (Guru)
  --> Idempotent Check Text (wa_text_sent_at). If null -> Send Gateway Text -> Update DB.
  --> Loop (Maks. 3 Lampiran Media):
       |--> Idempotent Check File Index (wa_sent_files). 
       |--> If not sent -> Send Gateway Media URL -> Update DB.
  (Exception thrown if API false -> Job retried properly until Tries=3 consumed)

Guru Klik Link WA
  --> GET /ijin-approval/{token}
  --> Tampil halaman Blade Konfirmasi (Mengkalkulasi 5 states handling function)

Guru Mengklik Setujui (Aksi POST CSRF Token)
  --> Controller DB Transaction Initialization
       |--> Panggil logic resolveState() kembali, lolos == 'confirm'
       |--> Atomik Update Query: set status = approved, token_used_at = now()
       |--> Panggil class helper syncAttendances()
       |--> Panggil recordLog() mengisi variabel log user_agent, IP.
  --> Commit Keseluruhan Transaction
  --> afterCommit Trigger: Lempar Job WA Balasan (Siswa) Queueing
  --> Tampil Halaman Purna (Sukses UI)
```

---

## STEP Testing (Feature Tests) (v3.1)
**File:** `tests/Feature/LeaveRequestApprovalWaTest.php`
Daftar _Assertion_ pokok testing (gunakan _Queue Faking_):
1. Pengunjungan GET `/ijin-approval/{token}` direspon HTTP status _200 OK_, tanpa mengubah bit kolom Token manapun.
2. Form submit POST rute yang ditarget dengan susunan token tidak ber-regex 8 huruf (kurang/lebih) me-return standar _HTTP 404_.
3. Proses `POST /action` disimulasikan menggunakan model berisi kolom kedaluwarsa, harus me-return konklusi halaman ber-state `expired`, bukan status `used`.
4. Uji **Kondisi Race**: Dilakukan dua (2) simulasi POST instance PHP beruntun dengan Payload dan Session persis di _Token String_ yang sama (misal `affected = 0`). Hanya 1 instance yang berhasil dan diakui _Commit_-nya, sisanya dibelokkan ke blade `used` yang jinak. Buktikan pula bahwa metode `Queue::fake()` mengonfirmasi Dispatch job notifikasi siswa benar-benar _hanya 1 (satu) entri yang tercipta_.
5. Pem-mockingan kegagalan / `Exception` melempar `generateApprovalToken()` terbukti dipendam oleh form Livewire (Aksi Try Catch) dan siswa disuguhi *success form submission* di UI.
6. Accessor `$hidden`: Memanggil Model eloquent `LeaveRequest::first()->toArray()` memvalidasi tidak eksisnya string key *approval_token*.
7. Rute `GET /ijin-media`: Validasi _MIME nosniff_ pada unduhan file asli 200, validasi penolakan 403 bila argumen signature Expired/dimanipulasi. Buktikan _Accessor_ sanggup men-Deduplikasi JSON kembar dan skip data yang sengaja di-_delete_ / hilang di storage.
8. Uji coba _Job_ WA: Bila parameter target Wali kelas di-Mock _Null_, buktikan Job me-return dan berlabuh damai tanpa Exception. Buktikan JSON Idempoten berjalan; API text `sendMessage()` tidak ditrigger di Loop percobaan Retry kedua.
9. Di tabel DB pasca aksi, buktikan kolom `approved_by` dan `approved_by_type` tetap `null` karena form via link WhatsApp.

---

## STEP Deployment / Ops (v3.1)
1. **Rollback Plan Pertama**: Jika penerapan menimbulkan problem, *revert* pull request Git code dan hapus Route dari source code terlebih dahulu. Hal ini akan memastikan semua _Link WhatsApp Guru_ yang beredar tertutup otomatis dan menghasilkan 404/403.
2. **Rollback DB**: Baru setelah langkah nomor 1 dikonfirmasi mati, lakukan eksekusi terminal `php artisan migrate:rollback --step=1`. Segenap ijin pending yang terhapus kolom tokennya dapat diproses staf sekolah menggunakan tombol Setujui yang ada di antarmuka web Portal Admin Filament seperti biasanya.
3. Konfigurasi _Worker Server_: Gunakan angka `timeout=150` pada program CLI _supervisord_ Anda, dan sesuaikan pula `retry_after=150` pada konfigurasi koneksi array database di file `config/queue.php`. Nilai limit `$timeout = 120` pada deklarasi Class Job akan memastikan Job sengaja melempar _exception timeout logic_ sesaat sebelum OS Supervisor me-KILL koneksi basisdata, mencegah _stuck process_ secara permanen.
4. Gunakan deklarasi _environment_ mutlak `APP_URL=https://...` dipadu perintah `URL::forceScheme('https')` di ServiceProvider web jika Anda me-routing server laravel di balik _reverse proxy nginx_. Parameter mutlak diwajibkan untuk merealisasikan pembuatan Signed-URL Media https SSL valid.

---

## Checklist
- [x] Plan v3.1 dibuat dan diverifikasi
- [x] STEP 1: Migration DB
- [x] STEP 2: Update Model `LeaveRequest`
- [x] STEP 3: Modifikasi `WhatsAppGatewayService`
- [x] STEP 4: Registrasi Routes (Approval & Media)
- [x] STEP 5: Controller `LeaveRequestApprovalController`
- [x] STEP 6: Blade View `leave-request-approval`
- [x] STEP 7: Integrasi ke `IjinKehadiranForm`
- [x] STEP 8: Buat `SendLeaveRequestWaNotificationJob`
- [x] STEP 9: Buat `SendLeaveRequestResultNotificationJob`
- [x] STEP 10: Perbarui tampilan template di Admin
- [x] STEP Testing
- [x] STEP Deployment

> **Self-Check Selesai:** 
> - Poin 1 (Accessor) tercakup komprehensif di Step 2, Job Step 8, dan _Testing_ #7.
> - Poin 2 (Alur approve lama) dievaluasi di Awal Verifikasi dan disamakan perilakunya di Controller eksekutor (Step 5). Job terpisah disediakan (Step 9).
> - Poin 3 (Approver null & log via `$changes`) telah diterapkan ke Step 5.
> - Poin 4 (State resolve resolver) sukses diisolasi ke sebuah method dedikasi di Step 5.
> - Poin 5 (Fillable protection & forceFill) telah diperjelas dan diterapkan dalam logic mutasi Model (Step 2, Step 8).
> - Poin 6 (Queue timeout setup config) diinstruksikan dalam detail nominal pada sub bab *Deployment* poin 3.
> - Poin 7 (Akses lampiran publik *hashName* disk public) dipertahankan dengan kalkulasi kompromi mitigasi (diterima) di Keputusan Desain.
> - Poin 8 (Ketahanan submit siswa `catch`) dilindungi pada Step 7, fallback failed Job ditegaskan.
> - Poin 9 (Gateway boolean contract vs exception handling) dikaji dan diakomodir penuh dalam perilaku Job & Service WA (Step 3 & 8).
> - Poin 10 (Regex where route & 419 bootstrap exception helper) dilibatkan di *routes* (Step 4).
> - Poin 11 (Re-write & Perluas cakupan Testing 9 sub bagian) telah didokumentasikan di *Testing*.
> - Poin 12 (Logika & hierarki Rollback Git-lalu-Migrate) telah tertuang pada poin 1 dan 2 *Deployment*.

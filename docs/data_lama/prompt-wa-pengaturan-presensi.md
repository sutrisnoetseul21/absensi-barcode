# Rencana Fitur: Pengaturan Notifikasi WhatsApp
## Modul Presensi & Perpustakaan — ERP Hafla IT Solution

Status: **SELESAI SEPENUHNYA (10 Agu 2026)**
Prioritas: Modul Presensi telah rampung, modul Perpustakaan bisa menyusul.

---

## 1. Latar Belakang

ERP sudah punya modul presensi (check-in/check-out) dan perpustakaan (peminjaman/pengembalian).
Sekarang perlu lapisan notifikasi WhatsApp untuk 2 kondisi eksepsi (bukan broadcast massal):

1. Siswa **terlambat / tidak hadir / sakit / izin** → notifikasi ke orang tua dan/atau wali kelas.
2. Peminjaman buku **telat dikembalikan** → notifikasi ke siswa dan/atau orang tua.

Backend WA gateway: **Evolution API** (self-hosted, Docker, di laptop dedicated i3 gen 10 / 4GB RAM).
Komunikasi Laravel → Evolution API lewat HTTP internal, idealnya lewat WireGuard (pola yang sama seperti akses antar-VPS Anda yang sudah ada).

---

## 2. Keputusan Arsitektur

### 2.1 Koneksi API WA: Global per-aplikasi (dikonfirmasi cocok dengan deployment Anda)

**Konfirmasi (09 Agu 2026):** ERP presensi & perpustakaan ini **instalasi terpisah per sekolah**
(bukan multi-tenant 1 database), sama seperti pola eRaport. Artinya kekhawatiran "beda sekolah
beda nomor WA" otomatis terpenuhi secara alami — setiap instalasi Laravel punya database sendiri,
jadi 1 baris singleton `whatsapp_settings` per aplikasi **sudah tepat**, tidak perlu kolom
`sekolah_id` atau desain multi-tenant tambahan.

**Masalah kalau disalin mentah antar-modul (bukan antar-sekolah):** kalau tabel/pengaturan koneksi
API digandakan di halaman presensi dan perpustakaan **dalam 1 instalasi yang sama**, Anda punya 2
sumber kebenaran untuk 1 nomor WA yang sama. Kalau token diganti, harus update 2 tempat — rawan
lupa salah satu, dan bisa bikin salah satu modul diam-diam berhenti kirim notifikasi tanpa Anda sadari.

**Solusi:** satu tabel `whatsapp_settings` (singleton, 1 baris) sebagai sumber kebenaran tunggal. Form pengaturan koneksinya tetap **ditampilkan di kedua halaman** (Presensi & Perpustakaan) untuk kemudahan akses — tapi keduanya membaca/menulis ke record yang sama. Kalau Anda ubah base URL/token dari halaman presensi, otomatis kepakai juga di perpustakaan.

Field yang disimpan:
- `base_url` — URL Evolution API (mis. `http://<wireguard-ip>:8080`)
- `api_key` — token API (**disimpan terenkripsi**, pakai Laravel encrypted cast)
- `instance_name` — nama session/instance WA yang dipakai
- `sender_number` — nomor WA pengirim (untuk display, bukan untuk otentikasi)
- `is_active` — master switch, matikan semua notifikasi WA tanpa hapus konfigurasi
- `delay_between_messages_seconds` — jeda antar pesan (anti-ban), default 3-5 detik
- `send_window_start`, `send_window_end` — jam kirim yang diizinkan (mis. 06:00–17:00), supaya tidak kirim notif tengah malam

### 2.2 Pengaturan Notifikasi: Per-Modul (yang memang disalin)

Berbeda dari koneksi API, ini **memang harus terpisah** karena logikanya beda antar modul.

**Desain penerima dibuat dinamis dari tabel `jabatans` yang sudah ada (dikonfirmasi 09 Agu 2026):**
awalnya direncanakan checkbox hardcode (Ortu/Wali Kelas/Guru BK/Kepala Sekolah), tapi ternyata
sistem **sudah punya infrastruktur role/jabatan terstruktur**: tabel `jabatans` (kolom
`nama_jabatan`) + pivot `teacher_jabatan` (relasi many-to-many lewat method `jabatans()` di
model `Guru`). Value yang sudah ada di seeder: Kepala Sekolah, Waka Kurikulum, Waka Kesiswaan,
Waka Sarpras, Waka Humas, **Guru BK**, Operator, Tata Usaha, **Pustakawan**, Pembina
Ekstrakurikuler, Kepala Lab.

Karena infrastrukturnya sudah ada, checkbox penerima di Fase 2 **tidak perlu hardcode** —
cukup ambil daftar jabatan dari `Jabatan::all()` secara dinamis, ditambah 2 opsi spesial yang
bukan jabatan tapi relasional: **Ortu** (dari `no_hp` siswa) dan **Wali Kelas** (dari relasi
`kelasAjarans()`, bukan dari tabel `jabatans`, karena itu penugasan per-kelas per-tahun-ajaran,
bukan jabatan struktural). Kalau nanti sekolah menambah jabatan baru di tabel `jabatans`,
otomatis muncul jadi opsi penerima tanpa perlu ubah kode sama sekali.

**Konvensi penyimpanan `recipients` (JSON array of string):**
- `"ortu"` — keyword spesial, resolve dari `no_hp` siswa
- `"wali_kelas"` — keyword spesial, resolve dari relasi `kelasAjarans()`
- Selain 2 keyword di atas, isi array adalah **`nama_jabatan` persis** (mis. `"Guru BK"`,
  `"Kepala Sekolah"`) — dipilih string nama (bukan ID) supaya tetap terbaca jelas di
  database/log meski tanpa join, dan tidak masalah karena `nama_jabatan` sudah jadi
  sumber kebenaran unik di seeder yang ada.

Contoh isi: `["ortu", "wali_kelas"]` atau `["ortu", "Guru BK", "Kepala Sekolah"]`.

**Manfaat tambahan untuk Fase 4 (salin ke Perpustakaan):** karena jabatan "Pustakawan" sudah
ada di tabel yang sama, komponen checkbox penerima ini bisa **dipakai ulang persis sama** di
halaman pengaturan perpustakaan nanti — tidak perlu desain ulang.

**Presensi** — tabel `presensi_notification_settings`, satu baris per status kehadiran.
Status yang sudah berjalan di sistem ada 6: **Hadir, Sakit, Izin, Alpa, Terlambat, Pulang**
(dikonfirmasi 09 Agu 2026). Semua 6 status tetap dibuatkan barisnya (fleksibel untuk
diaktifkan kapan saja), tapi default `is_active` untuk **Hadir** dan **Pulang** adalah **false**
karena di luar kebutuhan awal Anda (notifikasi eksepsi, bukan konfirmasi kehadiran normal).
Status **Sakit, Izin, Alpa, Terlambat** adalah kandidat utama yang relevan diaktifkan sejak awal.

| Status | Default is_active | Penerima (checkbox tersedia) | Template |
|---|---|---|---|
| Terlambat | bisa diaktifkan | Ortu / Wali Kelas / + semua jabatan dari tabel `jabatans` | text area + placeholder |
| Alpa | bisa diaktifkan | Ortu / Wali Kelas / + semua jabatan dari tabel `jabatans` | text area + placeholder |
| Sakit | bisa diaktifkan | Ortu / Wali Kelas / + semua jabatan dari tabel `jabatans` | text area + placeholder |
| Izin | bisa diaktifkan | Ortu / Wali Kelas / + semua jabatan dari tabel `jabatans` | text area + placeholder |
| Hadir | false (opsional ke depan) | Ortu / Wali Kelas / + semua jabatan dari tabel `jabatans` | text area + placeholder |
| Pulang | false (opsional ke depan) | Ortu / Wali Kelas / + semua jabatan dari tabel `jabatans` | text area + placeholder |

Catatan: kolom "Penerima" di atas adalah **pilihan yang tersedia**, bukan berarti semua
diaktifkan sekaligus per status — tiap baris status bisa pilih kombinasi berbeda (mis.
"Alpa" kirim ke Ortu + Wali Kelas + Kepala Sekolah, sementara "Terlambat" cukup Ortu saja).

**Perpustakaan** — tabel `perpustakaan_notification_settings`, satu baris per jenis event:

| Event | is_active | Penerima | Template |
|---|---|---|---|
| Buku Telat Dikembalikan | toggle | checkbox: Siswa / Ortu / Pustakawan | text area + placeholder |
| Reminder H-1 Jatuh Tempo (opsional) | toggle | checkbox: Siswa / Ortu | text area + placeholder |

### 2.3 Placeholder Template Pesan

Supaya template bisa dipakai ulang dan dinamis, dukung placeholder minimal:

```
{nama_siswa}, {kelas}, {tanggal}, {jam}, {status_kehadiran},
{nama_wali_kelas}, {nama_sekolah}, {judul_buku}, {tanggal_jatuh_tempo}, {denda}
```

Placeholder yang tidak relevan untuk suatu event cukup diabaikan/tidak tersedia di editor untuk event itu (mis. `{judul_buku}` tidak muncul di template presensi).

### 2.4 Sumber Nomor HP Penerima

**Dikonfirmasi 09 Agu 2026:**
- Nomor HP orang tua **sudah ada** di tabel siswa/wali. Nama kolom persis tetap perlu
  dikonfirmasi Antigravity saat investigasi Fase 1 (lihat prompt), tapi tidak perlu migration baru.
- Nomor HP wali kelas **sudah ada**, termasuk relasi guru ↔ kelas. Sama seperti di atas,
  nama kolom/relasi persis dikonfirmasi saat Fase 1.
- **Format sudah `628xxxxxxxxxx`** — sesuai format yang dibutuhkan WhatsApp/Evolution API.
  Artinya **tidak perlu langkah normalisasi nomor** di fase awal. Cukup tambahkan validasi
  ringan (guard clause) untuk berjaga-jaga kalau ada data lama yang formatnya beda
  (`08xxx`/`+62xxx`), tapi ini bukan prioritas, bisa ditangani belakangan kalau memang ditemukan.

### 2.5 Log Pengiriman (Audit Trail)

Tabel `whatsapp_notification_logs`:
- `recipient_type` (ortu/wali_kelas/siswa/pustakawan)
- `recipient_number`
- `message` (hasil render template)
- `status` (pending/sent/failed)
- `response_payload` (respons mentah dari Evolution API, untuk debugging)
- `related_type`, `related_id` (polymorphic — siswa_id / peminjaman_id)
- `sent_at`

Berguna untuk: (a) audit kalau orang tua komplain "tidak dapat notif", (b) debugging kalau Evolution API down, (c) dasar retry job yang gagal.

### 2.6 Alur Kerja (Flow)

```
Event terjadi (status presensi diubah jadi Terlambat/Alpha/Sakit/Izin)
        │
        ▼
Observer/Listener model Presensi
        │
        ▼
Cek presensi_notification_settings untuk status ini — aktif?
        │  ya
        ▼
Ambil daftar penerima sesuai checkbox (Ortu/Wali Kelas) + nomor HP
        │
        ▼
Render template dengan data siswa (placeholder → nilai aktual)
        │
        ▼
Dispatch Job ke queue (bukan panggil langsung — biar tahan gangguan WA gateway)
        │
        ▼
Job: panggil WhatsAppGatewayService → HTTP ke Evolution API
        │
        ▼
Simpan hasil ke whatsapp_notification_logs (sent/failed)
        │
        ▼
Kalau failed → retry otomatis (job retry Laravel, backoff bertahap)
```

---

## 3. Halaman Filament yang Dibuat/Dimodifikasi

- `admin-presensi/pengaturan-presensi` — tambah section baru **"Notifikasi WhatsApp"**:
  - Sub-section 1: Koneksi API (shared, baca/tulis ke `whatsapp_settings`)
  - Sub-section 2: Aturan Notifikasi Presensi (per status, baca/tulis ke `presensi_notification_settings`)
  - Tombol "Test Koneksi" untuk kirim pesan uji ke nomor tertentu
- `admin-perpustakaan/pengaturan-perpustakaan` — pola identik, disalin setelah presensi selesai & teruji:
  - Sub-section 1: Koneksi API (record yang **sama**, bukan baru)
  - Sub-section 2: Aturan Notifikasi Perpustakaan

---

## 4. Tahapan Eksekusi (Staged Review — sesuai pola kerja biasa)

| Fase | Isi | Gate |
|---|---|---|
| 1 | Migration + Model (`whatsapp_settings`, `presensi_notification_settings`, `whatsapp_notification_logs`) — tanpa UI dulu | Stop & report sebelum lanjut |
| 2 | Filament Settings Page: form UI untuk koneksi API + aturan notifikasi presensi, termasuk validasi & tombol test-koneksi | Stop & report, review UI |
| 3 | Service layer (`WhatsAppGatewayService`) + Job (`SendWhatsAppNotificationJob`) + trigger otomatis saat status presensi berubah | Stop & report, uji end-to-end di lingkungan testing |
| 4 | Setelah Presensi stabil terverifikasi jalan, salin pola ke Perpustakaan (model, migration, halaman) | Stop & report |

---

## 5. Status Konfirmasi (09 Agu 2026)

Semua pertanyaan open di versi draft sebelumnya sudah terjawab:

| Pertanyaan | Jawaban | Dampak ke Fase 1 |
|---|---|---|
| Nomor HP ortu/wali sudah ada? | Ya, sudah ada | Tidak perlu migration kolom baru, tinggal cari nama kolom persis |
| Nomor HP & relasi wali kelas sudah ada? | Ya, sudah ada | Sama seperti di atas |
| Status presensi apa saja? | Hadir, Sakit, Izin, Alpa, Terlambat, Pulang (6) | Seeder Fase 1 diperluas jadi 6 baris, bukan 4 |
| Format nomor HP tersimpan? | `628xxxxxxxxxx` | Tidak perlu service normalisasi nomor |
| Global vs per-sekolah untuk `whatsapp_settings`? | Instalasi terpisah per sekolah | Desain singleton per-aplikasi sudah tepat, tidak perlu `sekolah_id` |

**Sisa yang tetap perlu dikonfirmasi Antigravity sendiri saat investigasi Fase 1** — **SUDAH DIINVESTIGASI & DIKONFIRMASI (09 Agu 2026):**

| Item | Temuan Aktual |
|---|---|
| Nomor HP orang tua | Tabel `students`, kolom `no_hp` (ditambahkan migration `2026_08_08_132115_add_no_hp_to_users_teachers_students_tables.php`). **Dikonfirmasi 09 Agu 2026: kolom `no_hp` siswa memang diisi nomor orang tua** (bukan nomor pribadi siswa) — aman dipakai langsung untuk notifikasi. |
| Nomor HP wali kelas | Tabel `teachers`, kolom `no_hp` |
| Relasi wali kelas | Model `App\Models\Guru` → relasi `kelasAjarans()` (plural) → tabel `class_academic_year`, dengan `teacher_id` sebagai penanda wali kelas untuk `class_id` + `academic_year_id` tertentu. **Masih perlu divalidasi sebelum Fase 3:** karena relasinya jamak, apakah `teacher_id` di tabel ini khusus wali kelas, atau tercampur dengan assignment guru mata pelajaran biasa. |
| Enum status presensi (lowercase, sesuai migration `create_attendances_table` & `upgrade_attendances_table_for_scan_out`) | `hadir`, `sakit`, `izin`, `alpa`, `telat` (kolom `status` — saat check-in), dan `pulang`, `sakit`, `izin`, `alpa` (kolom `status_pulang` — saat check-out). **Masih perlu diputuskan sebelum Fase 3:** `sakit`/`izin`/`alpa` bisa muncul di 2 kolom berbeda (check-in maupun check-out) — apakah keduanya memicu notifikasi terpisah atau perlu dedup supaya orang tua tidak dapat 2 notif untuk kejadian yang sama. |

**Catatan penamaan value enum untuk seeder Fase 1:** gunakan value persis dari kode —
`hadir`, `sakit`, `izin`, `alpa`, `telat` (bukan `terlambat`), `pulang`.

## 6. Catatan Eksekusi Fase 1 (Log Perbaikan)

Fase 1 sempat menyimpang sekali dari desain: migration awal `whatsapp_settings` sempat
ditambahi kolom `module` (unique) dengan maksud memisahkan baris per-modul (presensi vs
perpustakaan). Ini **bertentangan dengan keputusan 2.1** (koneksi API harus singleton
1 baris untuk seluruh aplikasi, karena 1 sekolah = 1 nomor WA). Sudah dikoreksi:
- Migration tambahan `remove_module_from_whatsapp_settings_table` men-drop kolom `module`.
- Method `WhatsAppSetting::current()` diubah jadi singleton murni: `firstOrCreate(['id' => 1], [...])`,
  tanpa parameter `$module`.
- Tabel kosong saat perbaikan dilakukan (0 baris), jadi tidak ada risiko kehilangan data.

**Pelajaran untuk fase-fase berikutnya:** saat instruksi ke Antigravity di Fase 3/4 nanti,
tegaskan ulang secara eksplisit "singleton, tanpa kolom pemisah modul apa pun" supaya tidak
terulang, karena istilah "per-modul" di percakapan bisa disalahartikan sebagai "per-baris
di tabel yang sama" alih-alih "1 tabel dipakai bersama, hanya UI-nya yang muncul di 2 tempat".

**Status akhir Fase 1 (dikonfirmasi 10 Agu 2026):** migration, model, dan seeder untuk
`whatsapp_settings` (singleton murni), `presensi_notification_settings` (6 baris status),
dan `whatsapp_notification_logs` sudah selesai dan berjalan tanpa error. **Fase 2 disetujui
untuk dimulai.**

**Status akhir Fase 2 (dikonfirmasi 10 Agu 2026):** UI Filament untuk koneksi API dan aturan
notifikasi presensi sudah jalan. Sempat ada 2 bug (namespace `Filament\Forms\Components\Actions`
tidak ditemukan di Filament v4, dan tabel `jabatans` kosong di environment testing sehingga
opsi checkbox jabatan tidak muncul) — keduanya sudah diperbaiki dan diverifikasi manual oleh
Sutrisno di browser. **Fase 2 selesai dan lolos verifikasi.**

**Status investigasi relasi wali kelas (dikonfirmasi 10 Agu 2026) — RESOLVED:**
Struktur database sudah rapi terpisah, tidak tercampur seperti yang dikhawatirkan:
- **Wali Kelas:** tabel `class_academic_year` (model `KelasAjaran`), kolom `teacher_id`.
  1 baris = 1 wali kelas untuk 1 kelas pada 1 tahun ajaran. Murni khusus wali kelas.
- **Guru Mapel:** tabel terpisah `pengajarans` (model `Pengajaran`), menghubungkan
  `class_academic_year_id` + `teacher_id` + `mata_pelajaran_id`. Tidak bersinggungan
  dengan penentuan wali kelas.
- **Tahun ajaran aktif:** model `TahunAjaran` (tabel `academic_years`) punya kolom `status`
  (`aktif`/`arsip`) dan query scope `TahunAjaran::aktif()->first()` yang siap pakai. Ada
  juga sinkronisasi otomatis ke `pengaturan_sekolahs.academic_year_id_active`.

**Fase 3A disetujui untuk mulai dieksekusi.**

**Log setup jaringan (10 Agu 2026) — RESOLVED:** Evolution API dipasang di laptop dedicated
(`10.77.77.21:8088` via WireGuard), tapi VM Laravel (`192.168.122.50`) awalnya tidak bisa
menjangkaunya karena trafik dari subnet QEMU (`192.168.122.0/24`) belum di-forward/NAT ke
tunnel WireGuard. Diperbaiki dengan menambahkan rule MASQUERADE di VPS host (bukan di VM,
bukan di laptop) lewat `PostUp`/`PostDown` di `/etc/wireguard/wg0.conf`:
```
iptables -t nat -A POSTROUTING -s 192.168.122.0/24 -o %i -j MASQUERADE
```
Disimpan persisten via `netfilter-persistent save`. Firewall laptop (dikelola HestiaCP,
bukan UFW) sudah membatasi port 8088 hanya dari `10.77.77.0/24` — dikonfirmasi tidak
memblokir jalur ini karena trafik dari VM ter-masquerade jadi terlihat datang dari `10.77.77.1`
(IP WireGuard VPS), yang termasuk dalam subnet yang diizinkan.

**Catatan untuk debugging jaringan serupa di masa depan:** satu arah saja yang perlu jalan
(VM → laptop), BUKAN dua arah. `ping` dari laptop ke IP asli VM (`192.168.122.50`) akan
selalu gagal by design (laptop tidak pernah melihat IP asli VM, hanya IP VPS hasil
masquerade) — ini bukan indikasi masalah. Verifikasi jalur harus selalu dilakukan dengan
`curl` dari DALAM VM yang bersangkutan (cek `hostname -I` dulu untuk pastikan benar-benar
di VM yang dimaksud, karena satu VPS bisa punya banyak akun/VM lewat HestiaCP yang mirip
tapi berbeda), bukan dari peer WireGuard lain yang punya jalur langsung sendiri.

Base URL final: `http://10.77.77.21:8088`, terverifikasi reachable dari VM
`projek-absensi-barcode` per 10 Agu 2026.

## 7. Fitur Baru: Laporan Harian Kelas untuk Wali Kelas (ditambahkan 10 Agu 2026)

Di luar rencana awal (notifikasi real-time per kejadian), muncul kebutuhan baru: **laporan
terjadwal harian** per kelas untuk wali kelas — mekanismenya beda total dari sistem yang
sudah dirancang, jadi didokumentasikan terpisah di sini.

### 7.1 Perbedaan dengan sistem yang sudah ada

| | Notifikasi Real-time (sudah dirancang) | Laporan Harian (baru) |
|---|---|---|
| Trigger | Event (status presensi siswa berubah) | Terjadwal (cron, 1x/hari setelah jam cutoff) |
| Granularitas | Per siswa, per kejadian | Per kelas, agregat 1 hari |
| Penerima | Ortu (utama), opsional Wali Kelas/jabatan lain | Wali Kelas |
| Tabel settings | `presensi_notification_settings` | Baru: `presensi_daily_report_settings` |

### 7.2 Contoh pesan (dari Sutrisno, 10 Agu 2026)

> "Laporan Kelas 7A total 32 siswa dengan rincian Hadir 30, terlambat 2 orang dan Alpa 2 orang.
> Mohon maaf bapak/ibu wali kelas, ananda [nama] belum presensi. Jika memang tidak hadir,
> silakan mengisi keterangan di web."

### 7.3 Kategori baru: "Belum Presensi"

Ini **bukan** salah satu dari 6 status yang sudah ada (`hadir`, `sakit`, `izin`, `alpa`,
`telat`, `pulang`) — ini murni siswa yang **belum punya record presensi sama sekali** hari itu
sampai jam cutoff. Logic: ambil semua siswa aktif di kelas tersebut, kurangi siswa yang sudah
punya record attendance hari ini (apapun statusnya) → sisanya adalah "belum presensi", dan
**nama-namanya disebut eksplisit** di pesan (beda dari status lain yang cukup angka).

### 7.4 Skema tabel baru: `presensi_daily_report_settings`

- `is_active` (boolean, default false)
- `cutoff_time` (time, default `08:00:00`) — jam job jalan & jam evaluasi "belum presensi"
- `template_pesan` (text) — placeholder: `{nama_kelas}`, `{tanggal}`, `{total_siswa}`,
  `{jumlah_hadir}`, `{jumlah_terlambat}`, `{jumlah_alpa}`, `{jumlah_sakit}`, `{jumlah_izin}`,
  `{daftar_belum_presensi}` (nama-nama, dipisah baris/koma, atau teks "Tidak ada" kalau kosong)
- `recipients` (JSON array, pola sama seperti sebelumnya — default `["wali_kelas"]`, tapi
  tetap dinamis kalau nanti mau ditambah Kepala Sekolah dsb.)
- timestamps

### 7.5 Kebutuhan infrastruktur tambahan

Fitur ini butuh **Laravel Task Scheduling** aktif — perlu dipastikan server tempat Laravel
ini di-deploy sudah ada cron entry:
```
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```
Kalau belum ada, ini perlu ditambahkan sebelum fitur ini bisa berjalan otomatis.

### 7.6 Sequencing yang disarankan

Karena ini mekanisme berbeda dari trigger real-time, disarankan dipecah jadi sub-fase
terpisah di bawah payung "Fase 3":
- **Fase 3A** — Service layer bersama (`WhatsAppGatewayService`) yang dipakai baik oleh
  trigger real-time maupun laporan harian.
- **Fase 3B** — Trigger real-time per-event untuk status presensi (sesuai rencana awal,
  termasuk dedup guard: 1 status hanya kirim 1x meski tercatat di kolom `status` maupun
  `status_pulang`). Catatan konteks (10 Agu 2026): status Sakit/Izin/Alpa biasanya
  **diisi manual oleh Wali Kelas sendiri** lewat web (bukan dari mesin scan), jadi
  real-time notif ke Wali Kelas untuk 3 status itu kurang relevan (dia sendiri pelakunya) —
  tapi tetap dibiarkan sebagai opsi checkbox yang bisa di-uncheck manual, bukan dihapus dari
  sistem.
- **Fase 3C** — Scheduled job laporan harian **per kelas** (fitur di bagian 7.4-7.5),
  dikirim ke Wali Kelas.
- **Fase 3D (baru, ditambahkan 10 Agu 2026)** — Scheduled job **rekap seluruh sekolah**,
  dikirim ke pihak yang butuh gambaran lintas kelas (mis. Kepala Sekolah, Waka Kesiswaan).

Masing-masing tetap pakai pola stop-and-report per sub-fase.

### 7.7 Fase 3D — Rekap Seluruh Sekolah

Beda dari 7.4 (yang isinya 1 kelas per pesan, dikirim ke wali kelas masing-masing kelas),
ini **1 pesan berisi rekap semua kelas sekaligus**, dikirim ke pihak level sekolah (bukan
per-kelas).

**Contoh dari Sutrisno (10 Agu 2026):**
> "Laporan presensi hari ini:
> 7A Hadir 30 Sakit 2 atas nama ini dan ini
> 7B Hadir 32 Siswa
> 7C dst"

**Skema tabel baru: `presensi_school_summary_settings`**
- `is_active` (boolean, default false)
- `cutoff_time` (time) — kemungkinan bisa sama atau beda dari cutoff per-kelas (7C), perlu
  dipastikan ke Sutrisno saat drafting prompt Fase 3D — masuk akal kalau rekap sekolah
  dikirim agak lebih siang dari laporan per-kelas, supaya semua wali kelas sempat
  mengisi status manual dulu.
- `template_pesan` (text) — header/intro bisa pakai placeholder `{tanggal}`, tapi baris
  per-kelas (nama kelas + rincian) di-generate otomatis oleh sistem, bukan placeholder
  manual tunggal (karena jumlah kelas dinamis) — perlu dirancang sebagai "template baris
  per kelas" yang di-loop, digabung jadi 1 pesan utuh. Detail format ini dirancang saat
  Fase 3D, bukan sekarang.
- `recipients` (JSON array) — **berbeda dari pola sebelumnya**: TIDAK ada opsi `ortu` atau
  `wali_kelas` di sini (karena laporan ini levelnya sekolah, bukan per-siswa/per-kelas),
  cukup opsi dinamis dari tabel `jabatans` saja (mis. Kepala Sekolah, Waka Kesiswaan, dst).
- timestamps

**Pertanyaan yang masih perlu dijawab sebelum Fase 3D dieksekusi:**
1. Jam kirim rekap sekolah — sama dengan jam laporan per-kelas (7C), atau lebih siang?
2. Apakah rincian nama (mis. "Sakit 2 atas nama ini dan ini") ditampilkan untuk semua status
   di semua kelas, atau cukup untuk status tertentu saja (mis. Alpa dan Sakit disebut nama,
   Terlambat cukup angka) — supaya pesan tidak kepanjangan kalau sekolahnya banyak kelas.
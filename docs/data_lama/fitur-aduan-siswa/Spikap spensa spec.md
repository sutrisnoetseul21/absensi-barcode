# SPIKAP SPENSA — Spesifikasi Fitur Tambahan & Prompt AI Agent

> **Catatan cakupan:** dokumen ini HANYA membahas fitur tambahan **SPIKAP**, bukan dokumentasi keseluruhan sistem ERP sekolah. Stack yang dipakai murni **Laravel** (backend) + **MySQL** (database), dengan Filament v4 sebagai admin panel yang sudah berjalan di atas stack tersebut.

---

## 1. Latar Belakang & Instruksi Asli Kepala Sekolah

Kepala sekolah SMPN 1 Majenang (SPENSA) meminta dibuatkan aplikasi **anti-perundungan berbasis web** yang memberi ruang aman bagi siswa untuk melapor secara jujur dan berbasis data. Poin instruksi asli:

1. Aplikasi web anti-perundungan, dikoordinasikan Pak Mulyanto & Pak Sutrisno.
2. Fitur pelaporan dengan **2 sifat laporan**:
   - **Biasa** → terhubung ke spreadsheet admin guru BK *(versi awal KS; di implementasi ini diganti masuk sistem, lihat §2)*.
   - **Darurat** → *(versi awal KS)* langsung ke WhatsApp Kepala Sekolah *(diganti, lihat §2)*.
   - Fasilitas unggah bukti multimedia: foto, screenshot (cyberbullying), rekaman suara/video.
3. Nama aplikasi: **SPIKAP SPENSA** (Sistem Pelaporan Integratif Konflik & Anti-Perundungan).
4. Manfaat strategis yang diharapkan:
   - **Anonimitas & keamanan identitas** — pelapor tidak takut diintimidasi balik atau dicap "tukang adu"; mendorong siswa saksi (bystander) untuk ikut lapor.
   - **Aksesibilitas berbasis web** — tanpa instal aplikasi, bisa diakses 24/7 dari HP/tablet/komputer, termasuk untuk kejadian di luar jam sekolah.
   - **Alur penanganan terintegrasi** — status laporan (Diterima / Dalam Investigasi / Selesai) yang transparan tanpa membuka identitas pelapor ke pihak yang tidak berhak.
   - **Pengelolaan data & deteksi dini** — audit trail rapi, pemetaan pola perundungan (jenis, lokasi, waktu rawan).
   - **Media edukasi** — bisa memuat materi edukasi, kode etik siswa, kontak darurat.

---

## 2. Penyesuaian Teknis dari Sutrisno (beda dari permintaan literal KS)

| Poin | Versi Kepala Sekolah | Versi Implementasi Sutrisno |
|---|---|---|
| Laporan darurat | Langsung ke WA pribadi KS (di luar sistem) | **Tetap wajib masuk & tercatat di sistem dulu** — WA hanya jadi kanal notifikasi otomatis, bukan jalur pelaporan utama |
| Penerima notifikasi darurat | KS saja | **Wali Kelas + Kepala Sekolah** (default), dan bisa ditambah lewat pengaturan admin — lihat §4 poin 3 |
| Channel notifikasi | Manual (siswa WA sendiri) | Otomatis dari Laravel, memakai infra WA gateway yang sudah ada (Evolution API self-hosted) |

**Alasan perubahan ini penting ditulis eksplisit ke AI agent:** tanpa masuk sistem dulu, laporan darurat tidak punya audit trail, tidak bisa di-tracking statusnya, dan rawan hilang/tidak ditindaklanjuti kalau HP KS penuh/ganti nomor. WA = notifikasi tambahan, bukan pengganti pencatatan sistem.

---

## 3. Spesifikasi Fitur SPIKAP

### 3.1 Sisi Siswa (setelah login)
- Menu baru **"SPIKAP"** di portal siswa.
- Form laporan:
  - Sifat laporan: **Biasa** / **Darurat**.
  - **Jika Biasa** → siswa memilih tujuan laporan: **Guru BK** atau **Wali Kelas** (dropdown pilihan, bukan otomatis).
  - **Jika Darurat** → otomatis dikirim ke **Wali Kelas DAN Kepala Sekolah** sekaligus (tidak perlu siswa pilih).
  - Jenis perundungan: fisik, verbal, sosial, digital/cyberbullying, lainnya.
  - Uraian kejadian, lokasi & waktu kejadian (opsional, untuk pemetaan pola).
  - Upload bukti multimedia: **foto dan video** (lihat §4 poin 4 soal storage).
  - Pilihan visibilitas laporan: **Disembunyikan** (identitas pelapor tidak ditampilkan ke guru penerima) atau **Arsip biasa** (identitas terlihat). Identitas siswa **tetap tercatat di database** dalam kedua kasus (karena siswa login), pilihan ini hanya mengatur apa yang tampil ke guru — bukan anonim penuh.
- Tracking status laporan milik sendiri: **Diterima → Dalam Investigasi → Selesai**.

### 3.2 Sisi Penerima (tetap di dalam Portal Guru yang sudah ada — bukan panel terpisah)
- Menu SPIKAP muncul sebagai **submenu baru di Portal Guru**, dengan **role/permission baru** khusus (misal `spikap.view`, `spikap.handle`) yang di-assign ke Guru BK, Wali Kelas, Waka, dan Kepala Sekolah sesuai kebutuhan — tapi tetap 1 portal, bukan aplikasi/panel terpisah.
- Laporan **biasa** → masuk ke antrian **Guru BK** atau **Wali Kelas**, sesuai pilihan siswa saat lapor.
- Laporan **darurat** → masuk sistem **+** trigger notifikasi WA otomatis ke **Wali Kelas** dan **Kepala Sekolah**.
- Penerima yang menangani bisa update status, tulis catatan tindak lanjut, tandai selesai.

### 3.3 Notifikasi WhatsApp
- Reuse infrastruktur WA gateway yang sudah dibangun (Evolution API, self-hosted, arsitektur 4 fase: gateway service, real-time trigger, laporan harian, ringkasan sekolah).
- Trigger baru: **laporan SPIKAP darurat masuk** → kirim WA ke Wali Kelas + Kepala Sekolah (default).
- **Daftar penerima notifikasi WA untuk laporan darurat harus bisa diatur lewat panel admin** — misalnya admin bisa memilih apakah cukup KS saja, atau KS + Wali Kelas + Guru BK, dst. Bukan hardcode di kode.

### 3.4 Data & Analitik
- Audit trail semua laporan (siapa handle, kapan, perubahan status).
- Dashboard pemetaan pola: jenis perundungan terbanyak, lokasi rawan, waktu rawan (untuk KS/BK, bukan siswa).
- Tidak perlu fitur eskalasi otomatis (SLA timeout) — di luar cakupan versi ini.

---

## 4. Keputusan yang Sudah Diambil (sebelumnya jadi pertanyaan terbuka)

1. **Routing laporan**: laporan **Biasa** → siswa memilih sendiri tujuan (**Guru BK** atau **Wali Kelas**). Laporan **Darurat** → otomatis ke **Wali Kelas + Kepala Sekolah**, tidak ada pilihan.
2. **Identitas & anonimitas**: identitas siswa **selalu tercatat di database** (siswa wajib login). Yang bisa diatur hanya visibilitas ke guru: **disembunyikan** dari tampilan guru, atau **arsip biasa** dengan identitas terlihat. Ini pilihan per-laporan yang dibuat siswa saat submit.
3. **Konfigurasi penerima notifikasi darurat**: bisa diatur lewat **admin settings** — default Wali Kelas + KS, tapi admin bisa menambah penerima lain (mis. Guru BK, Waka) tanpa perlu ubah kode.
4. **Penyimpanan bukti multimedia**: perlu dukungan **foto dan video** (video butuh perhatian ekstra: ukuran file lebih besar, perlu batas ukuran, format yang didukung, dan strategi storage — lihat prompt di §5 poin 5).
5. **Eskalasi otomatis**: **tidak diperlukan** untuk versi ini.
6. **Role & permission**: **perlu role baru** khusus untuk menu SPIKAP (bukan reuse role generik yang ada), tapi menu tetap tampil **di dalam Portal Guru** yang sudah ada — bukan panel/aplikasi terpisah.

---

## 5. Prompt Lengkap untuk AI Agent (Antigravity) — Tahap Analisa & Perencanaan

```
KONTEKS PROYEK
Saya mengembangkan sistem ERP sekolah berbasis Laravel + MySQL (nama proyek:
projek-absensi-barcode), dengan Filament v4 sebagai admin panel yang berjalan di atas
stack tersebut. Sistem yang SUDAH BERJALAN mencakup: single-auth (satu guard `web` di
tabel users, dengan tabel profil terpisah untuk guru & siswa), Portal Guru (Filament
panel) dengan berbagai role (wali kelas, guru BK, waka, kepala sekolah, admin), Portal
Siswa, modul kehadiran barcode, modul ijin kehadiran, modul perpustakaan, serta sistem
notifikasi WhatsApp otomatis — dibangun di atas Evolution API self-hosted (Docker),
dengan arsitektur 4 fase: gateway service, real-time trigger, laporan harian kelas,
ringkasan sekolah. SEMUA notifikasi baru WAJIB reuse gateway WA yang sudah ada, jangan
bikin integrasi WA baru dari nol.

Dokumen ini HANYA membahas satu fitur tambahan: SPIKAP. Jangan asumsikan perubahan di
luar cakupan ini.

TUGAS
Kepala sekolah meminta fitur pelaporan anti-perundungan bernama SPIKAP (Sistem
Pelaporan Integratif Konflik & Anti-Perundungan) — menu baru di Portal Siswa setelah
login, dan submenu baru di Portal Guru yang sudah ada (bukan panel terpisah). Sebelum
menulis kode apa pun, lakukan ANALISA MENYELURUH dan susun RENCANA IMPLEMENTASI
BERTAHAP (gate-per-phase, setiap fase saya test manual sebelum lanjut — JANGAN commit
otomatis tanpa konfirmasi saya).

REQUIREMENT FUNGSIONAL (sudah final, jangan diubah tanpa konfirmasi)
1. Siswa yang login membuat laporan lewat menu "SPIKAP" dengan field: sifat laporan
   (Biasa / Darurat), jenis perundungan (fisik/verbal/sosial/digital/lainnya), uraian
   kejadian, lokasi & waktu (opsional), upload bukti multimedia (foto dan video), dan
   pilihan visibilitas laporan (Disembunyikan dari guru / Arsip biasa dengan identitas
   terlihat). Identitas siswa SELALU tersimpan di database (karena wajib login) —
   pilihan visibilitas hanya memengaruhi apa yang ditampilkan ke guru penerima, bukan
   anonimitas penuh di level data.
2. Routing tujuan laporan:
   - Laporan "Biasa" → siswa memilih sendiri tujuan: Guru BK ATAU Wali Kelas.
   - Laporan "Darurat" → otomatis ke Wali Kelas DAN Kepala Sekolah (tanpa pilihan
     siswa).
3. ATURAN BISNIS YANG TIDAK BOLEH DIUBAH: laporan "Darurat" TETAP HARUS masuk dan
   tersimpan di sistem terlebih dahulu (bukan langsung dikirim WA saja tanpa tercatat).
   Sistem baru mengirim notifikasi WA otomatis (via gateway Evolution API yang sudah
   ada) ke Wali Kelas dan Kepala Sekolah SETELAH laporan tersimpan di database. WA
   adalah kanal notifikasi tambahan, bukan jalur pelaporan pengganti sistem.
4. Daftar penerima notifikasi WA untuk laporan darurat harus BISA DIATUR lewat panel
   admin (default: Wali Kelas + Kepala Sekolah; admin bisa menambah penerima lain
   seperti Guru BK atau Waka tanpa perlu ubah kode/hardcode).
5. Laporan bisa berstatus: Diterima → Dalam Investigasi → Selesai. Siswa pelapor bisa
   memantau status laporannya sendiri.
6. Perlu ROLE/PERMISSION BARU khusus untuk fitur SPIKAP (jangan reuse role generik
   yang sudah ada untuk hal lain), tapi menu SPIKAP tetap tampil DI DALAM Portal Guru
   yang sudah ada — bukan aplikasi atau panel Filament terpisah.
7. Sistem mencatat audit trail lengkap (siapa handle, kapan, perubahan status) dan
   menyediakan data untuk analitik pola perundungan (jenis, lokasi, waktu) bagi
   KS/BK. Tidak perlu fitur eskalasi otomatis (SLA timeout) di versi ini.

YANG SAYA MINTA DARI ANDA (output tahap ini — analisa & plan, BUKAN kode)
1. Rancangan skema database MySQL (tabel, kolom penting, relasi ke users/students/
   teachers yang sudah ada) untuk laporan SPIKAP, lampiran bukti (foto/video), log
   status, audit trail, dan tabel konfigurasi penerima notifikasi darurat (yang bisa
   diatur admin).
2. Rancangan logika routing/assignment sesuai requirement #2 di atas — termasuk
   bagaimana sistem tahu siapa "Wali Kelas" dari siswa yang lapor (relasi kelas siswa
   → wali kelas yang sudah ada di sistem).
3. Rancangan integrasi notifikasi WA: titik trigger di kode, cara memakai gateway
   Evolution API yang sudah ada tanpa mengubah arsitektur 4 fase yang berjalan,
   bagaimana daftar penerima dibaca dari konfigurasi admin (bukan hardcode), dan
   format pesan WA yang menghormati pilihan visibilitas laporan (tidak membocorkan
   identitas jika siswa pilih "Disembunyikan").
4. Rancangan struktur Filament: resource/page baru apa yang dibutuhkan di Portal
   Siswa dan sebagai submenu baru di Portal Guru, plus desain role/permission baru
   (nama role, permission apa saja) memakai Spatie permission yang sudah dipakai di
   sistem.
5. Rekomendasi penyimpanan bukti foto & video: disk driver (local vs cloud), validasi
   ukuran & format file, kompresi/thumbnail untuk video jika perlu, dan pertimbangan
   kapasitas storage server karena video jauh lebih besar dari foto.
6. Rencana implementasi bertahap (fase 1, 2, 3, ...) dengan definisi "selesai" yang
   jelas per fase, mengikuti pola kerja saya: setiap fase harus saya test manual dan
   approve dulu sebelum fase berikutnya dimulai.
7. Jika ada hal yang menurut Anda masih ambigu di luar requirement final di atas,
   TANYAKAN dulu ke saya — jangan berasumsi sendiri untuk keputusan yang berdampak
   besar ke desain.

BATASAN
- Jangan menulis kode implementasi pada tahap ini — fokus ke analisa dan rencana.
- Jangan mengganti arsitektur WA gateway yang sudah berjalan, hanya menambah trigger
  baru di dalamnya.
- Jangan bikin panel/aplikasi terpisah untuk SPIKAP — harus menyatu di Portal Guru
  dan Portal Siswa yang sudah ada.
- Prioritaskan solusi open-source/gratis, konsisten dengan stack yang sudah dipakai
  (Laravel, MySQL, Filament v4, Evolution API self-hosted).
```

---

### Catatan
Semua 6 pertanyaan terbuka dari versi sebelumnya sudah dijawab dan dipindah ke §4. Prompt di §5 sudah diperbarui mengikuti keputusan tersebut dan siap dilempar ke AI agent kapan pun kamu mau mulai.
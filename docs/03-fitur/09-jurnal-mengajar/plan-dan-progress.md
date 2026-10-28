# 📋 Rencana Pelaksanaan & Lembar Progres Fitur Jurnal Mengajar

Dokumen ini memuat daftar tugas teknis (*checklist*), tahapan eksekusi, serta catatan progres pengerjaan fitur **Jurnal Mengajar** pada ekosistem Smart School ERP.

---

## 🎯 Status Implementasi: Selesai & Teruji (Completed)

| No | Komponen / Fitur | Status | Tanggal Selesai | Catatan Teknis |
| :---: | :--- | :---: | :---: | :--- |
| **1** | Perencanaan Arsitektur & Sinkronisasi Kebutuhan | ✅ Selesai | 27 Okt 2026 | Selesai didiskusikan & disepakati bersama user. |
| **2** | Dokumentasi Awal di `docs/` | ✅ Selesai | 27 Okt 2026 | `docs/03-fitur/09-jurnal-mengajar/` & `docs/02-database/`. |
| **3** | Migrasi Basis Data `jurnal_pembelajarans` & detail presensi | ✅ Selesai | 27 Okt 2026 | Migration `2026_10_27_232500_create_jurnal_pembelajarans_table` berhasil dieksekusi. |
| **4** | Model Eloquent & Relasi Data | ✅ Selesai | 27 Okt 2026 | `JurnalPembelajaran` & `JurnalPembelajaranPresensi`. |
| **5** | Otorisasi Supervisi di `/admin/manajemen-akses-portal` | ✅ Selesai | 27 Okt 2026 | Toggle & permission `portal_guru:supervisi_jurnal`. |
| **6** | Komponen Livewire `JurnalMengajarList` | ✅ Selesai | 27 Okt 2026 | Dual-mode: Guru Reguler & Supervisi Kepala Sekolah (Cascade Guru &rarr; Mapel &rarr; Kelas). |
| **7** | Komponen Livewire `JurnalMengajarForm` | ✅ Selesai | 27 Okt 2026 | Hitung JP otomatis, otomasi 1 mapel, matriks presensi siswa rombel. |
| **8** | Registrasi Rute & Navigasi Sidebar Portal Guru | ✅ Selesai | 27 Okt 2026 | `/portal-guru/jurnal-mengajar` & `portal.blade.php`. |
| **9** | Integrasi Sinkronisasi Presensi `attendances` & Notif WA | ✅ Selesai | 27 Okt 2026 | Otomatis Telat untuk siswa yang belum absen & integrasi notifikasi WA. |
| **10** | Fitur Cetak Rekap Jurnal PDF | ✅ Selesai | 27 Okt 2026 | `JurnalMengajarCetakController` & `pdf/jurnal-mengajar-rekap.blade.php`. |
| **11** | Panel Monitoring Admin Filament (`/admin/jurnal-mengajar`) | ✅ Selesai | 27 Okt 2026 | `JurnalPembelajaranResource`, tabel presensi siswa, cetak PDF dari admin. |
| **12** | Shortcut & Widget Dashboard Guru (`/portal-guru`) | ✅ Selesai | 27 Okt 2026 | Pintasan Menu Utama: status jurnal hari ini, total jurnal, tombol + Isi Jurnal. |
| **13** | Pengujian Otomatis Fitur (Automated Feature Tests) | ✅ Selesai | 27 Okt 2026 | `tests/Feature/JurnalMengajarTest.php` (5 test case, 18 assertions, 100% pass). |
| **14** | Konfigurasi Bypass & Supervisi di `/admin/manajemen-akses-portal` | ✅ Selesai | 27 Okt 2026 | Toggle `bypass_semua_kelas` & `supervisi_jurnal_guru` tersambung aman ke Spatie Permission. |
| **15** | Desain UI/UX Modern Jurnal Mengajar | ✅ Selesai | 28 Okt 2026 | Overhaul visual: dark hero gradients, interactive time slot & JP calculator, quick chips, batch actions, dan sticky action bar. |
| **16** | Modal Cetak Rekap KBM & Ekspor Excel (.xlsx) | ✅ Selesai | 28 Okt 2026 | Modal dialog interaktif model Akademik: pilihan Bulanan, Semester Ganjil/Genap, Tahunan, filter kelas & mapel, cetak PDF & unduh Excel. |
| **17** | Penyelarasan Tema Dinamis Sistem Sekolah | ✅ Selesai | 28 Okt 2026 | Hero banner, kartu metrik, badge KBM, focus rings, sticky submit button, dan modal cetak 100% terhubung ke `--color-brand-primary` & `--color-brand-secondary`. |
| **18** | Filter Simpel 1 Baris (Bulan Juli-Juni & Sembunyikan Tahun Ajaran) | ✅ Selesai | 28 Okt 2026 | Dropdown Tahun Ajaran disembunyikan (otomatis aktif), Tanggal Mulai/Selesai manual digantikan dropdown Bulan (Juli-Juni), filter menyatu dalam 1 baris ringkas, mode supervisi menyertakan nama guru pengampu. |

---

## 📝 Catatan Perubahan & Keputusan Desain Penting

1. **Tanpa Nilai**: Tidak ada input angka skor/nilai di jurnal mengajar (penilaian tetap terpusat di `/portal-guru/nilai`).
2. **Kalkulasi JP**:
   - `jam_selesai = jam_mulai + durasi_jp - 1`. Label format: `"Jam ke 3 - 4 (2 JP)"`.
3. **Logika Kehadiran Siswa**:
   - Status gerbang Sakit/Izin/Alpa terkunci (*disabled*) di form jurnal. Siswa diarahkan ke Guru BK/Piket jika salah input di gerbang.
   - Siswa belum absen gerbang yang hadir di kelas otomatis dicatat **Telat** di `attendances` (`source = 'jurnal_guru'`, `is_manual_input = true`).
   - Notifikasi WhatsApp mengikuti konfigurasi di `/admin/manajemen-notifikasi-wa`.
4. **Pengaturan Bypass & Supervisi (`/admin/manajemen-akses-portal`)**:
   - Dikelola terpusat oleh Admin/Super Admin melalui menu **Manajemen Akses &rarr; Akses Portal Pengguna** (`/admin/manajemen-akses-portal`).
   - **Bypass Semua Kelas (`portal_guru:akses_semua_kelas`)**: Memberikan wewenang guru/staf untuk memantau absensi seluruh rombel/kelas di sekolah.
   - **Supervisi Jurnal Mengajar (`portal_guru:supervisi_jurnal`)**: Memberikan wewenang kepada Kepala Sekolah / Waka Kurikulum untuk memantau jurnal seluruh guru melalui filter cascade: **Pilih Guru &rarr; Pilih Mapel &rarr; Pilih Kelas**, serta mencetak rekap PDF seluruh guru.
5. **Logika Cascading Pemilihan (Guru Paten &rarr; Pilih Mapel &rarr; Kelas Baru Aktif)**:
   - **Guru Tunggal / Reguler**: Identitas guru dipatenkan secara otomatis (*read-only*), guru tidak perlu memilih profil guru lain.
   - **Mode Supervisi**: Dropdown Guru Pengampu diposisikan di baris teratas sehingga KS/Admin dapat memilih target guru yang disupervisi terlebih dahulu.
   - **Pilih Mata Pelajaran**: Guru memilih mapel yang diampu. Jika guru hanya mengajar 1 mapel, mapel otomatis terpatri/terpilih langsung.
   - **Kelas Rombel Baru Aktif**: Dropdown Kelas berstatus *disabled* (terkunci) sebelum Mata Pelajaran terpilih. Begitu Mata Pelajaran dipilih, dropdown Kelas otomatis aktif dan hanya memuat rombel-rombel yang diajar guru pada mapel tersebut. Berlaku konsisten di Modal Cetak, Filter List Halaman, dan Form Input Jurnal.
6. **Penyelarasan Tema Sistem Sekolah (Dynamic Brand Color)**:
   - Seluruh elemen hero banner, tombol aksi, quick chips, focus rings, dan dialog modal cetak di Jurnal Mengajar tidak lagi menggunakan warna hardcoded statis (`slate-900`, `indigo-600`, atau `purple-500`).
   - Semua warna visual terhubung dinamis ke token CSS `@theme` Tailwind v4 (`brand-primary` & `brand-secondary`) yang dihasilkan dari tabel `pengaturan_sekolahs` (`theme_primary` dan `theme_secondary`).
   - Jika admin/pengguna mengganti warna tema sekolah, seluruh halaman Jurnal Mengajar secara otomatis berubah mengikuti identitas warna instansi sekolah.
7. **Filter & Pencarian Ringkas 1 Baris**:
   - **Tahun Ajaran Disembunyikan**: Karena wajib menggunakan tahun ajaran aktif sekolah, dropdown pemilih tahun ajaran dihapus dari filter dan ditampilkan sebagai badge indikator status kecil di kanan atas.
   - **Filter Bulan Kalender Akademik (Juli – Juni)**: Input tanggal manual (`dateFrom` dan `dateTo`) digantikan dengan dropdown pilihan bulan dari Juli hingga Juni (lengkap dengan opsi *Semua Bulan*), default otomatis memilih bulan berjalan saat ini.
   - **1 Baris Terpadu**:
     - *Guru Biasa*: 4 kolom (Bulan KBM &bull; Mata Pelajaran &bull; Kelas Rombel &bull; Cari Materi).
     - *Mode Supervisi*: 5 kolom (Pilih Guru Pengampu &bull; Bulan KBM &bull; Mata Pelajaran &bull; Kelas Rombel &bull; Cari Materi).

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

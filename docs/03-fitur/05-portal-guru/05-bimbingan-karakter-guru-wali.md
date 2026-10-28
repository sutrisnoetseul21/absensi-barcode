# 👨‍🏫 Modul Bimbingan Karakter & Guru Wali (Permendikdasmen No. 11/2025)

Dokumen ini mendokumentasikan implementasi program pembinaan karakter siswa oleh guru wali asuh di bawah rute `/portal-guru/guru-wali/*`.

---

## 1. Peta Rute & Komponen Guru Wali

| Rute URL | Komponen Livewire | Fungsi Utama |
| :--- | :--- | :--- |
| `/kelompok` | `GuruWali\KelompokSaya` | Daftar siswa asuh yang dibina oleh guru bersangkutan. |
| `/jurnal` | `GuruWali\JurnalList` | Riwayat catatan harian pembinaan karakter dan interaksi siswa. |
| `/jurnal/tambah` | `GuruWali\JurnalForm` | Form pengisian jurnal bimbingan siswa perorangan. |
| `/jurnal/kelompok` | `GuruWali\JurnalKelompokForm` | Form pengisian bimbingan massal untuk satu kelompok binaan. |
| `/konsultasi` | `GuruWali\KonsultasiList` | Manajemen jadwal konsultasi tatap muka & obrolan chat dengan siswa. |
| `/pemantauan` | `GuruWali\PemantauanBulanan`| Evaluasi grafik kedisiplinan dan pemberian badge karakter siswa. |
| `/cetak` | `GuruWali\CetakLaporanGuruWali`| Generator cetak dokumen rekap bimbingan resmi (PDF / format cetak). |
| `/analitik` | `GuruWali\DashboardAnalitik`| Command Center statistik efektivitas pembinaan karakter kelompok. |

---

## 2. Buku Jurnal Bimbingan (`JurnalList` & `JurnalForm`)

Guru wali wajib mencatat perkembangan karakter siswa asuh secara berkala:
- **Kategori Bimbingan**: *Akademik*, *Karakter & Kedisiplinan*, *Minat & Bakat*, *Sosial & Psikologis*.
- **Uraian Kegiatan**: Ringkasan diskusi pembiasaan baik, kendala yang dihadapi siswa, dan kesepakatan tindak lanjut.
- **Penyematan Badge Karakter**: Guru wali dapat menghadiahkan badge digital (seperti: *Disiplin*, *Jujur*, *Gemar Membaca*, *Gotong Royong*) yang otomatis tampil di profil siswa.

---

## 3. Penanganan Konsultasi Siswa (`KonsultasiList`)

Guru wali menerima permohonan bimbingan yang dikirim siswa melalui Portal Siswa:
1. **Konfirmasi Jadwal**: Menyetujui usulan waktu pertemuan tatap muka di sekolah atau mengajukan jadwal alternatif.
2. **Chat Percakapan Terpadu**: Berbalas pesan teks langsung di tiket konsultasi (`pesan_konsultasi_guru_wali`).
3. **Penyelesaian & Feedback**: Guru menutup tiket sebagai *Selesai* dan siswa memberikan rating kepuasan bimbingan.

---

## 4. Eskalasi Rujukan ke Bimbingan Konseling (BK)

Jika permasalahan siswa tergolong berat (misal: indikasi depresi, perundungan berat, atau masalah keluarga akut):
- Dari lembar jurnal / konsultasi, guru wali dapat mengklik tombol **"Rujuk ke BK"**.
- Data otomatis diteruskan ke modul Guru BK (`konseling_bk`) untuk penanganan privat oleh konselor profesional tanpa menghilangkan jejak bimbingan awal.

# 🛡️ Modul Bimbingan Konseling (BK) & Penanganan Aduan SPIKAP

Dokumen ini mendokumentasikan peran khusus Guru BK dan tim penanganan kekerasan di bawah rute `/portal-guru/bk/*` dan `/portal-guru/{spikapSlug}/*`.

---

## 1. Modul BK: Rujukan Kasus & Sesi Konseling (`/portal-guru/bk/*`)

Dikhususkan untuk Guru BK dengan hak akses khusus:

| Rute URL | Komponen Livewire | Fungsi Utama |
| :--- | :--- | :--- |
| `/rujukan` | `Bk\RujukanKasusList` | Menerima pelimpahan kasus dari wali kelas dan guru wali asuh. |
| `/konseling` | `Bk\KonselingList` | Arsip seluruh sesi bimbingan konseling formal yang tercatat. |
| `/konseling/tambah` | `Bk\KonselingForm` | Form pencatatan kronologi kasus, dinamika psikologis, dan solusi. |
| `/siswa-binaan` | `Bk\SiswaBinaanList` | Daftar siswa dalam pemantauan intensif / masa pendampingan khusus BK. |

### Prinsip Kerahasiaan Konseling:
- Isi catatan sesi konseling dilindungi hak akses ketat.
- Guru mata pelajaran biasa tidak dapat membaca hasil catatan konseling siswa demi menjaga privasi dan kesehatan mental peserta didik.

---

## 2. Modul SPIKAP Guru: Anti-Bullying & Aduan Siswa (`/portal-guru/{spikapSlug}/*`)

Diproteksi middleware [`EnsureCanAccessSpikapGuru`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Middleware/EnsureCanAccessSpikapGuru.php):

### A. Kotak Masuk Laporan (`SpikapInbox`)
- Menampilkan seluruh tiket laporan masuk dari siswa (perundungan, kekerasan fisik/verbal, pemalakan, atau keluhan fasilitas).
- Mendukung laporan berkategori **Anonim** (nama pelapor disamarkan oleh sistem).

### B. Lembar Detail & Investigasi (`SpikapDetail`)
- Memeriksa foto bukti / video / screenshot lampiran aduan.
- Menetapkan tingkat urgensi (*Rendah*, *Sedang*, *Tinggi / Kritis*).
- Mencatat log tahapan investigasi (`spikap_log_status`): *Diterima &rarr; Investigasi &rarr; Mediasi &rarr; Selesai*.

### C. Analitik & Pelaporan (`SpikapAnalitik`)
- Dashboard grafik pemetaan kasus: persentase jenis kasus terbanyak, kelas yang paling sering terlibat, dan durasi rata-rata penyelesaian masalah.
- Digunakan oleh Kepala Sekolah dan Komite Pencegahan Kekerasan Sekolah untuk pelaporan ke dinas pendidikan terkait.

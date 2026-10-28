# SI-WALI (Sistem Informasi & Pendampingan Guru Wali)

Dokumen ini membedah arsitektur proses, logika bisnis (*business logic*), alur data (*data flow*), serta interaksi antar aktor (Siswa, Guru Wali, Guru BK) dalam ekosistem aplikasi SI-WALI. 

Aplikasi ini didesain secara **Universal (General Use)** untuk dapat diimplementasikan di berbagai tipologi sekolah (Elit, Reguler, hingga Perbatasan). Fitur-fitur inovatif di dalamnya telah terbukti secara efektif mendukung *Mentoring Skill* dan *Pendampingan Karakter* (sampling kasus pada Lomba Apresiasi GTK Dedikatif 2026).

**Slogan & Tagline:**
- Slogan Utama (Khusus Lomba GTK Dedikatif 2026): *"Memberanikan Murid Perbatasan untuk Bercerita"*
- Tagline Platform/Aplikasi (Versi General Use): *"Ruang Aman Pendampingan Holistik, Jembatan Tumbuh Kembang Murid"*
- Tagline Alternatif: *"Satu Klik Ruang Aman, Dampingi Murid Wujudkan Masa Depan"*

---

## 1. Aktor & Peran (Role Analysis)

Sistem ini menganut pendekatan **Penanganan Berjenjang (Multi-Tier Intervention)**:
1. **Siswa**: Bertindak sebagai subjek utama. Siswa bisa bersifat pasif (dipantau) atau aktif (menginisiasi permintaan bantuan/konsultasi).
2. **Guru Wali**: Bertindak sebagai *First Responder* (Penangan Pertama). Berperan memantau keseharian, menyelesaikan konflik ringan, dan mencatat rekam jejak siswa sehari-hari.
3. **Guru BK**: Bertindak sebagai *Specialist* (Spesialis) atau ekskalator. Menangani kasus berat (psikologis, pelanggaran berat, mediasi keluarga) yang dilimpahkan oleh Guru Wali.

---

## 2. Bedah Modul & Analisa Alur Sistem

### 2.1. Interaksi Proaktif (Modul Konsultasi & Refleksi Siswa)
Modul ini menjembatani komunikasi privat dari siswa kepada wali kelasnya dengan pendekatan psikologis yang merangkul (*Safe-Space*).

**Sisi Siswa (`/portal-siswa/konsultasi-guru-wali`)**
1. **Sinyal Konsultasi Cepat (Safe-Space Signal)**: Saat mengajukan bimbingan, siswa tidak dipaksa mengetik narasi panjang. Siswa cukup memilih *quick signal*:
   - 🟢 *Diskusi Ringan*
   - 🟡 *Butuh Masukan Akademik/Ekskul*
   - 🔴 *Butuh Teman Curhat (Segera)*
2. **Formulir Pengajuan**: Siswa bisa menambahkan deskripsi (opsional) dan menentukan preferensi mode komunikasi (Pesan Portal atau Tatap Muka). Status awal tiket: `Menunggu`.
3. **Refleksi Bimbingan (Micro-Feedback)**: **[FITUR KUNCI]** Setelah sesi selesai, siswa wajib mengisi *rating/emoji* perasaan mereka (Lega / Biasa / Masih Bingung) sebagai bukti otentik efektivitas bimbingan guru.

**Sisi Guru Wali (`/portal-guru/guru-wali/konsultasi`)**
1. **Respon & Penjadwalan**: Guru Wali menerima notifikasi, meninjau sinyal yang dikirim siswa, lalu menyetujui/menjadwalkan pertemuan. Status berubah menjadi `Diproses`.
2. **Penyelesaian Sesi & Pemberian Badge**:
   - Setelah bimbingan selesai, Guru Wali mengisi **Log Hasil Konsultasi** (Akar masalah & Tindak Lanjut).
   - **Student Encouragement Badge**: Tepat sebelum menekan tombol "Selesai", sistem mewajibkan (atau menyarankan) Guru Wali untuk memberikan *Badge Karakter* (misal: "Anak Berani", "Pembelajar Gigih"). *Badge* ini akan langsung muncul di portal siswa sebagai apresiasi psikologis (Dopamin *Reward*).
   - Status tiket berubah menjadi `Selesai`.

### 2.2. Pencatatan Anekdotal & Observasi (Modul Jurnal & Pemantauan)
Modul observasi bagi Guru Wali untuk memonitor perkembangan siswa, baik secara insidental maupun terencana.

- **Modul Jurnal (`/portal-guru/guru-wali/jurnal`)**:
  - Alat catat kejadian tak terduga (*Insidental*).
  - Klasifikasi: **Positif** (Kebaikan, Prestasi), **Negatif** (Pelanggaran), **Netral** (Sakit).
  - *Integrasi Badge Manual*: Guru Wali juga dapat memberikan *Student Encouragement Badge* dari menu ini atas kejadian positif harian yang diamati.
- **Modul Pemantauan (`/portal-guru/guru-wali/pemantauan`)**:
  - Instrumen penilaian berkala (mingguan/bulanan).
  - Guru Wali mengisi rubrik terukur (misal: rentang 1-5 untuk Indikator Kedisiplinan) guna mengevaluasi efektivitas pendampingan pada siswa berstatus *flagging*.

### 2.3. Manajemen Administratif (Modul Kelompok & Dashboard Analitik)
- **URL**: `/portal-guru/guru-wali/kelompok`
- **Dashboard Analitik Impact**: Bukan sekadar tabel daftar nama siswa, halaman utama Guru Wali berfungsi sebagai pusat kendali (*Command Center*) yang menampilkan:
  - **Tren Partisipasi (Keterbukaan)**: Grafik persentase siswa yang berinisiatif menekan *Sinyal Konsultasi* setiap bulan.
  - **Peta Isu 4 Pilar**: Pie chart klasifikasi masalah siswa (Akademik, Karakter, Minat, Sosial-Psikologis).
  - **Efektivitas Bimbingan**: Rekap nilai akumulatif dari *Fitur Refleksi Siswa* (Berapa persen siswa yang merasa lega/terbantu setelah dibimbing).

### 2.4. Eskalasi & Penanganan Lanjut (Modul Rujukan & Konseling BK)
Alur pemindahan wewenang penanganan dari Tier 1 (Guru Wali) ke Tier 2 (Guru BK).

- **Alur Rujukan (`/portal-guru/bk/rujukan`)**:
  1. Guru Wali menekan tombol **"Rujuk ke BK"** pada riwayat kasus yang di luar kapasitasnya.
  2. Sistem merangkai otomatis *history* (Jurnal & Konsultasi sebelumnya) agar Guru BK memiliki konteks utuh. Status: `Pending di BK`.
- **Alur Konseling BK (`/portal-guru/bk/konseling`)**:
  1. Guru BK menyetujui rujukan dan merubah status menjadi `Dalam Penanganan BK`.
  2. Guru BK melaksanakan sesi (termasuk pemanggilan Orang Tua jika perlu), lalu mengisi Log Diagnosa & Rekomendasi.
  3. Saat Konseling ditutup, sistem otomatis mem-forward **Salinan Ringkas (Feedback)** ke portal Guru Wali, menutup siklus komunikasi dengan sempurna.

---

## 3. Spesifikasi Tampilan Cetak & Pengimbasan (Reporting)

Output sistem bukan sekadar lembar pertanggungjawaban fisik, namun juga sarana desiminasi/replikasi.

### 3.1. Modul Pengimbasan / Replikasi (Exporter)
- **Konteks**: Memudahkan implementasi aplikasi SI-WALI ke sekolah lain yang memiliki karakteristik masalah serupa.
- **Fitur Ekspor Konfigurasi**: Sistem mampu men-generate "Paket Standar Penanganan" yang berisi:
  1. File JSON/Excel konfigurasi template 4 Pilar & Kategori Jurnal.
  2. *Standard Operating Procedure* (SOP) integrasi Guru Wali - BK dalam format dokumen.

### 3.2. Buku Laporan Periodik & Pembuktian Fisik
1. **Laporan Pemantauan Siswa (Raport Karakter)**: 
   - Tampilan *Portrait*. Mencakup tabel instrumen kualitatif, grafik perubahan karakter, dan tanda tangan wali murid (Sangat ideal disisipkan pada Raport Akademik akhir semester).
2. **Buku Jurnal Guru Wali (Logbook Anekdotal)**:
   - Tampilan *Landscape*. Merekap harian/mingguan seluruh kejadian (No, Tanggal, Nama, Kasus, Penanganan).
3. **Rekapitulasi Konsultasi & Berita Acara**:
   - Memiliki **Lembar Tanda Tangan (Informed Consent)** siswa sebagai bukti nyata bahwa layanan mentoring telah diberikan.
4. **Surat Pemanggilan Resmi & Dokumen Eskalasi**:
   - Generator surat otomatis untuk keperluan pemanggilan Orang Tua oleh Guru BK, lengkap dengan Nomor Surat dan Kop resmi.

---

## 4. Kesimpulan Siklus Data (Data Lifecycle)

Siklus penanganan siswa di SI-WALI berjalan selaras, efisien, dan kaya *impact*:

1. **TRIGGER**: Inisiasi siswa melalui *Sinyal Safe-Space* di Konsultasi ATAU temuan Guru Wali di Jurnal.
2. **INTERVENTION**: Guru melakukan *Mentoring* dan memicu hormon positif siswa melalui pemberian *Encouragement Badge*.
3. **REFLECTION**: Siswa memberikan timbal-balik (*Micro-Feedback*) atas layanan guru.
4. **ESCALATION**: Masalah kompleks dilimpahkan secara *seamless* ke BK beserta rekam jejak.
5. **REPORTING & REPLICATING**: Evaluasi menyeluruh di *Dashboard Analitik* kelas dan kemampuan sistem diekspor bagi sekolah mitra.

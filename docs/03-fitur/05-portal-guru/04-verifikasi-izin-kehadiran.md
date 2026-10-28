# 📝 Verifikasi Permohonan Izin & Sakit Siswa

Dokumen ini menjelaskan alur peninjauan dan persetujuan surat izin/sakit yang diajukan oleh siswa atau orang tua melalui Portal Guru ([`App\Livewire\PortalGuru\IjinKehadiranList`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru) dan [`IjinKehadiranDetail`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru)).

---

## 1. Daftar Pengajuan Izin (`/portal-guru/ijin-kehadiran`)

Wali kelas menerima daftar seluruh pengajuan izin dari siswa di kelasnya:
- **Badge Status**:
  - 🟡 **Pending**: Permohonan baru yang membutuhkan tindakan wali kelas.
  - 🟢 **Approved**: Telah disetujui.
  - 🔴 **Rejected**: Ditolak (misal: alasan tidak jelas, surat tidak valid).
- **Informasi Singkat**: Nama siswa, jenis (Izin / Sakit), rentang tanggal, dan cuplikan alasan.

---

## 2. Lembar Telaah & Keputusan (`/portal-guru/ijin-kehadiran/{id}`)

Pada halaman detail pengajuan:
1. **Preview Dokumen Lampiran**: Wali kelas dapat melihat foto bukti surat dokter atau surat izin bertanda tangan orang tua secara langsung di browser.
2. **Aksi Setujui (*Approve*)**:
   - Status diubah menjadi `approved`.
   - **Otomatisasi Database**: Sistem langsung membuat/memperbarui record di tabel `attendances` untuk setiap hari dalam rentang tanggal izin dengan status `excused` (izin) atau `sick` (sakit) dan `source = 'leave_request'`.
   - **Notifikasi WhatsApp**: Sistem memicu pesan WA konfirmasi ke orang tua bahwa izin ananda telah diterima secara resmi oleh pihak sekolah.
3. **Aksi Tolak (*Reject*)**:
   - Status diubah menjadi `rejected`.
   - Wali kelas wajib mencantumkan alasan penolakan (misal: *"Surat dokter tidak terbaca, mohon kirim ulang"*).

---

## 3. Fitur Quick Approval via WhatsApp (One-Click Approval)

Sistem juga menyediakan rute publik khusus bertoken aman (`/ijin-approval/{token}`):
- Wali kelas menerima link persetujuan langsung di pesan WhatsApp saat ada pengajuan baru.
- Wali kelas dapat menyetujui izin siswa langsung dari handphone tanpa harus membuka komputer atau login terlebih dahulu (dilindungi token Base62 unik sekali pakai).

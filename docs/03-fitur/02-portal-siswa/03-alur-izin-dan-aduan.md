# 🔄 Alur Interaktif: Pengajuan Izin & Aduan SPIKAP

Dokumen ini memetakan alur kerja interaktif ketika siswa mengajukan permohonan izin ketidakhadiran atau mengirimkan tiket laporan aduan via Portal Siswa.

---

## 1. Alur Pengajuan Izin / Sakit Mandiri

```mermaid
sequenceDiagram
    participant Siswa as Siswa (Portal Siswa)
    participant Form as IjinKehadiranForm
    participant DB as MySQL (leave_requests)
    participant Wali as Wali Kelas (Portal Guru)
    participant Att as attendances Table
    participant WA as WhatsApp Gateway

    Siswa->>Form: Isi tipe (Izin/Sakit), tanggal, alasan, & upload foto surat
    Form->>DB: Insert record status = 'pending'
    Form-->>Siswa: Notifikasi pengajuan berhasil terkirim
    
    Wali->>DB: Buka portal guru & review lampiran surat izin
    alt Disetujui (Approved)
        Wali->>DB: Update status = 'approved'
        Wali->>Att: Insert otomatis ke attendances (status: excused / sick, source: leave_request)
        Wali->>WA: Kirim notifikasi WA konfirmasi ke nomor orang tua
    else Ditolak (Rejected)
        Wali->>DB: Update status = 'rejected' dengan catatan penolakan
    end
```

### Tahapan Kritis Pengajuan Izin:
1. **Pencegahan Tanggal Kedaluwarsa**: Siswa tidak dapat mengajukan izin untuk tanggal yang sudah lewat melampaui batas toleransi yang diatur sekolah.
2. **Sinkronisasi Presensi**: Begitu wali kelas menekan tombol **Setujui**, sistem otomatis membuat record kehadiran di tabel `attendances` sehingga status siswa pada hari tersebut otomatis berubah dari *Belum Hadir* menjadi *Izin* atau *Sakit*.
3. **Pemberitahuan Orang Tua**: Memicu pengiriman pesan WhatsApp ke nomor HP orang tua bahwa surat izin ananda telah disetujui pihak sekolah.

---

## 2. Alur Pelaporan Aduan & Kasus SPIKAP

```mermaid
sequenceDiagram
    participant Siswa as Siswa Pelapor
    participant Form as SpikapLaporanForm
    participant DB as spikap_laporan & lampiran
    participant BK as Guru BK & Admin SPIKAP
    participant Alert as Notifikasi Darurat WA

    Siswa->>Form: Tulis kronologi kejadian, kategori (perundungan, fasilitas, dll), & upload bukti
    alt Pilihan Anonim
        Form->>DB: Simpan laporan dengan flag is_anonim = true
    else Identitas Terbuka
        Form->>DB: Simpan laporan terhubung ke student_id
    end
    
    Form->>Alert: Jika kategori "Kritis/Darurat", kirim WA alert ke nomor darurat tim BK
    Form-->>Siswa: Terbitkan Kode Tiket Aduan Unik

    BK->>DB: Buka dashboard SPIKAP & telaah bukti
    BK->>DB: Update status laporan ('diproses' -> 'selesai')
    BK->>DB: Jadwalkan sesi konseling privat dengan siswa bersangkutan
```

### Jaminan Keamanan & Privasi Siswa:
1. **Opsi Laporan Anonim**: Siswa yang takut melaporkan tindak perundungan (*bullying*) dapat menyamarkan identitasnya.
2. **Akses Terisolasi**: Guru mata pelajaran biasa tidak dapat membaca laporan SPIKAP. Data hanya dapat diakses oleh Guru BK yang memiliki permission `spikap_guru_bk` dan Kepala Sekolah.
3. **Audit Log Perubahan Status**: Setiap tahapan investigasi dicatat pada tabel `spikap_log_status` untuk menjaga akuntabilitas penanganan kasus.

# 01. Project Overview

- **Latar belakang & masalah**: absensi manual lambat, rawan titip absen/kecurangan.
- **Tujuan proyek**: absensi otomatis via scan barcode, transparan lewat dashboard publik.
- **Target pengguna**: Admin (guru piket/TU), Kepala Sekolah/Wali Kelas (viewer), Publik/Orang tua (dashboard read-only).
- **Ruang lingkup (In-scope)**: manajemen siswa/kelas/tahun ajaran, absensi via scan, kalender libur, dashboard rekap, arsip tahun ajaran, kenaikan kelas.
- **Di luar ruang lingkup (Out-of-scope, utk versi awal)**: absensi guru/pegawai, integrasi RFID selain barcode, notifikasi WA/SMS (bisa jadi fase 2).
- **Ringkasan tech stack** (lihat detail di 09-third-party.md).
- **Stakeholder & kontak**.

---

## Visi Arsitektur (Menuju ERP)

Aplikasi ini menggunakan arsitektur modular yang memisahkan **Master Data** (entitas murni seperti Siswa, Kelas) dari **Operasional** (transaksi seperti Pendaftaran dan Presensi). 

Dengan pendekatan arsitektur 3-Layer (Master Data, Operasional, Presentasi UI) dan komunikasi antar modul berbasis *Event-Driven*, sistem absensi ini dirancang sebagai *stepping stone* menuju sistem **ERP (Enterprise Resource Planning)** sekolah yang lebih besar. Modul masa depan seperti E-Learning (LMS) atau Sistem Pembayaran SPP dapat ditambahkan di atas fondasi Master Data ini tanpa merusak fitur absensi yang sudah berjalan.

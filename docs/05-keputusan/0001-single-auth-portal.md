# ADR 0001: Penggunaan Single Authentication Session Lintas Portal

## Status
**Accepted** (2026-08-16)

## Konteks Masalah
Sistem memiliki beberapa portal: Panel Admin Filament (`/admin`), Portal Guru (`/portal-guru`), Portal Siswa (`/portal-siswa`), dan Portal SPIKAP (`/spikap`). 
Muncul pertanyaan apakah harus menggunakan multi-guard terpisah (misal guard `web`, `teacher`, `student`) atau satu guard `web` dengan otorisasi berbasis Role & Permission (RBAC).

## Keputusan yang Diambil
Menggunakan **Single Authentication Session** dengan guard bawaan Laravel (`web` / Filament provider) yang didukung oleh **Spatie Laravel-Permission**:
- Setiap user (baik guru, admin, maupun staf) hanya memiliki satu akun di tabel `users`.
- Akses ke tiap portal diproteksi oleh middleware role check (`hasRole('wali_kelas')`, `hasRole('super_admin')`, dll.).

## Konsekuensi
### Positif:
- Single Sign-On (SSO) internal: Pengguna tidak perlu login ulang saat berpindah antara Panel Admin, Portal Guru, dan Portal SPIKAP.
- Model data lebih bersih tanpa duplikasi sesi autentikasi.
### Negatif:
- Perlu ketelitian ekstra pada middleware rute agar siswa tidak dapat membuka URL portal guru dengan sesi yang sama.

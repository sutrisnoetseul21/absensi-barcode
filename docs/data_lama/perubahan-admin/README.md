# Dokumentasi: Konsolidasi 4 Panel Admin → 1 Panel Terpadu

## Tujuan
Menggabungkan 4 panel admin terpisah menjadi 1 panel terpadu di `/admin`
agar pengelolaan sistem lebih simpel dan tidak membingungkan.

## Panel yang Digabung

| Panel Lama | URL Lama | Status |
|---|---|---|
| Admin Utama | `/admin` | ✅ Tetap digunakan (menjadi panel utama) |
| Admin Akademik | `/admin-akademik` | 🔒 Tidak dihapus, tetap sebagai backup |
| Admin Presensi | `/admin-presensi` | 🔒 Tidak dihapus, tetap sebagai backup |
| Admin Perpustakaan | `/admin-perpustakaan` | 🔒 Tidak dihapus, tetap sebagai backup |

## Keputusan Desain

| Aspek | Keputusan |
|---|---|
| Siapa yang bisa login ke `/admin` | Semua role (akademik, presensi, perpus, super admin) |
| Dashboard `/admin` | Gabungkan semua widget, **tanpa QuickLinksWidget** |
| Visibilitas menu per role | Diatur nanti via halaman Manajemen Akses Portal |

## Daftar Tahap Pengerjaan

| File | Tahap | Status |
|---|---|---|
| [tahap-1-admin-panel-provider.md](./tahap-1-admin-panel-provider.md) | Daftarkan semua Resource ke panel `/admin` | ⏳ Belum dikerjakan |
| [tahap-2-navigation-group.md](./tahap-2-navigation-group.md) | Atur urutan sidebar tiap Resource/Page | ⏳ Belum dikerjakan |
| [tahap-3-dashboard-gabungan.md](./tahap-3-dashboard-gabungan.md) | Gabungkan semua widget ke Dashboard `/admin` | ⏳ Belum dikerjakan |
| [tahap-4-can-access-panel.md](./tahap-4-can-access-panel.md) | Izinkan semua role login ke `/admin` | ⏳ Belum dikerjakan |

## Catatan Penting
> ⚠️ Setiap tahap dikerjakan satu per satu dan dicek oleh pemilik proyek sebelum lanjut ke tahap berikutnya.
> Tidak ada kode di panel lama yang diubah atau dihapus.

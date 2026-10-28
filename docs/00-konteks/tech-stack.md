# ⚙️ Spesifikasi Tech Stack & Ekosistem

Dokumen ini merangkum seluruh teknologi, framework, pustaka, dan dependensi utama yang digunakan dalam pengembangan sistem.

---

## 1. Backend Core

| Komponen | Spesifikasi / Paket | Deskripsi & Peran |
| :--- | :--- | :--- |
| **Framework** | Laravel 12.x | Framework backend utama PHP |
| **Bahasa Pemrograman** | PHP 8.2+ | Runtime backend |
| **Admin Panel** | Filament v4 (v3.x / v4.x stack) | Admin dashboard builder berbasis TALL stack |
| **Database Engine** | MySQL 8.0+ / MariaDB | Relational Database Management System |
| **ORM** | Eloquent ORM | Model mapping, eager loading, observer |
| **Authentication** | Laravel Custom / Filament Guard | Single authentication session dengan role guard |
| **Permission Management** | Spatie Laravel-Permission | Role-based access control (RBAC) terpadu |

---

## 2. Frontend & UI Engine

| Komponen | Spesifikasi | Deskripsi & Peran |
| :--- | :--- | :--- |
| **Reactive Component** | Livewire v3 | Dynamic UI reactive tanpa page reload penuh |
| **Client Interaction** | Alpine.js v3 | Micro-interactions, dropdown, modal state, scanner listener |
| **CSS Styling** | Tailwind CSS v3 | Utility-first CSS framework dengan kustomisasi theme sekolah |
| **Icons** | Blade Icons / Heroicons / Tabler | Ikon grafis vektor UI |
| **Document Export** | Barryvdh DomPDF / Maatwebsite Excel | Generate laporan kehadiran PDF dan spreadsheet rekap Excel |

---

## 3. Integrasi & Komponen Eksternal

| Layanan / Driver | Fungsi |
| :--- | :--- |
| **WhatsApp Gateway** | Pengiriman notifikasi kehadiran dan peringatan siswa secara real-time via REST API. |
| **Hardware Scanner** | USB / Serial Barcode Scanner & RFID Reader (input emulasi keyboard tanpa driver khusus). |
| **Storage Driver** | Local disk (`storage/app/public`) dengan symlink ke `public/storage`. |
| **Job Queue & Scheduler** | Laravel Scheduler (`cron`) untuk rekap otomatis dan pengiriman pesan terjadwal. |

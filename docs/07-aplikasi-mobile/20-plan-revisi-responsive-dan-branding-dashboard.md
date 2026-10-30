# 📐 Plan Matang: Revisi Tampilan Responsif (Tablet/HP), Logo AppBar, Foto Profil Hero & Navigasi Sistem

Dokumen ini merupakan **Blueprint Teknis & Rencana Perbaikan Antarmuka (Tahap 1 s.d. Tahap 4)** untuk menyempurnakan tampilan **Dashboard Utama Portal Guru** dan **Dashboard Utama Portal Siswa** pada aplikasi mobile Flutter (`apk-android-presensi`).

Rencana ini secara langsung merespons evaluasi visual pengguna pada pengujian tablet dan smartphone Android:
1. **Layout Responsif Tablet**: Mengatasi kartu menu yang memanjang horizontal penuh di layar lebar/tablet dengan menerapkan **Adaptive Grid Multi-Kolom** (1 kolom di HP, 2 kolom di tablet, 3 kolom di desktop).
2. **Branding Logo Sekolah di AppBar**: Menampilkan logo resmi sekolah tepat di samping judul *"Portal Guru / Portal Siswa"* dan nama sekolah.
3. **Foto Profil di Hero Welcome Banner**: Menambahkan avatar foto guru/siswa di sisi kanan kartu gradasi biru *"Selamat Datang, [Nama Pendidik/Siswa]"*.
4. **Navigasi Sistem Android Non-Fullscreen**: Memastikan bilah navigasi bawah Android (Tombol Back, Home, Recent Apps) dan Status Bar atas selalu tampil (*persistent*) tanpa dipaksa masuk ke mode *immersive fullscreen*.

---

## 📸 Analisis Masalah Berdasarkan Tangkapan Layar Pengguna

```
┌───────────────────────────────────────────────────────────────────────────────┐
│ TAMPILAN SAAT INI DI TABLET (Gambar 1)                                        │
│ ┌───────────────────────────────────────────────────────────────────────────┐ │
│ │ [          KARTU MENU MEMANJANG PENUH DARI KIRI KE KANAN                ] │ │
│ │ [ [Tombol Aksi Sangat Panjang .........................................] ]│ │
│ └───────────────────────────────────────────────────────────────────────────┘ │
│  ⚠️ Masalah: Ruang kosong terbuang sia-sia, proporsi kartu aneh di tablet.    │
├───────────────────────────────────────────────────────────────────────────────┤
│ USULAN PENYEMPURNAAN RESPONSIF (Adaptive Grid 2 Kolom di Tablet)              │
│ ┌──────────────────────────────────────┐ ┌──────────────────────────────────┐ │
│ │ 1. Presensi                          │ │ 2. Jurnal Mengajar               │ │
│ │ [Buka Presensi Kelas ->]             │ │ [Buka Jurnal Mengajar ->]        │ │
│ └──────────────────────────────────────┘ └──────────────────────────────────┘ │
│ ┌──────────────────────────────────────┐ ┌──────────────────────────────────┐ │
│ │ 3. Persetujuan Ijin                  │ │ 4. Guru Wali (SI-WALI)           │ │
│ │ [Buka Persetujuan ->]                │ │ [Buka SI-WALI ->]                │ │
│ └──────────────────────────────────────┘ └──────────────────────────────────┘ │
│  ✅ Hasil: Padat, proporsional, ergonomis di kedua orientasi (HP & Tablet).   │
└───────────────────────────────────────────────────────────────────────────────┘
```

---

## 1. Spesifikasi Detail Perubahan

### A. Layout Responsif (Smartphone vs Tablet/Layar Lebar)
* **Smartphone Portrait (Lebar < 650dp)**:
  - Tetap menampilkan layout 1 kolom vertikal yang nyaman dioperasikan satu tangan (seperti tampilan HP di Gambar 2).
* **Tablet / Layar Lebar (Lebar >= 650dp)**:
  - Menggunakan `LayoutBuilder` yang memecah daftar menu menjadi **Grid 3 Kolom dalam satu baris (*3 Cards per row*)** sesuai arahan pengguna.
  - Setiap kartu memiliki tinggi seragam yang proporsional (*aspect ratio* seimbang) dan padding rapi.
* **Terapkan pada Kedua Portal**:
  - `DashboardGuruScreen` (9 Menu Utama Guru -> Rapi terbagi dalam 3 baris x 3 kolom = 9 menu pas!).
  - `DashboardSiswaScreen` (7 Menu Utama Siswa -> Rapi dalam format multi-kolom).

---

### B. Logo Sekolah di AppBar
* **Kondisi Lama**:
  - Teks terpusat vertikal tanpa logo:
    ```dart
    Column(
      children: [
        Text('Portal Guru'),
        Text(widget.schoolName),
      ],
    )
    ```
* **Penyempurnaan Baru**:
  - Menggabungkan Logo Sekolah dan Teks Branding secara horizontal (`Row`):
    ```dart
    Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        // Wadah Logo Sekolah (Lingkaran / Rounded dengan border putih lembut)
        Container(
          width: 38,
          height: 38,
          decoration: BoxDecoration(
            color: Colors.white,
            shape: BoxShape.circle,
            boxShadow: [
              BoxShadow(color: Colors.black12, blurRadius: 4),
            ],
          ),
          child: ClipOval(
            child: _buildSchoolLogoWidget(),
          ),
        ),
        SizedBox(width: 10),
        // Nama Portal & Nama Sekolah
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            Text('Portal Guru', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
            Text(widget.schoolName, style: TextStyle(fontSize: 11, color: Colors.white70)),
          ],
        ),
      ],
    )
    ```
  - **Dukungan Logo**:
    1. Menggunakan URL logo dinamis dari database backend (`logo_url`).
    2. Fallback gambar lokal aset `assets/images/logo_sekolah.png`.
    3. Fallback ikon elegan sekolah jika aset/jaringan belum tersedia.

---

### C. Foto Profil Guru / Siswa di Hero Welcome Card (Kotak Biru)
* **Kondisi Lama**:
  - Hanya menampilkan teks: Badge "PORTAL GURU", "Selamat Datang, [Nama]!", dan uraian deskripsi.
* **Penyempurnaan Baru**:
  - Mengubah layout dalam kotak biru menjadi `Row` (Teks di kiri, Avatar di kanan):
    ```dart
    Row(
      crossAxisAlignment: CrossAxisAlignment.center,
      children: [
        // Sisi Kiri: Teks Sambutan
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Badge PORTAL GURU
              // Selamat Datang, [Nama Pendidik / Siswa]!
              // Teks Deskripsi Ringkas
            ],
          ),
        ),
        SizedBox(width: 16),
        // Sisi Kanan: Foto Profil Guru / Siswa (Interaktif & Indikator Aktif)
        GestureDetector(
          onTap: () => _openProfilMenu(),
          child: Stack(
            children: [
              Container(
                width: 68,
                height: 68,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(color: Colors.white, width: 2.5),
                  boxShadow: [
                    BoxShadow(color: Colors.black26, blurRadius: 8, offset: Offset(0, 3)),
                  ],
                ),
                child: ClipOval(
                  child: _buildTeacherAvatar(),
                ),
              ),
              // Dot Hijau Online / Aktif
              Positioned(
                bottom: 2,
                right: 2,
                child: Container(
                  width: 14,
                  height: 14,
                  decoration: BoxDecoration(
                    color: Color(0xFF22C55E),
                    shape: BoxShape.circle,
                    border: Border.all(color: Colors.white, width: 2),
                  ),
                ),
              ),
            ],
          ),
        ),
      ],
    )
    ```
  - **Interaktivitas**: Mengetuk avatar foto langsung membuka layar **Menu 9: Profil Saya**.
  - **Fallback Cerdas**: Jika guru belum mengunggah foto profil, sistem menampilkan inisial nama berlatar gradien profesional.

---

### D. Kontrol Navigasi Sistem Android (Persistent Navigation Bar / Non-Fullscreen)
* **Problem**: 
  - Di beberapa versi Android/tablet, aplikasi Flutter menyembunyikan navigation bar bawah (tombol segitiga Back, lingkaran Home, kotak Apps) atau menyembunyikan status bar atas sehingga terasa "100% fullscreen" dan pengguna terpaksa harus *swipe* samping/bawah untuk memunculkan tombol kembali.
* **Solusi di Flutter**:
  - Konfigurasi eksplisit pada `main.dart` sebelum `runApp`:
    ```dart
    SystemChrome.setEnabledSystemUIMode(
      SystemUiMode.manual,
      overlays: [SystemUiOverlay.top, SystemUiOverlay.bottom],
    );
    ```
  - Menetapkan `SystemUiOverlayStyle` yang bersih:
    ```dart
    SystemChrome.setSystemUIOverlayStyle(
      const SystemUiOverlayStyle(
        statusBarColor: Colors.transparent,
        statusBarIconBrightness: Brightness.light,
        systemNavigationBarColor: Colors.white,
        systemNavigationBarIconBrightness: Brightness.dark,
        systemNavigationBarDividerColor: Colors.transparent,
      ),
    );
    ```
  - Hasil:
    - Status Bar atas (Jam, Sinyal, Baterai) dan Navigation Bar bawah (Back, Home, Recent Apps) **selalu standby dan terlihat jelas**.
    - Tombol fisik/sentuh tidak akan tersembunyi kecuali pengguna sendiri yang mengaktifkan mode fullscreen gesture di pengaturan HP-nya.

---

## 2. Rencana Kerja Bertahap (Tahap 1 s.d. Tahap 4)

### 🔹 Tahap 1: Konfigurasi Navigasi Sistem Android & Sinkronisasi Logo Sekolah
- Menambahkan konfigurasi `SystemUiMode.manual` dengan `overlays: [SystemUiOverlay.top, SystemUiOverlay.bottom]` di `main.dart`.
- Menambahkan field `schoolLogoUrl` di `SessionStorage` dan meneruskannya saat proses login berhasil di `LoginScreen`.
- Membuat widget reusable `SchoolLogoWidget` yang mendukung base64, URL network dengan caching, dan fallback aset lokal.

### 🔹 Tahap 2: Tampilan Dashboard Guru (AppBar Logo & Foto Hero)
- Memperbarui AppBar di `DashboardGuruScreen`: Menggabungkan Logo Sekolah dan Teks "Portal Guru • Nama Sekolah".
- Memperbarui Hero Welcome Card di `DashboardGuruScreen`: Menambahkan foto avatar pendidik di sisi kanan yang terhubung ke menu Profil Saya.

### 🔹 Tahap 3: Implementasi Layout Responsif Multi-Kolom di Dashboard Guru
- Membungkus daftar menu di `DashboardGuruScreen` dengan `LayoutBuilder`.
- Menggunakan breakpoint adaptif (`maxWidth >= 650`):
  - Layar HP: 1 kolom vertikal.
  - Layar Tablet / Landscape: 2 kolom Grid berdampingan (`Row` berpasangan atau `Wrap` / `GridView.count`).
- Memastikan tombol aksi dan badge kelas di dalam kartu tetap rapi dan tidak melar berlebihan.

### 🔹 Tahap 4: Penerapan Desain Serupa di Dashboard Siswa & Finalisasi
- Menerapkan layout responsif 2 kolom di `DashboardSiswaScreen`.
- Menambahkan logo sekolah di AppBar Portal Siswa dan avatar siswa di Hero Card Siswa.
- Pengujian tampilan di emulator tablet (landscape/portrait) dan smartphone.
- Verifikasi static analysis `analyze_files` (0 error) dan push ke kedua repositori GitHub.

---

## 3. Status Rencana Eksekusi
- [x] **Tahap 1: Konfigurasi Navigasi Sistem Android & Sinkronisasi Logo Sekolah**
- [ ] **Tahap 2: Tampilan Dashboard Guru (AppBar Logo & Foto Hero)**
- [ ] **Tahap 3: Implementasi Layout Responsif Multi-Kolom di Dashboard Guru**
- [ ] **Tahap 4: Penerapan Desain Serupa di Dashboard Siswa & Finalisasi**


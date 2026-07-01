# 🎨 Tailwind CSS v3 — Panduan Mutlak untuk AI Agent

> [!IMPORTANT]
> **Pesan untuk AI Coding Agent (Claude, Cursor, Copilot, dsb):**
> Proyek ini menggunakan **Tailwind CSS v3** (versi stabil terakhir: v3.4.x). Baca seluruh dokumen ini sebelum men-styling atau memodifikasi file terkait antarmuka. Aturan di bawah ini bersifat **mutlak** dan overriding pengetahuan Anda tentang Tailwind CSS v4.

Tailwind CSS v3 menggunakan konfigurasi berbasis JavaScript (`tailwind.config.js`) dan PostCSS. Panduan ini mencegah AI menggunakan sintaks v4 seperti `@theme {}`, `@import "tailwindcss"`, atau pola CSS-first yang **tidak ada** di v3.

---

## 0. WAJIB: Self-Check Sebelum Menulis Kode CSS/Class

> [!CAUTION]
> Seiring waktu, training data AI untuk Tailwind v4 semakin banyak. Pastikan Anda tidak mencampur sintaks kedua versi. Jika ragu, cek ke `tailwind.config.js` di root proyek — jika file itu ada, berarti proyek ini v3.

Sebelum menulis atau memodifikasi styling, jalankan checklist ini:

1. **Apakah saya akan menulis `@import "tailwindcss"`?** → STOP. Di v3 harus `@tailwind base/components/utilities`.
2. **Apakah saya akan menulis `@theme {}`?** → STOP. Itu sintaks Tailwind v4. Di v3 gunakan `tailwind.config.js`.
3. **Apakah saya menghapus `tailwind.config.js`?** → STOP. File ini **wajib ada** di v3.
4. **Apakah saya menulis `@tailwindcss/vite` sebagai Vite plugin?** → STOP. Di v3 gunakan PostCSS + `postcss.config.js`.
5. **Apakah class menggunakan `bg-linear-to-r`?** → STOP. Di v3 masih `bg-gradient-to-r`.
6. **Apakah important modifier ditulis `flex!`?** → STOP. Di v3 important di **depan**: `!flex`.
7. **Apakah saya menulis `bg-opacity-50`?** → STOP. Gunakan sintaks slash: `bg-blue-500/50` (direkomendasikan di v3).
8. **Apakah saya menggunakan container queries tanpa plugin?** → STOP. Di v3 butuh `@tailwindcss/container-queries`. Native baru ada di v4.

---

## 1. Setup & Konfigurasi

### Instalasi Tailwind v3 di Laravel

```bash
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p
```

Perintah di atas menghasilkan dua file:
- `tailwind.config.js`
- `postcss.config.js`

### `tailwind.config.js` (WAJIB ADA)

```javascript
// tailwind.config.js
import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    primary: '#1d4ed8',
                    secondary: '#7c3aed',
                },
            },
        },
    },
    plugins: [],
};
```

> [!CAUTION]
> **JANGAN hapus `tailwind.config.js`** — file ini adalah jantung dari konfigurasi Tailwind v3. Tanpanya, semua customisasi tema hilang.

> [!NOTE]
> **Array `content` sangat kritis di v3.** Tailwind v3 menggunakan JIT engine yang hanya men-generate class yang *benar-benar muncul* di file yang terdaftar di `content`. Jika file Blade/JS Anda tidak terdaftar, class-nya tidak akan di-generate dan styling tidak akan muncul di browser.

### `postcss.config.js` (WAJIB ADA)

```javascript
// postcss.config.js
export default {
    plugins: {
        tailwindcss: {},
        autoprefixer: {},
    },
};
```

### `resources/css/app.css` (Entry Point)

```css
/* resources/css/app.css */
@tailwind base;
@tailwind components;
@tailwind utilities;
```

> [!CAUTION]
> **JANGAN** ganti tiga baris `@tailwind` dengan `@import "tailwindcss"` — itu sintaks Tailwind v4 dan tidak akan bekerja di v3.

### `vite.config.js`

```javascript
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
```

> [!NOTE]
> Di Tailwind v3, **tidak ada** `@tailwindcss/vite` plugin. Tailwind berjalan melalui PostCSS, bukan sebagai Vite plugin langsung.

---

## 2. JIT Engine — Selalu Aktif di v3

> [!IMPORTANT]
> Tailwind v3 **selalu menggunakan JIT (Just-In-Time) engine** secara default sejak v3.0. Tidak perlu konfigurasi `mode: 'jit'` seperti di Tailwind v2.

### Implikasi JIT untuk Development

1. **`content` config harus lengkap:** JIT hanya men-generate class yang ditemukan di file yang terdaftar. Class yang tidak ada di file `content` tidak akan di-generate, meski Anda menulisnya secara manual.

2. **Arbitrary values berfungsi penuh:** JIT memungkinkan nilai kustom di dalam kurung kotak `[]`.

3. **Build time lebih cepat:** JIT hanya build class yang dibutuhkan, bukan semua kemungkinan class.

### Arbitrary Values (Nilai Kustom)

Gunakan kurung kotak untuk nilai yang tidak ada di skala default:

```html
<!-- Lebar kustom -->
<div class="w-[137px]">...</div>

<!-- Warna kustom (hex) -->
<div class="bg-[#1da1f2]">...</div>

<!-- Warna kustom (oklch/hsl) -->
<div class="text-[hsl(240,100%,50%)]">...</div>

<!-- Kalkulasi -->
<div class="w-[calc(100%-2rem)]">...</div>

<!-- CSS property kustom (arbitrary property) -->
<div class="[mask-type:luminance]">...</div>

<!-- Kombinasi dengan responsive/state modifier -->
<div class="lg:w-[768px] hover:bg-[#ff6b6b]">...</div>
```

> [!NOTE]
> Arbitrary values adalah fitur sah Tailwind v3 — BUKAN fitur eksklusif v4. Gunakan dengan bebas saat nilai tidak tersedia di scale default.

---

## 3. Kustomisasi Tema (Extend vs Override)

### Extend — Tambahkan ke Palet Default (Direkomendasikan)

```javascript
// tailwind.config.js
export default {
    theme: {
        extend: {
            // Menambah warna baru TANPA menghapus warna default
            colors: {
                brand: {
                    50: '#eff6ff',
                    500: '#3b82f6',
                    900: '#1e3a8a',
                },
            },
            fontFamily: {
                sans: ['Inter', 'Figtree', 'sans-serif'],
                display: ['Outfit', 'sans-serif'],
            },
            spacing: {
                '18': '4.5rem',
                '88': '22rem',
            },
            borderRadius: {
                '4xl': '2rem',
            },
        },
    },
};
```

### Override — Ganti Total (Hati-hati)

```javascript
// Mengganti SELURUH warna (warna default seperti blue, red, dsb akan hilang)
export default {
    theme: {
        colors: {  // tanpa 'extend' = override total
            primary: '#1d4ed8',
            secondary: '#7c3aed',
            white: '#ffffff',
            black: '#000000',
        },
    },
};
```

---

## 4. Dark Mode

Di Tailwind v3, dark mode dikonfigurasi di `tailwind.config.js`:

```javascript
// tailwind.config.js
export default {
    darkMode: 'class',  // atau 'media'
    // ...
};
```

**Strategi `class`** (untuk toggle manual via JavaScript/Alpine):
```html
<!-- Tambah class 'dark' ke <html> untuk aktifkan dark mode -->
<html class="dark">
    <body>
        <div class="bg-white dark:bg-gray-900 text-black dark:text-white">
            Konten
        </div>
    </body>
</html>
```

**Toggle dengan Alpine.js:**
```html
<div x-data="{ dark: false }" :class="{ 'dark': dark }">
    <button @click="dark = !dark">
        Toggle Dark Mode
    </button>
</div>
```

**Strategi `media`** (ikuti preferensi sistem):
```javascript
darkMode: 'media',  // tidak butuh JavaScript toggle
```

> [!NOTE]
> Di Tailwind v4, dark mode dikonfigurasi via `@custom-variant dark` di CSS. Di v3, tetap di `tailwind.config.js` seperti di atas.

---

## 5. Plugins Tailwind v3

Tambahkan plugin di `tailwind.config.js`:

```javascript
// tailwind.config.js
export default {
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
        require('@tailwindcss/aspect-ratio'),
        require('@tailwindcss/container-queries'),  // lihat section berikut
    ],
};
```

```bash
# Install plugin yang dibutuhkan:
npm install -D @tailwindcss/forms @tailwindcss/typography
```

> [!NOTE]
> Di Tailwind v4, plugin dipanggil via `@plugin` di CSS. Di v3, **harus** di `plugins: []` dalam `tailwind.config.js`.

---

## 6. Container Queries di v3

> [!CAUTION]
> **Container queries TIDAK tersedia native di Tailwind v3.** Fitur ini baru hadir native di Tailwind v4.
> Di v3, Anda harus menggunakan plugin terpisah: `@tailwindcss/container-queries`.

```bash
npm install -D @tailwindcss/container-queries
```

```javascript
// tailwind.config.js
export default {
    plugins: [
        require('@tailwindcss/container-queries'),
    ],
};
```

Penggunaan dengan plugin (sintaks berbeda dari native v4):

```html
<!-- Tandai container dengan @container -->
<div class="@container">
    <!-- Responsif terhadap lebar container, bukan viewport -->
    <div class="@md:grid-cols-2 @lg:grid-cols-3 grid grid-cols-1 gap-4">
        ...
    </div>
</div>
```

> [!NOTE]
> Jika Anda melihat contoh container queries tanpa plugin (misal, langsung `@container` di CSS dengan `@` prefix di config), itu kemungkinan sintaks v4. Di v3 butuh plugin.

---

## 7. Penggunaan `@apply`

Gunakan `@apply` di dalam CSS untuk membuat komponen yang reusable:

```css
/* resources/css/app.css */
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer components {
    .btn-primary {
        @apply inline-flex items-center px-4 py-2 bg-blue-600 text-white
               font-semibold rounded-lg shadow hover:bg-blue-700
               focus:outline-none focus:ring-2 focus:ring-blue-500
               transition duration-150 ease-in-out;
    }

    .input-field {
        @apply block w-full rounded-md border-gray-300 shadow-sm
               focus:border-blue-500 focus:ring-blue-500 sm:text-sm;
    }

    .card {
        @apply bg-white rounded-xl shadow-md overflow-hidden p-6;
    }
}
```

**Penggunaan di Blade:**
```html
<button class="btn-primary">Simpan</button>
<input class="input-field" type="text">
<div class="card">Konten</div>
```

---

## 8. Safelist — Class Dinamis

Jika class Tailwind dibangun secara dinamis di PHP atau JavaScript, tambahkan ke `safelist` agar tidak di-purge oleh JIT:

```javascript
// tailwind.config.js
export default {
    content: ['./resources/views/**/*.blade.php'],
    safelist: [
        'bg-red-500',
        'bg-green-500',
        'bg-blue-500',
        // Pattern regex untuk range:
        {
            pattern: /bg-(red|green|blue)-(100|500|900)/,
        },
    ],
};
```

> [!NOTE]
> Di Tailwind v4, safelist diganti dengan `@source inline(...)` di CSS. Di v3, **harus** di `safelist: []` dalam `tailwind.config.js`.

---

## 9. Integrasi dengan Livewire v3

Tidak ada aturan khusus yang kompleks. Gunakan class Tailwind langsung di HTML Blade seperti biasa:

```html
{{-- resources/views/livewire/search-users.blade.php --}}
<div class="max-w-2xl mx-auto p-6">
    <input
        wire:model.live="search"
        type="text"
        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        placeholder="Cari user..."
    >

    <div class="mt-4 space-y-2">
        @foreach ($this->results as $user)
            <div class="flex items-center p-3 bg-white rounded-lg shadow">
                <span class="font-medium text-gray-900">{{ $user->name }}</span>
            </div>
        @endforeach
    </div>
</div>
```

---

## 10. Responsive Design

```html
<!-- Mobile first: default, lalu override untuk layar lebih besar -->
<div class="
    grid grid-cols-1
    sm:grid-cols-2
    md:grid-cols-3
    lg:grid-cols-4
    xl:grid-cols-5
    gap-4
">
    <!-- Items -->
</div>
```

**Breakpoint default Tailwind v3:**

| Breakpoint | Min Width |
|------------|-----------|
| `sm` | 640px |
| `md` | 768px |
| `lg` | 1024px |
| `xl` | 1280px |
| `2xl` | 1536px |

---

## 11. Opacity Warna di v3 — PENTING

> [!IMPORTANT]
> Ada kebingungan umum soal cara menambahkan opacity warna di v3. Berikut kejelasannya:

### ✅ Cara yang DIREKOMENDASIKAN: Slash Syntax

Sintaks slash (`/`) untuk opacity warna sudah tersedia dan **direkomendasikan sejak Tailwind v3**:

```html
<!-- Background dengan opacity -->
<div class="bg-blue-500/50">...</div>    <!-- 50% opacity -->
<div class="bg-blue-500/75">...</div>    <!-- 75% opacity -->
<div class="bg-blue-500/10">...</div>    <!-- 10% opacity -->

<!-- Text dengan opacity -->
<p class="text-gray-900/80">...</p>

<!-- Border dengan opacity -->
<div class="border border-blue-500/30">...</div>

<!-- Arbitrary opacity -->
<div class="bg-blue-500/[0.35]">...</div>
```

### ⚠️ Cara LAMA yang DEPRECATED: `bg-opacity-*`

Utility `bg-opacity-*`, `text-opacity-*`, dsb. **masih berfungsi di v3** (tidak error), tapi sudah **deprecated** dan dihapus total di v4. Hindari penggunaannya:

```html
<!-- ⚠️ DEPRECATED di v3 — jangan gunakan untuk kode baru -->
<div class="bg-blue-500 bg-opacity-50">...</div>

<!-- ✅ Gunakan ini sebagai gantinya -->
<div class="bg-blue-500/50">...</div>
```

---

## 12. Perbedaan Penting v3 vs v4

> [!CAUTION]
> Tabel ini adalah sumber kebenaran mutlak. AI agent harus selalu mengacu ke kolom v3 saat menulis kode untuk proyek ini.

| Aspek | ✅ Tailwind v3 (yang kita pakai) | ❌ Tailwind v4 (JANGAN dipakai) |
|-------|----------------------------------|----------------------------------|
| **File konfigurasi** | `tailwind.config.js` (wajib ada) | Tidak ada config JS |
| **Entry CSS** | `@tailwind base/components/utilities` | `@import "tailwindcss"` |
| **Kustomisasi tema** | `theme.extend` di config JS | `@theme {}` di CSS |
| **Dark mode config** | `darkMode: 'class'` di config JS | `@custom-variant dark` di CSS |
| **Plugin** | `require('@tailwindcss/forms')` di config | `@plugin` di CSS |
| **Safelist** | `safelist: []` di config | `@source inline(...)` di CSS |
| **Vite plugin** | Tidak ada — pakai PostCSS | `@tailwindcss/vite` |
| **Gradient** | `bg-gradient-to-r` | `bg-linear-to-r` |
| **Important modifier** | `!flex` (tanda seru di **depan**) | `flex!` (tanda seru di **belakang**) |
| **Opacity warna** | `bg-blue-500/50` ✅ direkomendasikan — `bg-opacity-50` deprecated tapi masih jalan | `bg-blue-500/50` satu-satunya cara, `bg-opacity-*` dihapus total |
| **Container queries** | Butuh plugin `@tailwindcss/container-queries` | Native, tanpa plugin |
| **JIT engine** | Aktif by default (sejak v3.0) | Selalu aktif |

---

## 🛑 Tabel Cepat DOs and DON'Ts AI Agent

| Kategori | ❌ JANGAN | ✅ LAKUKAN |
|----------|-----------|-----------|
| **Config** | Hapus `tailwind.config.js` | Pertahankan & edit `tailwind.config.js` |
| **Config** | Tulis `@theme {}` di CSS | Gunakan `theme.extend` di config JS |
| **Entry CSS** | `@import "tailwindcss"` | `@tailwind base; @tailwind components; @tailwind utilities` |
| **PostCSS** | Hapus `postcss.config.js` | Pertahankan, wajib ada untuk v3 |
| **Plugin** | `@plugin "@tailwindcss/forms"` | `require('@tailwindcss/forms')` di config |
| **Gradient** | `bg-linear-to-r` | `bg-gradient-to-r` |
| **Dark mode** | `@custom-variant dark` di CSS | `darkMode: 'class'` di config JS |
| **Important** | `flex!` (tanda seru belakang) | `!flex` (tanda seru depan) |
| **Opacity** | `bg-blue-500 bg-opacity-50` | `bg-blue-500/50` |
| **Safelist dinamis** | `@source inline(...)` | `safelist: []` di config JS |
| **Container queries** | Langsung pakai `@container` tanpa plugin | Install `@tailwindcss/container-queries` dulu |
| **Content array kosong** | `content: []` | Isi dengan semua path file Blade & JS |

---

## 📚 Referensi

| Resource | URL |
|----------|-----|
| Dokumentasi Tailwind v3 | https://v3.tailwindcss.com/docs |
| Arbitrary values v3 | https://v3.tailwindcss.com/docs/adding-custom-styles#using-arbitrary-values |
| Plugin container queries | https://github.com/tailwindlabs/tailwindcss-container-queries |
| Tailwind v4 (untuk kontras) | https://tailwindcss.com/docs |

---

*Dokumentasi ini untuk stack Laravel 12 + Livewire v3 + Tailwind CSS v3 + Alpine v3 + Filament v4.*
*Diperbarui 1 Juli 2026 — semua klaim teknis diverifikasi ke dokumentasi resmi Tailwind v3.4.x.*

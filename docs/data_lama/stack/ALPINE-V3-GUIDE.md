# 🏔️ Alpine.js v3 — Panduan Mutlak untuk AI Agent

> [!IMPORTANT]
> **Pesan untuk AI Coding Agent (Claude, Cursor, Copilot, dsb):**
> Proyek ini menggunakan **Alpine.js v3** yang **terintegrasi secara native di dalam Livewire v4**. Baca seluruh dokumen ini untuk menghindari kesalahan umum di mana AI mengasumsikan pola Alpine v2 usang dari *training data* masa lalu. Aturan ini bersifat **mutlak**.

Alpine.js v3 memiliki banyak perbedaan dari v2 (terutama dalam menangani inisialisasi, *scope*, dan registrasi fungsi). Kesalahan dalam memahami transisi ini akan menyebabkan *silent visual bugs* atau DOM yang rusak.

---

## 1. 🛑 Tidak Perlu Instalasi CDN (Sudah Bundled dengan Livewire)

Livewire v3 dan v4 secara otomatis menginjeksikan Alpine.js. 

> [!CAUTION]
> - **DILARANG KERAS** menyarankan atau menyuntikkan tag `<script src="https://cdn.jsdelivr.net/.../alpine.js"></script>` secara manual di *layout* utama jika halaman tersebut menggunakan Livewire.
> - **Pengecualian:** Jika untuk suatu alasan langka Anda harus memasangnya di halaman statis murni tanpa Livewire sama sekali, Anda **wajib** menyertakan atribut `defer` pada tag `<script>`.

---

## 2. Integrasi Livewire 4 (Matinya `@entangle` Blade Directive)

### 🚨 `@entangle` Blade Directive DILARANG
> [!CAUTION]
> Directive Blade `@entangle('prop')` **sudah deprecated di Livewire v4**. Menggunakannya akan memicu masalah saat menghapus elemen DOM. Jangan pernah menggunakan sintaks `x-data="{ title: @entangle('title') }"`.

### ✅ Prioritas 1: Akses Properti Secara Langsung (Default)
Di dalam *scope* Alpine apa pun yang berada di bawah komponen Livewire, objek proxy **`$wire`** tersedia secara otomatis tanpa perlu di-*passing*. Gunakan ini untuk mengakses atau memodifikasi properti (jauh lebih efisien dari *entangling* manual karena tidak menduplikasi state):
- **Binding input:** `<input x-model="$wire.title">`
- **Akses nilai:** `<span x-text="$wire.title"></span>`

### ✅ Prioritas 2: `$wire.entangle()` (Kasus Langka Sinkronisasi Dua Arah)
Jika Anda benar-benar membutuhkan *two-way binding* di dalam objek `x-data` kompleks (misal untuk komponen pihak ketiga), gunakan **method** `$wire.entangle()`, BUKAN *directive* Blade:
- **Default (Deferred):** `x-data="{ title: $wire.entangle('title') }"`
- **Real-time (Live):** Jika butuh sinkronisasi langsung ke server, tambahkan `.live`: `x-data="{ title: $wire.entangle('title').live }"`
*(Catatan: pola lama `.defer` dari v2 sudah dihapus).*

### Menjalankan Fungsi Server (`$wire`)
AI dilarang menggunakan API `window.livewire.emit` (ini adalah pola usang Livewire v2).
- Gunakan `$wire` untuk memanggil method di komponen PHP: `<button x-on:click="$wire.save()">Simpan</button>`.
- Jika ingin men-*dispatch* *event* Livewire: `<button x-on:click="$wire.dispatch('post-created')">Buat</button>`.

---

## 3. 🚨 JEBAKAN V2: 5 Pola Usang yang Wajib Dihindari

Ini adalah jantung dari panduan ini. Data latih AI lama penuh dengan sintaks v2 yang kini akan merusak UI Anda secara diam-diam. AI **DILARANG KERAS** menggunakan 5 pola v2 berikut:

1. ❌ **`@click.away`**
   - **Salah:** `<div @click.away="open = false">`
   - ✅ **Ganti Menjadi:** `<div @click.outside="open = false">`. (Ini sumber *bug dropdown/modal* paling umum).

2. ❌ **`x-init="init()"`**
   - **Salah:** `<div x-data="dropdown" x-init="init()">`
   - ✅ **Ganti Menjadi:** Cukup `<div x-data="dropdown">`. Di v3, jika sebuah objek memiliki *method* bernama `init()`, Alpine akan mengeksekusinya secara otomatis tanpa perlu dipanggil. Memanggilnya manual via `x-init` adalah berlebihan.

3. ❌ **`x-init` return function**
   - **Salah:** `<div x-init="() => { return () => { console.log('Selesai inisialisasi') } }">`
   - ✅ **Ganti Menjadi:** Gunakan `$nextTick()` untuk menunda eksekusi hingga DOM ter-render sempurna: `<div x-init="$nextTick(() => { console.log('Selesai inisialisasi') })">`

4. ❌ **Mengasumsikan `$el` = Root Element**
   - Di v2, `$el` selalu merujuk ke elemen root komponen.
   - ✅ **Penting:** Di v3, `$el` merujuk ke elemen DOM **tempat ia ditulis/dievaluasi saat itu**. Jika Anda butuh mengambil elemen root dari child, gunakan `$root`. Ini adalah bahaya *silent bug* yang fatal.

5. ❌ **Named Function dengan Kurung (`x-data="dropdown()"`)**
   - **Salah (Untuk Ekstraksi):** Menulis fungsi global `function dropdown() { return {} }` dan dipanggil dengan `x-data="dropdown()"`.
   - ✅ **Benar:** Wajib diregistrasi via `Alpine.data()` dan dipanggil **tanpa kurung** (`x-data="dropdown"`). Lihat penjelasan lengkap di Section 4.
   - *Pengecualian:* Object *inline* literal murni seperti `x-data="{ open: false }"` tetap sah dan sangat disarankan untuk komponen simpel.

---

## 4. Ekstraksi Logika (`Alpine.data`) & `x-cloak`

### A. Ekstraksi Komponen Kompleks
Untuk logika yang rumit (misal: sistem *billing*, render MathJax/KaTeX untuk bank soal), hindari *HTML pollution* dengan ratusan baris JS *inline* di dalam atribut `x-data`. Ekstrak menggunakan `Alpine.data`:

```javascript
// Di file JS atau blok <script>
document.addEventListener('alpine:init', () => {
    Alpine.data('dropdown', () => ({
        open: false,
        toggle() {
            this.open = ! this.open
        },
        init() {
            // Otomatis tereksekusi di v3!
        }
    }))
})
```

```html
<!-- Di file Blade -->
<div x-data="dropdown">
    <!-- Panggilan di atas TANPA tanda kurung () -->
    <button @click="toggle()">Toggle</button>
    <div x-show="open" x-cloak>Konten Menu</div>
</div>
```

### B. Kewajiban `x-cloak`
Elemen yang dirender kondisional (seperti modal dan *dropdown*) wajib diberi atribut `x-cloak` agar tidak mengalami *flicker* (berkedip terlihat sebelum JS selesai *load*). Pastikan *style* penunjangnya sudah ada di CSS utama Anda:
```css
[x-cloak] { display: none !important; }
```

---

## 5. Dilarang Keras Mencampur jQuery / Manipulasi DOM Murni

Karena arsitektur *Morphdom* dari Livewire terus-menerus memperbarui DOM HTML, **jangan gunakan** pendekatan berbasis ID seperti `document.getElementById('foo').innerHTML = 'bar'` atau pustaka *jQuery*.

**Aturan Mutlak:** 
- Gunakan 100% kapabilitas Alpine (`x-text`, `x-html`, `x-show`, *data binding*) untuk mengubah UI di sisi klien. Memanipulasi DOM secara manual akan menyebabkan *state* Alpine rusak seketika saat Livewire melakukan *roundtrip* ke server.

---

## 🛑 Tabel Cepat DOs and DON'Ts AI Agent

| Skenario | ❌ DON'T (JANGAN LAKUKAN INI) | ✅ DO (LAKUKAN INI) |
|----------|--------------------------------|----------------------|
| **Instalasi Alpine** | Suntik CDN `<script>` manual jika pakai Livewire | Biarkan Livewire yang menangani |
| **Akses State Livewire** | `x-data="{ title: @entangle('title') }"` (Blade Directive) | Langsung pakai `$wire.title` (atau `$wire.entangle` jika terpaksa) |
| **Memanggil Event** | `window.livewire.emit('foo')` | `$wire.dispatch('foo')` |
| **Klik di luar elemen** | `@click.away` (Alpine v2) | `@click.outside` (Alpine v3) |
| **Inisialisasi Method** | `x-data="foo" x-init="init()"` | `x-data="foo"` (init tereksekusi otomatis) |
| **Ekstraksi Komponen** | `x-data="myFunc()"` (fungsi global biasa) | `x-data="myFunc"` (registrasi via `Alpine.data`) |
| **Mencari Elemen Root**| `$el` di *child component* | `$root` |

---
*Dokumentasi ini otomatis didaftarkan sebagai context untuk AI agent melalui `boost:guideline add`.*

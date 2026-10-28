# ⚡ Livewire v3 — Panduan Mutlak untuk AI Agent

> [!IMPORTANT]
> **Pesan untuk AI Coding Agent (Claude, Cursor, Copilot, dsb):**
> Proyek ini menggunakan **Livewire v3**. Baca seluruh dokumen ini sebelum membuat atau memodifikasi komponen Livewire. Aturan di bawah ini bersifat **mutlak** dan overriding pengetahuan Anda tentang Livewire v2 maupun v4.

Livewire v3 adalah versi major dengan perubahan signifikan dari v2: perubahan `wire:model`, `navigate`, dan fitur teleport. Panduan ini mencegah AI menggunakan sintaks v2 yang sudah usang maupun menggunakan fitur v4 (seperti Islands dan SFC native).

---

## 0. WAJIB: Verifikasi Versi Sebelum Coding

> [!CAUTION]
> **Jangan langsung asumsikan versi Livewire.** Beberapa project lama mungkin masih berjalan di Livewire v2. Salah asumsi = kode error.

Sebelum menulis atau mengubah komponen apa pun:

```bash
php artisan livewire:about
# atau cek langsung
cat composer.json | grep livewire/livewire
```

- Jika versi **3.x** → ikuti seluruh panduan ini.
- Jika versi **2.x** → **JANGAN** terapkan `wire:model.live`, Volt, atau navigate. Gunakan sintaks v2 sampai project di-upgrade secara eksplisit oleh user.
- Jika ragu, **tanyakan ke user** versi Livewire yang dipakai sebelum lanjut.

---

## 1. Membuat Komponen di Livewire v3

Proyek ini sepenuhnya menggunakan pola **Class + View Terpisah** standar (tidak menggunakan Volt).

Pendekatan tradisional yang didukung penuh di v3:

```bash
php artisan make:livewire NamaKomponen
```

Menghasilkan dua file:
- `app/Livewire/NamaKomponen.php` — class PHP berisi logika
- `resources/views/livewire/nama-komponen.blade.php` — template Blade

**Contoh class:**
```php
<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;

class SearchUsers extends Component
{
    #[Validate('required|min:2')]
    public string $search = '';

    #[Computed]
    public function results()
    {
        return User::where('name', 'like', "%{$this->search}%")->get();
    }

    public function render()
    {
        return view('livewire.search-users');
    }
}
```

**Contoh view:**
```html
<div>
    <input wire:model.live="search" type="text" placeholder="Cari user...">

    @foreach ($this->results as $user)
        <div>{{ $user->name }}</div>
    @endforeach
</div>
```

---


## 2. PHP Attributes di Livewire v3

Livewire v3 memperkenalkan **PHP Attributes** sebagai cara modern menggantikan properti array di v2:

| ❌ JANGAN (Gaya v2) | ✅ GUNAKAN (Gaya v3) |
|---------------------|----------------------|
| `protected $rules = [...]` | `#[Validate('required')]` di atas properti |
| `protected $listeners = [...]` | `#[On('event-name')]` di atas method |
| `public function getFooProperty()` | `#[Computed]` + method `foo()` |
| `protected $queryString = [...]` | `#[Url]` di atas properti |
| `protected $layout = 'layouts.app'` | `#[Layout('layouts.app')]` di class |

> [!NOTE]
> Gaya properti array v2 (`$rules`, `$listeners`, dll) masih bisa dipakai di v3, tapi **sangat disarankan** pakai PHP Attributes untuk kode baru.

---

## 3. `wire:model` — Perubahan Penting di v3

> [!CAUTION]
> **Breaking change dari v2:** Di Livewire v3, `wire:model` tidak lagi *auto-sync* secara real-time. Kini bersifat **deferred** (sync saat form submit) secara default.
>
> **Sangat Penting:** Modifier `.blur` dan `.change` di v3 **hanya** menunda *network request* ke server. Di sisi client, sinkronisasi state ke object `$wire` (misalnya untuk Alpine.js) tetap terjadi secara instan (real-time) setiap ketikan. Jangan mengasumsikan delay tersebut berlaku juga di DOM client.

### Modifier yang tersedia:

| Modifier | Kapan Sync | Kapan Dipakai |
|----------|-----------|---------------|
| `wire:model` | Saat form submit | Input yang tidak butuh live update |
| `wire:model.live` | Setiap keystroke | Search field, filter real-time |
| `wire:model.blur` | Saat kehilangan fokus | Input form biasa dengan validasi |
| `wire:model.change` | Saat value berubah | Dropdown, checkbox |
| `wire:model.lazy` | Alias `.blur` (kompatibilitas) | Warisan dari v2 |
| `wire:model.debounce.300ms` | Setelah berhenti ketik 300ms | Search dengan debounce |

```html
{{-- Live search: sync setiap keystroke --}}
<input wire:model.live="search" type="text">

{{-- Form biasa: sync saat blur --}}
<input wire:model.blur="email" type="email">

{{-- Submit form: sync saat submit --}}
<input wire:model="name" type="text">
<button wire:click="save">Simpan</button>

{{-- Dengan debounce --}}
<input wire:model.live.debounce.500ms="search">
```

---

## 4. Fitur Navigasi: `wire:navigate`

Livewire v3 memperkenalkan SPA-style navigation tanpa memerlukan Inertia.js:

```html
{{-- Link biasa (full page reload) --}}
<a href="/dashboard">Dashboard</a>

{{-- wire:navigate: ganti konten tanpa full reload --}}
<a href="/dashboard" wire:navigate>Dashboard</a>

{{-- Prefetch on hover --}}
<a href="/dashboard" wire:navigate.hover>Dashboard</a>
```

> [!NOTE]
> `wire:navigate` bekerja menggunakan history.pushState dan hanya mengganti body halaman. Sangat cocok untuk navigasi antar halaman dalam app yang sama.

---

## 5. Loading States (`wire:loading`)

Di Livewire v3, gunakan `wire:loading` untuk menampilkan/menyembunyikan elemen saat request berjalan:

```html
{{-- Tampilkan saat loading --}}
<div wire:loading>
    <span>Memuat...</span>
</div>

{{-- Sembunyikan saat loading --}}
<div wire:loading.remove>
    <span>Konten normal</span>
</div>

{{-- Loading pada target spesifik --}}
<button wire:click="save">
    Simpan
    <span wire:loading wire:target="save">...</span>
</button>

{{-- Disable button saat loading --}}
<button wire:click="save" wire:loading.attr="disabled">
    Simpan
</button>
```

> [!NOTE]
> **Berbeda dengan Livewire v4:** Di v3 masih menggunakan `wire:loading`, `wire:loading.attr`, `wire:loading.remove`. Jangan gunakan `data-loading:*` (itu sintaks Tailwind v4 untuk Livewire v4).

---

## 6. Lazy Loading

```php
use Livewire\Attributes\Lazy;

#[Lazy]
class HeavyComponent extends Component
{
    public function render()
    {
        return view('livewire.heavy-component');
    }

    // Tampilan placeholder saat belum dimuat:
    public function placeholder()
    {
        return <<<'HTML'
        <div class="animate-pulse bg-gray-200 h-32 rounded"></div>
        HTML;
    }
}
```

---

## 7. Persist — State Bertahan Saat Navigasi

```php
use Livewire\Attributes\Session;

class ShoppingCart extends Component
{
    // Data akan bertahan di session antar halaman
    #[Session]
    public array $cartItems = [];
}
```

---

## 8. Teleport

Render konten ke bagian lain di DOM (misalnya ke `<body>` untuk modal):

```html
<div>
    <button wire:click="$set('showModal', true)">Buka Modal</button>

    @teleport('body')
        @if($showModal)
            <div class="fixed inset-0 bg-black/50 flex items-center justify-center">
                <div class="bg-white p-6 rounded-lg">
                    <h2>Modal Title</h2>
                    <button wire:click="$set('showModal', false)">Tutup</button>
                </div>
            </div>
        @endif
    @endteleport
</div>
```

---

## 9. Routing Komponen

```php
// routes/web.php

// Komponen class:
Route::get('/dashboard', DashboardComponent::class);

// Dengan middleware dan layout:
Route::get('/admin', AdminDashboard::class)
    ->middleware('auth')
    ->layout('layouts.admin');
```

---

## 10. Integrasi JS Pihak Ketiga (`wire:ignore`)

> [!CAUTION]
> Library JS yang memanipulasi DOM (Flatpickr, Select2, TinyMCE, dll) akan **rusak** saat Livewire melakukan re-render karena Morphdom menimpa perubahan DOM mereka.

Tambahkan `wire:ignore` pada wrapper element:

```html
{{-- ✅ Benar: wire:ignore di wrapper div, bukan di input --}}
<div wire:ignore>
    <input type="text" id="tanggal" x-data x-init="flatpickr($el, { dateFormat: 'd/m/Y' })">
</div>

{{-- ✅ Benar: Select2 dengan Alpine --}}
<div wire:ignore>
    <select id="kategori" x-data x-init="$($el).select2()">
        @foreach($options as $opt)
            <option value="{{ $opt->id }}">{{ $opt->name }}</option>
        @endforeach
    </select>
</div>
```

---

## 11. Testing Komponen Livewire (Pest)

```php
<?php

use Livewire\Livewire;
use App\Livewire\SearchUsers;

it('dapat mencari user berdasarkan nama', function () {
    $user = User::factory()->create(['name' => 'Budi Santoso']);

    Livewire::test(SearchUsers::class)
        ->set('search', 'Budi')
        ->assertSee('Budi Santoso');
});

it('validasi berhasil saat search kosong', function () {
    Livewire::test(SearchUsers::class)
        ->set('search', '')
        ->call('doSearch')
        ->assertHasErrors(['search' => 'required']);
});
```

---

## 🛑 Tabel Referensi Cepat DOs and DON'Ts

| Kategori | ❌ DON'T (JANGAN) | ✅ DO (LAKUKAN) |
|----------|-------------------|-----------------|
| **Cek versi** | Langsung asumsi versi Livewire | Cek via `php artisan livewire:about` |
| **wire:model** | `wire:model="search"` untuk live search | `wire:model.live="search"` |
| **wire:model v2** | `wire:model.lazy` untuk lazy update | `wire:model.blur` (di v3) |
| **Loading** | `data-loading:opacity-50` (sintaks v4) | `wire:loading.attr="disabled"` |
| **JS Eksternal** | Library DOM manipulation tanpa `wire:ignore` | Bungkus dengan `<div wire:ignore>` |
| **Navigasi** | Semua link pakai `<a>` biasa | `wire:navigate` untuk SPA feel |
| **Computed v2** | `public function getFooProperty()` | `#[Computed]` + method `foo()` |

---

## 12. Kompatibilitas Stack

```
Livewire v3   : PHP 8.1+, Laravel 10+
Stack ini     : PHP 8.2, Laravel 12, Tailwind v3, Alpine.js v3
Filament v4   : Berjalan di atas Livewire v3 ✅
```

---

*Dokumentasi ini untuk stack Laravel 12 + Livewire v3 + Tailwind v3 + Alpine v3 + Filament v4.*

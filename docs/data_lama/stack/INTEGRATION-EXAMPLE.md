# 🎯 Rosetta Stone: Contoh Integrasi TALL Stack v3

Dokumen ini mendemonstrasikan bagaimana **Livewire v3, Tailwind CSS v3, dan Alpine.js v3** diintegrasikan dalam satu contoh nyata. Kasus yang diambil: **Input Tanggal Lahir** menggunakan library eksternal *Flatpickr*.

Gunakan file ini sebagai rujukan utama (SOP) saat AI agent perlu merajut berbagai teknologi menjadi satu kesatuan.

> [!NOTE]
> Stack yang digunakan: **Laravel 12 · Livewire v3 · Tailwind CSS v3 · Alpine.js v3**

---

## Contoh A: Multi-File Component (MFC) — Pola Utama di Livewire v3

### `app/Livewire/Siswa/FormTanggalLahir.php`

```php
<?php

namespace App\Livewire\Siswa;

use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;
use Carbon\Carbon;

class FormTanggalLahir extends Component
{
    // 1. PHP Attributes gaya Livewire v3
    #[Validate('required|date_format:Y-m-d')]
    public string $tanggalLahir = '';

    public function mount(string $initialDate = ''): void
    {
        $this->tanggalLahir = $initialDate ?: Carbon::now()->subYears(7)->format('Y-m-d');
    }

    #[Computed]
    public function umurSiswa(): int
    {
        return Carbon::parse($this->tanggalLahir)->age;
    }

    public function simpanTanggal(): void
    {
        $this->validate();

        // Logika simpan ke database...

        $this->dispatch('tanggal-tersimpan');
    }

    public function render()
    {
        return view('livewire.siswa.form-tanggal-lahir');
    }
}
```

### `resources/views/livewire/siswa/form-tanggal-lahir.blade.php`

```html
<!-- 2. Alpine x-data (memanggil nama fungsi terdaftar) -->
<div class="p-6 max-w-md mx-auto bg-white rounded-xl shadow-md" x-data="datePickerForm">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Input Tanggal Lahir (Dapodik)</h2>

    <!-- 3. wire:ignore membungkus elemen eksternal (Flatpickr) -->
    <div class="mb-4" wire:ignore>
        <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Tanggal</label>

        <!-- Input Flatpickr. x-ref untuk referensi instance -->
        <input
            type="text"
            x-ref="dateInput"
            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500/50 outline-none"
            placeholder="YYYY-MM-DD"
        >
    </div>

    <!-- Tampilkan validasi Livewire -->
    @error('tanggalLahir')
        <p class="text-red-500 text-sm mt-1 mb-3">{{ $message }}</p>
    @enderror

    <!-- 4. Binding $wire langsung ke Computed Property -->
    <div class="mb-6 p-3 bg-gray-50 rounded-md">
        <p class="text-sm text-gray-600">
            Usia terhitung: <span class="font-bold text-indigo-600" x-text="$wire.umurSiswa"></span> tahun
        </p>
    </div>

    <!-- 5. Livewire v3: wire:loading.attr untuk loading state -->
    <button
        x-on:click="$wire.simpanTanggal()"
        wire:loading.attr="disabled"
        wire:loading.class="opacity-50 cursor-not-allowed"
        class="w-full py-2 px-4 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors"
    >
        <span wire:loading.remove>Simpan Data Dapodik</span>
        <span wire:loading>Menyimpan...</span>
    </button>
</div>

<!-- 6. Script Alpine (terpisah agar HTML tetap bersih) -->
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('datePickerForm', () => ({
            fp: null,

            init() {
                // Inisialisasi library eksternal (terlindungi oleh wire:ignore)
                // this.$wire tersedia di Livewire v3 saat Alpine mount
                this.fp = flatpickr(this.$refs.dateInput, {
                    dateFormat: "Y-m-d",
                    defaultDate: this.$wire.tanggalLahir,
                    onChange: (selectedDates, dateStr) => {
                        // Sinkronisasi manual dari Flatpickr ke Livewire
                        this.$wire.tanggalLahir = dateStr;
                    }
                });
            },

            // Alpine v3 panggil destroy() otomatis saat elemen dihapus dari DOM
            destroy() {
                if (this.fp) {
                    this.fp.destroy();
                }
            }
        }));
    });
</script>

<!-- 7. Tailwind v3: scoped style biasa (tidak perlu @reference) -->
<style>
    /* Override style internal Flatpickr jika perlu */
    .flatpickr-calendar {
        font-family: 'Inter', sans-serif;
        border-radius: 0.5rem;
    }
</style>
```

---

## Mengapa Pola Ini Anti-Bug?

1. **`wire:ignore` di tempat yang benar:** Ditempatkan di `<div>` pembungkus, bukan hanya di `<input>`. Saat Flatpickr menyuntikkan *popup kalender* ke DOM, Livewire tidak akan "membersihkannya" saat komponen re-render.

2. **Alpine `init()` otomatis:** Kita tidak menulis `x-init="init()"`. Method `init()` dieksekusi otomatis oleh Alpine v3 saat blok `x-data` di-*mount*.

3. **Komunikasi state efisien:** Data bergerak lewat inisialisasi (`this.$wire.tanggalLahir`) dan *update* eksplisit (`this.$wire.tanggalLahir = dateStr`). Tidak ada `@entangle` yang menduplikasi *state*.

4. **Tailwind v3 `@apply` via CSS:** Konfigurasi tema berada di `tailwind.config.js`, bukan di `@theme {}` CSS. Gunakan `@apply` di `app.css` untuk komponen reusable.

5. **PHP Attributes Livewire v3:** `#[Validate]`, `#[Computed]` adalah cara modern di v3. Jangan pakai array `$rules = []` atau method `getFooProperty()` (gaya v2).

---

## ⚠️ Tabel Perbedaan Penting: Livewire v3 vs v4

> [!CAUTION]
> AI agent sering mencampur sintaks v3 dan v4. Tabel ini membantu mencegah kesalahan tersebut.

| Fitur | ❌ Livewire v4 (JANGAN) | ✅ Livewire v3 (GUNAKAN) |
|-------|------------------------|--------------------------|
| **Loading state** | `data-loading:opacity-50` | `wire:loading.class="opacity-50"` |
| **Loading disable** | `data-loading:cursor-not-allowed` | `wire:loading.attr="disabled"` |
| **Lazy loading** | `wire:lazy` | `#[Lazy]` attribute di class |
| **wire:model** | Default = live (sync tiap keystroke) | Default = deferred (sync saat submit) |
| **wire:model live** | `wire:model` sudah live | `wire:model.live` |
| **Alpine inject** | Otomatis via Vite (tanpa manual) | Perlu `@livewireStyles` + `@livewireScripts` |

---

> [!WARNING]
> **Kewaspadaan Reaktivitas `$wire` pada Computed Property:**
> Pada `<span x-text="$wire.umurSiswa"></span>`, *Computed Properties* dievaluasi di backend PHP. Saat Anda mengubah nilai tanggal melalui Flatpickr secara lokal (client-side), nilai `umurSiswa` **TIDAK AKAN** seketika berubah sampai ada roundtrip ke server. Pertimbangkan `$wire.$refresh()` jika UI butuh respons real-time sebelum submit.

> [!NOTE]
> **Pembersihan Resource Eksternal:**
> Gunakan method `destroy()` pada objek `Alpine.data()` untuk membersihkan instance eksternal (seperti `this.fp.destroy()`). Alpine v3 memanggil ini otomatis saat komponen dihapus dari DOM, mencegah *memory leak*.

---

*Dokumentasi ini untuk stack Laravel 12 + Livewire v3 + Tailwind v3 + Alpine v3 + Filament v4.*

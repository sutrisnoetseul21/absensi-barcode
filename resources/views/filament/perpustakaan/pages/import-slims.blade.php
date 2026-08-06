<x-filament-panels::page>

    {{-- ═══════════════════════════════════════════════════════════════════════
         STEP 1: Form Koneksi (tampil jika belum terkoneksi)
    ═══════════════════════════════════════════════════════════════════════ --}}
    @if (!$slimsConnected)
        <div class="space-y-4">

            {{-- Info Banner --}}
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-700 dark:bg-blue-950/40">
                <div class="flex gap-3">
                    <x-heroicon-o-information-circle class="mt-0.5 h-5 w-5 shrink-0 text-blue-500" />
                    <div>
                        <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">Cara Penggunaan</p>
                        <p class="mt-1 text-xs text-blue-700 dark:text-blue-400">
                            Isi kredensial database SLiMS di bawah ini, lalu klik <strong>Tes Koneksi</strong>.
                            Data koneksi hanya disimpan sementara di session — tidak akan tersimpan ke file <code>.env</code>.
                            Setelah terhubung, Anda bisa memilih menu import atau download data sebagai Excel.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Form Koneksi --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <form wire:submit.prevent="testKoneksi">
                    {{ $this->form }}

                    <div class="mt-6">
                        <x-filament::button
                            type="submit"
                            icon="heroicon-o-signal"
                            wire:loading.attr="disabled"
                            wire:target="testKoneksi"
                        >
                            <span wire:loading.remove wire:target="testKoneksi">Tes Koneksi ke SLiMS</span>
                            <span wire:loading wire:target="testKoneksi">Mengecek koneksi...</span>
                        </x-filament::button>
                    </div>
                </form>
            </div>
        </div>

    {{-- ═══════════════════════════════════════════════════════════════════════
         STEP 2: Dashboard Import (tampil setelah koneksi berhasil)
    ═══════════════════════════════════════════════════════════════════════ --}}
    @else
        <div class="space-y-5">

            {{-- Badge Koneksi Aktif --}}
            <div class="flex items-center gap-2 rounded-xl border border-green-200 bg-green-50 px-4 py-3 dark:border-green-700 dark:bg-green-950/40">
                <span class="inline-flex h-2.5 w-2.5 animate-pulse rounded-full bg-green-500"></span>
                <span class="text-sm font-semibold text-green-800 dark:text-green-300">
                    Terhubung ke database:
                    <span class="font-mono font-bold">{{ app(\App\Services\SlimsConnectionService::class)->getDatabaseName() }}</span>
                </span>
            </div>

            {{-- ⚠️ WARNING BANNER --}}
            <div class="rounded-xl border-2 border-red-400 bg-red-50 p-4 dark:border-red-600 dark:bg-red-950/40">
                <div class="flex gap-3">
                    <x-heroicon-o-exclamation-triangle class="mt-0.5 h-5 w-5 shrink-0 text-red-600 dark:text-red-400" />
                    <div>
                        <p class="text-sm font-bold text-red-800 dark:text-red-300">⚠️ PERHATIAN — Data ERP akan di-OVERWRITE!</p>
                        <p class="mt-1 text-xs text-red-700 dark:text-red-400">
                            Proses import akan <strong>menimpa (overwrite)</strong> data yang sudah ada di ERP berdasarkan ISBN, kode eksemplar, atau nama DDC.
                            Pastikan Anda sudah <strong>backup database ERP</strong> sebelum menjalankan import.
                            Urutan yang benar: <strong>DDC → Buku → Eksemplar</strong>.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Statistik SLiMS --}}
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($slimsStats['biblio'] ?? 0) }}</p>
                    <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">Judul Buku</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-2xl font-bold text-teal-600 dark:text-teal-400">{{ number_format($slimsStats['item'] ?? 0) }}</p>
                    <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">Eksemplar</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($slimsStats['mst_topic'] ?? 0) }}</p>
                    <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">Topik DDC</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-2xl font-bold text-gray-600 dark:text-gray-300">{{ number_format($slimsStats['publisher'] ?? 0) }}</p>
                    <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">Penerbit</p>
                </div>
            </div>

            {{-- ── Grid Aksi Import ── --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h3 class="mb-1 text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Import Data ke ERP</h3>
                <p class="mb-4 text-xs text-gray-400 dark:text-gray-500">Setiap tombol akan meminta konfirmasi sebelum dijalankan. Ikuti urutan: DDC → Buku → Eksemplar.</p>
                <div class="flex flex-wrap gap-3">

                    {{-- Import DDC --}}
                    <x-filament::button
                        wire:click="$dispatch('open-modal', { id: 'import-ddc-modal' })"
                        icon="heroicon-o-tag"
                        color="warning"
                        wire:loading.attr="disabled"
                        wire:target="jalanImportDdc"
                    >
                        <span wire:loading.remove wire:target="jalanImportDdc">① Import DDC</span>
                        <span wire:loading wire:target="jalanImportDdc">Mengimport DDC...</span>
                    </x-filament::button>

                    {{-- Import Buku --}}
                    <x-filament::button
                        wire:click="$dispatch('open-modal', { id: 'import-buku-modal' })"
                        icon="heroicon-o-book-open"
                        color="warning"
                        wire:loading.attr="disabled"
                        wire:target="jalanImportBuku"
                    >
                        <span wire:loading.remove wire:target="jalanImportBuku">② Import Buku</span>
                        <span wire:loading wire:target="jalanImportBuku">Mengimport Buku... (mohon tunggu)</span>
                    </x-filament::button>

                    {{-- Import Eksemplar --}}
                    <x-filament::button
                        wire:click="$dispatch('open-modal', { id: 'import-eksemplar-modal' })"
                        icon="heroicon-o-cube"
                        color="warning"
                        wire:loading.attr="disabled"
                        wire:target="jalanImportEksemplar"
                    >
                        <span wire:loading.remove wire:target="jalanImportEksemplar">③ Import Eksemplar</span>
                        <span wire:loading wire:target="jalanImportEksemplar">Mengimport Eksemplar... (mohon tunggu)</span>
                    </x-filament::button>

                    {{-- Import Semua --}}
                    <x-filament::button
                        wire:click="$dispatch('open-modal', { id: 'import-semua-modal' })"
                        icon="heroicon-o-arrow-path"
                        color="danger"
                        wire:loading.attr="disabled"
                        wire:target="jalanImportSemua"
                    >
                        <span wire:loading.remove wire:target="jalanImportSemua">🚀 Import SEMUA</span>
                        <span wire:loading wire:target="jalanImportSemua">Proses import berlangsung... (mohon tunggu)</span>
                    </x-filament::button>
                </div>
            </div>

            {{-- ── Grid Download XLS ── --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h3 class="mb-1 text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Download Data SLiMS sebagai Excel</h3>
                <p class="mb-4 text-xs text-gray-400 dark:text-gray-500">Berguna untuk sekolah baru yang hanya butuh referensi DDC, atau untuk arsip data sebelum migrasi penuh.</p>
                <div class="flex flex-wrap gap-3">

                    <x-filament::button
                        wire:click="downloadDdcXls"
                        icon="heroicon-o-arrow-down-tray"
                        color="info"
                        wire:loading.attr="disabled"
                        wire:target="downloadDdcXls"
                    >
                        <span wire:loading.remove wire:target="downloadDdcXls">📥 DDC (.xlsx)</span>
                        <span wire:loading wire:target="downloadDdcXls">Menyiapkan file...</span>
                    </x-filament::button>

                    <x-filament::button
                        wire:click="downloadBukuXls"
                        icon="heroicon-o-arrow-down-tray"
                        color="info"
                        wire:loading.attr="disabled"
                        wire:target="downloadBukuXls"
                    >
                        <span wire:loading.remove wire:target="downloadBukuXls">📥 Katalog Buku (.xlsx)</span>
                        <span wire:loading wire:target="downloadBukuXls">Menyiapkan file...</span>
                    </x-filament::button>

                    <x-filament::button
                        wire:click="downloadEksemplarXls"
                        icon="heroicon-o-arrow-down-tray"
                        color="info"
                        wire:loading.attr="disabled"
                        wire:target="downloadEksemplarXls"
                    >
                        <span wire:loading.remove wire:target="downloadEksemplarXls">📥 Eksemplar (.xlsx)</span>
                        <span wire:loading wire:target="downloadEksemplarXls">Menyiapkan file...</span>
                    </x-filament::button>
                </div>
            </div>

            {{-- ── Laporan Hasil Terakhir ── --}}
            @if ($lastReport)
                <div class="rounded-xl border border-green-200 bg-green-50 p-5 dark:border-green-700 dark:bg-green-950/30">
                    <h3 class="mb-3 text-sm font-bold text-green-800 dark:text-green-300">
                        ✅ Laporan Import Terakhir — {{ $lastReport['jenis'] }}
                    </h3>

                    @if ($lastReport['jenis'] === 'Semua')
                        @foreach (['ddc' => 'DDC', 'buku' => 'Buku', 'eksemplar' => 'Eksemplar'] as $key => $label)
                            <div class="mb-3">
                                <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-green-700 dark:text-green-400">{{ $label }}</p>
                                <div class="flex flex-wrap gap-3 text-xs">
                                    <span class="rounded bg-green-200 px-2 py-1 font-mono dark:bg-green-800">Baru: {{ $lastReport['hasil'][$key]['baru'] ?? 0 }}</span>
                                    <span class="rounded bg-blue-200 px-2 py-1 font-mono dark:bg-blue-800">Diupdate: {{ $lastReport['hasil'][$key]['diupdate'] ?? 0 }}</span>
                                    @if (isset($lastReport['hasil'][$key]['dilewati']))
                                        <span class="rounded bg-yellow-200 px-2 py-1 font-mono dark:bg-yellow-800">Dilewati: {{ $lastReport['hasil'][$key]['dilewati'] }}</span>
                                    @endif
                                    @if (isset($lastReport['hasil'][$key]['inventaris_dibuat']))
                                        <span class="rounded bg-purple-200 px-2 py-1 font-mono dark:bg-purple-800">Inventaris: {{ $lastReport['hasil'][$key]['inventaris_dibuat'] }}</span>
                                    @endif
                                    <span class="rounded bg-red-200 px-2 py-1 font-mono dark:bg-red-800">Error: {{ $lastReport['hasil'][$key]['error'] ?? 0 }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="flex flex-wrap gap-3 text-xs">
                            <span class="rounded bg-green-200 px-2 py-1 font-mono dark:bg-green-800">Baru: {{ $lastReport['hasil']['baru'] ?? 0 }}</span>
                            <span class="rounded bg-blue-200 px-2 py-1 font-mono dark:bg-blue-800">Diupdate: {{ $lastReport['hasil']['diupdate'] ?? 0 }}</span>
                            @if (isset($lastReport['hasil']['dilewati']))
                                <span class="rounded bg-yellow-200 px-2 py-1 font-mono dark:bg-yellow-800">Dilewati: {{ $lastReport['hasil']['dilewati'] }}</span>
                            @endif
                            @if (isset($lastReport['hasil']['inventaris_dibuat']))
                                <span class="rounded bg-purple-200 px-2 py-1 font-mono dark:bg-purple-800">Inventaris Dibuat: {{ $lastReport['hasil']['inventaris_dibuat'] }}</span>
                            @endif
                            <span class="rounded bg-red-200 px-2 py-1 font-mono dark:bg-red-800">Error: {{ $lastReport['hasil']['error'] ?? 0 }}</span>
                        </div>
                    @endif

                    @if (!empty($lastReport['hasil']['pesan_error'] ?? []))
                        <details class="mt-3">
                            <summary class="cursor-pointer text-xs font-semibold text-red-700 dark:text-red-400">Lihat detail error ({{ count($lastReport['hasil']['pesan_error']) }})</summary>
                            <ul class="mt-2 space-y-1">
                                @foreach (array_slice($lastReport['hasil']['pesan_error'], 0, 20) as $err)
                                    <li class="font-mono text-xs text-red-600 dark:text-red-400">{{ $err }}</li>
                                @endforeach
                            </ul>
                        </details>
                    @endif
                </div>
            @endif

        </div>

        {{-- ═══════════════════════════════════════════════════════════════════
             MODAL KONFIRMASI IMPORT
        ═══════════════════════════════════════════════════════════════════ --}}

        {{-- Modal Import DDC --}}
        <x-filament::modal id="import-ddc-modal" width="md">
            <x-slot name="heading">⚠️ Konfirmasi Import DDC</x-slot>
            <x-slot name="description">
                Data klasifikasi DDC di ERP akan <strong>di-OVERWRITE</strong> dengan data dari SLiMS.
                Tindakan ini tidak bisa dibatalkan. Lanjutkan?
            </x-slot>
            <x-slot name="footerActions">
                <x-filament::button wire:click="jalanImportDdc" wire:loading.attr="disabled" wire:target="jalanImportDdc" color="warning">
                    <span wire:loading.remove wire:target="jalanImportDdc">Ya, Import DDC</span>
                    <span wire:loading wire:target="jalanImportDdc">Mengimport...</span>
                </x-filament::button>
                <x-filament::button x-on:click="$dispatch('close-modal', { id: 'import-ddc-modal' })" color="gray">Batal</x-filament::button>
            </x-slot>
        </x-filament::modal>

        {{-- Modal Import Buku --}}
        <x-filament::modal id="import-buku-modal" width="md">
            <x-slot name="heading">⚠️ Konfirmasi Import Buku</x-slot>
            <x-slot name="description">
                Data buku di ERP akan <strong>di-OVERWRITE</strong> dengan data dari SLiMS.
                Proses ini mungkin membutuhkan beberapa menit untuk <strong>{{ number_format($slimsStats['biblio'] ?? 0) }} judul</strong>.
                Lanjutkan?
            </x-slot>
            <x-slot name="footerActions">
                <x-filament::button wire:click="jalanImportBuku" wire:loading.attr="disabled" wire:target="jalanImportBuku" color="warning">
                    <span wire:loading.remove wire:target="jalanImportBuku">Ya, Import Buku</span>
                    <span wire:loading wire:target="jalanImportBuku">Mengimport... mohon tunggu</span>
                </x-filament::button>
                <x-filament::button x-on:click="$dispatch('close-modal', { id: 'import-buku-modal' })" color="gray">Batal</x-filament::button>
            </x-slot>
        </x-filament::modal>

        {{-- Modal Import Eksemplar --}}
        <x-filament::modal id="import-eksemplar-modal" width="md">
            <x-slot name="heading">⚠️ Konfirmasi Import Eksemplar</x-slot>
            <x-slot name="description">
                Data eksemplar di ERP akan <strong>di-OVERWRITE</strong>. Inventaris otomatis akan dibuat per judul.
                Proses ini membutuhkan waktu cukup lama untuk <strong>{{ number_format($slimsStats['item'] ?? 0) }} eksemplar</strong>.
                <br><br>
                ⚠️ Pastikan sudah menjalankan <strong>Import Buku</strong> terlebih dahulu!
            </x-slot>
            <x-slot name="footerActions">
                <x-filament::button wire:click="jalanImportEksemplar" wire:loading.attr="disabled" wire:target="jalanImportEksemplar" color="warning">
                    <span wire:loading.remove wire:target="jalanImportEksemplar">Ya, Import Eksemplar</span>
                    <span wire:loading wire:target="jalanImportEksemplar">Mengimport... mohon tunggu</span>
                </x-filament::button>
                <x-filament::button x-on:click="$dispatch('close-modal', { id: 'import-eksemplar-modal' })" color="gray">Batal</x-filament::button>
            </x-slot>
        </x-filament::modal>

        {{-- Modal Import Semua --}}
        <x-filament::modal id="import-semua-modal" width="lg">
            <x-slot name="heading">🚨 Konfirmasi Import SEMUA Data</x-slot>
            <x-slot name="description">
                <div class="space-y-2 text-sm">
                    <p>Semua data berikut akan <strong class="text-red-600">di-OVERWRITE</strong>:</p>
                    <ul class="list-disc pl-4 space-y-1 text-gray-700 dark:text-gray-300">
                        <li>Klasifikasi DDC — {{ number_format($slimsStats['mst_topic'] ?? 0) }} topik</li>
                        <li>Katalog Buku — {{ number_format($slimsStats['biblio'] ?? 0) }} judul</li>
                        <li>Eksemplar — {{ number_format($slimsStats['item'] ?? 0) }} eksemplar</li>
                        <li>Inventaris — dibuat otomatis per judul</li>
                    </ul>
                    <p class="mt-3 font-semibold text-red-600">Pastikan database ERP sudah di-backup sebelum melanjutkan!</p>
                    <p class="text-xs text-gray-500">Proses ini bisa memakan waktu 5–15 menit. Jangan tutup halaman ini.</p>
                </div>
            </x-slot>
            <x-slot name="footerActions">
                <x-filament::button wire:click="jalanImportSemua" wire:loading.attr="disabled" wire:target="jalanImportSemua" color="danger">
                    <span wire:loading.remove wire:target="jalanImportSemua">🚀 Saya Siap, Import Semua</span>
                    <span wire:loading wire:target="jalanImportSemua">Proses berlangsung... mohon tunggu</span>
                </x-filament::button>
                <x-filament::button x-on:click="$dispatch('close-modal', { id: 'import-semua-modal' })" color="gray">Batal</x-filament::button>
            </x-slot>
        </x-filament::modal>

    @endif

</x-filament-panels::page>

<x-filament-panels::page>
    <div class="space-y-6">

        {{-- ══════════════════════════════════════════════════════════
             STEP 1: STATUS KONEKSI
        ══════════════════════════════════════════════════════════ --}}
        @if ($slimsConnected)
            <div class="flex items-center justify-between rounded-xl border border-green-200 bg-green-50 px-4 py-3 dark:border-green-700 dark:bg-green-950/30">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-green-500 animate-pulse"></span>
                    <span class="text-sm font-semibold text-green-800 dark:text-green-300">
                        Terhubung ke: <span class="font-mono">{{ session('slims_db_config.database', '?') }}</span>
                    </span>
                </div>
                <x-filament::button
                    wire:click="putusKoneksi"
                    color="gray"
                    size="sm"
                    icon="heroicon-o-x-circle"
                >Putus Koneksi</x-filament::button>
            </div>

            {{-- Statistik SLiMS --}}
            @if (!empty($slimsStats))
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="rounded-xl border border-indigo-100 bg-white px-4 py-4 text-center shadow-sm dark:border-indigo-800 dark:bg-gray-900">
                        <p class="text-3xl font-extrabold text-indigo-700 dark:text-indigo-400">{{ number_format($slimsStats['biblio'] ?? 0) }}</p>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wider text-gray-500">Judul Buku</p>
                    </div>
                    <div class="rounded-xl border border-teal-100 bg-white px-4 py-4 text-center shadow-sm dark:border-teal-800 dark:bg-gray-900">
                        <p class="text-3xl font-extrabold text-teal-700 dark:text-teal-400">{{ number_format($slimsStats['item'] ?? 0) }}</p>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wider text-gray-500">Eksemplar</p>
                    </div>
                    <div class="rounded-xl border border-purple-100 bg-white px-4 py-4 text-center shadow-sm dark:border-purple-800 dark:bg-gray-900">
                        <p class="text-3xl font-extrabold text-purple-700 dark:text-purple-400">{{ number_format($slimsStats['mst_topic'] ?? 0) }}</p>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wider text-gray-500">Topik DDC</p>
                    </div>
                    <div class="rounded-xl border border-amber-100 bg-white px-4 py-4 text-center shadow-sm dark:border-amber-800 dark:bg-gray-900">
                        <p class="text-3xl font-extrabold text-amber-700 dark:text-amber-400">{{ number_format($slimsStats['publisher'] ?? 0) }}</p>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wider text-gray-500">Penerbit</p>
                    </div>
                </div>
            @endif

            {{-- ═══ PANDUAN MIGRASI 2 LANGKAH ═══ --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900 mt-4">
                <h2 class="mb-4 text-lg font-bold text-gray-800 dark:text-gray-200">🚀 Panduan Migrasi Data SLiMS ke ERP</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Langkah 1: Download --}}
                    <div class="relative rounded-lg border-2 border-indigo-100 bg-indigo-50/50 p-5 dark:border-indigo-900/50 dark:bg-indigo-950/20">
                        <div class="absolute -left-3 -top-3 flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white shadow-md">1</div>
                        <h3 class="mb-2 text-md font-bold text-indigo-900 dark:text-indigo-300">Download Data (Excel)</h3>
                        <p class="mb-4 text-xs text-indigo-700 dark:text-indigo-400">
                            Download data dari SLiMS dalam format Excel yang sudah dioptimasi untuk ERP. File ini berisi struktur data yang siap diupload.
                        </p>
                        <div class="flex flex-col gap-2">
                            <x-filament::button wire:click="downloadDdcXls" icon="heroicon-o-arrow-down-tray" color="primary" size="sm" class="justify-start">
                                1. Download DDC (.xlsx)
                            </x-filament::button>
                            <x-filament::button wire:click="downloadBukuXls" icon="heroicon-o-arrow-down-tray" color="primary" size="sm" class="justify-start">
                                2. Download Katalog Buku (.xlsx)
                            </x-filament::button>
                        </div>
                    </div>

                    {{-- Langkah 2: Upload --}}
                    <div class="relative rounded-lg border-2 border-green-100 bg-green-50/50 p-5 dark:border-green-900/50 dark:bg-green-950/20">
                        <div class="absolute -left-3 -top-3 flex h-8 w-8 items-center justify-center rounded-full bg-green-600 text-sm font-bold text-white shadow-md">2</div>
                        <h3 class="mb-2 text-md font-bold text-green-900 dark:text-green-300">Upload Data ke ERP</h3>
                        <p class="mb-4 text-xs text-green-700 dark:text-green-400">
                            Pilih menu di bawah ini untuk mengupload file Excel yang telah Anda download pada Langkah 1.
                        </p>
                        <div class="flex flex-col gap-2">
                            <x-filament::button tag="a" href="{{ url('/admin-perpustakaan/klasifikasi-ddc') }}" icon="heroicon-o-arrow-up-tray" color="success" size="sm" class="justify-start">
                                1. Ke Halaman Klasifikasi DDC
                            </x-filament::button>
                            <x-filament::button tag="a" href="{{ url('/admin-perpustakaan/buku') }}" icon="heroicon-o-arrow-up-tray" color="success" size="sm" class="justify-start">
                                2. Ke Halaman Koleksi Buku
                            </x-filament::button>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 rounded-md bg-yellow-50 p-3 dark:bg-yellow-900/30">
                    <p class="text-xs text-yellow-800 dark:text-yellow-300">
                        <strong>Perhatian:</strong> Pastikan Anda melakukan import secara berurutan. Import DDC terlebih dahulu, baru kemudian Buku. Hal ini diperlukan karena buku membutuhkan referensi klasifikasi DDC.
                    </p>
                </div>
            </div>

        @else
            {{-- ═══ FORM KONEKSI ═══ --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="mb-4 flex items-center gap-3">
                    <x-heroicon-o-server class="h-7 w-7 text-indigo-500" />
                    <div>
                        <h2 class="text-base font-bold text-gray-800 dark:text-gray-200">Koneksi ke Database SLiMS</h2>
                        <p class="text-xs text-gray-500">Masukkan kredensial database SLiMS untuk mulai sinkronisasi.</p>
                    </div>
                </div>

                {{ $this->form }}

                <div class="mt-4">
                    <x-filament::button wire:click="testKoneksi" icon="heroicon-o-signal" color="primary">
                        🔌 Tes Koneksi
                    </x-filament::button>
                </div>
            </div>

            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-700 dark:bg-amber-950/30">
                <div class="flex items-start gap-2">
                    <x-heroicon-o-information-circle class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" />
                    <p class="text-xs text-amber-700 dark:text-amber-400">
                        <strong>Tip:</strong> Isi form di atas dengan kredensial database SLiMS yang biasanya ada di file
                        <code class="font-mono">sysconfig.local.inc.php</code> pada folder instalasi SLiMS.
                    </p>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>

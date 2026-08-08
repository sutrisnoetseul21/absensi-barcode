<x-filament-panels::page>
    <div class="space-y-6">

        {{-- ===== HEADER & FILTER SECTION (NATIVE FILAMENT STYLE) ===== --}}
        <x-filament::section>
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-950 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-identification class="w-6 h-6 text-primary-500" />
                        Manajemen Kartu Presensi Siswa
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pilih Tahun Ajaran dan Kelas di bawah ini, lalu klik tombol <strong class="text-gray-700 dark:text-gray-200">Proses</strong> untuk memuat & mencetak kartu presensi.</p>
                </div>

                <!-- Filter Controls & Action Button -->
                <div class="flex flex-wrap sm:flex-nowrap items-end gap-3 shrink-0">
                    {{-- Filter Tahun Ajaran --}}
                    <div class="flex flex-col gap-1 flex-1 sm:flex-none">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Tahun Ajaran</label>
                        <select wire:model.change="selectedAcademicYearId"
                            class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-xs font-bold px-3 py-2 shadow-sm focus:ring-2 focus:ring-primary-500 outline-none cursor-pointer">
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Kelas --}}
                    @if($classes->isNotEmpty())
                    <div class="flex flex-col gap-1 flex-1 sm:flex-none">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Kelas</label>
                        <select wire:model.change="selectedClassId"
                            class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-xs font-bold px-3 py-2 shadow-sm focus:ring-2 focus:ring-primary-500 outline-none cursor-pointer">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classes as $kelas)
                                <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Tombol Proses --}}
                    <x-filament::button wire:click="filterData" wire:loading.attr="disabled" icon="heroicon-m-arrow-path" color="primary">
                        <span wire:loading.remove wire:target="filterData">Proses</span>
                        <span wire:loading wire:target="filterData">Memproses...</span>
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        {{-- ===== TABEL SISWA ===== --}}
        @if(!$hasSubmittedFilter || !$selectedClassId)
            <x-filament::section>
                <div class="text-center py-12">
                    <div class="w-12 h-12 bg-primary-50 dark:bg-primary-950/50 text-primary-500 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <x-heroicon-o-identification class="w-6 h-6" />
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 mb-1">Daftar Kartu Siswa Belum Dimuat</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">Silakan pilih Kelas terlebih dahulu, lalu klik tombol <strong class="text-primary-600 dark:text-primary-400">Proses</strong> untuk menampilkan daftar dan mencetak kartu presensi siswa.</p>
                </div>
            </x-filament::section>
        @else
            <div>
                {{ $this->table }}
            </div>
        @endif
    </div>
</x-filament-panels::page>

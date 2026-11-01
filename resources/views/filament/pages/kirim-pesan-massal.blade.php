<x-filament-panels::page>
    {{-- Banner Informasi Jangkauan Sekolah --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center space-x-4">
            <div class="p-3 bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 rounded-lg">
                <x-heroicon-o-device-phone-mobile class="w-8 h-8" />
            </div>
            <div>
                <p class="text-xs uppercase font-semibold text-gray-400">Total HP Terhubung</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalDevices }} <span class="text-xs font-normal text-gray-500">Perangkat</span></p>
                <p class="text-xs text-gray-500 mt-0.5">Khusus sekolah ini</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center space-x-4">
            <div class="p-3 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 rounded-lg">
                <x-heroicon-o-academic-cap class="w-8 h-8" />
            </div>
            <div>
                <p class="text-xs uppercase font-semibold text-gray-400">HP Siswa Aktif</p>
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $totalSiswaDevices }} <span class="text-xs font-normal text-gray-500">Siswa</span></p>
                <p class="text-xs text-gray-500 mt-0.5">Menerima push otomatis</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center space-x-4">
            <div class="p-3 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 rounded-lg">
                <x-heroicon-o-user-group class="w-8 h-8" />
            </div>
            <div>
                <p class="text-xs uppercase font-semibold text-gray-400">HP Guru & Wali Kelas</p>
                <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $totalGuruDevices }} <span class="text-xs font-normal text-gray-500">Guru</span></p>
                <p class="text-xs text-gray-500 mt-0.5">Menerima push otomatis</p>
            </div>
        </div>
    </div>

    {{-- Form Pengiriman Pesan Massal --}}
    <form wire:submit="send">
        {{ $this->form }}

        <div class="flex justify-end mt-4">
            <x-filament::button
                type="submit"
                size="lg"
                icon="heroicon-o-paper-airplane"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-50 cursor-wait">
                <span wire:loading.remove>Kirim Pesan Massal Sekarang</span>
                <span wire:loading>Sedang Mengirim Notifikasi...</span>
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>

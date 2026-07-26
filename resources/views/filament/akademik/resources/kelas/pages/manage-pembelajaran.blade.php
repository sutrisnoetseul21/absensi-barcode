<x-filament-panels::page>
    <div class="fi-ta-header flex flex-col gap-y-1 mb-6 p-6 bg-white rounded-xl ring-1 ring-gray-950/5 shadow-sm dark:bg-gray-900 dark:ring-white/10">
        <h2 class="fi-ta-header-heading text-lg font-bold sm:text-xl md:text-2xl">
            Manajemen Pembelajaran: Kelas {{ $record->name }}
        </h2>
        <p class="fi-ta-header-description text-sm text-gray-500 dark:text-gray-400 mt-2">
            Tahun Ajaran Aktif saat ini: <span class="font-semibold text-primary-600 dark:text-primary-400 px-2 py-1 bg-primary-50 dark:bg-primary-500/10 rounded-md">{{ $activeTahunAjaranName }}</span>
        </p>
        <p class="text-xs text-gray-400 mt-1">
            * Seluruh daftar mata pelajaran di bawah ini dimuat secara otomatis dari data Master Mata Pelajaran. Anda hanya perlu menentukan siapa Guru yang mengajar mata pelajaran tersebut di kelas ini untuk tahun ajaran aktif.
        </p>
    </div>

    {{ $this->table }}
</x-filament-panels::page>

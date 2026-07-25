<x-filament-widgets::widget>
    <x-filament::section>
        <div class="mb-4">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Portal Navigasi Antar Panel</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Pilih panel yang ingin Anda tuju. Anda mungkin perlu login jika belum memiliki sesi aktif di panel tersebut.</p>
        </div>

        @php
            $user = auth()->user();
            $canAccessAdmin = $user->canAccessPanel(filament()->getPanel('admin'));
            $canAccessAkademik = $user->canAccessPanel(filament()->getPanel('admin-akademik'));
            $canAccessPresensi = $user->canAccessPanel(filament()->getPanel('admin-presensi'));
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Panel Super Admin --}}
            @if($canAccessAdmin)
            <a href="{{ url('/admin') }}" class="group block p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:border-primary-500 hover:ring-1 hover:ring-primary-500 transition-all">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg group-hover:bg-primary-50 group-hover:text-primary-600 dark:group-hover:bg-primary-900/50 dark:group-hover:text-primary-400">
                        @svg('heroicon-o-shield-check', 'w-6 h-6')
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400">Super Admin</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pengaturan & Sistem</p>
                    </div>
                </div>
            </a>
            @endif

            {{-- Panel Data Master & Akademik --}}
            @if($canAccessAkademik)
            <a href="{{ url('/admin-akademik') }}" class="group block p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:border-emerald-500 hover:ring-1 hover:ring-emerald-500 transition-all">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 rounded-lg group-hover:bg-emerald-100 dark:group-hover:bg-emerald-900/50">
                        @svg('heroicon-o-academic-cap', 'w-6 h-6')
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400">Data Master & Akademik</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Data Induk & Kesiswaan</p>
                    </div>
                </div>
            </a>
            @endif

            {{-- Panel Presensi --}}
            @if($canAccessPresensi)
            <a href="{{ url('/admin-presensi') }}" class="group block p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:border-amber-500 hover:ring-1 hover:ring-amber-500 transition-all">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 rounded-lg group-hover:bg-amber-100 dark:group-hover:bg-amber-900/50">
                        @svg('heroicon-o-finger-print', 'w-6 h-6')
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400">Presensi</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Absensi & Laporan</p>
                    </div>
                </div>
            </a>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

<x-filament-widgets::widget>
    <x-filament::section>
        <div class="mb-4">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Tautan Cepat (Akses Cepat)</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Gunakan pintasan di bawah ini untuk menuju ke menu-menu utama dengan cepat.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($quickLinks as $link)
                <a href="{{ $link['url'] }}" class="group block p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:border-{{ $link['color'] }}-500 hover:ring-1 hover:ring-{{ $link['color'] }}-500 transition-all">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-{{ $link['color'] }}-50 dark:bg-{{ $link['color'] }}-900/20 text-{{ $link['color'] }}-600 dark:text-{{ $link['color'] }}-400 rounded-lg group-hover:bg-{{ $link['color'] }}-100 dark:group-hover:bg-{{ $link['color'] }}-900/50">
                            @svg($link['icon'], 'w-6 h-6')
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-{{ $link['color'] }}-600 dark:group-hover:text-{{ $link['color'] }}-400">{{ $link['title'] }}</h3>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

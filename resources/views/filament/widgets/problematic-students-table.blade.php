<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Daftar Peringatan Siswa Bermasalah (Bulan Ini)
        </x-slot>

        @if(count($alerts) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        <tr>
                            <th class="px-4 py-3 border-b">Nama Siswa</th>
                            <th class="px-4 py-3 border-b">Kelas</th>
                            <th class="px-4 py-3 border-b">Total Alpa</th>
                            <th class="px-4 py-3 border-b">Total Keterlambatan</th>
                            <th class="px-4 py-3 border-b">Status Alert</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($alerts as $alert)
                            @php
                                $isAlpaCritical = $alert['total_alpa'] >= 3;
                                $isLateCritical = $alert['total_late_minutes'] >= 100;
                            @endphp
                            <tr class="bg-white dark:bg-gray-900">
                                <td class="px-4 py-3 font-medium">{{ $alert['siswa']['name'] ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $alert['kelas']['name'] ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="{{ $isAlpaCritical ? 'text-danger-600 font-bold' : '' }}">
                                        {{ $alert['total_alpa'] }} Hari
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="{{ $isLateCritical ? 'text-danger-600 font-bold' : '' }}">
                                        {{ $alert['total_late_minutes'] }} Menit
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        @if($isAlpaCritical)
                                            <span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-md text-xs font-medium bg-danger-100 text-danger-800 dark:bg-danger-800/30 dark:text-danger-500">
                                                Alpa Tinggi
                                            </span>
                                        @endif
                                        @if($isLateCritical)
                                            <span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-md text-xs font-medium bg-warning-100 text-warning-800 dark:bg-warning-800/30 dark:text-warning-500">
                                                Sering Telat
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-4 text-center text-gray-500 dark:text-gray-400">
                Tidak ada siswa yang melampaui batas alpa atau terlambat bulan ini.
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>

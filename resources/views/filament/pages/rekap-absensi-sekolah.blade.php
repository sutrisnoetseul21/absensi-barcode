<x-filament-panels::page>
    <div class="space-y-6">

        {{-- ===== FILTER CARD ===== --}}
        <x-filament::section>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 flex-wrap">
                <div>
                    <h2 class="text-lg font-bold text-gray-950 dark:text-white">Rekap Tahunan Per Kelas</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        Pilih Tahun Ajaran untuk memuat rekap presensi seluruh kelas secara tahunan.
                    </p>
                </div>

                <div class="flex flex-col gap-1 min-w-[200px]">
                    <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Tahun Ajaran</label>
                    <select wire:model.change="selectedAcademicYearId"
                        class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-3 py-2 shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none cursor-pointer">
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Legend --}}
            <div class="flex flex-wrap gap-5 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 text-sm">
                <div class="flex items-center gap-2 font-semibold text-success-600 dark:text-success-400">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-success-500"></span> Hadir (H)
                </div>
                <div class="flex items-center gap-2 font-semibold text-indigo-600 dark:text-indigo-400">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-indigo-500"></span> Sakit (S)
                </div>
                <div class="flex items-center gap-2 font-semibold text-blue-600 dark:text-blue-400">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-blue-500"></span> Izin (I)
                </div>
                <div class="flex items-center gap-2 font-semibold text-danger-600 dark:text-danger-400">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-danger-500"></span> Alpa (A)
                </div>
                <div class="text-gray-400 dark:text-gray-500 text-xs self-center ml-auto">
                    * Hadir = Hadir tepat waktu + Terlambat
                </div>
            </div>
        </x-filament::section>

        {{-- ===== EMPTY STATE ===== --}}
        @if(!$selectedAcademicYearId)
        <x-filament::section>
            <div class="flex items-center gap-4 py-4">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-warning-100 dark:bg-warning-900/30 flex items-center justify-center">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-warning-600 dark:text-warning-400"/>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-gray-100">Tahun Ajaran Belum Ditentukan</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Silakan buat dan aktifkan tahun ajaran baru terlebih dahulu.</p>
                </div>
            </div>
        </x-filament::section>

        @else

        {{-- ===== MATRIX TABLE ===== --}}
        <x-filament::section>
            <div class="overflow-x-auto" wire:loading.class="opacity-50">
                <table class="min-w-full text-xs">
                    <thead>
                        {{-- Row 1: Nama bulan --}}
                        <tr class="border-b-2 border-gray-300 dark:border-gray-600">
                            <th rowspan="2" class="px-3 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-10 border-r border-gray-200 dark:border-gray-700">No</th>
                            <th rowspan="2" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700">Kelas</th>
                            <th rowspan="2" class="px-3 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r-2 border-gray-300 dark:border-gray-600">Jml Siswa</th>
                            @foreach($monthsList as $m)
                                <th colspan="4" class="px-2 py-2 text-center text-gray-700 dark:text-gray-200 tracking-wider border-r-2 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800/60">
                                    <div class="font-bold text-xs uppercase">{{ $m['label'] }}</div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400 font-normal mt-0.5">Hari Efektif: {{ $m['effective_days'] }} Hari</div>
                                </th>
                            @endforeach
                        </tr>
                        {{-- Row 2: Sub-kolom H S I A --}}
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            @foreach($monthsList as $m)
                                <th class="px-2 py-2 text-center text-xs font-bold text-success-600 dark:text-success-400 w-8">H</th>
                                <th class="px-2 py-2 text-center text-xs font-bold text-indigo-600 dark:text-indigo-400 w-8">S</th>
                                <th class="px-2 py-2 text-center text-xs font-bold text-blue-600 dark:text-blue-400 w-8">I</th>
                                <th class="px-2 py-2 text-center text-xs font-bold text-danger-600 dark:text-danger-400 w-8 border-r-2 border-gray-300 dark:border-gray-600">A</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($classesData as $index => $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                                <td class="px-3 py-2.5 text-center text-gray-400 dark:text-gray-600 font-medium border-r border-gray-200 dark:border-gray-700">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-4 py-2.5 font-semibold text-gray-900 dark:text-gray-100 whitespace-nowrap border-r border-gray-200 dark:border-gray-700">
                                    {{ $row['name'] }}
                                </td>
                                <td class="px-3 py-2.5 text-center font-medium text-gray-700 dark:text-gray-300 border-r-2 border-gray-300 dark:border-gray-600">
                                    {{ $row['student_count'] }}
                                </td>
                                @foreach($monthsList as $m)
                                    @php
                                        $key   = "{$m['year']}-{$m['month']}";
                                        $stats = $row['months'][$key] ?? ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];
                                    @endphp
                                    <td class="px-2 py-2.5 text-center font-bold text-success-600 dark:text-success-400">{{ $stats['hadir'] ?: '-' }}</td>
                                    <td class="px-2 py-2.5 text-center font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['sakit'] ?: '-' }}</td>
                                    <td class="px-2 py-2.5 text-center font-bold text-blue-600 dark:text-blue-400">{{ $stats['izin'] ?: '-' }}</td>
                                    <td class="px-2 py-2.5 text-center font-bold text-danger-600 dark:text-danger-400 border-r-2 border-gray-300 dark:border-gray-600">{{ $stats['alpa'] ?: '-' }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 3 + (count($monthsList) * 4) }}" class="py-12 text-center text-gray-400 dark:text-gray-600">
                                    Belum ada data kelas yang terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        @endif

    </div>
</x-filament-panels::page>

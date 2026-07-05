<x-filament-panels::page>
    <div class="space-y-6">

        {{-- ===== FILTER SECTION ===== --}}
        <x-filament::section>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between flex-wrap">
                <div>
                    <h2 class="text-lg font-bold text-gray-950 dark:text-white">Rekapitulasi Absensi Kelas</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Kelola absensi siswa per kelas dan per bulan</p>
                </div>

                <div class="flex flex-wrap gap-3 items-end">
                    {{-- Filter Tahun Ajaran --}}
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Tahun Ajaran</label>
                        <select wire:model.change="selectedAcademicYearId"
                            class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-3 py-2 shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none cursor-pointer">
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Kelas --}}
                    @if($classes->isNotEmpty())
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Pilih Kelas</label>
                        <select wire:model.change="selectedClassId"
                            class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-3 py-2 shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none cursor-pointer">
                            @foreach($classes as $kelas)
                                <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Filter Bulan --}}
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Bulan</label>
                        <select wire:model.change="selectedMonth"
                            class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-3 py-2 shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none cursor-pointer">
                            @foreach(['07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember','01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni'] as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tombol Input Manual & Export --}}
                    @if($classes->isNotEmpty() && $selectedClassId)
                    <x-filament::button
                        wire:click="exportExcel"
                        icon="heroicon-o-arrow-down-tray"
                        color="success">
                        Export Excel
                    </x-filament::button>
                    
                    <x-filament::button
                        wire:click="openInputModal"
                        icon="heroicon-o-plus-circle"
                        color="warning">
                        Input Absen Manual
                    </x-filament::button>
                    @endif
                </div>
            </div>
        </x-filament::section>

        {{-- ===== STATS TODAY ===== --}}
        @if($classes->isNotEmpty() && $selectedClassId && !empty($todayStats))
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <x-filament::section>
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-success-100 dark:bg-success-900/30 flex items-center justify-center">
                        <x-heroicon-o-check-circle class="w-6 h-6 text-success-600 dark:text-success-400"/>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">HADIR HARI INI</p>
                        <p class="text-xl font-bold text-gray-950 dark:text-white">
                            {{ ($todayStats['hadir'] ?? 0) + ($todayStats['telat'] ?? 0) }} / {{ $todayStats['total'] ?? 0 }}
                            <span class="text-sm font-normal text-gray-400">({{ $todayStats['persentase_hadir'] ?? 0 }}%)</span>
                        </p>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-warning-100 dark:bg-warning-900/30 flex items-center justify-center">
                        <x-heroicon-o-clock class="w-6 h-6 text-warning-600 dark:text-warning-400"/>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">TERLAMBAT</p>
                        <p class="text-xl font-bold text-gray-950 dark:text-white">{{ $todayStats['telat'] ?? 0 }} Siswa</p>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-danger-100 dark:bg-danger-900/30 flex items-center justify-center">
                        <x-heroicon-o-x-circle class="w-6 h-6 text-danger-600 dark:text-danger-400"/>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">SAKIT / IZIN / ALPA</p>
                        <p class="text-xl font-bold text-danger-600 dark:text-danger-400">
                            {{ ($todayStats['sakit'] ?? 0) + ($todayStats['izin'] ?? 0) + ($todayStats['alpa'] ?? 0) }} Siswa
                        </p>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                        <x-heroicon-o-question-mark-circle class="w-6 h-6 text-gray-400"/>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">BELUM ABSEN</p>
                        <p class="text-xl font-bold text-gray-950 dark:text-white">{{ $todayStats['belum'] ?? 0 }} Siswa</p>
                    </div>
                </div>
            </x-filament::section>
        </div>
        @endif

        {{-- ===== TABEL KALENDER ===== --}}
        @if($classes->isNotEmpty() && $selectedClassId)
        <x-filament::section>
            <div class="overflow-x-auto" wire:loading.class="opacity-50">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="sticky left-0 z-10 bg-gray-50 dark:bg-gray-900 px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-48 border-r border-gray-200 dark:border-gray-700">
                                Nama Siswa
                            </th>
                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                @php $isToday = ($todayDate === date('Y') . '-' . str_pad($selectedMonth, 2, '0', STR_PAD_LEFT) . '-' . str_pad($d, 2, '0', STR_PAD_LEFT)); @endphp
                                <th class="px-2 py-3 text-center text-xs font-semibold {{ $isToday ? 'text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' : 'text-gray-500 dark:text-gray-400' }} uppercase tracking-wider min-w-[36px]">
                                    {{ $d }}
                                </th>
                            @endfor
                            <th class="px-3 py-3 text-center text-xs font-semibold text-success-600 dark:text-success-400 uppercase tracking-wider border-l border-gray-200 dark:border-gray-700">H</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-warning-600 dark:text-warning-400 uppercase tracking-wider">T</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">I</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">S</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-danger-600 dark:text-danger-400 uppercase tracking-wider">A</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($students as $student)
                            @php
                                $isAlpaWarning = in_array($student->id, $alerts['alpa'] ?? []);
                                $isTelatWarning = in_array($student->id, $alerts['telat'] ?? []);
                                $stat = $monthlyStats[$student->id] ?? [];
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors {{ ($isAlpaWarning || $isTelatWarning) ? 'bg-danger-50/30 dark:bg-danger-900/10' : '' }}">
                                <td class="sticky left-0 z-10 px-4 py-3 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap">{{ $student->name }}</p>
                                        @if($isAlpaWarning)
                                            <span class="inline-flex items-center gap-1 text-xs text-danger-600 dark:text-danger-400 font-medium">
                                                <x-heroicon-s-exclamation-triangle class="w-3 h-3"/> ≥ 3 Alpa
                                            </span>
                                        @endif
                                        @if($isTelatWarning)
                                            <span class="inline-flex items-center gap-1 text-xs text-warning-600 dark:text-warning-400 font-medium">
                                                <x-heroicon-s-exclamation-triangle class="w-3 h-3"/> ≥ 100mnt telat
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                @for ($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $code = $stat['daily'][$d] ?? '-';
                                        $cellClass = match($code) {
                                            'H' => 'text-success-600 dark:text-success-400 font-bold',
                                            'T' => 'text-warning-600 dark:text-warning-400 font-bold',
                                            'I' => 'text-blue-600 dark:text-blue-400 font-bold',
                                            'S' => 'text-indigo-600 dark:text-indigo-400 font-bold',
                                            'A' => 'text-danger-600 dark:text-danger-400 font-bold',
                                            'L' => 'text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 font-bold',
                                            default => 'text-gray-300 dark:text-gray-600',
                                        };
                                    @endphp
                                    <td class="px-2 py-3 text-center {{ $cellClass }}">{{ $code }}</td>
                                @endfor
                                <td class="px-3 py-3 text-center font-bold text-success-600 dark:text-success-400 border-l border-gray-200 dark:border-gray-700">{{ $stat['hadir'] ?? 0 }}</td>
                                <td class="px-3 py-3 text-center font-bold text-warning-600 dark:text-warning-400">{{ $stat['telat'] ?? 0 }}</td>
                                <td class="px-3 py-3 text-center font-bold text-blue-600 dark:text-blue-400">{{ $stat['izin'] ?? 0 }}</td>
                                <td class="px-3 py-3 text-center font-bold text-indigo-600 dark:text-indigo-400">{{ $stat['sakit'] ?? 0 }}</td>
                                <td class="px-3 py-3 text-center font-bold text-danger-600 dark:text-danger-400">{{ $stat['alpa'] ?? 0 }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $daysInMonth + 6 }}" class="py-12 text-center text-gray-400 dark:text-gray-600">
                                    Tidak ada data siswa terdaftar di kelas ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Loading overlay --}}
            <div wire:loading class="flex justify-center py-4">
                <x-filament::loading-indicator class="h-6 w-6 text-primary-500"/>
            </div>
        </x-filament::section>
        @endif

        {{-- ===== MODAL INPUT MANUAL ===== --}}
        @if($showInputModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-data>
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeInputModal"></div>

            {{-- Modal --}}
            <div class="relative w-full max-w-2xl bg-white dark:bg-gray-900 rounded-2xl shadow-2xl overflow-hidden z-10">
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div>
                        <h3 class="text-lg font-bold text-gray-950 dark:text-white">Input Absensi Manual</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Masukkan atau perbarui absensi siswa untuk tanggal tertentu</p>
                    </div>
                    <button wire:click="closeInputModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                        <x-heroicon-o-x-mark class="w-6 h-6"/>
                    </button>
                </div>

                {{-- Date Picker --}}
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-2">Tanggal Absensi</label>
                    <input type="date" wire:model.live="inputDate"
                        class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-3 py-2 shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                </div>

                {{-- Siswa List --}}
                <div class="overflow-y-auto max-h-[400px]">
                    <table class="min-w-full text-sm">
                        <thead class="sticky top-0 bg-gray-50 dark:bg-gray-800 z-10">
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Nama Siswa</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Menit Telat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($inputStudents as $studentId => $sData)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                                <td class="px-6 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $sData['name'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    <select wire:model.live="inputStudents.{{ $studentId }}.status"
                                        class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-2 py-1.5 shadow-sm focus:ring-2 focus:ring-primary-500 outline-none cursor-pointer">
                                        <option value="">-- Pilih --</option>
                                        <option value="hadir">Hadir</option>
                                        <option value="telat">Telat</option>
                                        <option value="sakit">Sakit</option>
                                        <option value="izin">Izin</option>
                                        <option value="alpa">Alpa</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if(($inputStudents[$studentId]['status'] ?? '') === 'telat')
                                    <input type="number" min="1" wire:model.lazy="inputStudents.{{ $studentId }}.late_minutes"
                                        placeholder="mnt"
                                        class="w-20 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-2 py-1.5 shadow-sm focus:ring-2 focus:ring-primary-500 outline-none text-center">
                                    @else
                                    <span class="text-gray-300 dark:text-gray-700">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <x-filament::button color="gray" wire:click="closeInputModal">
                        Batal
                    </x-filament::button>
                    <x-filament::button wire:click="saveManualInput" wire:loading.attr="disabled" icon="heroicon-o-check">
                        Simpan Absensi
                    </x-filament::button>
                </div>
            </div>
        </div>
        @endif

    </div>
</x-filament-panels::page>

<x-filament-panels::page>
    <div class="space-y-6">

        {{-- ===== FILTER SECTION ===== --}}
        <x-filament::section>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between flex-wrap">
                <div>
                    <h2 class="text-lg font-bold text-gray-950 dark:text-white">Input Presensi Manual</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Pilih tanggal dan kelas untuk menginput atau mengubah absen.</p>
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
                        <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Kelas</label>
                        <select wire:model.change="selectedClassId"
                            class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-3 py-2 shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none cursor-pointer">
                            @foreach($classes as $kelas)
                                <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Filter Tanggal --}}
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Tanggal</label>
                        <input type="date" wire:model.live="inputDate"
                            class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-3 py-2 shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- ===== TABEL SISWA ===== --}}
        @if($selectedClassId && $inputDate)
        @if($isInputDateHoliday)
        <x-filament::section>
            <div class="p-4 rounded-lg bg-danger-50 dark:bg-danger-900/30 text-danger-600 dark:text-danger-400 border border-danger-200 dark:border-danger-800 flex items-start gap-3">
                <x-heroicon-o-exclamation-triangle class="w-6 h-6 shrink-0"/>
                <div>
                    <h3 class="font-bold">Hari Libur</h3>
                    <p class="text-sm mt-1">Tanggal yang Anda pilih ({{ \Carbon\Carbon::parse($inputDate)->isoFormat('D MMMM Y') }}) adalah hari libur (Sabtu, Minggu, atau libur nasional/khusus). Sistem tidak dapat menerima input presensi pada hari libur.</p>
                </div>
            </div>
        </x-filament::section>
        @else
        <x-filament::section>
            @if(count($inputStudents) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="px-6 py-3 font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Siswa</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status Kehadiran</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Menit Telat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($inputStudents as $studentId => $sData)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                                {{ $sData['name'] }}
                                @if(isset($sData['is_manual_input']) && $sData['is_manual_input'] === false)
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                        Scan Otomatis
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center w-48">
                                <select wire:model.live="inputStudents.{{ $studentId }}.status"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-2 py-1.5 shadow-sm focus:ring-2 focus:ring-primary-500 outline-none cursor-pointer">
                                    <option value="">-- Pilih --</option>
                                    <option value="hadir">Hadir</option>
                                    <option value="telat">Terlambat</option>
                                    <option value="sakit">Sakit</option>
                                    <option value="izin">Izin</option>
                                    <option value="alpa">Alpa</option>
                                </select>
                            </td>
                            <td class="px-4 py-3 text-center w-32">
                                @if(($inputStudents[$studentId]['status'] ?? '') === 'telat')
                                <div class="flex items-center justify-center">
                                    <input type="number" min="1" wire:model.lazy="inputStudents.{{ $studentId }}.late_minutes"
                                        placeholder="0"
                                        class="w-20 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-2 py-1.5 shadow-sm focus:ring-2 focus:ring-primary-500 outline-none text-center">
                                    <span class="ml-2 text-xs text-gray-500">mnt</span>
                                </div>
                                @else
                                <span class="text-gray-300 dark:text-gray-700">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-end">
                <x-filament::button wire:click="saveManualInput" wire:loading.attr="disabled" icon="heroicon-o-check">
                    <span wire:loading.remove wire:target="saveManualInput">Simpan Presensi</span>
                    <span wire:loading wire:target="saveManualInput">Menyimpan...</span>
                </x-filament::button>
            </div>
            @else
            <div class="text-center py-12">
                <p class="text-gray-500 dark:text-gray-400">Tidak ada data siswa untuk kelas ini.</p>
            </div>
            @endif
        </x-filament::section>
        @endif
        @endif
    </div>
</x-filament-panels::page>

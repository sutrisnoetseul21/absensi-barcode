<x-filament-panels::page>
    <div class="space-y-6">

        {{-- ===== HEADER & FILTER SECTION (NATIVE FILAMENT STYLE) ===== --}}
        <x-filament::section>
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-950 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-pencil-square class="w-6 h-6 text-primary-500" />
                        Input Presensi Manual
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pilih Tahun Ajaran, Kelas, dan Tanggal, lalu klik tombol <strong class="text-gray-700 dark:text-gray-200">Proses</strong> untuk menampilkan daftar siswa.</p>
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

                    {{-- Filter Tanggal --}}
                    <div class="flex flex-col gap-1 flex-1 sm:flex-none">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Tanggal</label>
                        <input type="date" wire:model.live="inputDate"
                            class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-xs font-bold px-3 py-1.5 shadow-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>

                    {{-- Tombol Proses --}}
                    <x-filament::button wire:click="filterData" wire:loading.attr="disabled" icon="heroicon-m-arrow-path" color="primary">
                        <span wire:loading.remove wire:target="filterData">Proses</span>
                        <span wire:loading wire:target="filterData">Memproses...</span>
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        {{-- ===== TABEL SISWA ===== --}}
        @if(!$hasSubmittedFilter)
            <x-filament::section>
                <div class="text-center py-12">
                    <div class="w-12 h-12 bg-primary-50 dark:bg-primary-950/50 text-primary-500 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <x-heroicon-o-funnel class="w-6 h-6" />
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 mb-1">Daftar Siswa Belum Dimuat</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">Silakan pilih Tahun Ajaran, Kelas, dan Tanggal di atas, lalu klik tombol <strong class="text-primary-600 dark:text-primary-400">Proses</strong> untuk menampilkan tabel siswa.</p>
                </div>
            </x-filament::section>
        @elseif($selectedClassId && $inputDate)
            @if($isInputDateHoliday)
                <x-filament::section>
                    <div class="p-4 rounded-lg bg-danger-50 dark:bg-danger-900/30 text-danger-600 dark:text-danger-400 border border-danger-200 dark:border-danger-800 flex items-start gap-3">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6 shrink-0"/>
                        <div>
                            <h3 class="font-bold">Hari Libur Terdeteksi</h3>
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
                                    
                                    {{-- HEADER DATANG MASSAL --}}
                                    <th class="px-4 py-3 text-center font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        <div class="flex flex-col items-center gap-1.5">
                                            <span>Datang</span>
                                            <select wire:model.live="bulkStatusDatang" wire:change="applyBulkStatusDatang($event.target.value)"
                                                class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-xs px-2 py-1 shadow-2xs focus:ring-2 focus:ring-primary-500 outline-none cursor-pointer normal-case font-bold">
                                                <option value="">-- Set Massal --</option>
                                                <option value="hadir">⚡ Hadir Semua</option>
                                                <option value="telat">Terlambat Semua</option>
                                                <option value="izin">Izin Semua</option>
                                                <option value="sakit">Sakit Semua</option>
                                                <option value="alpa">Alpa Semua</option>
                                            </select>
                                        </div>
                                    </th>

                                    <th class="px-4 py-3 text-center font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Menit Telat</th>
                                    
                                    {{-- HEADER PULANG MASSAL --}}
                                    <th class="px-4 py-3 text-center font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        <div class="flex flex-col items-center gap-1.5">
                                            <span>Pulang</span>
                                            <select wire:model.live="bulkStatusPulang" wire:change="applyBulkStatusPulang($event.target.value)"
                                                class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-xs px-2 py-1 shadow-2xs focus:ring-2 focus:ring-primary-500 outline-none cursor-pointer normal-case font-bold">
                                                <option value="">-- Set Massal --</option>
                                                <option value="pulang">⚡ Pulang Semua</option>
                                                <option value="izin">Izin Semua</option>
                                                <option value="sakit">Sakit Semua</option>
                                                <option value="alpa">Alpa Semua</option>
                                            </select>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($inputStudents as $index => $sData)
                                @php
                                    $curStatus = $sData['status'] ?? '';
                                    $curPulang = $sData['status_pulang'] ?? '';
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors" wire:key="student-{{ $sData['id'] }}">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                                        {{ $sData['name'] ?? 'Data tidak valid' }}
                                        @if(isset($sData['is_manual_input']) && $sData['is_manual_input'] === false)
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                                Scan Otomatis Kiosk
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center w-48">
                                        <select wire:model.live="inputStudents.{{ $index }}.status"
                                            class="w-full rounded-lg border text-sm font-bold px-2 py-1.5 shadow-sm focus:ring-2 focus:ring-primary-500 outline-none cursor-pointer transition-colors
                                                {{ $curStatus === 'hadir' ? 'bg-emerald-50 border-emerald-300 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : '' }}
                                                {{ $curStatus === 'telat' ? 'bg-amber-50 border-amber-300 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : '' }}
                                                {{ $curStatus === 'izin' ? 'bg-sky-50 border-sky-300 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300' : '' }}
                                                {{ $curStatus === 'sakit' ? 'bg-indigo-50 border-indigo-300 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300' : '' }}
                                                {{ $curStatus === 'alpa' ? 'bg-rose-50 border-rose-300 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' : '' }}
                                                {{ empty($curStatus) ? 'bg-white border-gray-300 dark:bg-gray-900 dark:border-gray-600 text-gray-900 dark:text-gray-100' : '' }}
                                            ">
                                            <option value="">-- Pilih --</option>
                                            <option value="hadir">Hadir</option>
                                            <option value="telat">Terlambat</option>
                                            <option value="sakit">Sakit</option>
                                            <option value="izin">Izin</option>
                                            <option value="alpa">Alpa</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-3 text-center w-32">
                                        @if($curStatus === 'telat')
                                        <div class="flex items-center justify-center">
                                            <input type="number" min="1" wire:model.lazy="inputStudents.{{ $index }}.late_minutes"
                                                placeholder="0"
                                                class="w-20 rounded-lg border border-amber-300 bg-amber-50 dark:bg-amber-950/60 text-amber-900 dark:text-amber-200 font-bold text-sm px-2 py-1.5 shadow-sm focus:ring-2 focus:ring-amber-500 outline-none text-center">
                                            <span class="ml-2 text-xs text-gray-500">mnt</span>
                                        </div>
                                        @else
                                        <span class="text-gray-300 dark:text-gray-700">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center w-48">
                                        @php
                                            $isNonAttendance = in_array($curStatus, ['izin', 'sakit', 'alpa']);
                                        @endphp
                                        @if($isNonAttendance)
                                            <div class="w-full text-center py-1.5 px-2 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-500 dark:text-gray-400 text-sm italic cursor-not-allowed">
                                                {{ ucfirst($curStatus) }}
                                            </div>
                                        @else
                                            <select wire:model.live="inputStudents.{{ $index }}.status_pulang"
                                                class="w-full rounded-lg border text-sm font-bold px-2 py-1.5 shadow-sm focus:ring-2 focus:ring-primary-500 outline-none cursor-pointer transition-colors
                                                    {{ $curPulang === 'pulang' ? 'bg-emerald-50 border-emerald-300 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : '' }}
                                                    {{ $curPulang === 'izin' ? 'bg-sky-50 border-sky-300 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300' : '' }}
                                                    {{ $curPulang === 'sakit' ? 'bg-indigo-50 border-indigo-300 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300' : '' }}
                                                    {{ $curPulang === 'alpa' ? 'bg-rose-50 border-rose-300 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' : '' }}
                                                    {{ empty($curPulang) ? 'bg-white border-gray-300 dark:bg-gray-900 dark:border-gray-600 text-gray-900 dark:text-gray-100' : '' }}
                                                ">
                                                <option value="">-- Pilih --</option>
                                                <option value="pulang">Pulang</option>
                                                <option value="izin">Izin</option>
                                                <option value="sakit">Sakit</option>
                                                <option value="alpa">Alpa</option>
                                            </select>
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
                        <p class="text-gray-500 dark:text-gray-400 font-bold">Tidak ada data siswa untuk kelas ini.</p>
                    </div>
                    @endif
                </x-filament::section>
            @endif
        @endif
    </div>
</x-filament-panels::page>

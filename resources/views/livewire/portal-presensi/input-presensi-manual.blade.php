<div>
    <div class="mb-6 lg:mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Input Presensi Manual</h1>
            <p class="text-sm sm:text-base text-slate-500 mt-1">Kelola dan input data kehadiran siswa secara manual.</p>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session()->has('success'))
        <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div>
                <h3 class="text-sm font-bold text-emerald-800">Berhasil</h3>
                <p class="text-xs text-emerald-600 mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-start gap-3">
            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div>
                <h3 class="text-sm font-bold text-rose-800">Gagal</h3>
                <p class="text-xs text-rose-600 mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
    @endif
    @if (session()->has('warning'))
        <div class="mb-4 p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            <div>
                <h3 class="text-sm font-bold text-amber-800">Perhatian</h3>
                <p class="text-xs text-amber-600 mt-0.5">{{ session('warning') }}</p>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 mb-6">
        <div class="flex flex-col md:flex-row md:items-end gap-4">
            <!-- Filter Tahun Ajaran -->
            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Ajaran</label>
                <select wire:model.change="selectedAcademicYearId" class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm px-3 py-2 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Kelas -->
            @if($classes->isNotEmpty())
            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-700 mb-1">Kelas</label>
                <select wire:model.change="selectedClassId" class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm px-3 py-2 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($classes as $kelas)
                        <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Filter Tanggal -->
            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Presensi</label>
                <x-portal-presensi.date-indo
                    model="inputDate"
                    :value="$inputDate"
                    :live="true"
                    :nullable="true"
                    placeholder="Pilih tanggal presensi"
                    buttonClass="w-full flex items-center justify-between gap-2 rounded-xl border border-slate-300 bg-slate-50 hover:bg-white text-slate-900 text-sm font-bold px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all text-left cursor-pointer"
                />
            </div>

            <!-- Tombol Proses -->
            <div>
                <button wire:click="filterData" wire:loading.attr="disabled" class="w-full md:w-auto flex items-center justify-center gap-2 bg-brand-primary hover:bg-brand-primary-light text-white px-5 py-2.5 rounded-xl font-bold shadow-md shadow-brand-primary/20 transition-all">
                    <svg wire:loading.remove wire:target="filterData" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    <svg wire:loading wire:target="filterData" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span wire:loading.remove wire:target="filterData">Proses Data</span>
                    <span wire:loading wire:target="filterData">Memproses...</span>
                </button>
            </div>
        </div>
    </div>

    @if(!$hasSubmittedFilter)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-10 text-center flex flex-col items-center justify-center min-h-[300px]">
            <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Daftar Siswa Belum Dimuat</h3>
            <p class="text-slate-500 max-w-md">Silakan pilih Tahun Ajaran, Kelas, dan Tanggal di atas, lalu klik tombol <strong class="text-slate-700">Proses Data</strong> untuk menampilkan tabel input.</p>
        </div>
    @elseif($selectedClassId && $inputDate)
        @if($isInputDateHoliday)
            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-6 flex flex-col items-center justify-center text-center">
                <div class="w-12 h-12 bg-rose-100 text-rose-500 rounded-full flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <h3 class="text-lg font-bold text-rose-800 mb-1">Hari Libur Terdeteksi</h3>
                <p class="text-sm text-rose-600 max-w-lg">Tanggal yang Anda pilih ({{ \Carbon\Carbon::parse($inputDate)->locale('id')->translatedFormat('l, d F Y') }}) adalah hari libur. Sistem tidak dapat menerima input presensi pada hari libur.</p>
            </div>
        @else
            <div class="mb-4 px-4 py-3 rounded-xl bg-sky-50 border border-sky-200 flex flex-wrap items-center justify-between gap-2 text-xs text-sky-900">
                <div class="flex items-center gap-2 font-semibold">
                    <svg class="w-4 h-4 text-sky-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>Aturan Jam Masuk Sekolah:</span>
                    <span class="px-2 py-0.5 rounded-md bg-white border border-sky-200 font-extrabold">{{ $checkinHm }} WIB</span>
                    <span>• Toleransi Telat:</span>
                    <span class="px-2 py-0.5 rounded-md bg-white border border-sky-200 font-extrabold">{{ $lateThresholdMinutes }} mnt</span>
                    <span>(Status Terlambat berlaku di atas jam <strong class="text-rose-700">{{ $lateCutoffHm }} WIB</strong>)</span>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
                @if(count($inputStudents) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-600">
                                <tr>
                                    <th class="px-6 py-4 font-bold uppercase tracking-wider">Nama Siswa</th>
                                    <th class="px-4 py-4 text-center font-bold">
                                        <div class="flex flex-col items-center gap-2">
                                            <span class="uppercase tracking-wider">Status Datang</span>
                                            <select wire:model.live="bulkStatusDatang" wire:change="applyBulkStatusDatang($event.target.value)" class="bg-white border border-slate-300 rounded-lg px-2 py-1 text-xs font-semibold focus:ring-2 focus:ring-brand-primary/20 outline-none w-32">
                                                <option value="">-- Set Massal --</option>
                                                <option value="hadir">Hadir Semua</option>
                                                <option value="telat">Telat Semua</option>
                                                <option value="izin">Izin Semua</option>
                                                <option value="sakit">Sakit Semua</option>
                                                <option value="alpa">Alpa Semua</option>
                                            </select>
                                        </div>
                                    </th>
                                    <th class="px-4 py-4 text-center font-bold uppercase tracking-wider">Waktu Kehadiran</th>
                                    @if($enableCheckout)
                                    <th class="px-4 py-4 text-center font-bold">
                                        <div class="flex flex-col items-center gap-2">
                                            <span class="uppercase tracking-wider">Status Pulang</span>
                                            <select wire:model.live="bulkStatusPulang" wire:change="applyBulkStatusPulang($event.target.value)" class="bg-white border border-slate-300 rounded-lg px-2 py-1 text-xs font-semibold focus:ring-2 focus:ring-brand-primary/20 outline-none w-32">
                                                <option value="">-- Set Massal --</option>
                                                <option value="pulang">Pulang Semua</option>
                                                <option value="izin">Izin Semua</option>
                                                <option value="sakit">Sakit Semua</option>
                                                <option value="alpa">Alpa Semua</option>
                                            </select>
                                        </div>
                                    </th>
                                    <th class="px-4 py-4 text-center font-bold uppercase tracking-wider">Waktu Pulang</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($inputStudents as $index => $sData)
                                    @php
                                        $curStatus = $sData['status'] ?? '';
                                        $curPulang = $sData['status_pulang'] ?? '';
                                        $isNonAttendance = in_array($curStatus, ['izin', 'sakit', 'alpa']);
                                        $hasAlarm = !empty($sData['arrival_alarm']);
                                    @endphp
                                    <tr class="{{ $hasAlarm ? 'bg-rose-50/70' : 'hover:bg-slate-50' }} transition-colors" wire:key="student-{{ $sData['id'] }}">
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="font-bold text-slate-800">{{ $sData['name'] ?? 'Data tidak valid' }}</span>
                                                @if(isset($sData['is_manual_input']) && $sData['is_manual_input'] === false)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                        Scan Otomatis
                                                    </span>
                                                @endif
                                                @if(!empty($sData['manual_audit']['is_manual']))
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200" title="{{ $sData['note'] ?? '' }}">
                                                        {{ $sData['manual_audit']['summary'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <select wire:model.live="inputStudents.{{ $index }}.status"
                                                class="w-40 rounded-xl border text-sm font-bold px-3 py-2 outline-none transition-colors
                                                    {{ $curStatus === 'hadir' ? 'bg-emerald-50 border-emerald-300 text-emerald-800' : '' }}
                                                    {{ $curStatus === 'telat' ? 'bg-amber-50 border-amber-300 text-amber-800' : '' }}
                                                    {{ $curStatus === 'izin' ? 'bg-sky-50 border-sky-300 text-sky-800' : '' }}
                                                    {{ $curStatus === 'sakit' ? 'bg-indigo-50 border-indigo-300 text-indigo-800' : '' }}
                                                    {{ $curStatus === 'alpa' ? 'bg-rose-50 border-rose-300 text-rose-800' : '' }}
                                                    {{ empty($curStatus) ? 'bg-slate-50 border-slate-300 text-slate-900' : '' }}
                                                ">
                                                <option value="">-- Pilih --</option>
                                                <option value="hadir">Hadir</option>
                                                <option value="telat">Terlambat</option>
                                                <option value="sakit">Sakit</option>
                                                <option value="izin">Izin</option>
                                                <option value="alpa">Alpa</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if(in_array($curStatus, ['hadir', 'telat']))
                                                <div class="flex flex-col items-center gap-1">
                                                    <div class="w-40 {{ $hasAlarm ? 'ring-2 ring-rose-500 rounded-lg' : '' }}">
                                                        <x-portal-presensi.time-24-wib
                                                            :model="'inputStudents.' . $index . '.scan_time'"
                                                            :value="$sData['scan_time'] ?: '07:00'"
                                                            :compact="true"
                                                            :live="true"
                                                            :bgClass="$hasAlarm ? 'bg-rose-50 border-rose-300' : 'bg-slate-50'"
                                                        />
                                                    </div>
                                                    @if($hasAlarm)
                                                        <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-rose-100 border border-rose-300 text-rose-800 text-[11px] font-extrabold animate-pulse whitespace-normal max-w-[240px] leading-tight">
                                                            <span>🚨 {{ $sData['arrival_alarm'] }}</span>
                                                        </div>
                                                    @elseif($curStatus === 'telat')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-amber-100 border border-amber-200 text-amber-800 text-[11px] font-bold">
                                                            Telat +{{ $sData['late_minutes'] ?? 0 }} mnt
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-slate-300">-</span>
                                            @endif
                                        </td>
                                        @if($enableCheckout)
                                        <td class="px-4 py-3 text-center">
                                            @if($isNonAttendance)
                                                <div class="w-40 mx-auto py-2 px-2 bg-slate-100 border border-slate-200 rounded-xl text-slate-500 text-sm font-bold opacity-80 cursor-not-allowed">
                                                    {{ ucfirst($curStatus) }}
                                                </div>
                                            @else
                                                <select wire:model.live="inputStudents.{{ $index }}.status_pulang"
                                                    class="w-40 rounded-xl border text-sm font-bold px-3 py-2 outline-none transition-colors
                                                        {{ $curPulang === 'pulang' ? 'bg-emerald-50 border-emerald-300 text-emerald-800' : '' }}
                                                        {{ $curPulang === 'izin' ? 'bg-sky-50 border-sky-300 text-sky-800' : '' }}
                                                        {{ $curPulang === 'sakit' ? 'bg-indigo-50 border-indigo-300 text-indigo-800' : '' }}
                                                        {{ $curPulang === 'alpa' ? 'bg-rose-50 border-rose-300 text-rose-800' : '' }}
                                                        {{ empty($curPulang) ? 'bg-slate-50 border-slate-300 text-slate-900' : '' }}
                                                    ">
                                                    <option value="">-- Pilih --</option>
                                                    <option value="pulang">Pulang</option>
                                                    <option value="izin">Izin</option>
                                                    <option value="sakit">Sakit</option>
                                                    <option value="alpa">Alpa</option>
                                                </select>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if(!$isNonAttendance && $curPulang === 'pulang')
                                                <div class="w-40 mx-auto">
                                                    <x-portal-presensi.time-24-wib
                                                        :model="'inputStudents.' . $index . '.scan_out_time'"
                                                        :value="$sData['scan_out_time'] ?: '14:00'"
                                                        :compact="true"
                                                    />
                                                </div>
                                            @else
                                                <span class="text-slate-300">-</span>
                                            @endif
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-6 bg-slate-50 border-t border-slate-200 flex justify-end">
                        <button wire:click="saveManualInput" wire:loading.attr="disabled" class="flex items-center gap-2 bg-brand-primary hover:bg-brand-primary-light text-white px-6 py-2.5 rounded-xl font-bold shadow-md shadow-brand-primary/20 transition-all">
                            <svg wire:loading.remove wire:target="saveManualInput" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            <svg wire:loading wire:target="saveManualInput" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span wire:loading.remove wire:target="saveManualInput">Simpan Presensi</span>
                            <span wire:loading wire:target="saveManualInput">Menyimpan...</span>
                        </button>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </div>
                        <p class="text-slate-500 font-bold">Tidak ada data siswa untuk kelas ini.</p>
                    </div>
                @endif
            </div>
        @endif
    @endif
</div>

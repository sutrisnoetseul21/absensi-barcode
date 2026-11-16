<div class="min-h-full pb-16 font-jakarta">
    <!-- Header Section -->
    <div class="mb-6 lg:mb-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="w-3 h-3 rounded-full bg-teal-500 animate-pulse"></span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Dashboard Presensi Sholat Dhuhur</h1>
                @if($isHoliday)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                        Hari Libur{{ $holidayDesc ? ': ' . $holidayDesc : '' }}
                    </span>
                @endif
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">
                Pemantauan ibadah sholat dhuhur berjamaah seluruh kelas untuk <strong class="text-slate-700">{{ $dateFormatted }}</strong>.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Date Picker Indo -->
            <div class="relative">
                <x-portal-presensi.date-indo
                    model="selectedDate"
                    :value="$selectedDate"
                    :live="true"
                    placeholder="Pilih tanggal"
                    buttonClass="min-w-[215px] flex items-center justify-between gap-2 px-3 py-2 text-xs sm:text-sm bg-white rounded-xl border border-slate-200 font-bold text-slate-700 shadow-xs hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-600 transition-all text-left cursor-pointer"
                />
            </div>

            <!-- Academic Year -->
            <select wire:model.live="selectedAcademicYearId"
                class="py-2 pl-3 pr-8 text-xs sm:text-sm bg-white rounded-xl border border-slate-200 font-bold text-slate-700 shadow-xs focus:ring-2 focus:ring-teal-500/30 focus:border-teal-600 transition-all cursor-pointer">
                @foreach($academicYears as $ay)
                    <option value="{{ $ay->id }}">{{ $ay->name }} {{ $ay->status === 'aktif' ? '(Aktif)' : '' }}</option>
                @endforeach
            </select>

            <!-- Sinkron Pagi Semua Kelas -->
            <button type="button" wire:click="sinkronPresensiPagiSemuaKelas" wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 shadow-xs transition-all cursor-pointer">
                <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Sinkron Pagi</span>
            </button>

            <!-- PDF Per Kelas -->
            <button type="button" wire:click="downloadRekapKelasPdf" wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-teal-600 to-emerald-600 hover:opacity-95 shadow-md shadow-teal-600/20 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Rekap Kelas (PDF)</span>
            </button>

            <!-- Export Excel -->
            <button type="button" wire:click="exportExcel" wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 shadow-xs transition-all cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export Excel</span>
            </button>

            <!-- PDF Rekap Harian -->
            <button type="button" wire:click="downloadRekapHarianPdf" wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs sm:text-sm font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 shadow-xs transition-all cursor-pointer">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Rekap Harian</span>
            </button>
        </div>
    </div>

    <!-- Spotlight Card -->
    <div class="mb-6 relative overflow-hidden bg-gradient-to-r from-teal-600 via-teal-600 to-emerald-700 border border-teal-500/30 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-teal-600/20">
        <div class="absolute -top-12 -right-12 w-72 h-72 bg-white/10 rounded-full filter blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider bg-white/20 border border-white/25">
                    Rekapitulasi Sholat Dhuhur Harian
                </span>
                <h2 class="text-2xl sm:text-3xl font-black mt-3">Total Siswa Hadir Berjamaah</h2>
                <p class="text-teal-50 text-xs sm:text-sm mt-1">
                    Hadir: <strong>{{ $stats['hadir'] ?? 0 }}</strong> &bull; Ijin/Halangan: <strong>{{ $stats['ijin'] ?? 0 }}</strong> &bull; Alpa: <strong>{{ $stats['tidak_hadir'] ?? 0 }}</strong> &bull; Belum Absen: <strong>{{ $stats['belum'] ?? 0 }}</strong>
                    @if(($stats['non_muslim_total'] ?? 0) > 0)
                        &bull; Non-Muslim: <strong>{{ $stats['non_muslim_total'] }}</strong>
                    @endif
                </p>
            </div>
            <div class="bg-white/15 backdrop-blur-md border border-white/25 rounded-2xl px-5 py-4 text-right shrink-0">
                <div class="text-[11px] font-bold uppercase tracking-wider text-teal-100">Siswa Wajib Sholat</div>
                <div class="text-3xl font-black tabular-nums mt-0.5">
                    {{ $stats['hadir'] ?? 0 }} <span class="text-base font-semibold text-teal-100">/ {{ $stats['muslim_total'] ?? 0 }}</span>
                </div>
                <div class="text-xs font-bold text-emerald-200 mt-0.5">{{ $stats['persen'] ?? 0 }}% Kepatuhan</div>
            </div>
        </div>
    </div>

    <!-- 4 Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-xs">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Hadir Berjamaah</div>
            <div class="text-2xl sm:text-3xl font-black text-emerald-600 mt-1 tabular-nums">{{ $stats['hadir'] ?? 0 }}</div>
            <div class="text-[11px] text-slate-400 mt-1">L: {{ $stats['hadir_l'] ?? 0 }} &bull; P: {{ $stats['hadir_p'] ?? 0 }}</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-xs">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Ijin / Halangan</div>
            <div class="text-2xl sm:text-3xl font-black text-sky-600 mt-1 tabular-nums">{{ $stats['ijin'] ?? 0 }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Termasuk halangan/haid</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-xs">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tidak Hadir (Alpa)</div>
            <div class="text-2xl sm:text-3xl font-black text-rose-600 mt-1 tabular-nums">{{ $stats['tidak_hadir'] ?? 0 }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Tanpa keterangan</div>
        </div>

        <button type="button" wire:click="setTab('belum_sholat')"
            class="text-left bg-teal-600 text-white rounded-2xl p-4 sm:p-5 shadow-md shadow-teal-600/20 hover:opacity-95 transition-all cursor-pointer">
            <div class="text-xs font-bold text-teal-100 uppercase tracking-wider">Belum Absen Sholat</div>
            <div class="text-2xl sm:text-3xl font-black mt-1 tabular-nums">{{ $stats['belum'] ?? 0 }}</div>
            <div class="text-[11px] text-teal-100 mt-1">Klik untuk lihat siswa &rarr;</div>
        </button>
    </div>

    <!-- Tabs & Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 bg-slate-50/70">
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" wire:click="setTab('per_kelas')"
                    class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer {{ $activeTab === 'per_kelas' ? 'bg-teal-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                    Rekap Per Kelas ({{ count($classesSummary) }})
                </button>
                <button type="button" wire:click="setTab('belum_sholat')"
                    class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer {{ $activeTab === 'belum_sholat' ? 'bg-teal-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                    Belum Absen ({{ $unattendedCount }})
                </button>
                <button type="button" wire:click="setTab('semua_siswa')"
                    class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer {{ $activeTab === 'semua_siswa' ? 'bg-teal-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                    Semua Siswa
                </button>
            </div>

            @if($activeTab !== 'per_kelas')
                <div class="flex flex-wrap items-center gap-2">
                    <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Cari nama / NISN / kelas..."
                        class="px-3.5 py-2 text-xs sm:text-sm bg-white border border-slate-200 rounded-xl font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500/30">
                </div>
            @endif
        </div>

        @if($activeTab === 'per_kelas')
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-black text-slate-500 uppercase tracking-wider">
                            <th class="py-3.5 px-4 text-center w-12">No</th>
                            <th class="py-3.5 px-4">Kelas</th>
                            <th class="py-3.5 px-4">Wali Kelas</th>
                            <th class="py-3.5 px-4 text-center">Wajib Sholat</th>
                            <th class="py-3.5 px-4 text-center">Hadir</th>
                            <th class="py-3.5 px-4 text-center">Ijin/Haid</th>
                            <th class="py-3.5 px-4 text-center">Alpa</th>
                            <th class="py-3.5 px-4 text-center">Belum</th>
                            <th class="py-3.5 px-4 text-center">%</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($classesSummary as $row)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4 text-center text-slate-400 font-bold">{{ $loop->iteration }}</td>
                                <td class="py-3 px-4 font-bold text-slate-800">Kelas {{ $row['name'] }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $row['wali_kelas'] }}</td>
                                <td class="py-3 px-4 text-center font-bold text-slate-700">{{ $row['muslim_total'] }}</td>
                                <td class="py-3 px-4 text-center font-black text-emerald-600">{{ $row['hadir'] }}</td>
                                <td class="py-3 px-4 text-center font-bold text-sky-600">{{ $row['ijin'] }}</td>
                                <td class="py-3 px-4 text-center font-bold text-rose-600">{{ $row['tidak_hadir'] }}</td>
                                <td class="py-3 px-4 text-center font-bold text-amber-700">{{ $row['belum'] }}</td>
                                <td class="py-3 px-4 text-center font-extrabold text-teal-700">{{ $row['persen'] }}%</td>
                                <td class="py-3 px-4 text-center">
                                    <a href="{{ route('portal-presensi.sholat-dhuhur.input-manual', ['class_id' => $row['id']]) }}"
                                       class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-teal-50 text-teal-700 hover:bg-teal-100 border border-teal-200 transition-colors">
                                        Input
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-10 text-center text-slate-400 font-bold">Belum ada data kelas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-4 sm:p-5">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <select wire:model.live="selectedClassFilter" class="text-xs sm:text-sm font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-slate-700">
                            <option value="all">Semua Kelas</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}">Kelas {{ $c->name }}</option>
                            @endforeach
                        </select>

                        @if($activeTab === 'semua_siswa')
                            <select wire:model.live="filterStatus" class="text-xs sm:text-sm font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-slate-700">
                                <option value="all">Semua Status</option>
                                <option value="hadir">Hadir Berjamaah</option>
                                <option value="ijin">Ijin / Halangan</option>
                                <option value="tidak_hadir">Tidak Hadir (Alpa)</option>
                                <option value="belum">Belum Absen</option>
                                <option value="non_muslim">Non-Muslim</option>
                            </select>
                        @endif
                    </div>

                    <select wire:model.live="perPage" class="text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 text-slate-700">
                        <option value="25">25 / hal</option>
                        <option value="50">50 / hal</option>
                        <option value="100">100 / hal</option>
                    </select>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-black text-slate-500 uppercase tracking-wider">
                                <th class="py-3.5 px-4 text-center w-12">No</th>
                                <th class="py-3.5 px-4">Nama Siswa</th>
                                <th class="py-3.5 px-4 text-center">Kelas</th>
                                <th class="py-3.5 px-4 text-center">NISN</th>
                                <th class="py-3.5 px-4 text-center">Status Sholat</th>
                                <th class="py-3.5 px-4">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($paginatedStudents as $st)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3 px-4 text-center font-bold text-slate-400">{{ $paginatedStudents->firstItem() + $loop->index }}</td>
                                    <td class="py-3 px-4 font-bold text-slate-800">{{ $st['name'] }}</td>
                                    <td class="py-3 px-4 text-center font-bold text-teal-700">Kelas {{ $st['class_name'] }}</td>
                                    <td class="py-3 px-4 text-center text-slate-500">{{ $st['nisn'] ?? '-' }}</td>
                                    <td class="py-3 px-4 text-center">
                                        @if($st['status'] === 'hadir')
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Hadir</span>
                                        @elseif($st['status'] === 'ijin')
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-100 text-sky-800 border border-sky-200">Ijin / Halangan</span>
                                        @elseif($st['status'] === 'tidak_hadir')
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">Tidak Hadir</span>
                                        @elseif($st['status'] === 'non_muslim')
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">Non-Muslim</span>
                                        @else
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">Belum Absen</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-slate-500 text-xs">{{ $st['keterangan'] ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-slate-400 font-bold">Tidak ada data siswa ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($paginatedStudents && $paginatedStudents->hasPages())
                    <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-semibold">
                            Menampilkan {{ $paginatedStudents->firstItem() }}–{{ $paginatedStudents->lastItem() }} dari {{ $paginatedStudents->total() }} siswa
                        </span>
                        <div class="flex gap-1.5">
                            <button type="button" wire:click="previousPage" @disabled($paginatedStudents->onFirstPage()) class="px-3 py-1.5 rounded-lg border border-slate-200 font-bold disabled:opacity-40 cursor-pointer">&laquo; Prev</button>
                            <button type="button" wire:click="nextPage" @disabled(!$paginatedStudents->hasMorePages()) class="px-3 py-1.5 rounded-lg border border-slate-200 font-bold disabled:opacity-40 cursor-pointer">Next &raquo;</button>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>


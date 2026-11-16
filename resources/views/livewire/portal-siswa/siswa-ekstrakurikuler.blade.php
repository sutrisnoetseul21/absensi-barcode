<div class="space-y-6 pb-10">
    @if(!$selectedItem)
        <!-- Header & Empty State -->
        <div class="bg-gradient-to-br from-brand-primary via-sky-700 to-blue-800 rounded-3xl p-6 sm:p-8 text-white shadow-xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-white/15 border border-white/20 mb-2">
                        Ekstrakurikuler
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Ekstrakurikuler Saya</h1>
                    <p class="text-xs sm:text-sm text-sky-100/90 mt-1">Lihat informasi pembina, materi latihan, serta rekap kehadiran ekstrakurikulermu.</p>
                </div>
                <div class="w-full sm:w-56">
                    <select wire:model.live="selectedAcademicYearId" class="w-full rounded-xl border border-white/25 bg-white/15 text-white text-sm font-bold px-3.5 py-2.5 outline-none cursor-pointer">
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" class="text-slate-900">TA {{ $ay->name }} {{ $ay->status === 'aktif' ? '(Aktif)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center shadow-sm">
            <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
            </div>
            <h3 class="text-lg font-extrabold text-slate-800">Belum Terdaftar Ekstrakurikuler</h3>
            <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">Kamu belum terdaftar aktif sebagai anggota ekstrakurikuler pada Tahun Ajaran yang dipilih.</p>
        </div>
    @else
        <!-- Hero Banner Header selaras Portal Guru -->
        <div class="bg-gradient-to-br from-brand-primary via-sky-700 to-blue-800 rounded-3xl p-6 sm:p-7 text-white shadow-xl space-y-5">
            <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4">
                <div class="space-y-1.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-white/15 border border-white/20">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            TA {{ $selectedItem['tahun_ajaran'] }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/25 border border-emerald-300/30 text-emerald-100">
                            Anggota Aktif
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-white/15 border border-white/20">
                            Pembina: {{ $selectedItem['pembinas'] }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-white/15 border border-white/20">
                            Bergabung: {{ $selectedItem['tanggal_gabung'] }}
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
                        Ekstrakurikuler {{ $selectedItem['nama_ekskul'] }}
                    </h1>

                    @if($selectedItem['keterangan'])
                        <p class="text-xs text-sky-100/90 font-medium">
                            {{ $selectedItem['keterangan'] }}
                        </p>
                    @endif
                </div>

                <!-- Switcher Ekskul (jika ikut > 1) & Filter Tahun Ajaran -->
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    @if(count($ekskulList) > 1)
                        <select wire:change="selectEkskul($event.target.value)" class="rounded-xl border border-white/30 bg-white/15 text-white text-xs font-bold px-3.5 py-2.5 outline-none cursor-pointer">
                            @foreach($ekskulList as $eksOpt)
                                <option value="{{ $eksOpt['id'] }}" class="text-slate-900" {{ $eksOpt['id'] === $selectedItem['id'] ? 'selected' : '' }}>
                                    Ekskul: {{ $eksOpt['nama_ekskul'] }}
                                </option>
                            @endforeach
                        </select>
                    @endif

                    <select wire:model.live="selectedAcademicYearId" class="rounded-xl border border-white/30 bg-white/15 text-white text-xs font-bold px-3.5 py-2.5 outline-none cursor-pointer">
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" class="text-slate-900">TA {{ $ay->name }} {{ $ay->status === 'aktif' ? '(Aktif)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- 5 Ringkasan Kehadiran di dalam Hero Banner -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5">
                <div class="px-4 py-3 rounded-2xl bg-white/10 border border-white/15 backdrop-blur-sm">
                    <div class="text-xl font-black text-emerald-300">{{ $selectedItem['hadir'] }}</div>
                    <div class="text-[11px] font-bold text-white/80 uppercase tracking-wider">Hadir</div>
                </div>
                <div class="px-4 py-3 rounded-2xl bg-white/10 border border-white/15 backdrop-blur-sm">
                    <div class="text-xl font-black text-sky-200">{{ $selectedItem['izin'] }}</div>
                    <div class="text-[11px] font-bold text-white/80 uppercase tracking-wider">Izin</div>
                </div>
                <div class="px-4 py-3 rounded-2xl bg-white/10 border border-white/15 backdrop-blur-sm">
                    <div class="text-xl font-black text-indigo-200">{{ $selectedItem['sakit'] }}</div>
                    <div class="text-[11px] font-bold text-white/80 uppercase tracking-wider">Sakit</div>
                </div>
                <div class="px-4 py-3 rounded-2xl bg-white/10 border border-white/15 backdrop-blur-sm">
                    <div class="text-xl font-black text-rose-300">{{ $selectedItem['alpa'] }}</div>
                    <div class="text-[11px] font-bold text-white/80 uppercase tracking-wider">Alpa</div>
                </div>
                <div class="col-span-2 sm:col-span-1 px-4 py-3 rounded-2xl bg-white/15 border border-white/25 backdrop-blur-sm">
                    <div class="text-xl font-black text-white">{{ $selectedItem['pct'] }}%</div>
                    <div class="text-[11px] font-bold text-white/90 uppercase tracking-wider">Persentase Hadir</div>
                </div>
            </div>
        </div>

        <!-- Riwayat Pertemuan & Kehadiran -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="text-sm font-black text-slate-800">Riwayat Pertemuan &amp; Kehadiran ({{ $selectedItem['total_pertemuan'] }} Pertemuan)</h3>
                    <p class="text-[11px] text-slate-400">Catatan materi kegiatan dan status presensi setiap pertemuan</p>
                </div>
                @if($selectedItem['belum'] > 0)
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        Belum Tercatat: {{ $selectedItem['belum'] }}
                    </span>
                @endif
            </div>

            @if(count($selectedItem['pertemuans']) === 0)
                <div class="p-10 text-center text-xs font-semibold text-slate-400">
                    Belum ada pertemuan latihan yang tercatat oleh Pembina.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                <th class="px-5 py-3">Pertemuan</th>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Materi / Kegiatan</th>
                                <th class="px-4 py-3 text-center">Statusmu</th>
                                <th class="px-5 py-3 text-center">Waktu Scan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($selectedItem['pertemuans'] as $p)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-3.5">
                                        <span class="px-2.5 py-0.5 rounded-md text-xs font-black bg-slate-100 text-slate-700">
                                            Ke-{{ $p['pertemuan_ke'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-xs font-semibold text-slate-700 whitespace-nowrap">{{ $p['tanggal_label'] }}</td>
                                    <td class="px-4 py-3.5">
                                        <div class="text-xs font-bold text-slate-800">{{ $p['judul_kegiatan'] }}</div>
                                        @if($p['catatan_pembina'])
                                            <div class="text-[11px] text-slate-500 mt-0.5">{{ $p['catatan_pembina'] }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        @if($p['status'] === 'hadir')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Hadir</span>
                                        @elseif($p['status'] === 'izin')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-100 text-sky-800">Izin</span>
                                        @elseif($p['status'] === 'sakit')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">Sakit</span>
                                        @elseif($p['status'] === 'alpa')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">Alpa</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">Belum Tercatat</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-center text-xs font-mono font-bold text-slate-600">
                                        {{ $p['scan_time'] ? $p['scan_time'] . ' WIB' : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
</div>

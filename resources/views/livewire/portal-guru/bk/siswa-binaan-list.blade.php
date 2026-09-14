<div class="space-y-6">
    <!-- Header Page & Title -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-semibold mb-2">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>Direktori Peserta Didik</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Siswa Kelas Binaan BK</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Pemantauan profil, riwayat konseling, serta indikator kehadiran siswa pada kelas yang Anda bina.
                @if(!$canAccessAll && count($accessibleClasses) > 0)
                    &bull; Kelas: <strong>{{ $accessibleClasses->pluck('name')->join(', ') }}</strong>
                @endif
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="px-4 py-2 rounded-2xl bg-indigo-50/80 border border-indigo-100 text-xs font-bold text-indigo-900">
                Total Siswa Binaan: <span class="text-indigo-600 font-black text-sm">{{ $totalSiswaBinaan }}</span>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row gap-3 items-center justify-between">
        <div class="w-full md:w-80 relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" 
                   wire:model.live.debounce.300ms="search" 
                   placeholder="Cari nama atau NISN siswa..."
                   class="w-full pl-9 pr-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-hidden focus:border-indigo-500 focus:bg-white transition-all">
        </div>

        <div class="w-full md:w-auto flex flex-wrap items-center gap-2">
            <!-- Filter Kelas -->
            @if(count($accessibleClasses) > 1 || $canAccessAll)
                <select wire:model.live="filterClass" 
                        class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-hidden focus:border-indigo-500 font-medium">
                    <option value="">Semua Kelas Binaan</option>
                    @foreach($accessibleClasses as $cls)
                        <option value="{{ $cls->id }}">Kelas {{ $cls->name }}</option>
                    @endforeach
                </select>
            @endif

            <!-- Filter Status Atensi -->
            <select wire:model.live="filterIssue" 
                    class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-hidden focus:border-indigo-500 font-medium">
                <option value="all">Semua Siswa</option>
                <option value="pernah_konseling">Pernah Konseling BK</option>
                <option value="ada_rujukan">Ada Rujukan Guru Wali</option>
                <option value="ada_alpa">Memiliki Catatan Alpa</option>
            </select>
        </div>
    </div>

    <!-- Student Cards Grid -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        @if($enrollments->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-400">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-700">Tidak ada siswa ditemukan</h3>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    Tidak ditemukan data siswa sesuai filter kelas atau pencarian nama yang Anda masukkan.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-4 sm:p-5">
                @foreach($enrollments as $enr)
                    @php
                        $st = $enr->siswa;
                        $kCount = $konselingCounts[$st->id] ?? 0;
                        $rCount = $rujukanCounts[$st->id] ?? 0;
                        $aCount = $alpaCounts[$st->id] ?? 0;
                    @endphp
                    <div class="p-4 rounded-2xl border border-slate-200/80 bg-slate-50/50 hover:bg-white hover:border-indigo-300 hover:shadow-md transition-all flex flex-col justify-between space-y-4 group">
                        
                        <!-- Header Card: Foto & Identitas -->
                        <div class="flex items-start gap-3.5">
                            <img src="{{ $st->avatar_url }}" alt="Avatar" class="w-12 h-12 rounded-2xl object-cover border border-slate-200 shrink-0 group-hover:scale-105 transition-transform">
                            <div class="min-w-0 flex-1">
                                <div class="font-bold text-slate-900 text-sm leading-snug truncate group-hover:text-indigo-600 transition-colors">
                                    {{ $st->name }}
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    NISN: <span class="font-semibold">{{ $st->nisn }}</span>
                                </div>
                                <div class="inline-flex items-center gap-1.5 mt-1 px-2 py-0.5 rounded-md bg-indigo-50 border border-indigo-100 text-[10px] font-bold text-indigo-700">
                                    <span>Kelas {{ $enr->kelas->name ?? '—' }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $st->gender === 'L' ? 'L' : 'P' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Indicator Badges: Konseling, Rujukan, Alpa -->
                        <div class="grid grid-cols-3 gap-2 text-center text-[10px] pt-1">
                            <!-- Konseling -->
                            <div class="p-2 rounded-xl {{ $kCount > 0 ? 'bg-indigo-50 text-indigo-800 border border-indigo-200' : 'bg-white border border-slate-200 text-slate-500' }}">
                                <div class="font-black text-sm {{ $kCount > 0 ? 'text-indigo-600' : 'text-slate-700' }}">{{ $kCount }}</div>
                                <div class="text-[9px] uppercase tracking-wider font-semibold">Konseling</div>
                            </div>

                            <!-- Rujukan Guru Wali -->
                            <div class="p-2 rounded-xl {{ $rCount > 0 ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-white border border-slate-200 text-slate-500' }}">
                                <div class="font-black text-sm {{ $rCount > 0 ? 'text-amber-600' : 'text-slate-700' }}">{{ $rCount }}</div>
                                <div class="text-[9px] uppercase tracking-wider font-semibold">Rujukan</div>
                            </div>

                            <!-- Alpa -->
                            <div class="p-2 rounded-xl {{ $aCount > 0 ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-white border border-slate-200 text-slate-500' }}">
                                <div class="font-black text-sm {{ $aCount > 0 ? 'text-rose-600' : 'text-slate-700' }}">{{ $aCount }}</div>
                                <div class="text-[9px] uppercase tracking-wider font-semibold">Alpa</div>
                            </div>
                        </div>

                        <!-- Actions Footer -->
                        <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between gap-2">
                            @if($kCount > 0)
                                <a href="{{ route('portal-guru.bk.konseling', ['search' => $st->name]) }}" 
                                   class="px-2.5 py-1.5 rounded-xl text-[11px] font-bold text-slate-600 hover:bg-slate-200 transition-all">
                                    Riwayat ({{ $kCount }})
                                </a>
                            @else
                                <span class="text-[11px] text-slate-400 pl-1">Belum ada sesi</span>
                            @endif

                            <a href="{{ route('portal-guru.bk.konseling.create', ['student_id' => $st->id]) }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[11px] shadow-xs active:scale-95 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5H4.5" />
                                </svg>
                                <span>Konseling</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $enrollments->links() }}
            </div>
        @endif
    </div>
</div>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-600/10 border border-purple-600/20 text-purple-700 text-xs font-semibold mb-2">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>Command Center Pimpinan</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">SI-WALI: Dasbor Analitik</h1>
            <p class="text-[11px] font-bold text-brand-primary mt-1 uppercase tracking-wider">Sistem Informasi & Pendampingan Guru Wali</p>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 italic">
                "Satu Klik Ruang Aman, Dampingi Murid Wujudkan Masa Depan"
            </p>
        </div>
        <button type="button" wire:click="$refresh" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition-all">
            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>Segarkan Data</span>
        </button>
    </div>

    <!-- 1. KPI Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Bimbingan -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-500/5 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <div>
                    <h3 class="text-slate-500 font-bold text-xs uppercase tracking-wider">Total Bimbingan</h3>
                    <p class="text-[10px] text-slate-400">Jurnal & Konsultasi</p>
                </div>
            </div>
            <div>
                <span class="text-3xl font-black text-slate-900">{{ number_format($kpi['total_bimbingan']) }}</span>
                <span class="text-sm font-semibold text-slate-400 ml-1">Sesi</span>
            </div>
        </div>

        <!-- Tingkat Penyelesaian -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-500/5 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                </div>
                <div>
                    <h3 class="text-slate-500 font-bold text-xs uppercase tracking-wider">Tingkat Penyelesaian</h3>
                    <p class="text-[10px] text-slate-400">Dari Tiket Konsultasi</p>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900">{{ $kpi['persentase_selesai'] }}</span>
                <span class="text-xl font-bold text-emerald-500">%</span>
            </div>
            <div class="w-full bg-slate-100 h-1.5 rounded-full mt-2">
                <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $kpi['persentase_selesai'] }}%"></div>
            </div>
        </div>

        <!-- Rata-Rata Rating -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-500/5 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </div>
                <div>
                    <h3 class="text-slate-500 font-bold text-xs uppercase tracking-wider">Indeks Kepuasan</h3>
                    <p class="text-[10px] text-slate-400">Feedback Siswa</p>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900">{{ $kpi['avg_rating'] }}</span>
                <span class="text-sm font-bold text-slate-400">/ 5.0</span>
            </div>
        </div>

        <!-- Total Selesai -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-purple-500/5 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <h3 class="text-slate-500 font-bold text-xs uppercase tracking-wider">Kasus Tertutup</h3>
                    <p class="text-[10px] text-slate-400">Konsultasi Selesai</p>
                </div>
            </div>
            <div>
                <span class="text-3xl font-black text-slate-900">{{ number_format($kpi['total_konsultasi_selesai']) }}</span>
                <span class="text-sm font-semibold text-slate-400 ml-1">Selesai</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 2. Peta Isu 4 Pilar -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
            <h3 class="text-sm font-black text-slate-900 mb-1">Peta Isu 4 Pilar Pendampingan</h3>
            <p class="text-xs text-slate-500 mb-6">Distribusi kasus dari jurnal dan konsultasi siswa</p>
            
            <div class="space-y-4">
                @php
                    $totalPilar = array_sum($this->petaPilar);
                    $colors = [
                        'Akademik & Belajar' => 'bg-blue-500',
                        'Karakter & Kedisiplinan' => 'bg-purple-500',
                        'Minat & Bakat / Ekskul' => 'bg-amber-500',
                        'Sosial & Psikologis' => 'bg-emerald-500',
                    ];
                    $textColors = [
                        'Akademik & Belajar' => 'text-blue-700',
                        'Karakter & Kedisiplinan' => 'text-purple-700',
                        'Minat & Bakat / Ekskul' => 'text-amber-700',
                        'Sosial & Psikologis' => 'text-emerald-700',
                    ];
                @endphp
                
                @if($totalPilar > 0)
                    @foreach($this->petaPilar as $pilar => $count)
                        @php 
                            $pct = round(($count / $totalPilar) * 100); 
                            $barColor = $colors[$pilar] ?? 'bg-slate-500';
                            $textColor = $textColors[$pilar] ?? 'text-slate-700';
                        @endphp
                        <div>
                            <div class="flex justify-between items-end mb-1">
                                <span class="text-xs font-bold {{ $textColor }}">{{ $pilar }}</span>
                                <div class="text-right">
                                    <span class="text-xs font-black text-slate-800">{{ $count }}</span>
                                    <span class="text-[10px] text-slate-500">({{ $pct }}%)</span>
                                </div>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2.5">
                                <div class="{{ $barColor }} h-2.5 rounded-full transition-all duration-1000" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="py-8 text-center text-slate-400 text-xs font-medium">Belum ada data pilar terisi.</div>
                @endif
            </div>
        </div>

        <!-- 3. Sentimen Siswa -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
            <h3 class="text-sm font-black text-slate-900 mb-1">Tren Kondisi & Emosi Siswa</h3>
            <p class="text-xs text-slate-500 mb-6">Sebaran sentimen (emoji) dari feedback konsultasi siswa</p>
            
            <div class="grid grid-cols-2 gap-4">
                @php
                    $totalSentimen = array_sum($this->sentimenSiswa);
                @endphp

                @if($totalSentimen > 0)
                    @foreach($this->sentimenSiswa as $emoji => $count)
                        @php 
                            $pct = round(($count / $totalSentimen) * 100);
                            $emojiData = \App\Enums\KategoriSentimen::tryFrom($emoji);
                            $colorMap = [
                                'Lega' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'Biasa' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'Masih Bingung' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'Sedih' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                'Khawatir' => 'bg-rose-50 text-rose-700 border-rose-200',
                                'Marah' => 'bg-red-50 text-red-700 border-red-200',
                            ];
                            $cardColor = $colorMap[$emojiData?->label() ?? ''] ?? 'bg-slate-50 text-slate-700 border-slate-200';
                        @endphp
                        <div class="rounded-2xl p-4 border {{ $cardColor }} flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-3xl">{{ $emoji }}</span>
                                <div>
                                    <p class="text-xs font-bold">{{ $emojiData?->label() ?? 'Unknown' }}</p>
                                    <p class="text-[10px] opacity-70">{{ $pct }}% dari total</p>
                                </div>
                            </div>
                            <span class="text-2xl font-black">{{ $count }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="col-span-2 py-8 text-center text-slate-400 text-xs font-medium border-2 border-dashed border-slate-100 rounded-2xl">
                        Belum ada data feedback / sentimen.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 4. Top Guru Wali Aktif -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-sm font-black text-slate-900 mb-1">Leaderboard Guru Wali</h3>
                <p class="text-xs text-slate-500">Berdasarkan keaktifan mengisi jurnal pendampingan</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[10px] uppercase tracking-wider text-slate-400">
                        <th class="pb-3 font-bold px-2">Peringkat</th>
                        <th class="pb-3 font-bold px-2">Nama Guru</th>
                        <th class="pb-3 font-bold px-2 text-right">Total Jurnal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($topGuru as $index => $guru)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3 px-2">
                                <div class="flex items-center justify-center w-6 h-6 rounded-full {{ $index === 0 ? 'bg-amber-100 text-amber-600 font-black' : ($index === 1 ? 'bg-slate-200 text-slate-600 font-bold' : ($index === 2 ? 'bg-orange-100 text-orange-600 font-bold' : 'bg-slate-50 text-slate-500 font-medium')) }} text-xs">
                                    {{ $index + 1 }}
                                </div>
                            </td>
                            <td class="py-3 px-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-200 overflow-hidden shrink-0 border-2 border-white shadow-xs">
                                        @if($guru->photo_path)
                                            <img src="{{ asset('storage/' . $guru->photo_path) }}" alt="{{ $guru->name }}" class="w-full h-full object-cover">
                                        @else
                                            <img src="{{ asset('images/avatar-' . strtolower($guru->jenis_kelamin ?? 'm') . '.svg') }}" alt="Avatar" class="w-full h-full object-cover">
                                        @endif
                                    </div>
                                    <span class="text-xs font-bold text-slate-800">{{ $guru->name }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-2 text-right">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-black">
                                    {{ $guru->total_jurnal }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-6 text-center text-xs text-slate-400">Belum ada data guru aktif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

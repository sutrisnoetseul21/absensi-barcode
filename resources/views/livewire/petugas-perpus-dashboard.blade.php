<div class="p-6 lg:p-8 space-y-8 max-w-7xl mx-auto">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-teal-700 via-teal-800 to-indigo-900 rounded-3xl p-6 lg:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="bg-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider">Modul Perpustakaan ERP</span>
                <h1 class="text-3xl lg:text-4xl font-extrabold mt-3 tracking-tight">Dashboard Petugas Perpustakaan</h1>
                <p class="text-teal-100 text-sm mt-1">Selamat datang di sistem manajemen perpustakaan sekolah {{ $settings->school_name ?? 'Digital' }}.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('perpustakaan.kunjungan') }}" target="_blank" class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    Kiosk Kunjungan
                </a>
                <a href="{{ route('perpustakaan.sirkulasi') }}" target="_blank" class="px-4 py-2.5 bg-white/20 hover:bg-white/30 text-white rounded-2xl text-xs font-bold transition-all border border-white/30 backdrop-blur-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                    Kiosk Sirkulasi
                </a>
            </div>
        </div>
    </div>

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1: Total Judul -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Katalog Judul</p>
                    <h3 class="text-3xl font-extrabold text-slate-800 mt-1">{{ number_format($totalJudulBuku) }}</h3>
                    <p class="text-xs text-slate-500 mt-1">Judul Buku Terdaftar</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Eksemplar -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Fisik Buku</p>
                    <h3 class="text-3xl font-extrabold text-slate-800 mt-1">{{ number_format($totalEksemplar) }}</h3>
                    <p class="text-xs text-slate-500 mt-1"><span class="text-emerald-600 font-bold">{{ $eksemplarTersedia }} Tersedia</span> &bull; <span class="text-amber-600 font-bold">{{ $eksemplarDipinjam }} Dipinjam</span></p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
            </div>
        </div>

        <!-- Card 3: Peminjaman Aktif -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Peminjaman Aktif</p>
                    <h3 class="text-3xl font-extrabold text-slate-800 mt-1">{{ number_format($peminjamanAktifCount) }}</h3>
                    <p class="text-xs mt-1 {{ $peminjamanTerlambatCount > 0 ? 'text-rose-600 font-bold' : 'text-slate-500' }}">
                        {{ $peminjamanTerlambatCount }} Terlambat Jatuh Tempo
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                </div>
            </div>
        </div>

        <!-- Card 4: Kunjungan Hari Ini -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kunjungan Hari Ini</p>
                    <h3 class="text-3xl font-extrabold text-slate-800 mt-1">{{ number_format($kunjunganHariIniCount) }}</h3>
                    <p class="text-xs text-slate-500 mt-1">Siswa & Guru Berkunjung</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Tables Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Overdue Loans Table -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Pinjaman Terlambat / Jatuh Tempo</h3>
                    <p class="text-xs text-slate-500">Perlu tindak lanjut atau penagihan ke siswa/guru</p>
                </div>
                <a href="{{ route('portal-perpustakaan.sirkulasi') }}" class="text-xs font-bold text-teal-600 hover:text-teal-700">Lihat Semua &rarr;</a>
            </div>

            <div class="flex-1 overflow-x-auto">
                @if($overdueLoans->isEmpty())
                    <div class="py-12 text-center text-slate-400">
                        <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-xs font-medium">Tidak ada pinjaman terlambat saat ini.</p>
                    </div>
                @else
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-100">
                                <th class="pb-3 font-semibold">Peminjam</th>
                                <th class="pb-3 font-semibold">Buku</th>
                                <th class="pb-3 font-semibold text-right">Jatuh Tempo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($overdueLoans as $loan)
                                <tr>
                                    <td class="py-3 font-bold text-slate-800">
                                        {{ $loan->peminjam?->name ?? 'Anggota' }}
                                        <span class="block text-[10px] text-slate-400 font-normal capitalize">{{ $loan->peminjam_type }}</span>
                                    </td>
                                    <td class="py-3 text-slate-600 max-w-xs truncate">
                                        {{ $loan->eksemplarBuku?->buku?->judul ?? 'Buku' }}
                                        <span class="block font-mono text-[10px] text-slate-400">{{ $loan->eksemplarBuku?->kode_eksemplar }}</span>
                                    </td>
                                    <td class="py-3 text-right">
                                        <span class="px-2 py-1 bg-rose-100 text-rose-700 rounded-md font-bold text-[10px]">
                                            {{ \Carbon\Carbon::parse($loan->tanggal_jatuh_tempo)->format('d M Y') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <!-- Recent Visits List -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Pengunjung Terbaru Hari Ini</h3>
                    <p class="text-xs text-slate-500">Hasil pemindaian Kiosk Presensi Kunjungan</p>
                </div>
                <a href="{{ route('portal-perpustakaan.kunjungan') }}" class="text-xs font-bold text-teal-600 hover:text-teal-700">Lihat Log &rarr;</a>
            </div>

            <div class="flex-1 overflow-y-auto space-y-3">
                @if($recentVisits->isEmpty())
                    <div class="py-12 text-center text-slate-400">
                        <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <p class="text-xs font-medium">Belum ada kunjungan tercatat hari ini.</p>
                    </div>
                @else
                    @foreach($recentVisits as $visit)
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr($visit->pengunjung?->name ?? 'P', 0, 2)) }}
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-800">{{ $visit->pengunjung?->name ?? 'Pengunjung' }}</h4>
                                    <p class="text-[11px] text-slate-500 capitalize">{{ $visit->pengunjung_type }} &bull; {{ $visit->tujuan_kunjungan }}</p>
                                </div>
                            </div>
                            <span class="text-xs font-mono text-slate-400 bg-white px-2 py-1 rounded-md border border-slate-200">
                                {{ \Carbon\Carbon::parse($visit->waktu_masuk)->format('H:i:s') }}
                            </span>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

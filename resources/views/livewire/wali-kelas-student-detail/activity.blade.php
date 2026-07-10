<!-- SEKSI 5: Aktivitas Terbaru (1 Kolom) -->
<div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl border border-white/40 p-6 flex flex-col relative z-20">
    <h2 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-3">
        <div class="w-10 h-10 bg-rose-50 rounded-xl flex items-center justify-center text-rose-500 shadow-inner">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        Riwayat Bulan Ini
    </h2>
    
    <div class="flex-1 space-y-6 max-h-[600px] overflow-y-auto pr-2 custom-scrollbar">
        @forelse($recentActivity as $activity)
            @php
                $status = strtolower($activity->status);
                $iconColor = 'text-slate-500 bg-slate-100 shadow-slate-200/50';
                $iconPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                $title = 'Kehadiran';
                
                if ($status === 'hadir') {
                    $iconColor = 'text-emerald-500 bg-emerald-100 shadow-emerald-200/50';
                    $iconPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';
                    $title = 'Hadir Tepat Waktu';
                } elseif ($status === 'telat') {
                    $iconColor = 'text-amber-500 bg-amber-100 shadow-amber-200/50';
                    $iconPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                    $title = "Telat ({$activity->late_minutes} menit)";
                } elseif ($status === 'izin') {
                    $iconColor = 'text-blue-500 bg-blue-100 shadow-blue-200/50';
                    $iconPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                    $title = 'Izin Tidak Masuk';
                } elseif ($status === 'sakit') {
                    $iconColor = 'text-purple-500 bg-purple-100 shadow-purple-200/50';
                    $iconPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>';
                    $title = 'Sakit';
                } elseif ($status === 'alpa') {
                    $iconColor = 'text-rose-500 bg-rose-100 shadow-rose-200/50';
                    $iconPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';
                    $title = 'Alpa (Tanpa Keterangan)';
                }
            @endphp
            <div class="flex items-start gap-4 p-3 rounded-2xl hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100">
                <div class="flex-shrink-0 w-12 h-12 rounded-2xl {{ $iconColor }} shadow-lg flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $iconPath !!}</svg>
                </div>
                <div class="flex-1 pt-1">
                    <h4 class="text-sm font-black text-slate-800">{{ $title }}</h4>
                    <div class="flex flex-wrap items-center text-xs font-semibold text-slate-500 mt-1.5 gap-2">
                        <span class="flex items-center"><svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>{{ \Carbon\Carbon::parse($activity->date)->translatedFormat('l, d M Y') }}</span>
                        @if($activity->is_manual_input)
                            <span class="text-orange-600 font-bold border border-orange-200 bg-orange-50 px-2 py-0.5 rounded-md shadow-sm">Input Manual</span>
                        @elseif($activity->scan_time)
                            <span class="text-indigo-600 font-bold border border-indigo-200 bg-indigo-50 px-2 py-0.5 rounded-md shadow-sm flex items-center"><svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>{{ substr($activity->scan_time, 0, 5) }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center h-full text-slate-400 py-10 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-sm mb-4">
                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <p class="text-sm font-bold text-slate-500">Belum ada riwayat di bulan ini</p>
            </div>
        @endforelse
    </div>
</div>

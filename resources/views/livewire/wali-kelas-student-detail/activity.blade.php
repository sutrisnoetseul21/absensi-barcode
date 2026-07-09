<!-- SEKSI 5: Aktivitas Terbaru (1 Kolom) -->
<div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 flex flex-col">
    <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center">
        <svg class="w-6 h-6 mr-2 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        Riwayat Bulan Ini
    </h2>
    
    <div class="flex-1 space-y-6 max-h-[600px] overflow-y-auto pr-2">
        @forelse($recentActivity as $activity)
            @php
                $status = strtolower($activity->status);
                $iconColor = 'text-slate-400 bg-slate-100';
                $iconPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                $title = 'Kehadiran';
                
                if ($status === 'hadir') {
                    $iconColor = 'text-emerald-500 bg-emerald-100';
                    $iconPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';
                    $title = 'Hadir Tepat Waktu';
                } elseif ($status === 'telat') {
                    $iconColor = 'text-amber-500 bg-amber-100';
                    $iconPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                    $title = "Telat ({$activity->late_minutes} menit)";
                } elseif ($status === 'izin') {
                    $iconColor = 'text-blue-500 bg-blue-100';
                    $iconPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                    $title = 'Izin Tidak Masuk';
                } elseif ($status === 'sakit') {
                    $iconColor = 'text-purple-500 bg-purple-100';
                    $iconPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>';
                    $title = 'Sakit';
                } elseif ($status === 'alpa') {
                    $iconColor = 'text-red-500 bg-red-100';
                    $iconPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';
                    $title = 'Alpa (Tanpa Keterangan)';
                }
            @endphp
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-full {{ $iconColor }} flex items-center justify-center mt-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $iconPath !!}</svg>
                </div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-slate-800">{{ $title }}</h4>
                    <div class="flex flex-wrap items-center text-xs font-medium text-slate-500 mt-0.5 gap-2">
                        <span>{{ \Carbon\Carbon::parse($activity->date)->translatedFormat('l, d M Y') }}</span>
                        @if($activity->is_manual_input)
                            <span class="text-orange-600 font-semibold border border-orange-100 bg-orange-50 px-1.5 py-0.5 rounded">Input Manual</span>
                        @elseif($activity->scan_time)
                            <span class="text-indigo-600 font-semibold border border-indigo-100 bg-indigo-50 px-1.5 py-0.5 rounded">{{ substr($activity->scan_time, 0, 5) }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center h-full text-slate-400 py-10">
                <svg class="w-12 h-12 mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-sm font-medium">Belum ada riwayat di bulan ini</p>
            </div>
        @endforelse
    </div>
</div>

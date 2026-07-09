<!-- SEKSI 4: Kalender Grid (2 Kolom) -->
<div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-200 p-6 overflow-hidden">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-slate-800 flex items-center">
            <svg class="w-6 h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            Kalender <span x-text="monthName" class="ml-1"></span>
        </h2>
        
        <!-- Keterangan Kalender Desktop -->
        <div class="hidden sm:flex items-center gap-3 text-xs font-medium text-slate-500">
            <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-emerald-500 mr-1.5"></span> Hadir</div>
            <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-amber-500 mr-1.5"></span> Telat</div>
            <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-1.5"></span> Izin</div>
            <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-purple-500 mr-1.5"></span> Sakit</div>
            <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-red-500 mr-1.5"></span> Alpa</div>
        </div>
    </div>

    <!-- Grid Kalender -->
    <div class="grid grid-cols-7 gap-2 sm:gap-4">
        <!-- Hari Header -->
        @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dayName)
            <div class="text-center text-xs font-bold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100">{{ $dayName }}</div>
        @endforeach

        <!-- Offset Empty Cells -->
        @for($i = 0; $i < $startOfMonthOffset; $i++)
            <div class="aspect-square bg-slate-50/50 rounded-xl"></div>
        @endfor

        <!-- Date Cells -->
        @for($d = 1; $d <= $daysInMonth; $d++)
            @php
                $parts = explode('-', $selectedMonthYear);
                $month = $parts[0];
                $year = $parts[1];
                $dateString = date('Y-m-d', strtotime($year . '-' . $month . '-' . $d));
                $isToday = ($dateString === $todayDate);
                
                $data = $attendanceData[$d] ?? null;
                
                $bgColor = 'bg-slate-50 hover:bg-slate-100 border border-slate-100';
                $textColor = 'text-slate-600';
                $statusIcon = null;
                $tooltip = '';
                
                if ($data) {
                    $scanTimeStr = isset($data['scan_time']) && $data['scan_time'] ? ' pada ' . $data['scan_time'] : '';
                    
                    if ($data['status'] === 'hadir') { 
                        $bgColor = 'bg-emerald-100 border border-emerald-200 hover:bg-emerald-200'; 
                        $textColor = 'text-emerald-700 font-bold';
                        $statusIcon = 'H';
                        $tooltip = 'Hadir Tepat Waktu' . $scanTimeStr;
                    } elseif ($data['status'] === 'telat') { 
                        $bgColor = 'bg-amber-100 border border-amber-200 hover:bg-amber-200'; 
                        $textColor = 'text-amber-700 font-bold';
                        $statusIcon = 'T';
                        $tooltip = 'Terlambat ' . $data['late_minutes'] . ' Menit' . $scanTimeStr;
                    } elseif ($data['status'] === 'izin') { 
                        $bgColor = 'bg-blue-100 border border-blue-200 hover:bg-blue-200'; 
                        $textColor = 'text-blue-700 font-bold';
                        $statusIcon = 'I';
                        $tooltip = 'Izin';
                    } elseif ($data['status'] === 'sakit') { 
                        $bgColor = 'bg-purple-100 border border-purple-200 hover:bg-purple-200'; 
                        $textColor = 'text-purple-700 font-bold';
                        $statusIcon = 'S';
                        $tooltip = 'Sakit';
                    } elseif ($data['status'] === 'alpa') { 
                        $bgColor = 'bg-red-100 border border-red-200 hover:bg-red-200'; 
                        $textColor = 'text-red-700 font-bold';
                        $statusIcon = 'A';
                        $tooltip = 'Alpa (Tanpa Keterangan)';
                    }
                } elseif (isset($holidays[$d]) && $holidays[$d]) {
                    $bgColor = 'bg-slate-200 border border-slate-300';
                    $textColor = 'text-slate-500 font-bold';
                    $statusIcon = 'L';
                    $tooltip = 'Libur';
                } elseif (strtotime($dateString) > strtotime($todayDate)) {
                    $bgColor = 'bg-transparent border border-dashed border-slate-200';
                    $textColor = 'text-slate-300';
                }
            @endphp
            
            <div class="aspect-square rounded-xl {{ $bgColor }} flex flex-col items-center justify-center relative group transition-all cursor-default {{ $isToday ? 'ring-2 ring-indigo-500 ring-offset-2' : '' }}" title="{{ $tooltip }}">
                <span class="text-sm sm:text-base {{ $textColor }}">{{ $d }}</span>
                
                @if($statusIcon)
                    <div class="mt-1 flex flex-col items-center">
                        <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-full flex items-center justify-center text-[10px] sm:text-xs font-black shadow-sm
                            {{ $statusIcon === 'H' ? 'bg-emerald-500 text-white' : '' }}
                            {{ $statusIcon === 'T' ? 'bg-amber-500 text-white' : '' }}
                            {{ $statusIcon === 'I' ? 'bg-blue-500 text-white' : '' }}
                            {{ $statusIcon === 'S' ? 'bg-purple-500 text-white' : '' }}
                            {{ $statusIcon === 'A' ? 'bg-red-500 text-white' : '' }}
                            {{ $statusIcon === 'L' ? 'bg-slate-400 text-slate-700' : '' }}
                        ">
                            {{ $statusIcon }}
                        </div>
                        @if(isset($data['is_manual_input']) && $data['is_manual_input'])
                            <span class="text-[9px] sm:text-[10px] mt-0.5 font-bold tracking-tight opacity-80 {{ $textColor }}">
                                Manual
                            </span>
                        @elseif(isset($data['scan_time']) && $data['scan_time'])
                            <span class="text-[9px] sm:text-[10px] mt-0.5 font-bold tracking-tight opacity-80 {{ $textColor }}">
                                {{ substr($data['scan_time'], 0, 5) }}
                            </span>
                        @endif
                    </div>
                @endif
                
                @if($isToday)
                    <div class="absolute -top-1 -right-1 w-3 h-3 bg-indigo-500 rounded-full animate-ping opacity-75"></div>
                    <div class="absolute -top-1 -right-1 w-3 h-3 bg-indigo-500 rounded-full border-2 border-white"></div>
                @endif
            </div>
        @endfor
    </div>

    <!-- Keterangan Kalender Mobile -->
    <div class="mt-6 flex sm:hidden flex-wrap gap-3 text-xs font-medium text-slate-500 bg-slate-50 p-4 rounded-xl border border-slate-100">
        <div class="flex items-center w-[45%]"><span class="w-3 h-3 rounded-full bg-emerald-500 mr-2"></span> Hadir</div>
        <div class="flex items-center w-[45%]"><span class="w-3 h-3 rounded-full bg-amber-500 mr-2"></span> Telat</div>
        <div class="flex items-center w-[45%]"><span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span> Izin</div>
        <div class="flex items-center w-[45%]"><span class="w-3 h-3 rounded-full bg-purple-500 mr-2"></span> Sakit</div>
        <div class="flex items-center w-[45%]"><span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span> Alpa</div>
    </div>
</div>

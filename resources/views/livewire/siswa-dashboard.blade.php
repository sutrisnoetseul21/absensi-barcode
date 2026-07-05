<div class="min-h-screen bg-slate-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header / Profil -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        @if($student->photo_path)
                            <img src="{{ asset('storage/'.$student->photo_path) }}" alt="Foto Siswa" class="w-20 h-20 rounded-full border-4 border-white/30 shadow-lg object-cover">
                        @else
                            <div class="w-20 h-20 rounded-full border-4 border-white/30 shadow-lg bg-emerald-100 flex items-center justify-center text-emerald-700 text-3xl font-bold">
                                {{ substr($student->name, 0, 1) }}
                            </div>
                        @endif
                        <div class="text-white">
                            <h1 class="text-2xl font-bold">{{ $student->name }}</h1>
                            <p class="text-emerald-100 mt-1 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                                NISN: {{ $student->nisn }}
                            </p>
                            @if($enrollment)
                            <div class="inline-flex mt-2 px-3 py-1 bg-white/20 rounded-full text-xs font-semibold backdrop-blur-sm">
                                Kelas: {{ $enrollment->kelas->name ?? '-' }} | {{ $enrollment->tahunAjaran->name ?? '-' }}
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <form action="{{ route('siswa.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-red-500/80 hover:bg-red-500 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center backdrop-blur-sm border border-red-400/50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @if(!$enrollment)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-r-xl">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-yellow-800">Tidak ada Enrollment Aktif</h3>
                        <p class="mt-2 text-yellow-700">Akun Anda saat ini tidak didaftarkan di kelas manapun untuk tahun ajaran yang sedang berlangsung.</p>
                    </div>
                </div>
            </div>
        @else

        <!-- Controls -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-slate-800 flex items-center">
                <svg class="w-6 h-6 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Riwayat Kehadiran Bulanan
            </h2>
            
            <div class="w-48 relative">
                <select wire:model.live="selectedMonth" class="block w-full pl-3 pr-10 py-2.5 text-base border-slate-300 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-xl shadow-sm bg-white cursor-pointer appearance-none">
                    @foreach(['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $num => $name)
                        <option value="{{ $num }}">{{ $name }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>
        </div>

        <!-- Ringkasan Statistik -->
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 p-5 text-center transition-transform hover:-translate-y-1 duration-200">
                <p class="text-sm font-medium text-emerald-600 mb-1">Hadir (H)</p>
                <p class="text-3xl font-bold text-slate-800">{{ $monthlyStats['H'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-yellow-100 p-5 text-center transition-transform hover:-translate-y-1 duration-200">
                <p class="text-sm font-medium text-yellow-600 mb-1">Telat (T)</p>
                <p class="text-3xl font-bold text-slate-800">{{ $monthlyStats['T'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5 text-center transition-transform hover:-translate-y-1 duration-200">
                <p class="text-sm font-medium text-blue-600 mb-1">Izin (I)</p>
                <p class="text-3xl font-bold text-slate-800">{{ $monthlyStats['I'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-5 text-center transition-transform hover:-translate-y-1 duration-200">
                <p class="text-sm font-medium text-purple-600 mb-1">Sakit (S)</p>
                <p class="text-3xl font-bold text-slate-800">{{ $monthlyStats['S'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-red-100 p-5 text-center transition-transform hover:-translate-y-1 duration-200">
                <p class="text-sm font-medium text-red-600 mb-1">Alpa (A)</p>
                <p class="text-3xl font-bold text-slate-800">{{ $monthlyStats['A'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-orange-100 p-5 text-center transition-transform hover:-translate-y-1 duration-200">
                <p class="text-sm font-medium text-orange-600 mb-1">Total Telat</p>
                <p class="text-3xl font-bold text-slate-800">{{ $monthlyStats['late_minutes'] ?? 0 }}<span class="text-sm font-normal text-slate-500 ml-1">mnt</span></p>
            </div>
        </div>

        <!-- Tabel Kalender -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden relative" wire:loading.class="opacity-60 cursor-wait">
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider sticky left-0 bg-slate-50 z-10 w-48 shadow-[1px_0_0_0_#e2e8f0]">
                                Tanggal
                            </th>
                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $dateString = date('Y-m-d', strtotime(date('Y') . '-' . $selectedMonth . '-' . $d));
                                    $isToday = ($dateString === $todayDate);
                                @endphp
                                <th scope="col" class="px-3 py-4 text-center text-xs font-semibold {{ $isToday ? 'text-emerald-600 bg-emerald-50' : 'text-slate-500' }} uppercase tracking-wider min-w-[50px]">
                                    {{ $d }}
                                </th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 sticky left-0 bg-white group-hover:bg-slate-50 z-10 shadow-[1px_0_0_0_#e2e8f0]">
                                Status Kehadiran
                            </td>
                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $data = $attendanceData[$d] ?? null;
                                    $statusCode = '-';
                                    $bgColor = 'bg-slate-50';
                                    $textColor = 'text-slate-400';
                                    
                                    if ($data) {
                                        if ($data['status'] === 'hadir') { $statusCode = 'H'; $bgColor = 'bg-green-100'; $textColor = 'text-green-700 font-bold'; }
                                        elseif ($data['status'] === 'telat') { $statusCode = 'T'; $bgColor = 'bg-yellow-100'; $textColor = 'text-yellow-700 font-bold'; }
                                        elseif ($data['status'] === 'izin') { $statusCode = 'I'; $bgColor = 'bg-blue-100'; $textColor = 'text-blue-700 font-bold'; }
                                        elseif ($data['status'] === 'sakit') { $statusCode = 'S'; $bgColor = 'bg-purple-100'; $textColor = 'text-purple-700 font-bold'; }
                                        elseif ($data['status'] === 'alpa') { $statusCode = 'A'; $bgColor = 'bg-red-100'; $textColor = 'text-red-700 font-bold'; }
                                    }
                                @endphp
                                <td class="px-2 py-3 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center justify-center w-8 h-8 rounded-lg {{ $bgColor }} {{ $textColor }} text-sm transition-transform hover:scale-110 cursor-default"
                                         @if($data && $data['status'] === 'telat' && $data['late_minutes'] > 0) title="Telat {{ $data['late_minutes'] }} menit" @endif>
                                        {{ $statusCode }}
                                    </div>
                                </td>
                            @endfor
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div wire:loading class="absolute inset-0 z-20 flex items-center justify-center bg-white/50 backdrop-blur-sm">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-600"></div>
            </div>
        </div>
        
        <div class="mt-6 flex flex-wrap gap-4 text-sm text-slate-500 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="font-medium mr-2">Keterangan:</span>
            <div class="flex items-center"><span class="w-4 h-4 bg-green-100 text-green-700 flex items-center justify-center rounded-sm mr-2 text-[10px] font-bold">H</span> Hadir</div>
            <div class="flex items-center"><span class="w-4 h-4 bg-yellow-100 text-yellow-700 flex items-center justify-center rounded-sm mr-2 text-[10px] font-bold">T</span> Telat (Sorot lambang untuk melihat menit telat)</div>
            <div class="flex items-center"><span class="w-4 h-4 bg-blue-100 text-blue-700 flex items-center justify-center rounded-sm mr-2 text-[10px] font-bold">I</span> Izin</div>
            <div class="flex items-center"><span class="w-4 h-4 bg-purple-100 text-purple-700 flex items-center justify-center rounded-sm mr-2 text-[10px] font-bold">S</span> Sakit</div>
            <div class="flex items-center"><span class="w-4 h-4 bg-red-100 text-red-700 flex items-center justify-center rounded-sm mr-2 text-[10px] font-bold">A</span> Alpa</div>
            <div class="flex items-center"><span class="w-4 h-4 bg-slate-100 text-slate-400 flex items-center justify-center rounded-sm mr-2 text-[10px] font-bold">-</span> Belum ada data / Kosong</div>
        </div>

        @endif
    </div>
</div>

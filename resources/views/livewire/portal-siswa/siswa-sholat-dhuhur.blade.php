<div class="min-h-full bg-slate-50 flex flex-col font-jakarta"
    x-data="{
        selectedMonthYear: @entangle('selectedMonthYear').live,
        showCardModal: false,
        
        get monthName() {
            if (!this.selectedMonthYear) return '';
            const parts = this.selectedMonthYear.split('-');
            const monthStr = parts[0];
            const yearStr = parts[1];
            
            const months = {
                '01':'Januari','02':'Februari','03':'Maret','04':'April','05':'Mei','06':'Juni',
                '07':'Juli','08':'Agustus','09':'September','10':'Oktober','11':'November','12':'Desember'
            };
            return (months[monthStr] || '') + ' ' + yearStr;
        }
    }">

    <!-- SEKSI 1: Banner Pengumuman -->
    @if(isset($pengumuman) && $pengumuman->count() > 0)
    <div class="bg-amber-50 border-b border-amber-200 shadow-sm relative overflow-hidden z-20">
        <div class="max-w-7xl mx-auto flex items-center">
            <div class="bg-amber-500 text-white px-4 py-3 font-bold flex items-center z-10 shadow-[4px_0_10px_rgba(0,0,0,0.1)] whitespace-nowrap">
                <svg class="w-5 h-5 mr-2 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                INFORMASI
            </div>
            <div class="overflow-hidden flex-1 py-3 px-4">
                <div class="whitespace-nowrap inline-block animate-[shimmer_25s_linear_infinite]">
                    @foreach($pengumuman as $p)
                        <span class="text-amber-800 font-medium mx-4">
                            @if($p->tipe === 'peringatan') 🔴
                            @elseif($p->tipe === 'penting') 🟡
                            @else 🔵
                            @endif
                            {{ $p->judul }} &mdash; {{ $p->isi }}
                        </span>
                        @if(!$loop->last) <span class="text-amber-300 mx-2">|</span> @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        
        <!-- SEKSI 2: Header Profil Siswa -->
        <div class="relative bg-brand-primary rounded-3xl overflow-hidden shadow-xl border border-brand-primary mb-8">
            <!-- Background Blobs -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-teal-500 mix-blend-multiply filter blur-3xl opacity-50 animate-blob"></div>
                <div class="absolute top-12 -right-24 w-96 h-96 rounded-full bg-emerald-500 mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-2000"></div>
                <div class="absolute -bottom-24 left-1/3 w-96 h-96 rounded-full bg-cyan-600 mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-4000"></div>
            </div>

            <div class="relative z-10 p-8 flex flex-col md:flex-row items-center justify-between gap-6">
                <!-- Info Siswa -->
                <div class="flex items-center gap-6">
                    <div class="relative">
                        <!-- Persentase Kehadiran Circular Ring -->
                        <svg class="w-32 h-32 transform -rotate-90">
                            <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="8" fill="transparent" class="text-emerald-900/40" />
                            <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="8" fill="transparent" 
                                    :stroke-dasharray="2 * Math.PI * 56" 
                                    :stroke-dashoffset="(2 * Math.PI * 56) * (1 - ({{ $attendancePercentage }} / 100))"
                                    class="text-green-400 transition-all duration-1000 ease-out" stroke-linecap="round" />
                        </svg>
                        
                        <div class="absolute inset-0 p-3">
                            @if($student->photo_path)
                                <img src="{{ asset('storage/'.$student->photo_path) }}" alt="Foto Siswa" class="w-full h-full rounded-full border-4 border-white shadow-lg object-cover">
                            @else
                                <div class="w-full h-full rounded-full border-4 border-white shadow-lg bg-emerald-100 flex items-center justify-center text-emerald-700 text-4xl font-bold">
                                    {{ substr($student->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        
                        <!-- Badge Kehadiran -->
                        <div class="absolute -bottom-2 -right-2 bg-white text-emerald-700 text-xs font-bold px-3 py-1.5 rounded-full shadow-lg border border-emerald-100 flex items-center">
                            {{ $attendancePercentage }}%
                        </div>
                    </div>
                    
                    <div class="text-white">
                        <h1 class="text-3xl font-extrabold tracking-tight drop-shadow-md mb-1">{{ $student->name }}</h1>
                        <p class="text-emerald-100 font-medium flex items-center text-lg drop-shadow">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                            NISN: {{ $student->nisn }}
                        </p>
                        @if($enrollment)
                        <div class="flex items-center gap-2 mt-3 flex-wrap">
                            <div class="inline-flex px-4 py-1.5 bg-white/20 rounded-full text-sm font-semibold backdrop-blur-md border border-white/30 shadow-sm">
                                Kelas: {{ $enrollment->kelas->name ?? '-' }} | {{ $enrollment->tahunAjaran->name ?? '-' }}
                            </div>
                            <div class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-emerald-500/30 text-emerald-100 rounded-full text-sm font-bold backdrop-blur-md border border-emerald-300/30 shadow-sm">
                                <svg class="w-4 h-4 text-emerald-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                Presensi Sholat Dhuhur
                            </div>
                            <button type="button" @click="showCardModal = true" class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-full text-sm font-bold shadow-md border border-amber-400/50 transition-all cursor-pointer">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                Cetak Kartu Siswa
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Tombol Pilih Bulan -->
                <div class="flex flex-col items-end gap-4 w-full md:w-auto">
                    <div class="relative w-full md:w-56">
                        <select wire:model.live="selectedMonthYear" class="block w-full pl-4 pr-10 py-2.5 text-slate-800 border-white focus:outline-none focus:ring-2 focus:ring-white sm:text-sm rounded-xl shadow-lg bg-white/90 backdrop-blur-md cursor-pointer appearance-none font-bold">
                            @foreach($availableMonths as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(!$enrollment)
            <!-- State Tidak Ada Enrollment -->
            <div class="bg-yellow-50 border border-yellow-200 p-6 rounded-2xl flex items-start shadow-sm">
                <div class="flex-shrink-0 bg-yellow-100 p-3 rounded-full">
                    <svg class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-xl font-bold text-yellow-800">Akun Belum Aktif di Kelas Manapun</h3>
                    <p class="mt-2 text-yellow-700 text-sm">Sistem mendeteksi bahwa akun Anda saat ini tidak didaftarkan (di-*enroll*) pada kelas manapun untuk tahun ajaran yang sedang berlangsung. Silakan hubungi wali kelas atau admin sekolah.</p>
                </div>
            </div>
        @else

        <!-- SEKSI 3: Ringkasan Statistik -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-2xl shadow-sm border border-emerald-200 p-5 flex flex-col items-center justify-center transition-transform hover:-translate-y-1 duration-300 hover:shadow-md group">
                <div class="w-10 h-10 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-500 mb-2 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <p class="text-xs font-semibold text-slate-500 uppercase text-center leading-tight">Hadir Berjamaah</p>
                <p class="text-3xl font-extrabold text-slate-800 mt-1">{{ $monthlyStats['hadir'] ?? 0 }}</p>
            </div>
            
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl shadow-sm border border-blue-200 p-5 flex flex-col items-center justify-center transition-transform hover:-translate-y-1 duration-300 hover:shadow-md group">
                <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center text-blue-500 mb-2 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <p class="text-xs font-semibold text-slate-500 uppercase text-center leading-tight">Ijin / Halangan</p>
                <p class="text-3xl font-extrabold text-slate-800 mt-1">{{ $monthlyStats['ijin'] ?? 0 }}</p>
            </div>

            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-2xl shadow-sm border border-red-200 p-5 flex flex-col items-center justify-center transition-transform hover:-translate-y-1 duration-300 hover:shadow-md group">
                <div class="w-10 h-10 bg-red-50 rounded-full flex items-center justify-center text-red-500 mb-2 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </div>
                <p class="text-xs font-semibold text-slate-500 uppercase text-center leading-tight">Tidak Hadir</p>
                <p class="text-3xl font-extrabold text-slate-800 mt-1">{{ $monthlyStats['tidak_hadir'] ?? 0 }}</p>
            </div>

            <div class="bg-gradient-to-br from-slate-100 to-slate-200 rounded-2xl shadow-sm border border-slate-300 p-5 flex flex-col items-center justify-center transition-transform hover:-translate-y-1 duration-300 hover:shadow-md group">
                <div class="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-slate-600 mb-2 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <p class="text-xs font-semibold text-slate-500 uppercase text-center leading-tight">Hari Libur</p>
                <p class="text-3xl font-extrabold text-slate-800 mt-1">{{ $monthlyStats['libur'] ?? 0 }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8" wire:loading.class="opacity-50">
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
                        <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-1.5"></span> Izin / Halangan</div>
                        <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-red-500 mr-1.5"></span> Tidak Hadir</div>
                        <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-slate-400 mr-1.5"></span> Libur</div>
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
                            $statusLabel = null;
                            $tooltip = '';
                            
                            if ($data) {
                                $st = $data['status'] ?? null;
                                if ($st === 'hadir') { 
                                    $bgColor = 'bg-emerald-100 border border-emerald-200 hover:bg-emerald-200'; 
                                    $textColor = 'text-emerald-700 font-bold';
                                    $statusIcon = 'H';
                                    $statusLabel = 'Hadir';
                                    $tooltip = 'Hadir Berjamaah' . (!empty($data['keterangan']) ? ' (' . $data['keterangan'] . ')' : '');
                                } elseif ($st === 'ijin') { 
                                    $bgColor = 'bg-blue-100 border border-blue-200 hover:bg-blue-200'; 
                                    $textColor = 'text-blue-700 font-bold';
                                    $statusIcon = 'I';
                                    $statusLabel = 'Ijin';
                                    $tooltip = 'Ijin / Berhalangan: ' . ($data['keterangan'] ?: 'Ijin');
                                } elseif ($st === 'tidak_hadir') { 
                                    $bgColor = 'bg-red-100 border border-red-200 hover:bg-red-200'; 
                                    $textColor = 'text-red-700 font-bold';
                                    $statusIcon = 'TH';
                                    $statusLabel = 'Absen';
                                    $tooltip = 'Tidak Hadir' . (!empty($data['keterangan']) ? ' (' . $data['keterangan'] . ')' : '');
                                } elseif ($st === 'libur') {
                                    $bgColor = 'bg-slate-200 border border-slate-300';
                                    $textColor = 'text-slate-500 font-bold';
                                    $statusIcon = 'L';
                                    $statusLabel = 'Libur';
                                    $tooltip = 'Libur: ' . ($data['keterangan'] ?: 'Hari Libur');
                                }
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
                                        {{ $statusIcon === 'I' ? 'bg-blue-500 text-white' : '' }}
                                        {{ $statusIcon === 'TH' ? 'bg-red-500 text-white' : '' }}
                                        {{ $statusIcon === 'L' ? 'bg-slate-400 text-slate-700' : '' }}
                                    ">
                                        {{ $statusIcon }}
                                    </div>
                                    @if($statusLabel)
                                        <span class="text-[9px] sm:text-[10px] mt-0.5 font-bold tracking-tight opacity-80 {{ $textColor }}">
                                            {{ $statusLabel }}
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
                    <div class="flex items-center w-[45%]"><span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span> Izin / Halangan</div>
                    <div class="flex items-center w-[45%]"><span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span> Tidak Hadir</div>
                    <div class="flex items-center w-[45%]"><span class="w-3 h-3 rounded-full bg-slate-400 mr-2"></span> Libur</div>
                </div>
            </div>

            <!-- SEKSI 5: Aktivitas Terbaru (1 Kolom) -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 flex flex-col">
                <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Aktivitas Terbaru
                </h2>
                
                <div class="flex-1 space-y-6">
                    @forelse($recentActivity as $activity)
                        @php
                            $status = strtolower($activity->status);
                            $iconColor = 'text-slate-400 bg-slate-100';
                            $iconPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                            $title = 'Presensi Sholat';
                            
                            if ($status === 'hadir') {
                                $iconColor = 'text-emerald-500 bg-emerald-100';
                                $iconPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';
                                $title = 'Hadir Sholat Dhuhur';
                            } elseif ($status === 'ijin') {
                                $iconColor = 'text-blue-500 bg-blue-100';
                                $iconPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                                $title = 'Ijin / Berhalangan';
                            } elseif ($status === 'tidak_hadir') {
                                $iconColor = 'text-red-500 bg-red-100';
                                $iconPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';
                                $title = 'Tidak Hadir';
                            }
                        @endphp
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full {{ $iconColor }} flex items-center justify-center mt-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $iconPath !!}</svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-bold text-slate-800">{{ $title }}</h4>
                                <div class="flex items-center text-xs font-medium text-slate-500 mt-0.5">
                                    <span>{{ \Carbon\Carbon::parse($activity->date)->translatedFormat('l, d M Y') }}</span>
                                    @if(!empty($activity->keterangan))
                                        <span class="mx-1.5">•</span>
                                        <span class="text-slate-600 font-semibold border border-slate-200 bg-slate-50 px-1.5 py-0.5 rounded">{{ $activity->keterangan }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-full text-slate-400 py-10">
                            <svg class="w-12 h-12 mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-sm font-medium">Belum ada aktivitas di bulan ini</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        
        @endif
    </div>

    <style>
        .font-jakarta { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        @keyframes shimmer {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob { animation: blob 7s infinite; }
        .animation-delay-2000 { animation-delay: 2s; }
        .animation-delay-4000 { animation-delay: 4s; }
    </style>

    <!-- Modal Cetak Kartu Siswa -->
    <x-modal-cetak-kartu-siswa :student="$student" />
</div>

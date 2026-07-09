<div class="min-h-screen bg-slate-50 flex flex-col font-jakarta"
    x-data="{ 
        mode: '{{ $mode }}',
        activeSlide: 0, 
        selectedAngkatan: '', 
        selectedKelas: '', 
        allClasses: @js($allClasses), 
        slideInterval: null,
        
        now: new Date(),
        clockInterval: null,
        
        init() {
            this.clockInterval = setInterval(() => {
                this.now = new Date();
            }, 1000);

            if (this.mode === 'display') {
                this.startAutoAdvance();
            }

            // Listen to Livewire chart update event
            $wire.on('update-charts', (data) => {
                const chartsData = data[0];
                this.updateCharts(chartsData);
            });
            
            // Initial chart render
            this.$nextTick(() => {
                this.renderCharts({
                    donut: @js($donutData),
                    bar: {
                        grade7: @js($barData7),
                        grade8: @js($barData8),
                        grade9: @js($barData9)
                    },
                    line: @js($lineData)
                });
            });
        },
        
        get formattedTime() {
            return this.now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        },
        
        get formattedDate() {
            return this.now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        },

        startAutoAdvance() {
            this.slideInterval = setInterval(() => {
                this.activeSlide = (this.activeSlide + 1) % 3;
            }, 8000);
        },
        
        stopAutoAdvance() {
            if (this.slideInterval) {
                clearInterval(this.slideInterval);
            }
        },
        
        goToAngkatan(angkatan) {
            if (this.mode === 'display') return;
            if (angkatan) {
                this.activeSlide = parseInt(angkatan) - 7;
            }
        },
        
        goToKelas(classId) {
            if (this.mode === 'display') return;
            if (classId) {
                const targetClass = this.allClasses.find(c => c.id === parseInt(classId) || c.id === classId);
                if (targetClass) {
                    this.activeSlide = parseInt(targetClass.grade_level) - 7;
                    this.selectedAngkatan = targetClass.grade_level.toString();
                    
                    this.$nextTick(() => {
                        const el = document.getElementById('class-card-' + classId);
                        if(el) {
                            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    });
                }
            }
        },
        
        // Chart instances
        charts: {},
        
        renderCharts(data) {
            if (!window.Chart) return;
            
            // Donut
            const ctxDonut = document.getElementById('donutChart');
            if (ctxDonut) {
                this.charts.donut = new Chart(ctxDonut, {
                    type: 'doughnut',
                    data: {
                        labels: ['Hadir', 'Telat', 'Sakit', 'Izin', 'Alpa', 'Belum Absen'],
                        datasets: [{
                            data: [
                                data.donut.hadir, 
                                data.donut.telat, 
                                data.donut.sakit, 
                                data.donut.izin, 
                                data.donut.alpa,
                                data.donut.belum_absen
                            ],
                            backgroundColor: ['#10b981', '#f59e0b', '#3b82f6', '#8b5cf6', '#ef4444', '#cbd5e1'],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        cutout: '75%',
                        plugins: { 
                            legend: { position: 'right', labels: { usePointStyle: true, boxWidth: 8 } } 
                        } 
                    }
                });
            }
            
            // Line Trend
            const ctxLine = document.getElementById('lineChart');
            if (ctxLine) {
                this.charts.line = new Chart(ctxLine, {
                    type: 'line',
                    data: {
                        labels: data.line.labels,
                        datasets: [{
                            label: '% Kehadiran',
                            data: data.line.data,
                            borderColor: '#3b82f6',
                            borderWidth: 3,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#3b82f6',
                            pointBorderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            backgroundColor: (context) => {
                                const ctx = context.chart.ctx;
                                const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                                gradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
                                gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');
                                return gradient;
                            }
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        scales: { 
                            y: { min: 0, max: 100, grid: { borderDash: [4, 4] } },
                            x: { grid: { display: false } }
                        },
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }
            
            // Bar Charts
            ['grade7', 'grade8', 'grade9'].forEach((grade, idx) => {
                const ctxBar = document.getElementById('barChart' + (idx + 7));
                if (ctxBar) {
                    this.charts[grade] = new Chart(ctxBar, {
                        type: 'bar',
                        data: {
                            labels: data.bar[grade].labels,
                            datasets: [{
                                label: '% Kehadiran Bulanan',
                                data: data.bar[grade].data,
                                backgroundColor: '#4f46e5',
                                borderRadius: 4,
                            }]
                        },
                        options: { 
                            responsive: true, 
                            maintainAspectRatio: false, 
                            scales: { 
                                y: { min: 0, max: 100, grid: { borderDash: [4, 4] } },
                                x: { grid: { display: false } }
                            },
                            plugins: { legend: { display: false } }
                        }
                    });
                }
            });
        },
        
        updateCharts(data) {
            if(this.charts.donut) {
                this.charts.donut.data.datasets[0].data = [
                    data.donut.hadir, data.donut.telat, data.donut.sakit, 
                    data.donut.izin, data.donut.alpa, data.donut.belum_absen
                ];
                this.charts.donut.update();
            }
            if(this.charts.line) {
                this.charts.line.data.labels = data.line.labels;
                this.charts.line.data.datasets[0].data = data.line.data;
                this.charts.line.update();
            }
            ['grade7', 'grade8', 'grade9'].forEach((grade) => {
                if(this.charts[grade]) {
                    this.charts[grade].data.labels = data.bar[grade].labels;
                    this.charts[grade].data.datasets[0].data = data.bar[grade].data;
                    this.charts[grade].update();
                }
            });
        }
    }">

    <!-- SEKSI 1: Hero Section -->
    <div class="relative bg-indigo-900 overflow-hidden pb-8">
        <!-- Background Blobs -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
            <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-blue-600 mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
            <div class="absolute top-12 -right-24 w-96 h-96 rounded-full bg-indigo-500 mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-24 left-1/3 w-96 h-96 rounded-full bg-purple-600 mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 pt-10 pb-6 text-center">
            <div class="inline-flex items-center justify-center p-3 bg-white/10 backdrop-blur-md rounded-2xl mb-6 shadow-xl ring-1 ring-white/20">
                <svg class="w-8 h-8 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path></svg>
                <h1 class="text-xl sm:text-2xl font-bold text-white tracking-wide">Presensi Digital</h1>
            </div>
            
            <h2 class="text-4xl sm:text-5xl font-extrabold text-white mb-6 drop-shadow-md">
                {{ $pengaturanSekolah ? $pengaturanSekolah->school_name : 'SMP Negeri 3 Kedungreja' }}
            </h2>
            
            <div class="flex flex-col sm:flex-row justify-center items-center gap-4 text-indigo-100">
                <div class="flex items-center bg-indigo-950/50 backdrop-blur-sm px-6 py-3 rounded-full border border-indigo-500/30">
                    <svg class="w-5 h-5 mr-2 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span x-text="formattedTime" class="font-mono text-xl font-medium tabular-nums tracking-widest"></span>
                </div>
                <div class="flex items-center bg-indigo-950/50 backdrop-blur-sm px-6 py-3 rounded-full border border-indigo-500/30">
                    <svg class="w-5 h-5 mr-2 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span x-text="formattedDate" class="text-sm sm:text-base font-medium"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- SEKSI 2: Banner Pengumuman -->
    @if($pengumuman->count() > 0)
    <div class="bg-amber-50 border-b border-amber-200 shadow-sm relative overflow-hidden z-20">
        <div class="max-w-7xl mx-auto flex items-center">
            <div class="bg-amber-500 text-white px-4 py-3 font-bold flex items-center z-10 shadow-[4px_0_10px_rgba(0,0,0,0.1)] whitespace-nowrap">
                <svg class="w-5 h-5 mr-2 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                PENGUMUMAN
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

    <!-- Main Content Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-1">
        
        <!-- SEKSI 3: Portal Login Grid (4 Portal Utama) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
            
            <!-- Portal Siswa -->
            <a href="{{ route('siswa.login') }}" class="group relative bg-white rounded-2xl p-6 shadow-sm border border-slate-200 hover:shadow-xl hover:border-blue-300 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-blue-500 opacity-5 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center mb-4 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300 shadow-inner">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-1">Portal Siswa</h3>
                <p class="text-sm text-slate-500 mb-4">Akses khusus siswa & wali murid untuk riwayat absen.</p>
                <div class="text-blue-600 font-semibold text-sm flex items-center group-hover:translate-x-1 transition-transform">
                    Masuk Sekarang <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </div>
            </a>

            <!-- Portal Wali Kelas -->
            <a href="{{ route('wali-kelas.login') }}" class="group relative bg-white rounded-2xl p-6 shadow-sm border border-slate-200 hover:shadow-xl hover:border-emerald-300 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-emerald-500 opacity-5 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center mb-4 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300 shadow-inner">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-1">Portal Wali Kelas</h3>
                <p class="text-sm text-slate-500 mb-4">Rekap harian dan bulanan khusus untuk wali kelas.</p>
                <div class="text-emerald-600 font-semibold text-sm flex items-center group-hover:translate-x-1 transition-transform">
                    Masuk Sekarang <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </div>
            </a>

            <!-- Admin Panel -->
            <a href="/admin" class="group relative bg-white rounded-2xl p-6 shadow-sm border border-slate-200 hover:shadow-xl hover:border-amber-300 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-amber-500 opacity-5 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center mb-4 group-hover:bg-amber-600 group-hover:text-white transition-colors duration-300 shadow-inner">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-1">Admin Panel</h3>
                <p class="text-sm text-slate-500 mb-4">Manajemen master data dan pengaturan sekolah (Filament).</p>
                <div class="text-amber-600 font-semibold text-sm flex items-center group-hover:translate-x-1 transition-transform">
                    Masuk Sekarang <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </div>
            </a>

            <!-- Mode Kiosk -->
            <a href="{{ route('kiosk.scan') }}" class="group relative bg-slate-900 rounded-2xl p-6 shadow-sm border border-slate-700 hover:shadow-xl hover:shadow-indigo-500/20 hover:border-indigo-500 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-900/50 to-slate-900 z-0"></div>
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-indigo-500/20 text-indigo-400 rounded-xl flex items-center justify-center mb-4 group-hover:bg-indigo-500 group-hover:text-white transition-colors duration-300 shadow-inner ring-1 ring-indigo-500/50">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-1">Presensi Digital</h3>
                    <p class="text-sm text-slate-400 mb-4">Mode Presensi Digital siswa.</p>
                    <div class="text-indigo-400 font-semibold text-sm flex items-center group-hover:translate-x-1 transition-transform group-hover:text-indigo-300">
                        Buka Scanner <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </div>
            </a>
        </div>

        <!-- SEKSI 4: 6 Stat Cards Global (Live Polling) -->
        @if(!$isTodayHoliday)
        <div wire:poll.300000ms="$refresh" class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-10">
            <!-- Total Siswa Aktif -->
            <div class="bg-white rounded-xl shadow-sm border border-indigo-100 p-5 flex flex-col justify-center relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-16 h-16 bg-indigo-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                <p class="text-xs sm:text-sm font-medium text-indigo-600 mb-1 relative z-10 flex items-center">Total Siswa</p>
                <div class="flex items-baseline relative z-10">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-800">{{ $realStats['total_siswa'] }}</span>
                    <span class="ml-1 text-xs sm:text-sm font-medium text-slate-500">Siswa</span>
                </div>
            </div>
            <!-- Hadir -->
            <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-5 flex flex-col justify-center relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-16 h-16 bg-emerald-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                <p class="text-xs sm:text-sm font-medium text-emerald-600 mb-1 relative z-10 flex items-center"><span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span> Hadir & Telat</p>
                <div class="flex items-baseline relative z-10">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-800">{{ $realStats['hadir_telat'] }}</span>
                    <span class="ml-1 text-xs sm:text-sm font-medium text-slate-500">Siswa</span>
                </div>
            </div>
            <!-- Belum Presensi -->
            <div class="bg-white rounded-xl shadow-sm border border-amber-100 p-5 flex flex-col justify-center relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-16 h-16 bg-amber-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                <p class="text-xs sm:text-sm font-medium text-amber-600 mb-1 relative z-10 flex items-center"><span class="w-2 h-2 rounded-full bg-amber-500 mr-1.5"></span> Belum Absen</p>
                <div class="flex items-baseline relative z-10">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-800">{{ $realStats['belum_absen'] }}</span>
                    <span class="ml-1 text-xs sm:text-sm font-medium text-slate-500">Siswa</span>
                </div>
            </div>
            <!-- Sakit -->
            <div class="bg-white rounded-xl shadow-sm border border-blue-100 p-5 flex flex-col justify-center relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-16 h-16 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                <p class="text-xs sm:text-sm font-medium text-blue-600 mb-1 relative z-10 flex items-center"><span class="w-2 h-2 rounded-full bg-blue-500 mr-1.5"></span> Sakit</p>
                <div class="flex items-baseline relative z-10">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-800">{{ $realStats['sakit'] }}</span>
                    <span class="ml-1 text-xs sm:text-sm font-medium text-slate-500">Siswa</span>
                </div>
            </div>
            <!-- Izin -->
            <div class="bg-white rounded-xl shadow-sm border border-purple-100 p-5 flex flex-col justify-center relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-16 h-16 bg-purple-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                <p class="text-xs sm:text-sm font-medium text-purple-600 mb-1 relative z-10 flex items-center"><span class="w-2 h-2 rounded-full bg-purple-500 mr-1.5"></span> Izin</p>
                <div class="flex items-baseline relative z-10">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-800">{{ $realStats['izin'] }}</span>
                    <span class="ml-1 text-xs sm:text-sm font-medium text-slate-500">Siswa</span>
                </div>
            </div>
            <!-- Alpa -->
            <div class="bg-white rounded-xl shadow-sm border border-red-100 p-5 flex flex-col justify-center relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-16 h-16 bg-red-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                <p class="text-xs sm:text-sm font-medium text-red-600 mb-1 relative z-10 flex items-center"><span class="w-2 h-2 rounded-full bg-red-500 mr-1.5"></span> Tanpa Keterangan (Alpa)</p>
                <div class="flex items-baseline relative z-10">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-800">{{ $realStats['alpa_db'] }}</span>
                    <span class="ml-1 text-xs sm:text-sm font-medium text-slate-500">Siswa</span>
                </div>
            </div>
        </div>
        @endif

        <!-- SEKSI 5: Widget Libur & Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10" wire:ignore>
            
            <!-- Widget Hari Libur -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col h-[350px]">
                <div class="flex items-center gap-3 mb-5 border-b border-slate-100 pb-4">
                    <div class="bg-rose-100 p-2 rounded-lg text-rose-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">Libur Terdekat</h3>
                </div>
                
                <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar space-y-4">
                    @forelse($hariLiburs as $libur)
                        <div class="flex items-start gap-4">
                            <div class="flex flex-col items-center justify-center w-12 flex-shrink-0 bg-slate-50 rounded-lg border border-slate-200 py-1">
                                <span class="text-xs font-semibold text-slate-500 uppercase">{{ $libur->start_date->translatedFormat('M') }}</span>
                                <span class="text-lg font-bold text-slate-800 leading-none">{{ $libur->start_date->format('d') }}</span>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-800 text-sm leading-tight">{{ $libur->description }}</h4>
                                <p class="text-xs text-slate-500 mt-1">
                                    <span class="inline-block w-2 h-2 rounded-full mr-1 {{ $libur->type === 'nasional' ? 'bg-rose-500' : 'bg-amber-500' }}"></span>
                                    {{ ucfirst($libur->type) }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-full text-slate-400">
                            <svg class="w-12 h-12 mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-sm font-medium">Tidak ada hari libur terdekat</p>
                        </div>
                    @endforelse
                </div>
            </div>

            @if($isTodayHoliday)
                <!-- Card Hari Ini Libur (Menggantikan Donut Chart & Line Chart) -->
                <div class="lg:col-span-2 bg-gradient-to-br from-amber-50 to-orange-50 dark:from-slate-800 dark:to-slate-900 rounded-2xl shadow-sm border border-amber-200 dark:border-slate-700 p-8 h-[350px] flex flex-col items-center justify-center text-center">
                    <div class="bg-amber-100 p-4 rounded-full text-amber-600 mb-4 animate-pulse">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">HARI INI SEKOLAH LIBUR</h3>
                    <p class="text-lg font-semibold text-amber-700 dark:text-amber-400 mb-1">
                        Keterangan: {{ $todayHolidayName }}
                    </p>
                    <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md">
                        Pencatatan presensi digital dinonaktifkan sementara dan tidak ada data kehadiran hari ini. Selamat berlibur!
                    </p>
                </div>
            @else
                <!-- Donut Chart -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 h-[350px] flex flex-col">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-bold text-slate-800">Komposisi Presensi</h3>
                        <span class="text-xs font-medium bg-slate-100 text-slate-600 px-2 py-1 rounded">Hari Ini</span>
                    </div>
                    <div class="relative flex-1">
                        <canvas id="donutChart"></canvas>
                    </div>
                </div>
                
                <!-- Line Chart -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 h-[350px] flex flex-col">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-bold text-slate-800">Tren Kehadiran</h3>
                        <span class="text-xs font-medium bg-slate-100 text-slate-600 px-2 py-1 rounded">30 Hari</span>
                    </div>
                    <div class="relative flex-1">
                        <canvas id="lineChart"></canvas>
                    </div>
                </div>
            @endif

        </div>

        <!-- SEKSI 6: Laporan Per Angkatan (Slider) -->
        <div wire:ignore class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-10 overflow-hidden">
            <h2 class="text-2xl font-bold text-slate-800 mb-6 flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Laporan Per Angkatan
            </h2>
            
            <div class="relative w-full h-[600px] bg-slate-50/50 rounded-xl border border-slate-100 p-4">
                @foreach([7, 8, 9] as $index => $grade)
                    <!-- Slide untuk Angkatan {{ $grade }} -->
                    <div class="absolute inset-4 transition-all duration-700 ease-in-out flex flex-col"
                         x-show="activeSlide === {{ $index }}"
                         x-transition:enter="transform transition ease-out duration-500"
                         x-transition:enter-start="translate-x-full opacity-0"
                         x-transition:enter-end="translate-x-0 opacity-100"
                         x-transition:leave="transform transition ease-in duration-500"
                         x-transition:leave-start="translate-x-0 opacity-100"
                         x-transition:leave-end="-translate-x-full opacity-0"
                         style="display: none;">
                        
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-bold text-slate-800">Angkatan {{ $grade }}</h3>
                            <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm font-semibold border border-indigo-200 shadow-sm">Tingkat {{ $grade }}</span>
                        </div>

                        <!-- Grid Kelas & Chart -->
                        <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar flex flex-col gap-6">
                            <!-- Bar Chart -->
                            <div class="w-full h-56 flex-shrink-0 bg-white p-4 rounded-xl border border-slate-200">
                                <canvas id="barChart{{ $grade }}"></canvas>
                            </div>
                            
                            <!-- Grid Kartu Kelas -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 pb-4">
                                <template x-for="kelas in allClasses.filter(c => c.grade_level == {{ $grade }})" :key="kelas.id">
                                    <div :id="'class-card-' + kelas.id" class="bg-white rounded-xl p-5 border border-slate-200 hover:border-indigo-300 hover:shadow-md transition-all duration-300">
                                        <div class="flex justify-between items-start mb-4">
                                            <h4 class="text-lg font-bold text-slate-800" x-text="kelas.name"></h4>
                                            <div class="bg-slate-100 text-slate-600 text-xs px-2 py-1 rounded font-medium border border-slate-200" x-text="kelas.total_students + ' Siswa'"></div>
                                        </div>
                                        <div class="space-y-4">
                                            <div>
                                                <div class="flex justify-between text-xs text-slate-500 mb-1.5">
                                                    <span>Hari Ini (Hadir)</span>
                                                    <span class="font-bold text-slate-700" x-text="kelas.present_today + ' / ' + kelas.total_students + ' Siswa'"></span>
                                                </div>
                                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                                    <div class="bg-emerald-500 h-full rounded-full transition-all duration-1000" :style="'width: ' + kelas.today_percentage + '%'"></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="flex justify-between text-xs text-slate-500 mb-1.5">
                                                    <span>Bulan Ini (Rata-rata)</span>
                                                    <span class="font-bold text-slate-700" x-text="kelas.month_present_avg + ' / ' + kelas.total_students + ' Siswa'"></span>
                                                </div>
                                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                                    <div class="bg-indigo-500 h-full rounded-full transition-all duration-1000" :style="'width: ' + kelas.month_percentage + '%'"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Slider Navigation Dots -->
            <div class="flex justify-center mt-6 gap-3">
                @foreach([7, 8, 9] as $index => $grade)
                    <button @click="activeSlide = {{ $index }}; selectedAngkatan = '{{ $grade }}'" 
                            class="h-3 rounded-full transition-all duration-300"
                            :class="activeSlide === {{ $index }} ? 'bg-indigo-600 w-8' : 'bg-slate-300 hover:bg-slate-400 w-3'"></button>
                @endforeach
            </div>
        </div>

    </div>

    <!-- SEKSI 7: Footer -->
    <footer class="bg-slate-900 text-slate-400 py-8 border-t border-slate-800 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center sm:text-left flex flex-col sm:flex-row justify-between items-center gap-4">
            <div>
                <h4 class="text-white font-bold text-lg mb-1">{{ $pengaturanSekolah ? $pengaturanSekolah->school_name : 'Sistem Presensi' }}</h4>
                <p class="text-sm max-w-md">{{ $pengaturanSekolah ? $pengaturanSekolah->school_address : '' }}</p>
            </div>
            <div class="text-sm">
                <p>&copy; {{ date('Y') }} Hak Cipta Dilindungi.</p>
                <p class="mt-1">Sistem Presensi Berbasis Barcode</p>
            </div>
        </div>
    </footer>

    <style>
        .font-jakarta { font-family: 'Plus Jakarta Sans', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }
        
        @keyframes shimmer {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
    </style>
</div>

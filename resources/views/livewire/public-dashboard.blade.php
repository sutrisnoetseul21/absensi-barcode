<div
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
    }"
    class="min-h-screen bg-slate-50 flex flex-col font-jakarta">

    <!-- ====================== HEADER / NAVBAR MODERN ====================== -->
    <header class="fixed top-0 left-0 right-0 z-50 transition-all duration-500" id="main-navbar"
        x-data="{ scrolled: false, mobileMenuOpen: false }"
        @scroll.window="scrolled = window.scrollY > 30"
        :class="scrolled ? 'bg-white/80 backdrop-blur-xl shadow-lg shadow-indigo-100/50 border-b border-white/60' : (mobileMenuOpen ? 'bg-slate-950/70 backdrop-blur-2xl border-b border-white/10' : 'bg-transparent')">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Header Kiri: Logo & Nama Sekolah -->
                <div class="flex items-center gap-3 group">
                    @if($pengaturanSekolah && $pengaturanSekolah->school_logo_path)
                        <div class="relative">
                            <div class="absolute inset-0 bg-indigo-400/30 rounded-xl blur-md group-hover:blur-lg transition-all duration-300"></div>
                            <img src="{{ asset('storage/' . $pengaturanSekolah->school_logo_path) }}" alt="Logo"
                                class="relative h-10 sm:h-12 w-auto object-contain drop-shadow-md">
                        </div>
                    @endif
                    <div>
                        <h1 class="text-lg sm:text-xl font-extrabold tracking-tight leading-tight transition-colors duration-300"
                            :class="scrolled ? 'text-slate-800' : 'text-white drop-shadow-md'">
                            {{ $pengaturanSekolah ? $pengaturanSekolah->school_name : 'SMPN 1 Majenang' }}
                        </h1>
                        <p class="text-xs font-medium transition-colors duration-300" :class="scrolled ? 'text-indigo-600' : 'text-indigo-200'">
                            Sistem Presensi Digital
                        </p>
                    </div>
                </div>

                <!-- Header Kanan: Menu Navigasi Desktop -->
                <nav class="hidden lg:flex items-center space-x-1">
                    <a href="{{ route('siswa.login') }}"
                        class="relative group px-4 py-2 rounded-xl font-semibold text-sm transition-all duration-200"
                        :class="scrolled ? 'text-slate-600 hover:text-indigo-700 hover:bg-indigo-50' : 'text-white/80 hover:text-white hover:bg-white/10'">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Portal Siswa
                        </span>
                    </a>
                    <a href="{{ route('wali-kelas.login') }}"
                        class="relative group px-4 py-2 rounded-xl font-semibold text-sm transition-all duration-200"
                        :class="scrolled ? 'text-slate-600 hover:text-indigo-700 hover:bg-indigo-50' : 'text-white/80 hover:text-white hover:bg-white/10'">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Wali Kelas
                        </span>
                    </a>
                    <a href="/admin"
                        class="relative group px-4 py-2 rounded-xl font-semibold text-sm transition-all duration-200"
                        :class="scrolled ? 'text-slate-600 hover:text-indigo-700 hover:bg-indigo-50' : 'text-white/80 hover:text-white hover:bg-white/10'">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Admin
                        </span>
                    </a>
                    <a href="{{ route('kiosk.scan') }}"
                        class="ml-4 flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-indigo-500/30 transition-all duration-300 transform hover:scale-105 hover:shadow-indigo-500/50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        Presensi Digital
                        <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse ml-1"></span>
                    </a>
                </nav>

                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 rounded-xl transition-all duration-200"
                    :class="scrolled ? 'text-slate-700 hover:bg-slate-100' : 'text-white hover:bg-white/10'">
                    <svg x-show="!mobileMenuOpen" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileMenuOpen" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <!-- Mobile Dropdown Menu -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="lg:hidden absolute top-full left-0 w-full backdrop-blur-xl border-b shadow-xl transition-colors duration-300"
             :class="scrolled ? 'bg-white/95 border-slate-200' : 'bg-slate-950/80 border-white/10'"
             style="display: none;">
            <div class="px-4 pt-2 pb-6 space-y-2">
                <a href="{{ route('siswa.login') }}" class="block px-4 py-3 rounded-xl font-semibold transition-colors"
                   :class="scrolled ? 'text-slate-700 hover:bg-indigo-50 hover:text-indigo-600' : 'text-slate-200 hover:bg-white/10 hover:text-white'">
                    Portal Siswa
                </a>
                <a href="{{ route('wali-kelas.login') }}" class="block px-4 py-3 rounded-xl font-semibold transition-colors"
                   :class="scrolled ? 'text-slate-700 hover:bg-indigo-50 hover:text-indigo-600' : 'text-slate-200 hover:bg-white/10 hover:text-white'">
                    Wali Kelas
                </a>
                <a href="/admin" class="block px-4 py-3 rounded-xl font-semibold transition-colors"
                   :class="scrolled ? 'text-slate-700 hover:bg-indigo-50 hover:text-indigo-600' : 'text-slate-200 hover:bg-white/10 hover:text-white'">
                    Admin
                </a>
                <a href="{{ route('kiosk.scan') }}" class="mt-4 flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600 to-violet-600 text-white px-4 py-3 rounded-xl font-bold shadow-md shadow-indigo-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    Presensi Digital
                </a>
            </div>
        </div>
    </header>

    <!-- ====================== HERO SECTION MODERN ====================== -->
    <x-public-dashboard.hero 
        :pengaturanSekolah="$pengaturanSekolah" 
        :isTodayHoliday="$isTodayHoliday" 
        :realStats="$realStats" 
    />


    <!-- SEKSI 2: Banner Pengumuman -->
    <x-public-dashboard.announcement :pengumuman="$pengumuman" />

    <!-- Main Content Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-1">

        <!-- SEKSI 4: 6 Stat Cards Global (Live Polling) - MODERN VERSION -->
        @if(!$isTodayHoliday)
        <div wire:poll.300000ms="$refresh" class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-10">

            <!-- Total Siswa Aktif -->
            <div class="relative bg-gradient-to-br from-indigo-600 to-indigo-800 rounded-2xl p-5 shadow-lg shadow-indigo-200/50 overflow-hidden group cursor-default">
                <div class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
                <div class="absolute -right-3 -top-3 w-16 h-16 bg-white/10 rounded-full"></div>
                <div class="absolute -right-1 -top-1 w-8 h-8 bg-white/10 rounded-full"></div>
                <p class="text-xs font-semibold text-indigo-200 uppercase tracking-widest mb-2">Total Siswa</p>
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-black text-white">{{ $realStats['total_siswa'] }}</span>
                    <span class="text-sm font-medium text-indigo-300">Siswa</span>
                </div>
                <div class="mt-2 flex items-center gap-1 text-indigo-200 text-xs"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg> Aktif Tahun Ini</div>
            </div>

            <!-- Hadir & Telat -->
            <div class="relative bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-5 shadow-lg shadow-emerald-200/50 overflow-hidden group cursor-default">
                <div class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
                <div class="absolute -right-3 -top-3 w-16 h-16 bg-white/10 rounded-full"></div>
                <div class="absolute -right-1 -top-1 w-8 h-8 bg-white/10 rounded-full"></div>
                <p class="text-xs font-semibold text-emerald-100 uppercase tracking-widest mb-2 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> Hadir & Telat
                </p>
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-black text-white">{{ $realStats['hadir_telat'] }}</span>
                    <span class="text-sm font-medium text-emerald-200">Siswa</span>
                </div>
                <div class="mt-2 text-emerald-100 text-xs">Hari ini · Real-time</div>
            </div>

            <!-- Belum Absen -->
            <div class="relative bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-5 shadow-lg shadow-amber-200/50 overflow-hidden group cursor-default">
                <div class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
                <div class="absolute -right-3 -top-3 w-16 h-16 bg-white/10 rounded-full"></div>
                <div class="absolute -right-1 -top-1 w-8 h-8 bg-white/10 rounded-full"></div>
                <p class="text-xs font-semibold text-amber-100 uppercase tracking-widest mb-2">Belum Absen</p>
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-black text-white">{{ $realStats['belum_absen'] }}</span>
                    <span class="text-sm font-medium text-amber-200">Siswa</span>
                </div>
                <div class="mt-2 text-amber-100 text-xs">Belum tercatat hari ini</div>
            </div>

            <!-- Sakit -->
            <div class="relative bg-gradient-to-br from-sky-500 to-blue-600 rounded-2xl p-5 shadow-lg shadow-sky-200/50 overflow-hidden group cursor-default">
                <div class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
                <div class="absolute -right-3 -top-3 w-16 h-16 bg-white/10 rounded-full"></div>
                <div class="absolute -right-1 -top-1 w-8 h-8 bg-white/10 rounded-full"></div>
                <p class="text-xs font-semibold text-sky-100 uppercase tracking-widest mb-2">Sakit</p>
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-black text-white">{{ $realStats['sakit'] }}</span>
                    <span class="text-sm font-medium text-sky-200">Siswa</span>
                </div>
                <div class="mt-2 text-sky-100 text-xs">Dengan surat keterangan</div>
            </div>

            <!-- Izin -->
            <div class="relative bg-gradient-to-br from-violet-500 to-purple-700 rounded-2xl p-5 shadow-lg shadow-violet-200/50 overflow-hidden group cursor-default">
                <div class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
                <div class="absolute -right-3 -top-3 w-16 h-16 bg-white/10 rounded-full"></div>
                <div class="absolute -right-1 -top-1 w-8 h-8 bg-white/10 rounded-full"></div>
                <p class="text-xs font-semibold text-violet-200 uppercase tracking-widest mb-2">Izin</p>
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-black text-white">{{ $realStats['izin'] }}</span>
                    <span class="text-sm font-medium text-violet-300">Siswa</span>
                </div>
                <div class="mt-2 text-violet-200 text-xs">Izin resmi tercatat</div>
            </div>

            <!-- Alpa (Tanpa Keterangan) -->
            <div class="relative bg-gradient-to-br from-rose-500 to-red-700 rounded-2xl p-5 shadow-lg shadow-rose-200/50 overflow-hidden group cursor-default">
                <div class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
                <div class="absolute -right-3 -top-3 w-16 h-16 bg-white/10 rounded-full"></div>
                <div class="absolute -right-1 -top-1 w-8 h-8 bg-white/10 rounded-full"></div>
                <p class="text-xs font-semibold text-rose-200 uppercase tracking-widest mb-2">Alpa</p>
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-black text-white">{{ $realStats['alpa_db'] }}</span>
                    <span class="text-sm font-medium text-rose-300">Siswa</span>
                </div>
                <div class="mt-2 text-rose-200 text-xs">Tanpa keterangan</div>
            </div>
        </div>
        @endif

        <!-- SEKSI 5: Widget Libur, Charts & Wall of Fame -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-10" wire:ignore>

            <!-- Widget Hari Libur -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 flex flex-col h-[380px]">
                <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 flex items-center justify-center bg-rose-100 rounded-2xl text-rose-600 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800 leading-tight">Kalender Pendidikan</h3>
                        <p class="text-xs text-slate-400">Agenda sekolah & jadwal libur</p>
                    </div>
                </div>
                <!-- Alpine Calendar Widget -->
                <div class="flex-1 flex flex-col" x-data="{
                    currDate: new Date(),
                    holidays: @js(collect($hariLiburs)->map(function($h) { 
                        return [
                            'start' => $h->start_date->format('Y-m-d'), 
                            'end' => $h->end_date ? $h->end_date->format('Y-m-d') : $h->start_date->format('Y-m-d'), 
                            'desc' => $h->description, 
                            'type' => $h->type
                        ];
                    })->values()),
                    monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                    dayNames: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                    get currMonth() { return this.currDate.getMonth(); },
                    get currYear() { return this.currDate.getFullYear(); },
                    prevMonth() { this.currDate = new Date(this.currYear, this.currMonth - 1, 1); },
                    nextMonth() { this.currDate = new Date(this.currYear, this.currMonth + 1, 1); },
                    get blankDays() {
                        let firstDay = new Date(this.currYear, this.currMonth, 1).getDay();
                        return Array.from({ length: firstDay });
                    },
                    get daysInMonth() {
                        let days = new Date(this.currYear, this.currMonth + 1, 0).getDate();
                        return Array.from({ length: days }, (_, i) => i + 1);
                    },
                    isToday(date) {
                        let d = new Date(this.currYear, this.currMonth, date);
                        let today = new Date();
                        return today.toDateString() === d.toDateString();
                    },
                    getHoliday(date) {
                        let dStr = this.currYear + '-' + String(this.currMonth + 1).padStart(2, '0') + '-' + String(date).padStart(2, '0');
                        for(let h of this.holidays) {
                            if(dStr >= h.start && dStr <= h.end) return h;
                        }
                        return null;
                    }
                }">
                    <!-- Calendar Header -->
                    <div class="flex items-center justify-between mb-3 px-1">
                        <button @click="prevMonth()" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <div class="font-bold text-slate-800 text-sm" x-text="monthNames[currMonth] + ' ' + currYear"></div>
                        <button @click="nextMonth()" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="grid grid-cols-7 gap-1 mb-2">
                        <template x-for="day in dayNames">
                            <div class="text-center text-[10px] font-bold text-slate-400 uppercase" x-text="day"></div>
                        </template>
                    </div>
                    
                    <div class="grid grid-cols-7 gap-1 flex-1 content-start relative">
                        <template x-for="blank in blankDays">
                            <div class="aspect-square"></div>
                        </template>
                        <template x-for="date in daysInMonth" :key="date">
                            <div class="aspect-square relative group">
                                <div class="w-full h-full flex flex-col items-center justify-center rounded-lg border text-[13px] font-semibold transition-all cursor-default"
                                    :class="{
                                        'bg-rose-50 text-rose-600 border-rose-200': getHoliday(date) && getHoliday(date).type === 'nasional',
                                        'bg-amber-50 text-amber-600 border-amber-200': getHoliday(date) && getHoliday(date).type !== 'nasional',
                                        'bg-indigo-600 text-white shadow-md shadow-indigo-200 border-indigo-600': isToday(date) && !getHoliday(date),
                                        'text-slate-700 hover:bg-slate-50 border-transparent': !isToday(date) && !getHoliday(date)
                                    }">
                                    <span x-text="date"></span>
                                    
                                    <!-- Tooltip / Hover Event -->
                                    <div x-show="getHoliday(date)" class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none w-max max-w-[150px] bg-slate-800 text-white text-[10px] py-1.5 px-2.5 rounded-lg text-center z-20 shadow-xl border border-slate-700" style="display: none;">
                                        <div class="font-bold truncate" x-text="getHoliday(date) ? getHoliday(date).desc : ''"></div>
                                        <!-- Arrow -->
                                        <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-800"></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    
                    <!-- Legend -->
                    <div class="mt-auto pt-3 border-t border-slate-100 flex items-center justify-center gap-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                        <div class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-full bg-rose-400"></div> Nasional</div>
                        <div class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-full bg-amber-400"></div> Sekolah</div>
                        <div class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-full bg-indigo-600"></div> Hari Ini</div>
                    </div>
                </div>
            </div>

            @if($isTodayHoliday)
                <!-- Card Hari Ini Libur -->
                <div class="lg:col-span-3 bg-gradient-to-br from-amber-50 to-orange-50 rounded-3xl shadow-sm border border-amber-200 p-8 h-[380px] flex flex-col items-center justify-center text-center">
                    <div class="bg-amber-100 p-4 rounded-full text-amber-600 mb-4 animate-pulse">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800 mb-2">HARI INI SEKOLAH LIBUR</h3>
                    <p class="text-lg font-semibold text-amber-700 mb-1">Keterangan: {{ $todayHolidayName }}</p>
                    <p class="text-sm text-slate-500 max-w-md">Pencatatan presensi digital dinonaktifkan sementara. Selamat berlibur!</p>
                </div>
            @else
                <!-- Donut Chart -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 h-[380px] flex flex-col">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Komposisi Presensi</h3>
                            <p class="text-xs text-slate-400">Rekap hari ini</p>
                        </div>
                        <span class="text-xs font-semibold bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-full border border-emerald-100">● Live</span>
                    </div>
                    <div class="relative flex-1"><canvas id="donutChart"></canvas></div>
                </div>

                <!-- Line Chart -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 h-[380px] flex flex-col">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Tren Kehadiran</h3>
                            <p class="text-xs text-slate-400">30 hari terakhir</p>
                        </div>
                        <span class="text-xs font-semibold bg-indigo-50 text-indigo-700 px-3 py-1.5 rounded-full border border-indigo-100">30 Hari</span>
                    </div>
                    <div class="relative flex-1"><canvas id="lineChart"></canvas></div>
                </div>

                <!-- 🏆 WIDGET WALL OF FAME -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 h-[380px] flex flex-col">
                    <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-100">
                        <div class="w-10 h-10 flex items-center justify-center bg-amber-100 rounded-2xl text-amber-600 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800 leading-tight">Kelas Teladan</h3>
                            <p class="text-xs text-slate-400">Peringkat kehadiran terbaik bulan ini</p>
                        </div>
                    </div>
                    <div class="flex-1 flex flex-col justify-center space-y-3">
                        @foreach($wallOfFame as $index => $kelas)
                        @php
                            $medals = ['🥇','🥈','🥉','4️⃣','5️⃣'];
                            $barColors = ['bg-amber-400','bg-slate-400','bg-orange-400','bg-indigo-300','bg-sky-300'];
                            $textColors = ['text-amber-600','text-slate-600','text-orange-600','text-indigo-500','text-sky-500'];
                        @endphp
                        <div class="group">
                            <div class="flex items-center justify-between mb-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg leading-none">{{ $medals[$index] }}</span>
                                    <span class="text-sm font-bold text-slate-700">{{ $kelas['name'] }}</span>
                                </div>
                                <span class="text-sm font-extrabold {{ $textColors[$index] }}">{{ $kelas['percentage'] }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                <div class="{{ $barColors[$index] }} h-2.5 rounded-full transition-all duration-700 ease-out"
                                    style="width: {{ $kelas['percentage'] }}%"></div>
                            </div>
                        </div>
                        @endforeach
                        @if(empty($wallOfFame))
                            <div class="text-center text-slate-400 py-8">
                                <p class="text-sm">Belum ada data kehadiran bulan ini.</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div><!-- End charts grid -->

        <!-- SEKSI 6: Laporan Per Angkatan (Slider) -->
        <div wire:ignore class="relative bg-white rounded-3xl shadow-sm border border-slate-100 p-8 mb-10 overflow-hidden">
            <!-- Decorative background accent -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-indigo-50 to-transparent rounded-3xl pointer-events-none"></div>

            <!-- Section Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 sm:gap-0 mb-8 relative z-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800 leading-tight">Laporan Per Angkatan</h2>
                        <p class="text-xs text-slate-400">Statistik kehadiran per tingkat kelas</p>
                    </div>
                </div>
                <!-- Tab Buttons -->
                <div class="flex items-center gap-1 sm:gap-2 bg-slate-100 p-1 rounded-xl self-start sm:self-auto w-full sm:w-auto">
                    @foreach([7, 8, 9] as $index => $grade)
                        <button @click="activeSlide = {{ $index }}; selectedAngkatan = '{{ $grade }}'"
                            class="flex-1 sm:flex-none px-3 sm:px-5 py-2 rounded-lg text-xs sm:text-sm font-bold transition-all duration-300 whitespace-nowrap"
                            :class="activeSlide === {{ $index }} ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                            Kelas {{ $grade }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Slides -->
            <div class="relative w-full z-10">
                @foreach([7, 8, 9] as $index => $grade)
                    <div class="transition-all duration-500 ease-in-out flex flex-col"
                         x-show="activeSlide === {{ $index }}"
                         x-transition:enter="transition ease-out duration-400"
                         x-transition:enter-start="opacity-0 translate-x-4"
                         x-transition:enter-end="opacity-100 translate-x-0"
                         style="display: none;">

                        <!-- Bar Chart -->
                        <div class="w-full h-52 flex-shrink-0 bg-slate-50 border border-slate-100 rounded-2xl p-4 mb-6">
                            <canvas id="barChart{{ $grade }}"></canvas>
                        </div>

                        <!-- Grid Kartu Kelas -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            <template x-for="kelas in allClasses.filter(c => c.grade_level == {{ $grade }})" :key="kelas.id">
                                <div :id="'class-card-' + kelas.id"
                                    class="group bg-slate-50 hover:bg-white border border-slate-100 hover:border-indigo-200 rounded-2xl p-5 hover:shadow-lg hover:shadow-indigo-50/80 transition-all duration-300">
                                    <!-- Card Header -->
                                    <div class="flex justify-between items-center mb-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 bg-indigo-600 text-white rounded-xl flex items-center justify-center font-black text-xs" x-text="kelas.name.replace('Kelas ','').replace('kelas ','')"></div>
                                            <h4 class="text-sm font-bold text-slate-800" x-text="kelas.name"></h4>
                                        </div>
                                        <span class="text-xs font-semibold text-slate-500 bg-white border border-slate-200 px-2 py-1 rounded-lg" x-text="kelas.total_students + ' Siswa'"></span>
                                    </div>
                                    <div class="space-y-3">
                                        <!-- Hari Ini -->
                                        <div>
                                            <div class="flex justify-between text-xs mb-1.5">
                                                <span class="text-slate-500 font-medium">Hadir Hari Ini</span>
                                                <span class="font-bold text-emerald-600" x-text="kelas.today_percentage + '%'"></span>
                                            </div>
                                            <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                                <div class="bg-gradient-to-r from-emerald-400 to-emerald-600 h-full rounded-full transition-all duration-1000"
                                                    :style="'width: ' + kelas.today_percentage + '%'"></div>
                                            </div>
                                            <p class="text-xs text-slate-400 mt-1" x-text="kelas.present_today + ' / ' + kelas.total_students + ' siswa'"></p>
                                        </div>
                                        <!-- Bulan Ini -->
                                        <div>
                                            <div class="flex justify-between text-xs mb-1.5">
                                                <span class="text-slate-500 font-medium">Rata-rata Bulan Ini</span>
                                                <span class="font-bold text-indigo-600" x-text="kelas.month_percentage + '%'"></span>
                                            </div>
                                            <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                                <div class="bg-gradient-to-r from-indigo-400 to-violet-600 h-full rounded-full transition-all duration-1000"
                                                    :style="'width: ' + kelas.month_percentage + '%'"></div>
                                            </div>
                                            <p class="text-xs text-slate-400 mt-1" x-text="kelas.month_present_avg + ' / ' + kelas.total_students + ' siswa'"></p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Dot Indicators -->
            <div class="flex justify-center mt-6 gap-2 relative z-10">
                @foreach([7, 8, 9] as $index => $grade)
                    <button @click="activeSlide = {{ $index }}; selectedAngkatan = '{{ $grade }}'"
                            class="h-2 rounded-full transition-all duration-300"
                            :class="activeSlide === {{ $index }} ? 'bg-indigo-600 w-8' : 'bg-slate-300 hover:bg-slate-400 w-2'">
                    </button>
                @endforeach
            </div>
        </div>

    </div><!-- End Main Content Container -->

    <!-- SEKSI 7: Footer (Modern) -->
    <x-public-dashboard.footer :pengaturanSekolah="$pengaturanSekolah" />

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

        /* Modern Blob Animations */
        @keyframes moveblob1 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(60px, -40px) scale(1.1); }
            66% { transform: translate(-30px, 30px) scale(0.95); }
        }
        @keyframes moveblob2 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(-50px, 30px) scale(1.05); }
            66% { transform: translate(40px, -50px) scale(1.1); }
        }
        @keyframes moveblob3 {
            0%, 100% { transform: translate(-50%, -50%) scale(1); }
            50% { transform: translate(-50%, -50%) scale(1.2); }
        }

        /* Gradient text animation */
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        .animated-gradient-text {
            background-size: 200% auto;
            animation: gradientShift 4s ease infinite;
        }

        /* Hero content fade-in on load */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .hero-content > * {
            animation: fadeInUp 0.7s ease forwards;
        }
        .hero-content > *:nth-child(1) { animation-delay: 0.1s; opacity: 0; }
        .hero-content > *:nth-child(2) { animation-delay: 0.25s; opacity: 0; }
        .hero-content > *:nth-child(3) { animation-delay: 0.4s; opacity: 0; }
        .hero-content > *:nth-child(4) { animation-delay: 0.55s; opacity: 0; }
        .hero-content > *:nth-child(5) { animation-delay: 0.7s; opacity: 0; }
    </style>

</div>

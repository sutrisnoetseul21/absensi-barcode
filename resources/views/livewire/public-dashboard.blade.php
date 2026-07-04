<div class="min-h-screen bg-slate-50 py-8"
    x-data="{ 
        mode: '{{ $mode }}',
        activeSlide: 0, 
        selectedAngkatan: '', 
        selectedKelas: '', 
        allClasses: @js($allClasses), 
        slideInterval: null,
        
        init() {
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
                    
                    // Optional: scroll into view
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
                            backgroundColor: ['#22c55e', '#eab308', '#3b82f6', '#8b5cf6', '#ef4444', '#94a3b8']
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
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
                            tension: 0.3,
                            fill: true,
                            backgroundColor: 'rgba(59, 130, 246, 0.1)'
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, scales: { y: { min: 0, max: 100 } } }
                });
            }
            
            // Bar Charts for each grade
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
                                backgroundColor: '#10b981'
                            }]
                        },
                        options: { responsive: true, maintainAspectRatio: false, scales: { y: { min: 0, max: 100 } } }
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

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Dashboard Kehadiran Sekolah</h1>
            <p class="mt-2 text-lg text-slate-600">Pantau tingkat kehadiran secara real-time</p>
        </div>

        @if($mode === 'public')
            <!-- LAYER 1: Filter Panel (TIDAK ADA wire:ignore) -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Livewire Filters (Re-query Server) -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tahun Ajaran</label>
                        <select wire:model.live="selectedAcademicYearId" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Bulan</label>
                        <select wire:model.live="selectedMonth" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach($months as $num => $name)
                                <option value="{{ $num }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- AlpineJS Filters (Only Visual/Highlight, No Server Re-query) -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Pilih Angkatan (Visual)</label>
                        <select x-model="selectedAngkatan" x-on:change="goToAngkatan(selectedAngkatan)" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Semua Angkatan</option>
                            <option value="7">Angkatan 7</option>
                            <option value="8">Angkatan 8</option>
                            <option value="9">Angkatan 9</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Pilih Kelas (Visual)</label>
                        <select x-model="selectedKelas" x-on:change="goToKelas(selectedKelas)" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Semua Kelas</option>
                            <template x-for="c in allClasses" :key="c.id">
                                <option :value="c.id" x-text="c.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <!-- Loading Indicator -->
                <div wire:loading class="mt-4 text-blue-600 text-sm font-medium animate-pulse">
                    Memperbarui data dari server...
                </div>
            </div>
        @endif

        <!-- Global Charts & Wall of Fame -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8" wire:ignore>
            <!-- Donut Chart -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Status Hari Ini</h3>
                <div class="relative h-64">
                    <canvas id="donutChart"></canvas>
                </div>
            </div>
            
            <!-- Line Chart -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Tren 30 Hari Terakhir</h3>
                <div class="relative h-64">
                    <canvas id="lineChart"></canvas>
                </div>
            </div>

            <!-- Wall of Fame -->
            <div class="bg-gradient-to-br from-amber-400 to-amber-600 rounded-xl shadow-sm border border-amber-300 p-6 text-white">
                <div class="flex items-center gap-3 mb-4">
                    <svg class="w-6 h-6 text-amber-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                    <h3 class="text-xl font-bold">Wall of Fame</h3>
                </div>
                <p class="text-amber-100 text-sm mb-4">Top 5 Kelas Bulan Ini</p>
                <div class="space-y-3">
                    @forelse($wallOfFame as $idx => $item)
                        <div class="flex items-center justify-between bg-white/20 rounded-lg p-2 backdrop-blur-sm">
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-lg w-6">{{ $idx + 1 }}</span>
                                <span class="font-medium">{{ $item['name'] }}</span>
                            </div>
                            <span class="font-bold">{{ $item['percentage'] }}%</span>
                        </div>
                    @empty
                        <div class="text-amber-100 text-sm">Belum ada data absensi bulan ini.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- LAYER 2: Area Slider/Chart (HARUS ADA wire:ignore) -->
        <div wire:ignore class="relative overflow-hidden bg-slate-100 rounded-2xl shadow-inner p-6">
            <h2 class="text-2xl font-bold text-slate-800 mb-6 text-center">Laporan Per Angkatan</h2>
            
            <div class="relative w-full h-[600px]">
                
                @foreach([7, 8, 9] as $index => $grade)
                    <!-- Slide untuk Angkatan {{ $grade }} -->
                    <div class="absolute inset-0 transition-all duration-700 ease-in-out"
                         x-show="activeSlide === {{ $index }}"
                         x-transition:enter="transform transition ease-out duration-500"
                         x-transition:enter-start="translate-x-full opacity-0"
                         x-transition:enter-end="translate-x-0 opacity-100"
                         x-transition:leave="transform transition ease-in duration-500"
                         x-transition:leave-start="translate-x-0 opacity-100"
                         x-transition:leave-end="-translate-x-full opacity-0"
                         style="display: none;">
                        
                        <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-6 h-full flex flex-col">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-xl font-bold text-slate-800">Angkatan {{ $grade }}</h3>
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">Tingkat {{ $grade }}</span>
                            </div>

                            <!-- Flex Container: Grid Kelas & Chart -->
                            <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar flex flex-col gap-8">
                                
                                <!-- Bar Chart untuk Angkatan Ini -->
                                <div class="w-full h-64 flex-shrink-0">
                                    <canvas id="barChart{{ $grade }}"></canvas>
                                </div>
                                
                                <!-- Grid Kartu Kelas -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 pb-4">
                                    <template x-for="kelas in allClasses.filter(c => c.grade_level == {{ $grade }})" :key="kelas.id">
                                        <!-- Card Kelas -->
                                        <div :id="'class-card-' + kelas.id"
                                             class="bg-slate-50 rounded-lg p-4 border transition-all duration-300"
                                             :class="selectedKelas == kelas.id ? 'border-amber-400 border-2 shadow-lg scale-105 ring-2 ring-amber-200' : 'border-slate-200 hover:border-blue-300 hover:shadow-md'">
                                            
                                            <div class="flex justify-between items-start mb-3">
                                                <h4 class="text-lg font-bold text-slate-700" x-text="kelas.name"></h4>
                                                <div class="bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded font-medium" x-text="kelas.total_students + ' Siswa'"></div>
                                            </div>
                                            
                                            <div class="space-y-2">
                                                <div>
                                                    <div class="flex justify-between text-xs text-slate-500 mb-1">
                                                        <span>Hari Ini (Hadir)</span>
                                                        <span class="font-semibold" x-text="kelas.today_percentage + '%'"></span>
                                                    </div>
                                                    <div class="w-full bg-slate-200 rounded-full h-1.5">
                                                        <div class="bg-green-500 h-1.5 rounded-full" :style="'width: ' + kelas.today_percentage + '%'"></div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="flex justify-between text-xs text-slate-500 mb-1">
                                                        <span>Bulan Ini</span>
                                                        <span class="font-semibold" x-text="kelas.month_percentage + '%'"></span>
                                                    </div>
                                                    <div class="w-full bg-slate-200 rounded-full h-1.5">
                                                        <div class="bg-blue-500 h-1.5 rounded-full" :style="'width: ' + kelas.month_percentage + '%'"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                @endforeach
                
            </div>

            <!-- Slider Navigation Dots (Manual Navigation) -->
            <div class="flex justify-center mt-6 gap-2">
                @foreach([7, 8, 9] as $index => $grade)
                    <button @click="activeSlide = {{ $index }}; selectedAngkatan = '{{ $grade }}'" 
                            class="w-3 h-3 rounded-full transition-all duration-300"
                            :class="activeSlide === {{ $index }} ? 'bg-blue-600 w-6' : 'bg-slate-300 hover:bg-slate-400'"></button>
                @endforeach
            </div>
            
            <style>
                .custom-scrollbar::-webkit-scrollbar { width: 6px; }
                .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
                .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
            </style>
        </div>

    </div>
</div>

<div class="space-y-6 pb-12" 
     x-data="{
        charts: {},
        chartData: @js($chartData),
        init() {
            this.$nextTick(() => {
                this.renderAllCharts(this.chartData);
            });
            $wire.on('analitik-data-updated', (data) => {
                const newPayload = Array.isArray(data) ? data[0] : data;
                this.chartData = newPayload;
                this.updateAllCharts(newPayload);
            });
        },
        renderAllCharts(data) {
            if (!window.Chart) return;

            // 1. Line Chart: Tren Bulanan
            const ctxMonthly = document.getElementById('chartMonthly');
            if (ctxMonthly) {
                if (this.charts.monthly) this.charts.monthly.destroy();
                this.charts.monthly = new Chart(ctxMonthly, {
                    type: 'line',
                    data: {
                        labels: data.monthly.labels,
                        datasets: [
                            {
                                label: 'Darurat',
                                data: data.monthly.darurat,
                                borderColor: '#e11d48',
                                backgroundColor: 'rgba(225, 29, 72, 0.1)',
                                borderWidth: 2.5,
                                fill: true,
                                tension: 0.35,
                                pointBackgroundColor: '#e11d48',
                            },
                            {
                                label: 'Biasa',
                                data: data.monthly.biasa,
                                borderColor: '#0284c7',
                                backgroundColor: 'rgba(2, 132, 199, 0.08)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.35,
                                pointBackgroundColor: '#0284c7',
                            },
                            {
                                label: 'Total',
                                data: data.monthly.total,
                                borderColor: '#10b981',
                                borderDash: [4, 4],
                                borderWidth: 1.5,
                                fill: false,
                                tension: 0.3,
                                pointRadius: 3,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top', labels: { boxWidth: 12, font: { size: 12, family: 'Inter' } } },
                            tooltip: { mode: 'index', intersect: false }
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0, stepSize: 1 } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // 2. Doughnut Chart: Komposisi Jenis
            const ctxKategori = document.getElementById('chartKategori');
            if (ctxKategori) {
                if (this.charts.kategori) this.charts.kategori.destroy();
                this.charts.kategori = new Chart(ctxKategori, {
                    type: 'doughnut',
                    data: {
                        labels: data.kategori.labels,
                        datasets: [{
                            data: data.kategori.data,
                            backgroundColor: ['#ef4444', '#f59e0b', '#8b5cf6', '#06b6d4', '#9ca3af'],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } }
                        },
                        cutout: '68%'
                    }
                });
            }

            // 3. Bar Chart: Titik Lokasi Rawan
            const ctxLokasi = document.getElementById('chartLokasi');
            if (ctxLokasi) {
                if (this.charts.lokasi) this.charts.lokasi.destroy();
                this.charts.lokasi = new Chart(ctxLokasi, {
                    type: 'bar',
                    data: {
                        labels: data.lokasi.labels,
                        datasets: [{
                            label: 'Jumlah Insiden',
                            data: data.lokasi.data,
                            backgroundColor: '#f43f5e',
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: { beginAtZero: true, ticks: { precision: 0, stepSize: 1 } },
                            y: { grid: { display: false } }
                        }
                    }
                });
            }

            // 4. Bar Chart: Jam / Waktu Rawan
            const ctxWaktu = document.getElementById('chartWaktu');
            if (ctxWaktu) {
                if (this.charts.waktu) this.charts.waktu.destroy();
                this.charts.waktu = new Chart(ctxWaktu, {
                    type: 'bar',
                    data: {
                        labels: data.waktu.labels,
                        datasets: [{
                            label: 'Jumlah Laporan',
                            data: data.waktu.data,
                            backgroundColor: '#6366f1',
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0, stepSize: 1 } },
                            x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                        }
                    }
                });
            }
        },
        updateAllCharts(data) {
            if (this.charts.monthly) {
                this.charts.monthly.data.labels = data.monthly.labels;
                this.charts.monthly.data.datasets[0].data = data.monthly.darurat;
                this.charts.monthly.data.datasets[1].data = data.monthly.biasa;
                this.charts.monthly.data.datasets[2].data = data.monthly.total;
                this.charts.monthly.update();
            }
            if (this.charts.kategori) {
                this.charts.kategori.data.labels = data.kategori.labels;
                this.charts.kategori.data.datasets[0].data = data.kategori.data;
                this.charts.kategori.update();
            }
            if (this.charts.lokasi) {
                this.charts.lokasi.data.labels = data.lokasi.labels;
                this.charts.lokasi.data.datasets[0].data = data.lokasi.data;
                this.charts.lokasi.update();
            }
            if (this.charts.waktu) {
                this.charts.waktu.data.labels = data.waktu.labels;
                this.charts.waktu.data.datasets[0].data = data.waktu.data;
                this.charts.waktu.update();
            }
        }
     }">

    {{-- ── Navigasi Tab & Header ───────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-indigo-100 text-indigo-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Dashboard Analitik Pola Perundungan</h1>
                    <p class="text-slate-500 text-sm">Pemetaan tren insiden, kategori kasus, titik rawan, dan rekomendasi preventif</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('portal-guru.spikap') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-sm transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                </svg>
                Kembali ke Inbox
            </a>
        </div>
    </div>

    {{-- ── Filter Bar ─────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Filter Analisis:</span>
            <div wire:loading class="text-xs text-indigo-600 font-medium animate-pulse flex items-center gap-1">
                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                Memperbarui grafik...
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            {{-- Filter Sifat --}}
            <select wire:model.live="sifatFilter" class="text-xs font-semibold rounded-xl border-slate-200 bg-slate-50 text-slate-700 py-2 px-3 focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Sifat Kasus</option>
                <option value="biasa">Hanya Biasa</option>
                <option value="darurat">🚨 Hanya Darurat</option>
            </select>

            {{-- Filter Periode --}}
            <select wire:model.live="periode" class="text-xs font-semibold rounded-xl border-slate-200 bg-slate-50 text-slate-700 py-2 px-3 focus:ring-2 focus:ring-indigo-500">
                <option value="30_hari">30 Hari Terakhir</option>
                <option value="3_bulan">3 Bulan Terakhir</option>
                <option value="6_bulan">6 Bulan Terakhir</option>
                <option value="1_tahun">1 Tahun Terakhir</option>
                <option value="semua">Semua Waktu</option>
            </select>
        </div>
    </div>

    {{-- ── Kartu Ringkasan KPI ─────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Kasus</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-0.5">{{ $metrics['total'] }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Biasa: {{ $metrics['biasa'] }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border {{ $metrics['darurat'] > 0 ? 'border-rose-300 bg-rose-50/20 ring-2 ring-rose-500/20' : 'border-slate-200/80' }} shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl {{ $metrics['darurat'] > 0 ? 'bg-rose-500 text-white animate-pulse' : 'bg-rose-100 text-rose-600' }} flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-rose-600 uppercase tracking-wider">Laporan Darurat</p>
                <p class="text-2xl font-extrabold text-rose-700 mt-0.5">{{ $metrics['darurat'] }}</p>
                <p class="text-[11px] text-rose-500 mt-0.5">Eskalasi Cepat WA</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Tingkat Selesai</p>
                <p class="text-2xl font-extrabold text-emerald-700 mt-0.5">{{ $metrics['persen_selesai'] }}%</p>
                <p class="text-[11px] text-emerald-600/80 mt-0.5">{{ $metrics['selesai'] }} dari {{ $metrics['total'] }} kasus</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Dalam Proses</p>
                <p class="text-2xl font-extrabold text-amber-700 mt-0.5">{{ $metrics['proses'] }}</p>
                <p class="text-[11px] text-amber-600/80 mt-0.5">Butuh tindak lanjut</p>
            </div>
        </div>
    </div>

    {{-- ── Grid Grafik Utama ───────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Chart 1: Tren Bulanan (2 Cols) --}}
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900">Tren Kasus 6 Bulan Terakhir</h3>
                    <p class="text-xs text-slate-500">Pergerakan frekuensi laporan masuk bulanan</p>
                </div>
            </div>
            <div class="mt-4 relative h-72">
                <canvas id="chartMonthly"></canvas>
            </div>
        </div>

        {{-- Chart 2: Komposisi Jenis (1 Col) --}}
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900">Kategori Perundungan</h3>
                    <p class="text-xs text-slate-500">Proporsi jenis aduan siswa</p>
                </div>
            </div>
            <div class="mt-4 relative h-72">
                <canvas id="chartKategori"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Grid Grafik Sekunder: Lokasi & Waktu ─────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Chart 3: Peta Lokasi Rawan --}}
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900">Top 5 Lokasi Rawan Insiden</h3>
                    <p class="text-xs text-slate-500">Tempat paling sering disebutkan dalam laporan</p>
                </div>
            </div>
            <div class="mt-4 relative h-64">
                <canvas id="chartLokasi"></canvas>
            </div>
        </div>

        {{-- Chart 4: Jam / Waktu Rawan --}}
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900">Waktu & Jam Rawan Kejadian</h3>
                    <p class="text-xs text-slate-500">Frekuensi insiden berdasarkan perkiraan waktu</p>
                </div>
            </div>
            <div class="mt-4 relative h-64">
                <canvas id="chartWaktu"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Smart Insights & Rekomendasi Preventif ─────────────────────── --}}
    <div class="bg-gradient-to-br from-indigo-900 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl">
        <div class="flex items-start gap-4">
            <div class="p-3 rounded-2xl bg-white/10 text-amber-300 shrink-0 backdrop-blur-md">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
            </div>
            <div class="space-y-3 flex-1">
                <h2 class="text-xl font-bold tracking-tight text-white flex items-center gap-2">
                    Insight Analitik & Rekomendasi Preventif
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-amber-400/20 text-amber-300 border border-amber-400/30">Otomatis</span>
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                    {{-- Rekomendasi Kategori --}}
                    <div class="bg-white/5 rounded-2xl p-4 border border-white/10 backdrop-blur-sm">
                        <div class="text-xs font-semibold text-indigo-300 uppercase tracking-wider">Fokus Bimbingan</div>
                        @if($chartData['insights']['topKategori'])
                            <p class="text-sm text-slate-200 mt-1">
                                Perundungan jenis <strong class="text-amber-300">{{ $chartData['insights']['topKategori'] }}</strong> mendominasi laporan. Disarankan memperbanyak sesi sosialisasi dan bimbingan klasikal mengenai empati & anti-perundungan jenis ini.
                            </p>
                        @else
                            <p class="text-sm text-slate-400 mt-1">Belum cukup data laporan untuk mengidentifikasi pola jenis kasus.</p>
                        @endif
                    </div>

                    {{-- Rekomendasi Lokasi --}}
                    <div class="bg-white/5 rounded-2xl p-4 border border-white/10 backdrop-blur-sm">
                        <div class="text-xs font-semibold text-rose-300 uppercase tracking-wider">Patroli Pengawasan</div>
                        @if($chartData['insights']['topLokasi'])
                            <p class="text-sm text-slate-200 mt-1">
                                Titik <strong class="text-rose-300">"{{ $chartData['insights']['topLokasi'] }}"</strong> terdeteksi sebagai lokasi paling rawan ({{ $chartData['insights']['topLokasiCount'] }} insiden). Disarankan meningkatkan rotasi piket guru di area tersebut.
                            </p>
                        @else
                            <p class="text-sm text-slate-400 mt-1">Belum ada lokasi spesifik yang menonjol dari data laporan saat ini.</p>
                        @endif
                    </div>

                    {{-- Rekomendasi Alur Penanganan --}}
                    <div class="bg-white/5 rounded-2xl p-4 border border-white/10 backdrop-blur-sm">
                        <div class="text-xs font-semibold text-emerald-300 uppercase tracking-wider">Evaluasi Tindak Lanjut</div>
                        <p class="text-sm text-slate-200 mt-1">
                            Dari total laporan masuk, <strong class="text-emerald-300">{{ $metrics['persen_selesai'] }}%</strong> telah berstatus selesai. Pastikan kasus yang berstatus "Dalam Investigasi" segera dievaluasi bersama Guru BK & Wali Kelas.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

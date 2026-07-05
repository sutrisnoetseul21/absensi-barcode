<div class="absensi-container space-y-6">
    <style>
        .absensi-container {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .absensi-card {
            background-color: #18181b; /* dark mode zinc-900 */
            border: 1px solid #27272a; /* zinc-800 */
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
        }
        .light .absensi-card, [data-theme="light"] .absensi-card {
            background-color: #ffffff;
            border-color: #e4e4e7;
        }
        .absensi-header-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #f4f4f5;
        }
        .light .absensi-header-title, [data-theme="light"] .absensi-header-title {
            color: #18181b;
        }
        .absensi-header-desc {
            font-size: 0.875rem;
            color: #a1a1aa;
            margin-top: 0.25rem;
        }
        .light .absensi-header-desc, [data-theme="light"] .absensi-header-desc {
            color: #71717a;
        }
        .absensi-grid-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
        }
        @media (min-width: 768px) {
            .absensi-grid-filters {
                margin-top: 0;
            }
        }
        .absensi-filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .absensi-filter-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #a1a1aa;
        }
        .light .absensi-filter-label, [data-theme="light"] .absensi-filter-label {
            color: #71717a;
        }
        .absensi-select {
            background-color: #27272a;
            color: #f4f4f5;
            border: 1px solid #3f3f46;
            border-radius: 0.5rem;
            padding: 0.5rem 2rem 0.5rem 0.75rem;
            font-size: 0.875rem;
            outline: none;
            min-width: 140px;
            cursor: pointer;
            appearance: auto;
        }
        .light .absensi-select, [data-theme="light"] .absensi-select {
            background-color: #f4f4f5;
            color: #18181b;
            border-color: #d4d4d8;
        }
        .absensi-select:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
        }
        .absensi-alert {
            display: flex;
            gap: 0.75rem;
            background-color: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: #f59e0b;
            border-radius: 0.75rem;
            padding: 1rem;
            margin-top: 1.5rem;
        }
        .absensi-alert-title {
            font-weight: 700;
            font-size: 0.95rem;
        }
        .absensi-alert-desc {
            font-size: 0.875rem;
            margin-top: 0.25rem;
            color: rgba(245, 158, 11, 0.9);
        }
        .absensi-table-container {
            background-color: #18181b;
            border: 1px solid #27272a;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            margin-top: 1.5rem;
        }
        .light .absensi-table-container, [data-theme="light"] .absensi-table-container {
            background-color: #ffffff;
            border-color: #e4e4e7;
        }
        .absensi-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .absensi-table th {
            background-color: #27272a;
            color: #a1a1aa;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #3f3f46;
        }
        .light .absensi-table th, [data-theme="light"] .absensi-table th {
            background-color: #f4f4f5;
            color: #71717a;
            border-color: #e4e4e7;
        }
        .absensi-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #27272a;
            font-size: 0.875rem;
            color: #e4e4e7;
            vertical-align: middle;
        }
        .light .absensi-table td, [data-theme="light"] .absensi-table td {
            border-color: #e4e4e7;
            color: #27272a;
        }
        .absensi-table tr:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }
        .light .absensi-table tr:hover, [data-theme="light"] .absensi-table tr:hover {
            background-color: rgba(0, 0, 0, 0.01);
        }
        .absensi-table tr.row-warning {
            background-color: rgba(239, 68, 68, 0.08);
        }
        .absensi-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: 0.5rem;
            border: 1px solid #f59e0b;
            color: #f59e0b;
            background-color: transparent;
            cursor: pointer;
            transition: all 0.2s;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .absensi-btn:hover {
            background-color: #f59e0b;
            color: #18181b;
        }
        .badge-warning {
            display: inline-flex;
            align-items: center;
            padding: 0.125rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.65rem;
            font-weight: 600;
            background-color: rgba(239, 68, 68, 0.2);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
            margin-top: 0.25rem;
            width: fit-content;
        }
        .student-name {
            font-weight: 600;
            color: #ffffff;
            font-size: 0.9rem;
        }
        .light .student-name, [data-theme="light"] .student-name {
            color: #18181b;
        }

        /* Calendar View Styles */
        .absensi-table th.date-col {
            padding: 0.75rem 0.5rem;
            text-align: center;
            min-width: 35px;
        }
        .absensi-table td.date-col {
            padding: 0.75rem 0.5rem;
            text-align: center;
            font-weight: 700;
        }
        .text-hadir { color: #10b981; }
        .text-telat { color: #f59e0b; }
        .text-izin { color: #3b82f6; }
        .text-sakit { color: #6366f1; }
        .text-alpa { color: #ef4444; }
        .text-belum { color: #71717a; font-weight: normal !important; }
        
        /* Modal Styles */
        .absensi-modal-backdrop {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 50;
            backdrop-filter: blur(4px);
        }
        .absensi-modal {
            background-color: #18181b;
            border: 1px solid #27272a;
            border-radius: 1rem;
            width: 100%;
            max-width: 48rem;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }
        .light .absensi-modal, [data-theme="light"] .absensi-modal {
            background-color: #ffffff;
            border-color: #e4e4e7;
        }
        .absensi-modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #27272a;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .light .absensi-modal-header, [data-theme="light"] .absensi-modal-header {
            border-color: #e4e4e7;
        }
        .absensi-modal-body {
            padding: 1.5rem;
            overflow-y: auto;
        }
        .absensi-modal-footer {
            padding: 1.25rem 1.5rem;
            border-top: 1px solid #27272a;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }
        .light .absensi-modal-footer, [data-theme="light"] .absensi-modal-footer {
            border-color: #e4e4e7;
        }
        .btn-primary {
            background-color: #f59e0b;
            color: #18181b;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            border: none;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn-primary:hover { opacity: 0.9; }
        .btn-secondary {
            background-color: transparent;
            color: #f4f4f5;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            border: 1px solid #3f3f46;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-secondary:hover { background-color: rgba(255, 255, 255, 0.05); }
        .light .btn-secondary, [data-theme="light"] .btn-secondary {
            color: #18181b;
            border-color: #d4d4d8;
        }
        .light .btn-secondary:hover, [data-theme="light"] .btn-secondary:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        /* Stats Grid Styles */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1.25rem;
            margin-top: 1.5rem;
        }
        @media (min-width: 640px) {
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
        .stat-card {
            background-color: #18181b;
            border: 1px solid #27272a;
            border-radius: 0.75rem;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }
        .light .stat-card, [data-theme="light"] .stat-card {
            background-color: #ffffff;
            border-color: #e4e4e7;
        }
        .stat-icon {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .icon-hadir { background-color: rgba(16, 185, 129, 0.15); color: #10b981; }
        .icon-telat { background-color: rgba(245, 158, 11, 0.15); color: #f59e0b; }
        .icon-absen { background-color: rgba(239, 68, 68, 0.15); color: #ef4444; }
        .icon-belum { background-color: rgba(148, 163, 184, 0.15); color: #94a3b8; }
        
        .stat-content {
            display: flex;
            flex-direction: column;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.25;
        }
        .light .stat-value, [data-theme="light"] .stat-value {
            color: #18181b;
        }
        .stat-label {
            font-size: 0.7rem;
            font-weight: 700;
            color: #a1a1aa;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .light .stat-label, [data-theme="light"] .stat-label {
            color: #71717a;
        }
    </style>

    <!-- Header Card -->
    <div class="absensi-card">
        <div style="display: flex; flex-direction: column; justify-content: space-between; gap: 1rem; align-items: stretch;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                <div style="flex: 1;">
                    <h2 class="absensi-header-title">Rekapitulasi Absensi Kelas</h2>
                    <p class="absensi-header-desc">Kelola absensi siswa untuk kelas yang Anda ampu</p>
                </div>
                
                <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                    @if(count($classes) > 0 && $selectedClassId)
                        <button wire:click="openInputModal" class="btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            <svg style="width: 1.25rem; height: 1.25rem;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Input Absen Manual
                        </button>
                    @endif

                    @if(request()->routeIs('wali-kelas.*'))
                        <form action="{{ route('wali-kelas.logout') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background-color: #ef4444; color: #fff; font-size: 0.875rem; font-weight: 600; border: none; border-radius: 0.5rem; cursor: pointer; transition: background-color 0.2s;"
                                onmouseover="this.style.backgroundColor='#dc2626'"
                                onmouseout="this.style.backgroundColor='#ef4444'">
                                <svg style="width: 1rem; height: 1rem;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Keluar
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            
            <div class="absensi-grid-filters" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <div class="absensi-filter-group">
                    <label class="absensi-filter-label">Tahun Ajaran</label>
                    <select wire:model.change="selectedAcademicYearId" class="absensi-select">
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if(count($classes) > 0)
                    <div class="absensi-filter-group">
                        <label class="absensi-filter-label">Pilih Kelas</label>
                        <select wire:model.change="selectedClassId" class="absensi-select">
                            @foreach($classes as $kelas)
                                <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                
                <div class="absensi-filter-group">
                    <label class="absensi-filter-label">Bulan</label>
                    <select wire:model.change="selectedMonth" class="absensi-select">
                        @foreach([
                            '07' => 'Juli', '08' => 'Agustus', '09' => 'September', 
                            '10' => 'Oktober', '11' => 'November', '12' => 'Desember', 
                            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', 
                            '04' => 'April', '05' => 'Mei', '06' => 'Juni'
                        ] as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Stats Row -->
    @if(count($classes) > 0 && $selectedClassId && !empty($todayStats))
        <div class="stats-grid">
            <!-- Hadir -->
            <div class="stat-card">
                <div class="stat-icon icon-hadir">
                    <svg style="width: 1.5rem; height: 1.5rem;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Hadir Hari Ini</span>
                    <span class="stat-value">{{ $todayStats['hadir'] }} / {{ $todayStats['total'] }} <span style="font-size: 0.9rem; font-weight: 500; color: #a1a1aa;">({{ $todayStats['persentase_hadir'] }}%)</span></span>
                </div>
            </div>

            <!-- Telat -->
            <div class="stat-card">
                <div class="stat-icon icon-telat">
                    <svg style="width: 1.5rem; height: 1.5rem;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Terlambat</span>
                    <span class="stat-value">{{ $todayStats['telat'] }} Siswa</span>
                </div>
            </div>

            <!-- Absen (Sakit/Izin/Alpa) -->
            <div class="stat-card">
                <div class="stat-icon icon-absen">
                    <svg style="width: 1.5rem; height: 1.5rem;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Sakit / Izin / Alpa</span>
                    <span class="stat-value" style="color: #f87171;">{{ $todayStats['sakit'] + $todayStats['izin'] + $todayStats['alpa'] }} Siswa</span>
                </div>
            </div>

            <!-- Belum Absen -->
            <div class="stat-card">
                <div class="stat-icon icon-belum">
                    <svg style="width: 1.5rem; height: 1.5rem;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Belum Absen</span>
                    <span class="stat-value">{{ $todayStats['belum'] }} Siswa</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Alert / Empty State -->
    @if(!$selectedAcademicYearId)
        <div class="absensi-alert">
            <div style="margin-top: 0.15rem;">
                <svg style="width: 1.25rem; height: 1.25rem;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </div>
            <div>
                <div class="absensi-alert-title">Tahun Ajaran Belum Aktif</div>
                <p class="absensi-alert-desc">Sistem mendeteksi belum ada Tahun Ajaran yang diatur menjadi <strong>Aktif</strong>. Silakan buat atau aktifkan Tahun Ajaran terlebih dahulu di menu Data Master.</p>
            </div>
        </div>
    @elseif(!$selectedClassId)
        <div class="absensi-alert">
            <div style="margin-top: 0.15rem;">
                <svg style="width: 1.25rem; height: 1.25rem;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 111.063.854l-.018.04 -2.7 4.675a.75.75 0 01-1.082.268l-.042-.027a.75.75 0 01-.268-1.082l2.7-4.675zm0-2.625a.75.75 0 111.5 0 .75.75 0 01-1.5 0z" />
                </svg>
            </div>
            <div>
                <div class="absensi-alert-title">Tidak Ada Kelas yang Diampu</div>
                <p class="absensi-alert-desc">Anda tidak terdaftar mengampu kelas manapun pada tahun ajaran aktif ini.</p>
            </div>
        </div>
    @else
        <!-- Table Container -->
        <div class="absensi-table-container">
            <div style="overflow-x: auto;">
                <table class="absensi-table">
                    <thead>
                        <tr>
                            <th style="text-align: left; min-width: 150px; position: sticky; left: 0; background-color: inherit; z-index: 10;">Nama Siswa</th>
                            @for($i = 1; $i <= $daysInMonth; $i++)
                                <th class="date-col">{{ $i }}</th>
                            @endfor
                            <th style="text-align: center; color: #10b981;">H</th>
                            <th style="text-align: center; color: #f59e0b;">T</th>
                            <th style="text-align: center; color: #3b82f6;">I</th>
                            <th style="text-align: center; color: #6366f1;">S</th>
                            <th style="text-align: center; color: #ef4444;">A</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            @php
                                $isAlpaWarning = in_array($student->id, $alerts['alpa'] ?? []);
                                $isTelatWarning = in_array($student->id, $alerts['telat'] ?? []);
                                $rowClass = ($isAlpaWarning || $isTelatWarning) ? 'row-warning' : '';
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td style="position: sticky; left: 0; background-color: inherit; z-index: 10;">
                                    <div style="display: flex; flex-direction: column;">
                                        <span class="student-name">{{ $student->name }}</span>
                                        @if($isAlpaWarning)
                                            <span class="badge-warning">⚠️ &gt;= 3 Alpa</span>
                                        @endif
                                        @if($isTelatWarning)
                                            <span class="badge-warning">⚠️ &gt;= 100mnt telat</span>
                                        @endif
                                    </div>
                                </td>
                                @for($i = 1; $i <= $daysInMonth; $i++)
                                    @php
                                        $code = $monthlyStats[$student->id]['daily'][$i] ?? '-';
                                        $colorClass = match($code) {
                                            'H' => 'text-hadir',
                                            'T' => 'text-telat',
                                            'I' => 'text-izin',
                                            'S' => 'text-sakit',
                                            'A' => 'text-alpa',
                                            default => 'text-belum',
                                        };
                                    @endphp
                                    <td class="date-col {{ $colorClass }}">{{ $code }}</td>
                                @endfor
                                <td style="text-align: center; font-weight: 700; color: #10b981;">{{ $monthlyStats[$student->id]['hadir'] ?? 0 }}</td>
                                <td style="text-align: center; font-weight: 700; color: #f59e0b;">{{ $monthlyStats[$student->id]['telat'] ?? 0 }}</td>
                                <td style="text-align: center; font-weight: 700; color: #3b82f6;">{{ $monthlyStats[$student->id]['izin'] ?? 0 }}</td>
                                <td style="text-align: center; font-weight: 700; color: #6366f1;">{{ $monthlyStats[$student->id]['sakit'] ?? 0 }}</td>
                                <td style="text-align: center; font-weight: 700; color: #ef4444;">{{ $monthlyStats[$student->id]['alpa'] ?? 0 }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $daysInMonth + 6 }}" style="text-align: center; color: #a1a1aa; padding: 2rem 0;">
                                    Tidak ada data siswa terdaftar di kelas ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

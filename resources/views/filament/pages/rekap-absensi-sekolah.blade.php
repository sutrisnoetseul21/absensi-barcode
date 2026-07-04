<x-filament-panels::page>
    <div class="space-y-6 absensi-sekolah-container">
        <style>
            .absensi-sekolah-container {
                font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            }
            .sekolah-card {
                background-color: #18181b; /* dark mode zinc-900 */
                border: 1px solid #27272a; /* zinc-800 */
                border-radius: 0.75rem;
                padding: 1.5rem;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            }
            .light .sekolah-card, [data-theme="light"] .sekolah-card {
                background-color: #ffffff;
                border-color: #e4e4e7;
            }
            .sekolah-select {
                background-color: #27272a;
                color: #f4f4f5;
                border: 1px solid #3f3f46;
                border-radius: 0.5rem;
                padding: 0.5rem 2.5rem 0.5rem 1rem;
                font-size: 0.875rem;
                outline: none;
                cursor: pointer;
                appearance: auto;
                min-width: 200px;
            }
            .light .sekolah-select, [data-theme="light"] .sekolah-select {
                background-color: #f4f4f5;
                color: #18181b;
                border-color: #d4d4d8;
            }
            .sekolah-select:focus {
                border-color: #eab308;
                box-shadow: 0 0 0 2px rgba(234, 179, 8, 0.2);
            }
            .sekolah-legend {
                display: flex;
                flex-wrap: wrap;
                gap: 1.5rem;
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid #27272a;
                font-size: 0.8rem;
            }
            .light .sekolah-legend, [data-theme="light"] .sekolah-legend {
                border-color: #e4e4e7;
            }
            .legend-item {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-weight: 600;
            }
            .legend-dot {
                width: 0.65rem;
                height: 0.65rem;
                border-radius: 50%;
            }
            .dot-h { background-color: #10b981; }
            .dot-s { background-color: #6366f1; }
            .dot-i { background-color: #3b82f6; }
            .dot-a { background-color: #ef4444; }

            .sekolah-table-container {
                background-color: #18181b;
                border: 1px solid #27272a;
                border-radius: 0.75rem;
                overflow: hidden;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            }
            .light .sekolah-table-container, [data-theme="light"] .sekolah-table-container {
                background-color: #ffffff;
                border-color: #e4e4e7;
            }
            .sekolah-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.825rem;
        }
        .sekolah-table th {
            background-color: #27272a;
            color: #a1a1aa;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid #3f3f46;
            border-right: 1px solid #3f3f46;
            text-align: center;
            white-space: nowrap;
        }
        .light .sekolah-table th, [data-theme="light"] .sekolah-table th {
            background-color: #f4f4f5;
            color: #71717a;
            border-color: #e4e4e7;
        }
        /* Border tebal pemisah antar bulan */
        .sekolah-table th.month-header {
            border-bottom: 2px solid #3f3f46;
            font-size: 0.75rem;
        }
        .light .sekolah-table th.month-header, [data-theme="light"] .sekolah-table th.month-header {
            border-bottom-color: #d4d4d8;
        }
        .sekolah-table td {
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid #27272a;
            border-right: 1px solid #27272a;
            color: #e4e4e7;
            text-align: center;
        }
        .light .sekolah-table td, [data-theme="light"] .sekolah-table td {
            border-color: #e4e4e7;
            color: #27272a;
        }
        .sekolah-table tr:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }
        .light .sekolah-table tr:hover, [data-theme="light"] .sekolah-table tr:hover {
            background-color: rgba(0, 0, 0, 0.01);
        }
        /* Pemisah visual antar bulan pada kolom data */
        .sekolah-table td.month-end, .sekolah-table th.month-end {
            border-right: 2px solid #3f3f46;
        }
        .light .sekolah-table td.month-end, .light .sekolah-table th.month-end,
        [data-theme="light"] .sekolah-table td.month-end, [data-theme="light"] .sekolah-table th.month-end {
            border-right-color: #d4d4d8;
        }
        
        .stat-h { color: #10b981; font-weight: 700; }
        .stat-s { color: #6366f1; font-weight: 700; }
        .stat-i { color: #3b82f6; font-weight: 700; }
        .stat-a { color: #ef4444; font-weight: 700; }
        </style>

        <!-- Filter Card -->
        <div class="sekolah-card">
            <div style="display: flex; flex-direction: column; md:flex-row justify-content: space-between; align-items: flex-start; md:items-center gap-4; flex-wrap: wrap;">
                <div>
                    <h3 style="font-size: 1.1rem; font-weight: 700;" class="text-zinc-900 dark:text-zinc-100">Filter Tahun Ajaran</h3>
                    <p style="font-size: 0.85rem;" class="text-zinc-500 dark:text-zinc-400 mt-1">Pilih Tahun Ajaran untuk memuat rekap presensi seluruh kelas secara tahunan.</p>
                </div>
                
                <div>
                    <select wire:model.change="selectedAcademicYearId" class="sekolah-select">
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Legend Section -->
            <div class="sekolah-legend">
                <div class="legend-item text-zinc-500 dark:text-zinc-400">
                    <div class="legend-dot dot-h"></div>
                    Hadir (H)
                </div>
                <div class="legend-item text-zinc-500 dark:text-zinc-400">
                    <div class="legend-dot dot-s"></div>
                    Sakit (S)
                </div>
                <div class="legend-item text-zinc-500 dark:text-zinc-400">
                    <div class="legend-dot dot-i"></div>
                    Izin (I)
                </div>
                <div class="legend-item text-zinc-500 dark:text-zinc-400">
                    <div class="legend-dot dot-a"></div>
                    Alpa (A)
                </div>
            </div>
        </div>

        @if(!$selectedAcademicYearId)
            <div style="background-color: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); color: #f59e0b; border-radius: 0.75rem; padding: 1.5rem; display: flex; gap: 0.75rem;">
                <svg style="width: 1.5rem; height: 1.5rem;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <div>
                    <h4 style="font-weight: 700;">Tahun Ajaran Belum Ditentukan</h4>
                    <p style="font-size: 0.875rem; margin-top: 0.25rem;">Silakan buat dan aktifkan tahun ajaran baru terlebih dahulu.</p>
                </div>
            </div>
        @else
            <!-- Matrix Table -->
            <div class="sekolah-table-container">
                <div style="overflow-x: auto;">
                    <table class="sekolah-table">
                        <thead>
                            <!-- Row 1: Bulan-bulan (Colspan 4) -->
                            <tr>
                                <th rowspan="2" style="width: 50px; text-align: center; border-bottom: 2px solid #3f3f46;">No</th>
                                <th rowspan="2" style="text-align: left; border-bottom: 2px solid #3f3f46;">Kelas</th>
                                <th rowspan="2" style="text-align: center; width: 90px; border-bottom: 2px solid #3f3f46;" class="month-end">Jml Siswa</th>
                                @foreach($monthsList as $m)
                                    <th colspan="4" class="month-header month-end">{{ $m['label'] }}</th>
                                @endforeach
                            </tr>
                            <!-- Row 2: Sub-kolom H, S, I, A -->
                            <tr>
                                @foreach($monthsList as $m)
                                    <th class="stat-h" style="width: 32px;">H</th>
                                    <th class="stat-s" style="width: 32px;">S</th>
                                    <th class="stat-i" style="width: 32px;">I</th>
                                    <th class="stat-a month-end" style="width: 32px;">A</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($classesData as $index => $row)
                                <tr>
                                    <td style="text-align: center; font-weight: 600;" class="text-zinc-500 dark:text-zinc-400">
                                        {{ $index + 1 }}
                                    </td>
                                    <td style="font-weight: 700; text-align: left;" class="text-zinc-950 dark:text-zinc-100">
                                        {{ $row['name'] }}
                                    </td>
                                    <td style="text-align: center; font-weight: 600;" class="text-zinc-900 dark:text-zinc-100 month-end">
                                        {{ $row['student_count'] }}
                                    </td>
                                    @foreach($monthsList as $m)
                                        @php
                                            $key = "{$m['year']}-{$m['month']}";
                                            $stats = $row['months'][$key] ?? ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];
                                        @endphp
                                        <td class="stat-h">{{ $stats['hadir'] }}</td>
                                        <td class="stat-s">{{ $stats['sakit'] }}</td>
                                        <td class="stat-i">{{ $stats['izin'] }}</td>
                                        <td class="stat-a month-end">{{ $stats['alpa'] }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 3 + (count($monthsList) * 4) }}" style="text-align: center; padding: 3rem 0; color: #a1a1aa;">
                                        Belum ada data kelas yang terdaftar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>

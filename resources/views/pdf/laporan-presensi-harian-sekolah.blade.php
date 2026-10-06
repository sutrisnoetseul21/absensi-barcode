<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rekap Presensi Harian - {{ $selectedDateFormatted }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 8mm 12mm 8mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5px;
            color: #1e293b;
            margin: 0;
            padding: 0;
            line-height: 1.25;
        }

        /* KOP SURAT DINAS */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .kop-table td {
            vertical-align: middle;
            padding: 0;
        }
        .kop-logo {
            width: 65px;
            text-align: center;
        }
        .kop-logo img {
            max-height: 55px;
            max-width: 55px;
        }
        .kop-text {
            text-align: center;
            padding: 0 10px;
        }
        .kop-instansi {
            font-size: 10px;
            font-weight: bold;
            color: #334155;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
        }
        .kop-sekolah {
            font-size: 14px;
            font-weight: 900;
            color: #1e3a8a; /* Blue Dark */
            margin: 1px 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .kop-alamat {
            font-size: 7.5px;
            color: #475569;
            margin: 1px 0 0 0;
        }
        .kop-line {
            border-top: 2px solid #0f172a;
            border-bottom: 0.5px solid #0f172a;
            height: 3px;
            margin: 4px 0 8px 0;
        }

        /* TITLE SECTION */
        .report-header {
            text-align: center;
            margin-bottom: 8px;
        }
        .report-title {
            font-size: 11px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 2px 0;
        }
        .report-subtitle {
            font-size: 8px;
            font-weight: 600;
            color: #64748b;
            margin: 0;
        }

        /* DATA TABLE */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        .data-table thead tr th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            padding: 5px 3px;
            border: 1px solid #172554;
            text-transform: uppercase;
        }
        .data-table tbody tr td {
            padding: 4px 3px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }
        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .data-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        .data-table tfoot tr td {
            background-color: #e2e8f0;
            font-weight: 900;
            padding: 4px 3px;
            border: 1.5px solid #94a3b8;
        }

        .text-center { text-align: center; }
        .text-left   { text-align: left; }
        .text-right  { text-align: right; }
        .font-bold   { font-weight: bold; }

        /* FOOTER & SIGNATURE */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .signature-table td {
            vertical-align: top;
            padding: 0 15px;
        }
        .ttd-block {
            text-align: center;
            font-size: 8.5px;
        }
        .ttd-space {
            height: 40px;
        }
        .ttd-name {
            font-weight: bold;
            text-decoration: underline;
            font-size: 9px;
        }
        .ttd-nip {
            font-size: 8px;
            color: #64748b;
        }

        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 0.5px solid #cbd5e1;
            padding-top: 2px;
            font-size: 6.5px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- PAGE FOOTER -->
    <div class="page-footer">
        Sistem Presensi Digital &bull; {{ $sekolah?->school_name ?? 'SMP Negeri 3 Kedungreja' }} &bull; Dicetak: {{ $generatedAt }}
    </div>

    <!-- KOP SURAT DINAS -->
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @if($sekolah?->school_logo_path && file_exists(public_path('storage/' . $sekolah->school_logo_path)))
                    <img src="{{ public_path('storage/' . $sekolah->school_logo_path) }}" alt="Logo">
                @elseif(file_exists(public_path('images/logo.png')))
                    <img src="{{ public_path('images/logo.png') }}" alt="Logo">
                @endif
            </td>
            <td class="kop-text">
                <p class="kop-instansi">Pemerintah Kabupaten Cilacap &bull; Dinas Pendidikan dan Kebudayaan</p>
                <p class="kop-sekolah">{{ $sekolah?->school_name ?? 'SMP NEGERI 3 KEDUNGREJA' }}</p>
                <p class="kop-alamat">
                    {{ $sekolah?->school_address ?? 'Jl. Raya Tambaksari, Kedungreja, Kabupaten Cilacap' }}
                    @if($sekolah?->school_phone) &bull; Telp: {{ $sekolah->school_phone }} @endif
                    @if($sekolah?->school_email) &bull; Email: {{ $sekolah->school_email }} @endif
                </p>
            </td>
            <td class="kop-logo" style="text-align: right;">
                @if($sekolah?->district_logo_path && file_exists(public_path('storage/' . $sekolah->district_logo_path)))
                    <img src="{{ public_path('storage/' . $sekolah->district_logo_path) }}" alt="Kabupaten">
                @endif
            </td>
        </tr>
    </table>
    <div class="kop-line"></div>

    <!-- REPORT TITLE -->
    <div class="report-header">
        <h1 class="report-title">LAPORAN REKAPITULASI PRESENSI HARIAN SEKOLAH</h1>
        <p class="report-subtitle">
            Hari / Tanggal: <strong>{{ $selectedDateFormatted }}</strong> &bull; Tahun Ajaran: <strong>{{ $tahunAjaran?->name ?? '2026/2027' }}</strong> &bull; Waktu Data: <strong>{{ date('H:i') }} WIB</strong>
        </p>
    </div>

    <!-- TABLE RINCIAN PER KELAS -->
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 20px;">No</th>
                <th rowspan="2" style="width: 40px;">Kelas</th>
                <th rowspan="2" style="width: 120px; text-align: left; padding-left: 6px;">Wali Kelas</th>
                <th rowspan="2" style="width: 30px;">Total<br>Siswa</th>
                <th colspan="2" style="width: 40px;">Total L/P</th>
                <th colspan="2" style="background-color: #047857;">Siswa Hadir</th>
                <th colspan="2" style="background-color: #9f1239;">Sakit</th>
                <th colspan="2" style="background-color: #9f1239;">Izin</th>
                <th colspan="2" style="background-color: #9f1239;">Alpa</th>
                <th colspan="2">Belum Presensi</th>
                <th colspan="2">% Hadir</th>
                <th rowspan="2" style="width: 60px;">Keterangan</th>
            </tr>
            <tr class="sub-header">
                <!-- Total L/P -->
                <th style="width: 20px;">L</th>
                <th style="width: 20px;">P</th>
                <!-- Hadir -->
                <th style="width: 20px;">L</th>
                <th style="width: 20px;">P</th>
                <!-- Sakit -->
                <th style="width: 20px;">L</th>
                <th style="width: 20px;">P</th>
                <!-- Izin -->
                <th style="width: 20px;">L</th>
                <th style="width: 20px;">P</th>
                <!-- Alpa -->
                <th style="width: 20px;">L</th>
                <th style="width: 20px;">P</th>
                <!-- Belum -->
                <th style="width: 20px;">L</th>
                <th style="width: 20px;">P</th>
                <!-- % -->
                <th style="width: 25px;">L</th>
                <th style="width: 25px;">P</th>
            </tr>
        </thead>
        <tbody>
            @forelse($classesSummary as $index => $row)
            <tr>
                <td class="text-center font-bold">{{ $index + 1 }}</td>
                <td class="text-center font-bold" style="font-size: 8.5px;">{{ $row['name'] }}</td>
                <td class="text-left" style="padding-left: 6px;">{{ $row['wali_kelas'] }}</td>
                <td class="text-center font-bold">{{ $row['total'] }}</td>
                <td class="text-center">{{ $row['total_l'] }}</td>
                <td class="text-center">{{ $row['total_p'] }}</td>
                
                <td class="text-center font-bold" style="color: #047857;">{{ $row['hadir_l'] ?: '-' }}</td>
                <td class="text-center font-bold" style="color: #047857;">{{ $row['hadir_p'] ?: '-' }}</td>
                <td class="text-center" style="color: #6b21a8;">{{ $row['sakit_l'] ?: '-' }}</td>
                <td class="text-center" style="color: #6b21a8;">{{ $row['sakit_p'] ?: '-' }}</td>
                <td class="text-center" style="color: #1d4ed8;">{{ $row['izin_l'] ?: '-' }}</td>
                <td class="text-center" style="color: #1d4ed8;">{{ $row['izin_p'] ?: '-' }}</td>
                <td class="text-center font-bold" style="color: #b91c1c;">{{ $row['alpa_l'] ?: '-' }}</td>
                <td class="text-center font-bold" style="color: #b91c1c;">{{ $row['alpa_p'] ?: '-' }}</td>
                
                <td class="text-center {{ $row['belum_l'] > 0 ? 'font-bold' : '' }}" style="color: {{ $row['belum_l'] > 0 ? '#ea580c' : '#94a3b8' }};">{{ $row['belum_l'] ?: '-' }}</td>
                <td class="text-center {{ $row['belum_p'] > 0 ? 'font-bold' : '' }}" style="color: {{ $row['belum_p'] > 0 ? '#ea580c' : '#94a3b8' }};">{{ $row['belum_p'] ?: '-' }}</td>
                
                <td class="text-center font-bold">{{ $row['persen_l'] }}%</td>
                <td class="text-center font-bold">{{ $row['persen_p'] }}%</td>

                <td class="text-center" style="font-size: 7px; color: #64748b;">
                    @if($row['belum'] > 0)
                        <span style="color: #ea580c; font-weight: bold;">{{ $row['belum'] }} blm absen</span>
                    @elseif($row['total'] == $row['total_hadir'])
                        <span style="color: #047857;">Lengkap (100%)</span>
                    @else
                        <span>{{ $row['total_hadir'] }} terdata</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center" style="padding: 15px; color: #94a3b8;">
                    Tidak ada data kelas aktif pada tahun ajaran ini.
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right font-bold" style="padding-right: 8px;">TOTAL SEKOLAH:</td>
                <td class="text-center">{{ $stats['total_students'] ?? 0 }}</td>
                <td class="text-center">{{ $stats['total_l'] ?? 0 }}</td>
                <td class="text-center">{{ $stats['total_p'] ?? 0 }}</td>
                <td class="text-center" style="color: #047857;">{{ $stats['hadir_l'] ?? 0 }}</td>
                <td class="text-center" style="color: #047857;">{{ $stats['hadir_p'] ?? 0 }}</td>
                <td class="text-center" style="color: #6b21a8;">{{ $stats['sakit_l'] ?? 0 }}</td>
                <td class="text-center" style="color: #6b21a8;">{{ $stats['sakit_p'] ?? 0 }}</td>
                <td class="text-center" style="color: #1d4ed8;">{{ $stats['izin_l'] ?? 0 }}</td>
                <td class="text-center" style="color: #1d4ed8;">{{ $stats['izin_p'] ?? 0 }}</td>
                <td class="text-center" style="color: #b91c1c;">{{ $stats['alpa_l'] ?? 0 }}</td>
                <td class="text-center" style="color: #b91c1c;">{{ $stats['alpa_p'] ?? 0 }}</td>
                <td class="text-center" style="color: {{ ($stats['belum_l'] ?? 0) > 0 ? '#ea580c' : '#64748b' }};">{{ $stats['belum_l'] ?? 0 }}</td>
                <td class="text-center" style="color: {{ ($stats['belum_p'] ?? 0) > 0 ? '#ea580c' : '#64748b' }};">{{ $stats['belum_p'] ?? 0 }}</td>
                <td class="text-center">{{ $stats['persen_l'] ?? 0 }}%</td>
                <td class="text-center">{{ $stats['persen_p'] ?? 0 }}%</td>
                <td class="text-center" style="font-size: 8px; background-color: #ecfdf5; color: #065f46;">
                    <strong>{{ $stats['total_hadir'] ?? 0 }} Hadir</strong>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- SIGNATURES -->
    <table class="signature-table">
        <tr>
            <td style="width: 60%;">
                <p style="font-size: 7.5px; color: #64748b;">
                    <strong>Keterangan:</strong><br>
                    Data kehadiran divalidasi melalui sistem barcode scanning presensi digital sekolah.<br>
                    Siswa Hadir = Hadir tepat waktu + Terlambat.
                </p>
            </td>

            <!-- TTD Petugas Presensi -->
            <td style="width: 20%; text-align: center;">
                <div class="ttd-block">
                    Petugas Presensi,
                    <div class="ttd-space"></div>
                    <div class="ttd-name">__________________________</div>
                    <div class="ttd-nip">Admin Presensi</div>
                </div>
            </td>

            <!-- TTD Kepala Sekolah -->
            <td style="width: 20%; text-align: center;">
                <div class="ttd-block">
                    Mengetahui,<br>Kepala Sekolah
                    <div class="ttd-space">
                        @if($sekolah?->principal_signature_path && file_exists(public_path('storage/' . $sekolah->principal_signature_path)))
                            <img src="{{ public_path('storage/' . $sekolah->principal_signature_path) }}" style="height: 35px;" alt="TTD">
                        @endif
                    </div>
                    <div class="ttd-name">{{ $sekolah?->principal_name ?? 'Kepala Sekolah' }}</div>
                    <div class="ttd-nip">NIP. {{ $sekolah?->principal_nip ?? '-' }}</div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>

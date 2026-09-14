<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Presensi Sholat Dhuhur</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 8mm 15mm 8mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #1a1a2e;
            margin: 0;
            padding: 0;
        }

        /* JUDUL LAPORAN */
        .simple-header {
            text-align: center;
            margin-bottom: 15px;
            font-size: 11px;
            font-weight: bold;
            line-height: 1.5;
        }

        /* TABEL DATA */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5px;
        }
        .data-table thead tr th {
            background-color: #065f46;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            padding: 4px 1px;
            border: 1px solid #044e39;
        }
        .data-table tbody tr td {
            padding: 3px 2px;
            border: 1px solid #d1fae5;
            vertical-align: middle;
        }
        .data-table tbody tr:nth-child(even) {
            background-color: #f0fdf4;
        }
        .data-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        .data-table td.center { text-align: center; }
        .data-table td.nama   { font-weight: 600; text-align: left; padding-left: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 115px; }
        .data-table td.nisn   { text-align: center; white-space: nowrap; }

        /* TANDA TANGAN */
        .signature-section {
            margin-top: 15px;
            width: 100%;
            display: table;
        }
        .signature-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .signature-right {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .signature-right .ttd-title { font-size: 9px; margin-bottom: 40px; }
        .signature-right .ttd-name  { font-weight: bold; text-decoration: underline; font-size: 9.5px; }
        .signature-right .ttd-nip   { font-size: 8.5px; color: #555; }

        /* FOOTER */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 7px;
            color: #888;
            padding: 4px 0;
        }
    </style>
</head>
<body>

    {{-- FOOTER cetak --}}
    <div class="footer">
        Sistem Presensi Sholat Dhuhur Digital &bull; Dicetak pada: {{ $generatedAt }}
    </div>

    {{-- HEADER --}}
    <div class="simple-header">
        LAPORAN KEHADIRAN SHOLAT DHUHUR BERJAMAAH<br>
        PERIODE {{ strtoupper($periodeLabel) }}<br>
        {{ strtoupper($sekolah?->school_name ?? 'NAMA SEKOLAH') }}<br>
        KELAS {{ strtoupper($kelas?->name ?? '') }} &bull; TAHUN AJARAN {{ $tahunAjaran?->name ?? '2026/2027' }}
    </div>

    {{-- TABEL DATA --}}
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width:15px;">No</th>
                <th rowspan="2" style="width:45px;">NISN</th>
                <th rowspan="2" style="width:115px;">Nama Siswa</th>
                <th rowspan="2" style="width:15px;">JK</th>
                <th colspan="{{ $daysInMonth }}" style="text-align:center;">Tanggal</th>
                <th colspan="4" style="text-align:center;">Total</th>
            </tr>
            <tr>
                @for($d = 1; $d <= $daysInMonth; $d++)
                    <th style="width:14px;">{{ $d }}</th>
                @endfor
                <th style="width:18px;">H</th>
                <th style="width:18px;">I</th>
                <th style="width:18px;">A</th>
                <th style="width:24px;">%</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $no = 1; 
                $effDays = $effectiveDays ?? $classMonthlyStats['effective_days'] ?? 0;
            @endphp
            @forelse($students as $student)
                @php
                    $stat = $monthlyStats[$student->id] ?? [];
                    $hadir = $stat['hadir'] ?? 0;
                    $ijin = $stat['ijin'] ?? 0;
                    $alpa = $stat['tidak_hadir'] ?? 0;
                    $pct = $effDays > 0 ? round(($hadir / $effDays) * 100, 1) : 0;
                @endphp
            <tr>
                <td class="center">{{ $no++ }}</td>
                <td class="nisn">{{ $student->nisn }}</td>
                <td class="nama">{{ $student->name }}</td>
                <td class="center" style="font-weight: bold;">{{ $student->gender ?? '-' }}</td>
                @for($d = 1; $d <= $daysInMonth; $d++)
                    @php
                        $code = $stat['daily'][$d] ?? '-';
                        $color = match($code) {
                            'H' => '#065f46',
                            'I' => '#1e40af',
                            'A' => '#991b1b',
                            'L' => '#9ca3af',
                            default => '#666'
                        };
                        $bgColor = ($code === 'L') ? '#f3f4f6' : 'transparent';
                    @endphp
                    <td class="center" style="color: {{ $color }}; background-color: {{ $bgColor }}; font-weight: bold;">
                        {{ $code }}
                    </td>
                @endfor
                <td class="center" style="font-weight: bold; color: #065f46;">{{ $hadir }}</td>
                <td class="center" style="font-weight: bold; color: #1e40af;">{{ $ijin }}</td>
                <td class="center" style="font-weight: bold; color: #991b1b;">{{ $alpa }}</td>
                <td class="center" style="font-weight: bold;">{{ $pct }}%</td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ 4 + $daysInMonth + 4 }}" style="text-align:center; padding: 15px; color: #888;">
                    Tidak ada data siswa terdaftar di kelas ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- TANDA TANGAN --}}
    <div class="signature-section">
        <div class="signature-left">
            <p style="font-size:8px; color:#555; margin-top:5px; line-height: 1.4;">
                <strong>Keterangan Kode:</strong><br>
                H = Hadir Sholat Berjamaah &bull; I = Ijin / Berhalangan (Haid) &bull; A = Tidak Hadir &bull; L = Hari Libur / Sholat Jumat
            </p>
        </div>
        <div class="signature-right">
            <div class="ttd-title">
                Mengetahui,<br>Wali Kelas {{ $kelas?->name ?? '' }}
            </div>
            <div class="ttd-name">{{ $waliKelas?->name ?? '______________________' }}</div>
            <div class="ttd-nip">NIP: {{ $waliKelas?->nip ?? '-' }}</div>
        </div>
    </div>

</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Rekapitulasi Sholat Dhuhur</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm 10mm 20mm 10mm;
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
            margin-bottom: 20px;
            font-size: 11px;
            font-weight: bold;
            line-height: 1.5;
        }

        /* TABEL DATA */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        .data-table thead tr th {
            background-color: #065f46;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            padding: 4px 2px;
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
        .data-table td.nama   { font-weight: bold; text-align: left; padding-left: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px; }
        .data-table td.nisn   { text-align: center; white-space: nowrap; }

        .text-hadir { color: #065f46; font-weight: bold; }
        .text-ijin  { color: #1e40af; font-weight: bold; }
        .text-alpa  { color: #991b1b; font-weight: bold; }
        .bg-total   { background-color: #ecfdf5; font-weight: bold; }

        /* TANDA TANGAN */
        .signature-section {
            margin-top: 20px;
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
        REKAPITULASI KEHADIRAN SHOLAT DHUHUR BERJAMAAH<br>
        PERIODE {{ strtoupper($periodeLabel) }}<br>
        {{ strtoupper($sekolah?->school_name ?? 'NAMA SEKOLAH') }}<br>
        KELAS {{ strtoupper($kelas?->name ?? '') }} &bull; TAHUN AJARAN {{ $tahunAjaran?->name ?? '2026/2027' }}
    </div>

    {{-- TABEL DATA --}}
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 20px;">No</th>
                <th rowspan="2" style="width: 50px;">NISN</th>
                <th rowspan="2" style="width: 130px;">Nama Siswa</th>
                <th rowspan="2" style="width: 20px;">JK</th>
                @foreach($monthsList as $m)
                    <th colspan="3" style="text-align: center;">{{ $m['name'] }}</th>
                @endforeach
                <th colspan="4" style="text-align: center; background-color: #044e39;">Total Kumulatif</th>
            </tr>
            <tr>
                @foreach($monthsList as $m)
                    <th style="width: 16px;">H</th>
                    <th style="width: 16px;">I</th>
                    <th style="width: 16px;">A</th>
                @endforeach
                <th style="width: 18px; background-color: #044e39;">H</th>
                <th style="width: 18px; background-color: #044e39;">I</th>
                <th style="width: 18px; background-color: #044e39;">A</th>
                <th style="width: 24px; background-color: #044e39;">%</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($studentsData as $st)
                @php
                    $totH = $st['total']['hadir'] ?? 0;
                    $totI = $st['total']['ijin'] ?? 0;
                    $totA = $st['total']['tidak_hadir'] ?? 0;
                    $grandTotal = $totH + $totI + $totA;
                    $pct = $grandTotal > 0 ? round(($totH / $grandTotal) * 100, 1) : 0;
                @endphp
            <tr>
                <td class="center">{{ $no++ }}</td>
                <td class="nisn">{{ $st['nisn'] }}</td>
                <td class="nama">{{ $st['name'] }}</td>
                <td class="center" style="font-weight: bold;">{{ $st['gender'] ?? '-' }}</td>
                @foreach($monthsList as $m)
                    @php $mStats = $st['months'][$m['key']] ?? []; @endphp
                    <td class="center text-hadir">{{ $mStats['hadir'] ?? 0 }}</td>
                    <td class="center text-ijin">{{ $mStats['ijin'] ?? 0 }}</td>
                    <td class="center text-alpa">{{ $mStats['tidak_hadir'] ?? 0 }}</td>
                @endforeach
                <td class="center bg-total text-hadir">{{ $totH }}</td>
                <td class="center bg-total text-ijin">{{ $totI }}</td>
                <td class="center bg-total text-alpa">{{ $totA }}</td>
                <td class="center bg-total">{{ $pct }}%</td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ 4 + (count($monthsList) * 3) + 4 }}" style="text-align:center; padding: 15px; color: #888;">
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
                <strong>Keterangan:</strong><br>
                H = Hadir Sholat Berjamaah &bull; I = Ijin / Berhalangan (Haid) &bull; A = Alpa
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

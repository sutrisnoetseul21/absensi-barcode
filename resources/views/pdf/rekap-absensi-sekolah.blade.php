<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rekap Presensi Sekolah</title>
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
            background-color: #1e3a5f;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            padding: 5px 2px;
            border: 1px solid #144077;
        }
        .data-table tbody tr td {
            padding: 4px 3px;
            border: 1px solid #d0dde8;
            vertical-align: middle;
        }
        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .data-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        .data-table td.center { text-align: center; }
        .data-table td.kelas  { font-weight: 600; text-align: left; padding-left: 6px; white-space: nowrap; }
        
        .text-hadir { color: #166534; font-weight: bold; }
        .text-sakit { color: #4c1d95; font-weight: bold; }
        .text-izin  { color: #1e40af; font-weight: bold; }
        .text-alpa  { color: #991b1b; font-weight: bold; }

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
        Sistem Presensi Digital &bull; Dicetak pada: {{ $generatedAt }}
    </div>

    {{-- SIMPLE HEADER --}}
    <div class="simple-header">
        REKAP PRESENSI TAHUNAN PER KELAS<br>
        {{ strtoupper($sekolah?->school_name ?? 'NAMA SEKOLAH') }}<br>
        TAHUN AJARAN {{ strtoupper($tahunAjaran?->name ?? '2026/2027') }}
    </div>

    {{-- TABEL DATA --}}
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width:20px;">No</th>
                <th rowspan="2" style="width:100px;">Kelas</th>
                <th rowspan="2" style="width:45px;">Jml Siswa</th>
                @foreach($monthsList as $m)
                    <th colspan="4" style="text-align:center;">{{ strtoupper($m['label']) }}</th>
                @endforeach
            </tr>
            <tr>
                @foreach($monthsList as $m)
                    <th style="width:16px;">H</th>
                    <th style="width:16px;">S</th>
                    <th style="width:16px;">I</th>
                    <th style="width:16px;">A</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($classesData as $index => $row)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td class="kelas">{{ $row['name'] }}</td>
                <td class="center font-weight-bold">{{ $row['student_count'] }}</td>
                @foreach($monthsList as $m)
                    @php
                        $key   = "{$m['year']}-{$m['month']}";
                        $stats = $row['months'][$key] ?? ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];
                    @endphp
                    <td class="center text-hadir">{{ $stats['hadir'] ?: '-' }}</td>
                    <td class="center text-sakit">{{ $stats['sakit'] ?: '-' }}</td>
                    <td class="center text-izin">{{ $stats['izin'] ?: '-' }}</td>
                    <td class="center text-alpa">{{ $stats['alpa'] ?: '-' }}</td>
                @endforeach
            </tr>
            @empty
            <tr>
                <td colspan="{{ 3 + (count($monthsList) * 4) }}" style="text-align:center; padding: 15px; color: #888;">
                    Belum ada data kelas terdaftar.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- TANDA TANGAN --}}
    <div class="signature-section">
        <div class="signature-left">
            <p style="font-size:8.5px; color:#444; margin-top:5px; line-height: 1.4;">
                <strong>Keterangan:</strong><br>
                H = Hadir (Hadir tepat waktu & Terlambat) &bull; S = Sakit &bull; I = Izin &bull; A = Alpa
            </p>
        </div>
        <div class="signature-right">
            <div class="ttd-title">
                Mengetahui,<br>Kepala Sekolah
            </div>
            @if($sekolah?->principal_signature_path && file_exists(public_path('storage/' . $sekolah->principal_signature_path)))
                <img src="{{ public_path('storage/' . $sekolah->principal_signature_path) }}" style="height:35px; margin-bottom:5px;" alt="TTD">
            @endif
            <div class="ttd-name">{{ $sekolah?->principal_name ?? '______________________' }}</div>
            <div class="ttd-nip">Kepala Sekolah</div>
        </div>
    </div>

</body>
</html>

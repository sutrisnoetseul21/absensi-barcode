<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Presensi Siswa</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm 12mm 20mm 12mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
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

        /* META INFO */
        .meta-info {
            width: 100%;
            margin-bottom: 10px;
            font-size: 9px;
        }
        .meta-info table {
            width: 50%;
            border-collapse: collapse;
        }
        .meta-info td {
            padding: 1px 0;
            vertical-align: top;
        }
        .meta-info td.label { width: 90px; font-weight: bold; }
        .meta-info td.colon { width: 10px; text-align: center; }

        /* TABEL DATA */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px; /* Sangat kecil untuk menampung banyak kolom */
        }
        .data-table thead tr th {
            background-color: #1e3a5f;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            padding: 4px 2px;
            border: 1px solid #144077;
        }
        .data-table tbody tr td {
            padding: 3px 2px;
            border: 1px solid #d0dde8;
            vertical-align: middle;
        }
        .data-table tbody tr:nth-child(even) {
            background-color: #f0f5ff;
        }
        .data-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        .data-table td.center { text-align: center; }
        .data-table td.nama   { font-weight: bold; text-align: left; padding-left: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 90px; }
        .data-table td.nisn   { text-align: center; white-space: nowrap; }

        /* Warna kolom status */
        .text-hadir { color: #166534; font-weight: bold; }
        .text-telat { color: #854d0e; font-weight: bold; }
        .text-izin  { color: #1e40af; font-weight: bold; }
        .text-sakit { color: #4c1d95; font-weight: bold; }
        .text-alpa  { color: #991b1b; font-weight: bold; }
        
        .bg-total { background-color: #e2e8f0; }

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
        .signature-right .ttd-title { font-size: 9.5px; margin-bottom: 50px; }
        .signature-right .ttd-name  { font-weight: bold; text-decoration: underline; font-size: 10px; }
        .signature-right .ttd-nip   { font-size: 9px; color: #555; }

        /* FOOTER */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 8px;
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
        LAPORAN PRESENSI {{ strtoupper($periodeLabel) }}<br>
        {{ strtoupper($sekolah?->school_name ?? 'NAMA SEKOLAH') }}<br>
        KELAS {{ strtoupper($kelas?->name ?? '') }}<br>
        TAHUN AJARAN {{ $kelas?->enrollments?->first()?->tahunAjaran?->name ?? '2026/2027' }}
    </div>

    {{-- TABEL DATA --}}
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width:15px;">No</th>
                <th rowspan="2" style="width:35px;">NISN</th>
                <th rowspan="2" style="width:90px;">Nama Siswa</th>
                @foreach($monthsList as $m)
                    <th colspan="4" style="text-align:center;">{{ $m['label'] }}</th>
                @endforeach
                <th colspan="6" style="text-align:center;">TOTAL {{ strtoupper($jenisLaporan ?? '') }}</th>
            </tr>
            <tr>
                @foreach($monthsList as $m)
                    <th style="width:12px;">H</th>
                    <th style="width:12px;">S</th>
                    <th style="width:12px;">I</th>
                    <th style="width:12px;">A</th>
                @endforeach
                
                <th style="width:14px;">H</th>
                <th style="width:14px;">T</th>
                <th style="width:14px;">S</th>
                <th style="width:14px;">I</th>
                <th style="width:14px;">A</th>
                <th style="width:20px;">Telat(m)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($studentsData as $row)
            <tr>
                <td class="center">{{ $row['no'] }}</td>
                <td class="nisn">{{ $row['nisn'] }}</td>
                <td class="nama">{{ $row['name'] }}</td>
                
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
                
                <td class="center bg-total text-hadir">{{ $row['total']['hadir'] ?: '-' }}</td>
                <td class="center bg-total text-telat">{{ $row['total']['telat'] ?: '-' }}</td>
                <td class="center bg-total text-sakit">{{ $row['total']['sakit'] ?: '-' }}</td>
                <td class="center bg-total text-izin">{{ $row['total']['izin'] ?: '-' }}</td>
                <td class="center bg-total text-alpa">{{ $row['total']['alpa'] ?: '-' }}</td>
                <td class="center bg-total" style="font-size:7px; color:#555;">{{ $row['total']['late_minutes'] ?: '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ 3 + (count($monthsList) * 4) + 6 }}" style="text-align:center; padding: 15px; color: #888;">
                    Tidak ada data siswa terdaftar di kelas ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- TANDA TANGAN --}}
    <div class="signature-section">
        <div class="signature-left">
            <p style="font-size:9px; color:#555; margin-top:5px;">
                Keterangan: H = Hadir &bull; T = Terlambat &bull; I = Izin &bull; S = Sakit &bull; A = Alpa
            </p>
        </div>
        <div class="signature-right">
            <div class="ttd-title">
                Mengetahui,<br>Kepala Sekolah
            </div>
            @if($sekolah?->principal_signature_path && file_exists(public_path('storage/' . $sekolah->principal_signature_path)))
                <img src="{{ public_path('storage/' . $sekolah->principal_signature_path) }}" style="height:40px; margin-bottom:5px;" alt="TTD">
            @endif
            <div class="ttd-name">{{ $sekolah?->principal_name ?? '______________________' }}</div>
            <div class="ttd-nip">Kepala Sekolah</div>
        </div>
    </div>

</body>
</html>

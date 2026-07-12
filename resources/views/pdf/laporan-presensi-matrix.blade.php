<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Presensi Siswa</title>
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

        /* KOP SURAT */
        .kop-surat {
            width: 100%;
            border-bottom: 3px solid #1e3a5f;
            padding-bottom: 6px;
            margin-bottom: 10px;
            display: table;
        }
        .kop-logo {
            display: table-cell;
            width: 50px;
            vertical-align: middle;
            text-align: center;
        }
        .kop-logo img {
            width: 45px;
            height: 45px;
            object-fit: contain;
        }
        .kop-text {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }
        .kop-text .school-name {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #1e3a5f;
        }
        .kop-text .school-address {
            font-size: 8px;
            color: #555;
        }

        /* JUDUL LAPORAN */
        .laporan-title {
            text-align: center;
            margin-bottom: 8px;
        }
        .laporan-title h2 {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 3px 0;
            letter-spacing: 0.5px;
        }
        .laporan-title .subtitle {
            font-size: 9px;
            color: #444;
        }

        /* TABEL DATA */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5px; /* Sangat kecil agar 31 kolom muat */
        }
        .data-table thead tr th {
            background-color: #1e3a5f;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            padding: 4px 1px;
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
        .data-table td.nama   { font-weight: 600; text-align: left; padding-left: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 110px; }
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
        Sistem Presensi Digital &bull; Dicetak pada: {{ $generatedAt }}
    </div>

    {{-- KOP SURAT --}}
    <div class="kop-surat">
        <div class="kop-logo">
            @if($sekolah?->school_logo_path && file_exists(public_path('storage/' . $sekolah->school_logo_path)))
                <img src="{{ public_path('storage/' . $sekolah->school_logo_path) }}" alt="Logo">
            @endif
        </div>
        <div class="kop-text">
            <div class="school-name">{{ strtoupper($sekolah?->school_name ?? 'NAMA SEKOLAH') }}</div>
            <div class="school-address">{{ $sekolah?->school_address ?? '' }}</div>
        </div>
    </div>

    {{-- JUDUL --}}
    <div class="laporan-title">
        <h2>Laporan Presensi Siswa</h2>
        <div class="subtitle">Bulan {{ $periodeLabel }} &mdash; Kelas {{ $kelas?->name ?? '-' }}</div>
    </div>

    {{-- TABEL DATA --}}
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width:15px;">No</th>
                <th rowspan="2" style="width:45px;">NISN</th>
                <th rowspan="2" style="width:110px;">Nama Siswa</th>
                <th colspan="{{ $daysInMonth }}" style="text-align:center;">Tanggal</th>
                <th colspan="5" style="text-align:center;">Total</th>
            </tr>
            <tr>
                @for($d = 1; $d <= $daysInMonth; $d++)
                    <th style="width:14px;">{{ $d }}</th>
                @endfor
                <th style="width:18px;">H</th>
                <th style="width:18px;">T</th>
                <th style="width:18px;">I</th>
                <th style="width:18px;">S</th>
                <th style="width:18px;">A</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($students as $student)
                @php
                    $stat = $monthlyStats[$student->id] ?? [];
                @endphp
            <tr>
                <td class="center">{{ $no++ }}</td>
                <td class="nisn">{{ $student->nisn }}</td>
                <td class="nama">{{ $student->name }}</td>
                @for($d = 1; $d <= $daysInMonth; $d++)
                    @php
                        $code = $stat['daily'][$d] ?? '-';
                        $color = match($code) {
                            'H' => '#166534',
                            'T' => '#854d0e',
                            'I' => '#1e40af',
                            'S' => '#4c1d95',
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
                <td class="center" style="font-weight: bold; color: #166534;">{{ $stat['hadir'] ?? 0 }}</td>
                <td class="center" style="font-weight: bold; color: #854d0e;">{{ $stat['telat'] ?? 0 }}</td>
                <td class="center" style="font-weight: bold; color: #1e40af;">{{ $stat['izin'] ?? 0 }}</td>
                <td class="center" style="font-weight: bold; color: #4c1d95;">{{ $stat['sakit'] ?? 0 }}</td>
                <td class="center" style="font-weight: bold; color: #991b1b;">{{ $stat['alpa'] ?? 0 }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ 3 + $daysInMonth + 5 }}" style="text-align:center; padding: 15px; color: #888;">
                    Tidak ada data siswa terdaftar di kelas ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- TANDA TANGAN --}}
    <div class="signature-section">
        <div class="signature-left">
            <p style="font-size:8px; color:#555; margin-top:5px;">
                Keterangan: H = Hadir &bull; T = Terlambat &bull; I = Izin &bull; S = Sakit &bull; A = Alpa &bull; L = Libur
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

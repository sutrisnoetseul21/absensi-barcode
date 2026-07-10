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

        /* KOP SURAT */
        .kop-surat {
            width: 100%;
            border-bottom: 3px solid #1e3a5f;
            padding-bottom: 8px;
            margin-bottom: 12px;
            display: table;
        }
        .kop-logo {
            display: table-cell;
            width: 60px;
            vertical-align: middle;
            text-align: center;
        }
        .kop-logo img {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }
        .kop-text {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }
        .kop-text .school-name {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #1e3a5f;
        }
        .kop-text .school-address {
            font-size: 9px;
            color: #555;
        }

        /* JUDUL LAPORAN */
        .laporan-title {
            text-align: center;
            margin-bottom: 12px;
        }
        .laporan-title h2 {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 4px 0;
            letter-spacing: 0.5px;
        }
        .laporan-title .subtitle {
            font-size: 10px;
            color: #444;
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
            font-size: 9.5px;
        }
        .data-table thead tr th {
            background-color: #1e3a5f;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            padding: 6px 4px;
            border: 1px solid #144077;
        }
        .data-table tbody tr td {
            padding: 4px 5px;
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
        .data-table td.nama   { font-weight: 600; }

        /* Warna kolom status */
        .badge-hadir   { background-color: #dcfce7; color: #166534; padding: 1px 5px; border-radius: 3px; }
        .badge-telat   { background-color: #fef9c3; color: #854d0e; padding: 1px 5px; border-radius: 3px; }
        .badge-izin    { background-color: #dbeafe; color: #1e40af; padding: 1px 5px; border-radius: 3px; }
        .badge-sakit   { background-color: #ede9fe; color: #4c1d95; padding: 1px 5px; border-radius: 3px; }
        .badge-alpa    { background-color: #fee2e2; color: #991b1b; padding: 1px 5px; border-radius: 3px; }

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
        <div class="subtitle">{{ $periodeLabel }} &mdash; Kelas {{ $kelas?->name ?? '-' }}</div>
    </div>

    {{-- TABEL DATA --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:30px;">No</th>
                <th style="width:80px;">NISN</th>
                <th style="text-align:left; padding-left:8px;">Nama Siswa</th>
                <th style="width:55px;">Hadir</th>
                <th style="width:65px;">Terlambat</th>
                <th style="width:45px;">Izin</th>
                <th style="width:45px;">Sakit</th>
                <th style="width:45px;">Alpa</th>
                <th style="width:80px;">Total Telat (Mnt)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporanData as $row)
            <tr>
                <td class="center">{{ $row['no'] }}</td>
                <td class="center">{{ $row['nisn'] }}</td>
                <td class="nama" style="padding-left:8px;">{{ $row['name'] }}</td>
                <td class="center"><span class="badge-hadir">{{ $row['hadir'] }}</span></td>
                <td class="center"><span class="badge-telat">{{ $row['telat'] }}</span></td>
                <td class="center"><span class="badge-izin">{{ $row['izin'] }}</span></td>
                <td class="center"><span class="badge-sakit">{{ $row['sakit'] }}</span></td>
                <td class="center"><span class="badge-alpa">{{ $row['alpa'] }}</span></td>
                <td class="center">{{ $row['late_minutes'] }} mnt</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center; padding: 20px; color: #888;">
                    Tidak ada data presensi untuk periode ini.
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

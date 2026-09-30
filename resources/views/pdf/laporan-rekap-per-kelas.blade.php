<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rekap Kehadiran Siswa Per Kelas - {{ $selectedDateFormatted }}</title>
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
        .kop-dinas {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            margin: 1px 0;
            text-transform: uppercase;
        }
        .kop-sekolah {
            font-size: 14px;
            font-weight: 900;
            color: #047857; /* Emerald Dark */
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

        /* SUMMARY CARDS GRID */
        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin-bottom: 8px;
        }
        .summary-card {
            border-radius: 6px;
            padding: 5px 8px;
            text-align: center;
            vertical-align: middle;
        }
        .card-hadir {
            background-color: #ecfdf5;
            border: 1.5px solid #059669;
        }
        .card-total {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
        }
        .card-tidak-hadir {
            background-color: #fff1f2;
            border: 1px solid #f43f5e;
        }
        .card-persen {
            background-color: #eff6ff;
            border: 1px solid #3b82f6;
        }
        .card-label {
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            color: #475569;
            margin-bottom: 1px;
        }
        .card-value {
            font-size: 14px;
            font-weight: 900;
            line-height: 1;
        }

        /* DATA TABLE */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5px;
        }
        .data-table thead tr th {
            background-color: #064e3b; /* Emerald 900 */
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            padding: 4px 2px;
            border: 1px solid #047857;
            text-transform: uppercase;
        }
        .data-table thead tr.sub-header th {
            background-color: #047857; /* Emerald 700 */
            font-size: 7px;
        }
        .data-table tbody tr td {
            padding: 3.5px 3px;
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

        .paraf-box {
            height: 18px;
            border: 0.5px dashed #94a3b8;
            border-radius: 2px;
            background-color: #ffffff;
        }

        /* FOOTER & SIGNATURE */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .signature-table td {
            vertical-align: top;
            padding: 0 10px;
        }
        .notes-box {
            font-size: 7px;
            color: #475569;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 5px 8px;
            line-height: 1.35;
        }
        .ttd-block {
            text-align: center;
            font-size: 8px;
        }
        .ttd-space {
            height: 38px;
        }
        .ttd-name {
            font-weight: bold;
            text-decoration: underline;
            font-size: 8.5px;
        }
        .ttd-nip {
            font-size: 7.5px;
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
        Dokumen Rekapitulasi Presensi Harian Siswa &bull; {{ $sekolah?->school_name ?? 'SMP Negeri 3 Kedungreja' }} &bull; Dicetak: {{ $generatedAt }}
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
        <h1 class="report-title">LAPORAN REKAPITULASI KEHADIRAN SISWA PER KELAS</h1>
        <p class="report-subtitle">
            Hari / Tanggal: <strong>{{ $selectedDateFormatted }}</strong> &bull; Tahun Ajaran: <strong>{{ $tahunAjaran?->name ?? '2026/2027' }}</strong> &bull; Waktu Data: <strong>{{ date('H:i') }} WIB</strong>
        </p>
    </div>

    <!-- SUMMARY METRICS CARDS -->
    <table class="summary-table">
        <tr>
            <td class="summary-card card-hadir" style="width: 32%;">
                <div class="card-label" style="color: #047857;">TOTAL SISWA HADIR DI SEKOLAH</div>
                <div class="card-value" style="color: #065f46;">{{ $stats['total_hadir'] ?? 0 }} <span style="font-size: 9px; font-weight: normal;">Siswa</span></div>
            </td>
            <td class="summary-card card-total" style="width: 22%;">
                <div class="card-label">TOTAL SISWA TERDAFTAR</div>
                <div class="card-value" style="color: #1e293b;">{{ $stats['total_students'] ?? 0 }} <span style="font-size: 9px; font-weight: normal;">Siswa</span></div>
            </td>
            <td class="summary-card card-tidak-hadir" style="width: 24%;">
                <div class="card-label" style="color: #e11d48;">TIDAK HADIR (S / I / A)</div>
                <div class="card-value" style="color: #be123c;">
                    {{ ($stats['sakit'] ?? 0) + ($stats['izin'] ?? 0) + ($stats['alpa'] ?? 0) }}
                    <span style="font-size: 7.5px; font-weight: normal; color: #881337;">(S:{{ $stats['sakit'] ?? 0 }} I:{{ $stats['izin'] ?? 0 }} A:{{ $stats['alpa'] ?? 0 }})</span>
                </div>
            </td>
            <td class="summary-card card-persen" style="width: 22%;">
                <div class="card-label" style="color: #2563eb;">TINGKAT KEHADIRAN</div>
                <div class="card-value" style="color: #1d4ed8;">{{ $stats['persentase_hadir'] ?? 0 }}%</div>
            </td>
        </tr>
    </table>

    <!-- TABLE RINCIAN PER KELAS -->
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 20px;">No</th>
                <th rowspan="2" style="width: 50px;">Kelas</th>
                <th rowspan="2" style="width: 140px; text-align: left; padding-left: 6px;">Wali Kelas</th>
                <th rowspan="2" style="width: 55px;">Total Siswa</th>
                <th colspan="2" style="background-color: #047857;">Siswa Hadir</th>
                <th colspan="3" style="background-color: #9f1239;">Tidak Hadir</th>
                <th rowspan="2" style="width: 45px;">Belum Absen</th>
                <th rowspan="2" style="width: 45px;">% Hadir</th>
                <th rowspan="2" style="width: 90px;">Paraf Wali Kelas / PIC</th>
                <th rowspan="2" style="width: 80px;">Keterangan</th>
            </tr>
            <tr class="sub-header">
                <th style="width: 40px;">Tepat</th>
                <th style="width: 40px;">Telat</th>
                <th style="width: 28px;">S</th>
                <th style="width: 28px;">I</th>
                <th style="width: 28px;">A</th>
            </tr>
        </thead>
        <tbody>
            @forelse($classesSummary as $index => $row)
            <tr>
                <td class="text-center font-bold">{{ $index + 1 }}</td>
                <td class="text-center font-bold" style="font-size: 8.5px;">{{ $row['name'] }}</td>
                <td class="text-left" style="padding-left: 6px;">{{ $row['wali_kelas'] }}</td>
                <td class="text-center font-bold">{{ $row['total'] }}</td>
                
                <!-- SISWA HADIR (Tepat & Telat) -->
                <td class="text-center font-bold" style="color: #047857;">{{ $row['hadir'] }}</td>
                <td class="text-center font-bold" style="color: #b45309;">{{ $row['telat'] ?: '-' }}</td>

                <!-- Ketidakhadiran -->
                <td class="text-center" style="color: #6b21a8;">{{ $row['sakit'] ?: '-' }}</td>
                <td class="text-center" style="color: #1d4ed8;">{{ $row['izin'] ?: '-' }}</td>
                <td class="text-center font-bold" style="color: #b91c1c;">{{ $row['alpa'] ?: '-' }}</td>

                <!-- Belum Absen -->
                <td class="text-center {{ $row['belum'] > 0 ? 'font-bold' : '' }}" style="color: {{ $row['belum'] > 0 ? '#ea580c' : '#94a3b8' }};">
                    {{ $row['belum'] ?: '0' }}
                </td>

                <!-- Persen -->
                <td class="text-center font-bold">{{ $row['persen'] }}%</td>

                <!-- Kolom Paraf Serah Terima / Verifikasi -->
                <td>
                    <div class="paraf-box"></div>
                </td>

                <!-- Keterangan -->
                <td class="text-center" style="font-size: 7px; color: #64748b;">
                    @if($row['belum'] > 0)
                        <span style="color: #ea580c; font-weight: bold;">{{ $row['belum'] }} blm absen</span>
                    @elseif($row['total'] == $row['total_hadir'])
                        <span style="color: #047857;">Lengkap (100%)</span>
                    @else
                        <span>{{ $row['total_hadir'] }} siswa terdata</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="13" class="text-center" style="padding: 15px; color: #94a3b8;">
                    Tidak ada data kelas aktif pada tahun ajaran ini.
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right font-bold" style="padding-right: 8px;">TOTAL KESELURUHAN SEKOLAH:</td>
                <td class="text-center">{{ $stats['total_students'] ?? 0 }}</td>
                <td class="text-center" style="color: #047857;">{{ $stats['hadir'] ?? 0 }}</td>
                <td class="text-center" style="color: #b45309;">{{ $stats['telat'] ?? 0 }}</td>
                <td class="text-center" style="color: #6b21a8;">{{ $stats['sakit'] ?? 0 }}</td>
                <td class="text-center" style="color: #1d4ed8;">{{ $stats['izin'] ?? 0 }}</td>
                <td class="text-center" style="color: #b91c1c;">{{ $stats['alpa'] ?? 0 }}</td>
                <td class="text-center" style="color: {{ ($stats['belum'] ?? 0) > 0 ? '#ea580c' : '#64748b' }};">
                    {{ $stats['belum'] ?? 0 }}
                </td>
                <td class="text-center">{{ $stats['persentase_hadir'] ?? 0 }}%</td>
                <td colspan="2" class="text-center" style="background-color: #ecfdf5; color: #065f46; font-size: 8px;">
                    <strong>{{ $stats['total_hadir'] ?? 0 }} Siswa Hadir di Sekolah</strong>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- SIGNATURES & NOTES -->
    <table class="signature-table">
        <tr>
            <!-- SOP Notes -->
            <td style="width: 52%;">
                <div class="notes-box">
                    <strong>Ketentuan Verifikasi Kehadiran Siswa:</strong><br>
                    1. Data kehadiran di atas merupakan rekapitulasi kehadiran siswa real-time berdasarkan presensi digital sekolah.<br>
                    2. Siswa yang berstatus Sakit, Izin, atau Alpa dicatat sesuai konfirmasi dan surat keterangan dari orang tua/wali.<br>
                    3. Kolom paraf digunakan untuk verifikasi data kehadiran fisik siswa oleh wali kelas atau petugas piket harian.<br>
                    4. Laporan ini merupakan dokumen resmi pertanggungjawaban kehadiran harian sekolah.
                </div>
            </td>

            <!-- TTD Petugas Presensi -->
            <td style="width: 24%; text-align: center;">
                <div class="ttd-block">
                    Petugas / Koordinator Presensi,
                    <div class="ttd-space"></div>
                    <div class="ttd-name">__________________________</div>
                    <div class="ttd-nip">Admin Presensi</div>
                </div>
            </td>

            <!-- TTD Kepala Sekolah -->
            <td style="width: 24%; text-align: center;">
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

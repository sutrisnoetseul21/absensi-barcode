<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rekap Presensi Sholat Dhuhur Harian - {{ $selectedDateFormatted }}</title>
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

        /* DATA TABLE */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            margin-bottom: 10px;
        }
        .data-table thead tr th {
            background-color: #064e3b; /* Emerald 900 */
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            padding: 4.5px 3px;
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

        /* FOOTER & SIGNATURE */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
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
        Dokumen Rekapitulasi Presensi Sholat Dhuhur Harian &bull; {{ $sekolah?->school_name ?? 'SMP Negeri 1 Sampang' }} &bull; Dicetak: {{ $generatedAt }}
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
                <p class="kop-instansi">Pemerintah Kabupaten Sampang &bull; Dinas Pendidikan</p>
                <p class="kop-sekolah">{{ $sekolah?->school_name ?? 'SMP NEGERI 1 SAMPANG' }}</p>
                <p class="kop-alamat">
                    {{ $sekolah?->school_address ?? 'Jl. Jaksa Agung Suprapto No. 59, Sampang, Madura' }}
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
        <h1 class="report-title">LAPORAN REKAPITULASI HARIAN PRESENSI SHOLAT DHUHUR</h1>
        <p class="report-subtitle">
            Hari / Tanggal: <strong>{{ $selectedDateFormatted }}</strong> &bull; Tahun Ajaran: <strong>{{ $tahunAjaran?->name ?? '2026/2027' }}</strong> &bull; Waktu Data: <strong>{{ date('H:i') }} WIB</strong>
            @if(!empty($isHoliday))
                &bull; <span style="color: #dc2626; font-weight: bold;">(Hari Libur: {{ $holidayDesc ?? 'Libur Sholat' }})</span>
            @endif
        </p>
    </div>

    <!-- 1. RINGKASAN TINGKAT ANGKATAN / KELAS (GRADE LEVEL) -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 140px; text-align: left; padding-left: 8px;">Tingkat / Angkatan</th>
                <th style="width: 80px;">Total Siswa</th>
                <th style="width: 80px;">Siswa Muslim</th>
                <th style="width: 80px; background-color: #312e81;">Non-Muslim</th>
                <th style="width: 90px; background-color: #047857;">Hadir Berjamaah</th>
                <th style="width: 90px; background-color: #1d4ed8;">Izin / Halangan</th>
                <th style="width: 80px; background-color: #be123c;">Tidak Hadir</th>
                <th style="width: 80px; background-color: #475569;">Belum Absen</th>
                <th style="width: 90px;">% Kepatuhan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($gradeGroups as $grp)
            <tr>
                <td class="font-bold text-left" style="padding-left: 8px;">Kelas {{ $grp['grade_level'] }}</td>
                <td class="text-center font-bold">{{ $grp['total'] }}</td>
                <td class="text-center font-bold">{{ $grp['muslim_total'] }}</td>
                <td class="text-center" style="color: #4338ca;">{{ $grp['non_muslim_total'] }}</td>
                <td class="text-center font-bold" style="color: #047857;">{{ $grp['hadir'] }}</td>
                <td class="text-center font-bold" style="color: #1d4ed8;">{{ $grp['ijin'] }}</td>
                <td class="text-center font-bold" style="color: #dc2626;">{{ $grp['tidak_hadir'] }}</td>
                <td class="text-center font-bold" style="color: #ea580c;">{{ $grp['belum'] }}</td>
                <td class="text-center font-bold">{{ $grp['persen'] }}%</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="text-right font-bold" style="padding-right: 8px;">TOTAL SEKOLAH:</td>
                <td class="text-center font-bold">{{ $stats['total_students'] ?? 0 }}</td>
                <td class="text-center font-bold">{{ $stats['muslim_total'] ?? 0 }}</td>
                <td class="text-center font-bold" style="color: #4338ca;">{{ $stats['non_muslim_total'] ?? 0 }}</td>
                <td class="text-center font-bold" style="color: #047857;">{{ $stats['hadir'] ?? 0 }}</td>
                <td class="text-center font-bold" style="color: #1d4ed8;">{{ $stats['ijin'] ?? 0 }}</td>
                <td class="text-center font-bold" style="color: #dc2626;">{{ $stats['tidak_hadir'] ?? 0 }}</td>
                <td class="text-center font-bold" style="color: #ea580c;">{{ $stats['belum'] ?? 0 }}</td>
                <td class="text-center font-bold">{{ $stats['persen'] ?? 0 }}%</td>
            </tr>
        </tfoot>
    </table>

    <!-- 2. DAFTAR SISWA TIDAK HADIR / ALPA SHOLAT DHUHUR -->
    @php
        $alpaStudents = collect($allStudents)->filter(fn($s) => $s['status'] === 'tidak_hadir' && empty($s['is_non_muslim']))->values();
        $ijinStudents = collect($allStudents)->filter(fn($s) => $s['status'] === 'ijin' && empty($s['is_non_muslim']))->values();
    @endphp

    <div style="margin-top: 10px; margin-bottom: 4px;">
        <h3 style="font-size: 9px; font-weight: bold; text-transform: uppercase; margin: 0; color: #be123c;">
            Daftar Siswa Tidak Hadir / Alpa Sholat Dhuhur ({{ count($alpaStudents) }} Siswa)
        </h3>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 75px;">NISN</th>
                <th style="text-align: left; padding-left: 6px;">Nama Siswa</th>
                <th style="width: 35px;">L/P</th>
                <th style="width: 55px;">Kelas</th>
                <th style="width: 140px; text-align: left; padding-left: 6px;">Wali Kelas</th>
                <th style="text-align: left; padding-left: 6px;">Keterangan / Alasan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($alpaStudents as $idx => $st)
            <tr>
                <td class="text-center font-bold">{{ $idx + 1 }}</td>
                <td class="text-center">{{ $st['nisn'] ?: '-' }}</td>
                <td class="text-left font-bold" style="padding-left: 6px;">{{ $st['name'] }}</td>
                <td class="text-center">{{ $st['gender'] }}</td>
                <td class="text-center font-bold">{{ $st['class_name'] }}</td>
                <td class="text-left" style="padding-left: 6px;">{{ collect($classesSummary)->firstWhere('id', $st['class_id'])['wali_kelas'] ?? '-' }}</td>
                <td class="text-left" style="color: #dc2626; font-weight: bold; padding-left: 6px;">{{ $st['keterangan'] ?: 'Tidak Hadir Tanpa Keterangan' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 8px; color: #047857; font-weight: bold;">
                    Nihil &mdash; Tidak ada siswa yang tercatat Alpa Sholat Dhuhur hari ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 3. DAFTAR SISWA IZIN / HALANGAN (HAID / SAKIT) -->
    <div style="margin-top: 10px; margin-bottom: 4px;">
        <h3 style="font-size: 9px; font-weight: bold; text-transform: uppercase; margin: 0; color: #1d4ed8;">
            Daftar Siswa Izin / Berhalangan Syar'i ({{ count($ijinStudents) }} Siswa)
        </h3>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 75px;">NISN</th>
                <th style="text-align: left; padding-left: 6px;">Nama Siswa</th>
                <th style="width: 35px;">L/P</th>
                <th style="width: 55px;">Kelas</th>
                <th style="text-align: left; padding-left: 6px;">Keterangan Izin / Halangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ijinStudents->take(25) as $idx => $st)
            <tr>
                <td class="text-center font-bold">{{ $idx + 1 }}</td>
                <td class="text-center">{{ $st['nisn'] ?: '-' }}</td>
                <td class="text-left font-bold" style="padding-left: 6px;">{{ $st['name'] }}</td>
                <td class="text-center">{{ $st['gender'] }}</td>
                <td class="text-center font-bold">{{ $st['class_name'] }}</td>
                <td class="text-left" style="color: #1d4ed8; padding-left: 6px;">{{ $st['keterangan'] ?: 'Izin / Halangan' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 8px; color: #94a3b8;">
                    Tidak ada siswa izin / berhalangan hari ini.
                </td>
            </tr>
            @endforelse
            @if($ijinStudents->count() > 25)
            <tr>
                <td colspan="6" class="text-center" style="padding: 4px; font-style: italic; color: #64748b;">
                    ... dan {{ $ijinStudents->count() - 25 }} siswa lainnya tercatat berhalangan syar'i / izin. Rincian lengkap dapat dilihat pada file Excel.
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- SIGNATURE SECTION -->
    <table class="signature-table">
        <tr>
            <td style="width: 55%;">
                <div class="notes-box">
                    <strong>Catatan:</strong><br>
                    &bull; Laporan rekap harian ini mencerminkan hasil kehadiran ibadah sholat dhuhur berjamaah seluruh tingkat kelas.<br>
                    &bull; Siswa yang alpa / belum presensi ditindaklanjuti bersama wali kelas dan pembina keagamaan.<br>
                    &bull; Non-Muslim otomatis dibebaskan dari pembiasaan sholat dhuhur.
                </div>
            </td>
            <td style="width: 22%;">
                <div class="ttd-block">
                    Sampang, {{ \Carbon\Carbon::parse($date)->locale('id')->translatedFormat('d F Y') }}<br>
                    Koordinator Sholat Dhuhur,
                    <div class="ttd-space"></div>
                    <div class="ttd-name">{{ $koordinator?->name ?? 'ASIS, S.Pd.I.' }}</div>
                    <div class="ttd-nip">NIP. {{ $koordinator?->nip ?? '197601282007011009' }}</div>
                </div>
            </td>
            <td style="width: 23%;">
                <div class="ttd-block">
                    Mengetahui,<br>
                    Kepala {{ $sekolah?->school_name ?? 'SMP Negeri 1 Sampang' }},
                    <div class="ttd-space"></div>
                    <div class="ttd-name">{{ $sekolah?->principal_name ?? 'Drs. DIRWANTO' }}</div>
                    <div class="ttd-nip">NIP. {{ $sekolah?->principal_nip ?? '196705121995121002' }}</div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>

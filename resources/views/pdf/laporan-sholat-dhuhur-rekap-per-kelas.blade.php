<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rekap Presensi Sholat Dhuhur Per Kelas - {{ $selectedDateFormatted }}</title>
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
            height: 16px;
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
        Dokumen Rekapitulasi Presensi Sholat Dhuhur &bull; {{ $sekolah?->school_name ?? 'SMP Negeri 1 Sampang' }} &bull; Dicetak: {{ $generatedAt }}
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
        <h1 class="report-title">LAPORAN REKAPITULASI PRESENSI SHOLAT DHUHUR PER KELAS</h1>
        <p class="report-subtitle">
            Hari / Tanggal: <strong>{{ $selectedDateFormatted }}</strong> &bull; Tahun Ajaran: <strong>{{ $tahunAjaran?->name ?? '2026/2027' }}</strong> &bull; Waktu Data: <strong>{{ date('H:i') }} WIB</strong>
            @if(!empty($isHoliday))
                &bull; <span style="color: #dc2626; font-weight: bold;">(Hari Libur: {{ $holidayDesc ?? 'Libur Sholat' }})</span>
            @endif
        </p>
    </div>

    <!-- SUMMARY METRICS CARDS -->
    <table class="summary-table">
        <tr>
            <td class="summary-card card-hadir" style="width: 25%;">
                <div class="card-label" style="color: #047857;">HADIR SHOLAT BERJAMAAH</div>
                <div class="card-value" style="color: #065f46;">{{ $stats['hadir'] ?? 0 }} <span style="font-size: 9px; font-weight: normal;">Siswa</span></div>
            </td>
            <td class="summary-card card-total" style="width: 25%;">
                <div class="card-label">TOTAL SISWA MUSLIM</div>
                <div class="card-value" style="color: #1e293b;">
                    {{ $stats['muslim_total'] ?? 0 }} 
                    <span style="font-size: 8px; font-weight: normal; color: #64748b;">(Total: {{ $stats['total_students'] ?? 0 }}, Non-Muslim: {{ $stats['non_muslim_total'] ?? 0 }})</span>
                </div>
            </td>
            <td class="summary-card card-tidak-hadir" style="width: 25%;">
                <div class="card-label" style="color: #e11d48;">IZIN / HALANGAN & ALPA</div>
                <div class="card-value" style="color: #be123c;">
                    {{ ($stats['ijin'] ?? 0) + ($stats['tidak_hadir'] ?? 0) }}
                    <span style="font-size: 7.5px; font-weight: normal; color: #881337;">(Izin/Haid: {{ $stats['ijin'] ?? 0 }}, Alpa: {{ $stats['tidak_hadir'] ?? 0 }})</span>
                </div>
            </td>
            <td class="summary-card card-persen" style="width: 25%;">
                <div class="card-label" style="color: #2563eb;">TINGKAT KEPATUHAN BERJAMAAH</div>
                <div class="card-value" style="color: #1d4ed8;">{{ $stats['persen'] ?? 0 }}%</div>
            </td>
        </tr>
    </table>

    <!-- TABLE RINCIAN PER KELAS -->
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 20px;">No</th>
                <th rowspan="2" style="width: 38px;">Kelas</th>
                <th rowspan="2" style="width: 110px; text-align: left; padding-left: 6px;">Wali Kelas</th>
                <th rowspan="2" style="width: 28px;">Total<br>Siswa</th>
                <th colspan="2" style="width: 36px;">Total L/P</th>
                <th colspan="2" style="background-color: #312e81;">Non-Muslim</th>
                <th colspan="2" style="background-color: #047857;">Hadir Sholat</th>
                <th colspan="2" style="background-color: #1d4ed8;">Izin / Halangan</th>
                <th colspan="2" style="background-color: #be123c;">Tidak Hadir</th>
                <th colspan="2" style="background-color: #475569;">Belum Absen</th>
                <th colspan="2">% Hadir</th>
                <th rowspan="2" style="width: 65px;">Paraf Wali Kelas</th>
            </tr>
            <tr class="sub-header">
                <!-- Total L/P -->
                <th style="width: 18px;">L</th>
                <th style="width: 18px;">P</th>
                <!-- Non-Muslim -->
                <th style="width: 18px; background-color: #3730a3;">L</th>
                <th style="width: 18px; background-color: #3730a3;">P</th>
                <!-- Hadir -->
                <th style="width: 18px;">L</th>
                <th style="width: 18px;">P</th>
                <!-- Izin -->
                <th style="width: 18px; background-color: #2563eb;">L</th>
                <th style="width: 18px; background-color: #2563eb;">P</th>
                <!-- Tidak Hadir -->
                <th style="width: 18px; background-color: #e11d48;">L</th>
                <th style="width: 18px; background-color: #e11d48;">P</th>
                <!-- Belum -->
                <th style="width: 18px; background-color: #64748b;">L</th>
                <th style="width: 18px; background-color: #64748b;">P</th>
                <!-- % -->
                <th style="width: 24px;">L</th>
                <th style="width: 24px;">P</th>
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
                
                <!-- Non-Muslim -->
                <td class="text-center" style="color: #4338ca;">{{ $row['non_muslim_l'] ?: '-' }}</td>
                <td class="text-center" style="color: #4338ca;">{{ $row['non_muslim_p'] ?: '-' }}</td>

                <!-- Hadir Sholat -->
                <td class="text-center font-bold" style="color: #047857;">{{ $row['hadir_l'] ?: '-' }}</td>
                <td class="text-center font-bold" style="color: #047857;">{{ $row['hadir_p'] ?: '-' }}</td>
                
                <!-- Izin / Halangan -->
                <td class="text-center" style="color: #1d4ed8;">{{ $row['ijin_l'] ?: '-' }}</td>
                <td class="text-center" style="color: #1d4ed8;">{{ $row['ijin_p'] ?: '-' }}</td>
                
                <!-- Tidak Hadir -->
                <td class="text-center font-bold" style="color: #dc2626;">{{ $row['tidak_hadir_l'] ?: '-' }}</td>
                <td class="text-center font-bold" style="color: #dc2626;">{{ $row['tidak_hadir_p'] ?: '-' }}</td>
                
                <!-- Belum Absen -->
                <td class="text-center {{ $row['belum_l'] > 0 ? 'font-bold' : '' }}" style="{{ $row['belum_l'] > 0 ? 'background-color: #fff1f2; color: #e11d48;' : 'color: #94a3b8;' }}">
                    {{ $row['belum_l'] ?: '-' }}
                </td>
                <td class="text-center {{ $row['belum_p'] > 0 ? 'font-bold' : '' }}" style="{{ $row['belum_p'] > 0 ? 'background-color: #fff1f2; color: #e11d48;' : 'color: #94a3b8;' }}">
                    {{ $row['belum_p'] ?: '-' }}
                </td>
                
                <!-- Persentase Hadir -->
                <td class="text-center font-bold">{{ $row['persen_l'] }}%</td>
                <td class="text-center font-bold">{{ $row['persen_p'] }}%</td>
                
                <!-- Paraf -->
                <td class="text-center"><div class="paraf-box"></div></td>
            </tr>
            @empty
            <tr>
                <td colspan="19" class="text-center" style="padding: 15px; color: #94a3b8;">
                    Tidak ada data kelas untuk ditampilkan.
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right font-bold" style="padding-right: 8px;">TOTAL KESELURUHAN SEKOLAH:</td>
                <td class="text-center font-bold">{{ $stats['total_students'] ?? 0 }}</td>
                <td class="text-center">{{ $stats['total_l'] ?? 0 }}</td>
                <td class="text-center">{{ $stats['total_p'] ?? 0 }}</td>
                
                <td class="text-center" style="color: #4338ca; font-weight: bold;">{{ $stats['non_muslim_l'] ?? 0 }}</td>
                <td class="text-center" style="color: #4338ca; font-weight: bold;">{{ $stats['non_muslim_p'] ?? 0 }}</td>

                <td class="text-center font-bold" style="color: #047857;">{{ $stats['hadir_l'] ?? 0 }}</td>
                <td class="text-center font-bold" style="color: #047857;">{{ $stats['hadir_p'] ?? 0 }}</td>
                
                <td class="text-center font-bold" style="color: #1d4ed8;">{{ $stats['ijin_l'] ?? 0 }}</td>
                <td class="text-center font-bold" style="color: #1d4ed8;">{{ $stats['ijin_p'] ?? 0 }}</td>
                
                <td class="text-center font-bold" style="color: #dc2626;">{{ $stats['tidak_hadir_l'] ?? 0 }}</td>
                <td class="text-center font-bold" style="color: #dc2626;">{{ $stats['tidak_hadir_p'] ?? 0 }}</td>
                
                <td class="text-center font-bold" style="color: #e11d48;">{{ $stats['belum_l'] ?? 0 }}</td>
                <td class="text-center font-bold" style="color: #e11d48;">{{ $stats['belum_p'] ?? 0 }}</td>
                
                <td class="text-center font-bold">{{ $stats['persen_l'] ?? 0 }}%</td>
                <td class="text-center font-bold">{{ $stats['persen_p'] ?? 0 }}%</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <!-- SIGNATURE SECTION -->
    <table class="signature-table">
        <tr>
            <td style="width: 55%;">
                <div class="notes-box">
                    <strong>Catatan Penting Pelaksanaan Sholat Dhuhur:</strong><br>
                    &bull; Kewajiban Sholat Dhuhur berjamaah hanya berlaku bagi peserta didik beragama Islam.<br>
                    &bull; Peserta didik non-Muslim dibebaskan dari presensi dan tidak dihitung ke dalam angka ketidakhadiran sholat.<br>
                    &bull; Siswi yang berhalangan syar'i (haid) atau sakit tercatat dalam kolom <em>Izin / Halangan</em>.<br>
                    &bull; Data ketidakhadiran pagi (Alpa, Izin, Sakit) otomatis tersinkronisasi ke presensi sholat dhuhur setiap hari kerja pukul 11:00 WIB.
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

    <!-- HALAMAN 2: DAFTAR SISWA BELUM ABSEN SHOLAT -->
    <div style="page-break-before: always;"></div>
    
    <div class="report-header" style="margin-top: 15px;">
        <h2 class="report-title">DAFTAR SISWA MUSLIM BELUM PRESENSI SHOLAT DHUHUR</h2>
        <p class="report-subtitle">
            Hari / Tanggal: <strong>{{ $selectedDateFormatted }}</strong> &bull; Total Belum Presensi: <strong>{{ count($unattendedStudents) }} Siswa</strong>
        </p>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 100px;">NISN</th>
                <th style="text-align: left; padding-left: 6px;">Nama Siswa</th>
                <th style="width: 50px;">L/P</th>
                <th style="width: 80px;">Kelas</th>
                <th style="width: 150px;">Wali Kelas</th>
                <th style="width: 100px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($unattendedStudents as $st)
            <tr>
                <td class="text-center font-bold">{{ $no++ }}</td>
                <td class="text-center">{{ $st['nisn'] ?: '-' }}</td>
                <td class="text-left font-bold" style="padding-left: 6px;">{{ $st['name'] }}</td>
                <td class="text-center">{{ $st['gender'] }}</td>
                <td class="text-center font-bold">Kelas {{ $st['class_name'] }}</td>
                <td class="text-center">{{ collect($classesSummary)->firstWhere('id', $st['class_id'])['wali_kelas'] ?? '-' }}</td>
                <td class="text-center" style="color: #ea580c; font-weight: bold;">Belum Absen Sholat</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 15px; color: #047857; font-weight: bold;">
                    Alhamdulillah, seluruh siswa Muslim telah tercatat hadir atau memiliki keterangan sholat hari ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- HALAMAN 3: RINCIAN PRESENSI SELURUH SISWA -->
    <div style="page-break-before: always;"></div>
    
    <div class="report-header" style="margin-top: 15px;">
        <h2 class="report-title">RINCIAN PRESENSI SHOLAT DHUHUR SELURUH SISWA</h2>
        <p class="report-subtitle">
            Hari / Tanggal: <strong>{{ $selectedDateFormatted }}</strong> &bull; Total Siswa: <strong>{{ count($allStudents) }} Siswa</strong> &bull; Waktu Data: <strong>{{ date('H:i') }} WIB</strong>
        </p>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 80px;">NISN</th>
                <th style="text-align: left; padding-left: 6px;">Nama Siswa</th>
                <th style="width: 35px;">L/P</th>
                <th style="width: 55px;">Kelas</th>
                <th style="width: 60px;">Agama</th>
                <th style="width: 90px;">Status Sholat</th>
                <th style="width: 70px;">Waktu Presensi</th>
                <th style="text-align: left; padding-left: 6px;">Catatan / Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php $noAll = 1; @endphp
            @forelse($allStudents as $st)
            <tr>
                <td class="text-center font-bold">{{ $noAll++ }}</td>
                <td class="text-center">{{ $st['nisn'] ?: '-' }}</td>
                <td class="text-left font-bold" style="padding-left: 6px;">{{ $st['name'] }}</td>
                <td class="text-center">{{ $st['gender'] }}</td>
                <td class="text-center font-bold">{{ $st['class_name'] }}</td>
                <td class="text-center">{{ $st['religion'] ?: 'Islam' }}</td>
                <td class="text-center font-bold">
                    @if(!empty($st['is_non_muslim']))
                        <span style="color: #4338ca;">Non-Muslim</span>
                    @elseif($st['status'] === 'hadir')
                        <span style="color: #047857;">Hadir Berjamaah</span>
                    @elseif($st['status'] === 'ijin')
                        <span style="color: #1d4ed8;">Izin / Halangan</span>
                    @elseif($st['status'] === 'tidak_hadir')
                        <span style="color: #dc2626;">Tidak Hadir (Alpa)</span>
                    @else
                        <span style="color: #ea580c;">Belum Absen</span>
                    @endif
                </td>
                <td class="text-center">{{ $st['scan_time'] ? $st['scan_time'] . ' WIB' : '-' }}</td>
                <td class="text-left" style="padding-left: 6px;">{{ $st['keterangan'] ?: '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center" style="padding: 15px; color: #94a3b8;">
                    Tidak ada data siswa.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>

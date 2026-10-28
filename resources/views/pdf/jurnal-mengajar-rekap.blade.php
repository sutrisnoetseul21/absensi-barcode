<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rekap Jurnal Mengajar</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm 10mm 15mm 10mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #1a1a2e;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }

        /* HEADER & KOP */
        .kop-header {
            text-align: center;
            border-bottom: 2px solid #1e3a5f;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .kop-header h2 {
            margin: 0;
            font-size: 15px;
            text-transform: uppercase;
            font-weight: 800;
            color: #1e3a5f;
            letter-spacing: 0.5px;
        }
        .kop-header p {
            margin: 2px 0 0 0;
            font-size: 8.5px;
            color: #475569;
        }

        .title-section {
            text-align: center;
            margin-bottom: 12px;
        }
        .title-section h3 {
            margin: 0;
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .title-section p {
            margin: 2px 0 0 0;
            font-size: 9px;
            color: #64748b;
        }

        /* META INFO */
        .meta-table {
            width: 100%;
            margin-bottom: 10px;
            font-size: 9px;
        }
        .meta-table td {
            padding: 2px 4px;
            vertical-align: top;
        }

        /* DATA TABLE */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            margin-bottom: 15px;
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
            padding: 5px 4px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }
        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }

        /* TANDA TANGAN */
        .signature-table {
            width: 100%;
            margin-top: 15px;
            page-break-inside: avoid;
            font-size: 9px;
        }
        .signature-table td {
            text-align: center;
            vertical-align: top;
            width: 50%;
            padding: 5px 20px;
        }
        .signature-space {
            height: 55px;
        }
    </style>
</head>
<body>
    <!-- Kop Sekolah -->
    <div class="kop-header">
        <h2>{{ $sekolah?->school_name ?? 'SEKOLAH MENENGAH PERTAMA' }}</h2>
        <p>{{ $sekolah?->school_address ?? 'Alamat Sekolah Terpadu' }} | Email: {{ $sekolah?->school_email ?? '-' }} | Telp: {{ $sekolah?->school_phone ?? '-' }}</p>
    </div>

    <!-- Judul Dokumen -->
    <div class="title-section">
        <h3>JURNAL AGENDA PEMBELAJARAN GURU</h3>
        <p>Tahun Ajaran: {{ $tahunAjaran?->name ?? '-' }} | Periode: {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : 'Awal' }} s.d. {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : 'Sekarang' }}</p>
    </div>

    <!-- Metadata Pengampu -->
    <table class="meta-table">
        <tr>
            <td style="width: 15%; font-weight: bold;">Guru Pengampu</td>
            <td style="width: 35%;">: {{ $guru?->name ?? 'Semua Guru' }}</td>
            <td style="width: 15%; font-weight: bold;">Mata Pelajaran</td>
            <td style="width: 35%;">: {{ $mapel?->nama_mapel ?? 'Semua Mata Pelajaran' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">NIP / NUPTK</td>
            <td>: {{ $guru?->nip ?? '-' }}</td>
            <td style="font-weight: bold;">Kelas Rombel</td>
            <td>: {{ $kelas?->name ?? 'Semua Kelas' }}</td>
        </tr>
    </table>

    <!-- Tabel Data Jurnal KBM -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 65px;">Hari / Tgl</th>
                <th style="width: 70px;">Jam Ke (JP)</th>
                <th style="width: 45px;">Kelas</th>
                <th style="width: 85px;">Mata Pelajaran</th>
                <th style="width: 140px;">Materi Pokok</th>
                <th>Uraian Aktivitas Pembelajaran</th>
                <th style="width: 65px;">Kehadiran</th>
                <th style="width: 120px;">Refleksi / Tindak Lanjut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jurnals as $j)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="center">{{ $j->tanggal->translatedFormat('d/m/Y') }}<br><span style="font-size: 7.5px; color: #64748b;">{{ $j->tanggal->translatedFormat('l') }}</span></td>
                    <td class="center bold">{{ $j->jam_ke_label }}</td>
                    <td class="center bold">{{ $j->kelas?->name }}</td>
                    <td>{{ $j->mataPelajaran?->nama_mapel }}</td>
                    <td class="bold">{{ $j->materi }}</td>
                    <td>{{ $j->uraian_materi }}</td>
                    <td class="center">
                        <span class="bold" style="color: #166534;">{{ $j->total_hadir }}/{{ $j->total_siswa }}</span><br>
                        <span style="font-size: 7.5px; color: #475569;">({{ $j->persentase_kehadiran }}%)</span>
                        @if($j->total_tidak_mengikuti > 0)
                            <br><span style="font-size: 7px; color: #dc2626;">(-{{ $j->total_tidak_mengikuti }})</span>
                        @endif
                    </td>
                    <td style="font-size: 8px; color: #334155;">{{ $j->refleksi ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="center" style="padding: 15px; color: #64748b;">Tidak ada data jurnal mengajar pada rentang periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Bagian Tanda Tangan -->
    <table class="signature-table">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala Sekolah
                <div class="signature-space"></div>
                <b><u>{{ $sekolah?->principal_name ?? '.........................................' }}</u></b><br>
                NIP. {{ $sekolah?->principal_nip ?? '-' }}
            </td>
            <td>
                {{ $sekolah?->kota ?? 'Tempat' }}, {{ now()->translatedFormat('d F Y') }}<br>
                Guru Mata Pelajaran
                <div class="signature-space"></div>
                <b><u>{{ $guru?->name ?? '.........................................' }}</u></b><br>
                NIP. {{ $guru?->nip ?? '-' }}
            </td>
        </tr>
    </table>
</body>
</html>

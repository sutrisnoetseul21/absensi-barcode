<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Ekstrakurikuler - {{ $ekskul->nama_ekskul }}</title>
    <style>
        @page { size: A4 landscape; margin: 12mm 10mm 14mm 10mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; }
        .kop { text-align: center; border-bottom: 2px solid #1e3a5f; padding-bottom: 6px; margin-bottom: 10px; }
        .kop h1 { margin: 0; font-size: 13px; color: #0f172a; text-transform: uppercase; }
        .kop h2 { margin: 2px 0 0; font-size: 11px; color: #1e3a5f; text-transform: uppercase; }
        .kop p { margin: 2px 0 0; font-size: 8.5px; color: #475569; }
        .meta { margin-bottom: 8px; font-size: 8.5px; }
        .meta strong { color: #0f172a; }
        h3.section-title { font-size: 10px; color: #1e3a5f; margin: 10px 0 4px; text-transform: uppercase; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        table.data-table th, table.data-table td { border: 1px solid #cbd5e1; padding: 3px 4px; }
        table.data-table th { background-color: #1e3a5f; color: #ffffff; font-weight: bold; font-size: 8px; text-align: center; }
        table.data-table tr:nth-child(even) td { background-color: #f8fafc; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .signature-row { margin-top: 14px; width: 100%; }
        .signature-row td { width: 50%; vertical-align: top; font-size: 8.5px; }
    </style>
</head>
<body>
    <div class="kop">
        <h2>{{ strtoupper($sekolah?->school_name ?? 'SEKOLAH') }}</h2>
        <h1>Laporan Presensi &amp; Jurnal Ekstrakurikuler: {{ strtoupper($ekskul->nama_ekskul) }}</h1>
        <p>Tahun Ajaran {{ $ekskul->tahunAjaran?->name ?? '-' }} &bull; Periode: {{ $periodeLabel }}</p>
    </div>

    <div class="meta">
        <strong>Guru Pembina:</strong> {{ $ekskul->pembinas->pluck('name')->implode(', ') ?: '-' }} &nbsp;&bull;&nbsp;
        <strong>Total Anggota Aktif:</strong> {{ $summary['total_anggota'] }} siswa &nbsp;&bull;&nbsp;
        <strong>Total Pertemuan:</strong> {{ $summary['total_pertemuan'] }} kali &nbsp;&bull;&nbsp;
        <strong>Rata-rata Kehadiran:</strong> {{ $summary['avg_attendance_pct'] }}%
    </div>

    <h3 class="section-title">A. Rekap Kehadiran Anggota Per Pertemuan</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 20px;">No</th>
                <th style="width: 65px;">NISN</th>
                <th style="text-align: left; width: 120px;">Nama Siswa</th>
                <th style="width: 48px;">Kelas</th>
                @foreach($meetings as $m)
                    <th>P{{ $m['pertemuan_ke'] }}<br><span style="font-weight: normal; font-size: 6.5px;">{{ $m['tanggal_short'] }}</span></th>
                @endforeach
                <th style="width: 24px;">H</th>
                <th style="width: 24px;">I</th>
                <th style="width: 24px;">S</th>
                <th style="width: 24px;">A</th>
                <th style="width: 32px;">%</th>
            </tr>
        </thead>
        <tbody>
            @forelse($members as $mem)
                <tr>
                    <td class="center">{{ $mem['no'] }}</td>
                    <td class="center">{{ $mem['nisn'] }}</td>
                    <td class="bold">{{ $mem['name'] }}</td>
                    <td class="center">{{ $mem['kelas'] }}</td>
                    @foreach($meetings as $m)
                        @php
                            $code = $mem['daily'][$m['id']] ?? '-';
                            $color = match($code) {
                                'H' => '#166534',
                                'I' => '#0369a1',
                                'S' => '#4338ca',
                                'A' => '#b91c1c',
                                default => '#94a3b8',
                            };
                        @endphp
                        <td class="center bold" style="color: {{ $color }};">{{ $code }}</td>
                    @endforeach
                    <td class="center bold" style="color: #166534;">{{ $mem['hadir'] }}</td>
                    <td class="center bold" style="color: #0369a1;">{{ $mem['izin'] }}</td>
                    <td class="center bold" style="color: #4338ca;">{{ $mem['sakit'] }}</td>
                    <td class="center bold" style="color: #b91c1c;">{{ $mem['alpa'] }}</td>
                    <td class="center bold">{{ $mem['pct'] }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 9 + count($meetings) }}" class="center" style="padding: 10px; color: #64748b;">Belum ada anggota aktif.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <p style="font-size: 7.5px; color: #64748b; margin-top: 2px;">Keterangan: H = Hadir &bull; I = Izin &bull; S = Sakit &bull; A = Alpa &bull; P1, P2, ... = Pertemuan ke-1, ke-2, dst.</p>

    <h3 class="section-title">B. Jurnal Pertemuan Pembina</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 55px;">Pertemuan</th>
                <th style="width: 105px;">Tanggal</th>
                <th style="text-align: left; width: 170px;">Judul / Materi Kegiatan</th>
                <th style="text-align: left;">Catatan Pembina</th>
                <th style="width: 32px;">Hadir</th>
                <th style="width: 28px;">Izin</th>
                <th style="width: 28px;">Sakit</th>
                <th style="width: 28px;">Alpa</th>
            </tr>
        </thead>
        <tbody>
            @forelse($meetings as $m)
                <tr>
                    <td class="center bold">Ke-{{ $m['pertemuan_ke'] }}</td>
                    <td>{{ $m['tanggal_label'] }}</td>
                    <td class="bold">{{ $m['judul_kegiatan'] }}</td>
                    <td>{{ $m['catatan_pembina'] }}</td>
                    <td class="center bold" style="color: #166534;">{{ $m['hadir_count'] }}</td>
                    <td class="center bold" style="color: #0369a1;">{{ $m['izin_count'] }}</td>
                    <td class="center bold" style="color: #4338ca;">{{ $m['sakit_count'] }}</td>
                    <td class="center bold" style="color: #b91c1c;">{{ $m['alpa_count'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center" style="padding: 10px; color: #64748b;">Belum ada pertemuan tercatat pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="signature-row">
        <tr>
            <td style="text-align: left;">
                Pembina Ekstrakurikuler,<br><br><br><br>
                <strong>{{ $ekskul->pembinas->pluck('name')->first() ?? '______________________' }}</strong>
            </td>
            <td style="text-align: right;">
                Mengetahui,<br>Kepala Sekolah<br>
                @if($sekolah?->principal_signature_path && file_exists(public_path('storage/' . $sekolah->principal_signature_path)))
                    <img src="{{ public_path('storage/' . $sekolah->principal_signature_path) }}" style="height: 36px; margin: 4px 0;" alt="TTD"><br>
                @else
                    <br><br><br>
                @endif
                <strong>{{ $sekolah?->principal_name ?? '______________________' }}</strong>
            </td>
        </tr>
    </table>
</body>
</html>


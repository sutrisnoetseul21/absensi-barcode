<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Harian MBG</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .header p {
            margin: 3px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }
        th {
            font-weight: bold;
            text-transform: uppercase;
        }
        .no-border-left { border-left: none !important; }
        .no-border-right { border-right: none !important; }
        .no-border-top { border-top: none !important; }
        .no-border-bottom { border-bottom: none !important; }
    </style>
</head>
<body>

    @php
        $sekolah = \App\Models\PengaturanSekolah::current();
    @endphp
    <div class="header">
        <p>DAFTAR HARIAN PENGAMBILAN DAN PENGEMBALIAN MBG</p>
        <p>{{ strtoupper($sekolah?->school_name ?? 'SMP NEGERI 3 KEDUNGREJA') }}</p>
        <p>TAHUN AJARAN {{ $tahunAjaran->name ?? '-' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 4%">NO</th>
                <th rowspan="2" style="width: 13%">HARI/TANGGAL</th>
                <th rowspan="2" style="width: 7%">KELAS</th>
                <th colspan="7">ABSENSI</th>
                <th rowspan="2" style="width: 15%">NAMA DAN<br>PARAF PETUGAS</th>
                <th rowspan="2" style="width: 8%">KET</th>
            </tr>
            <tr>
                <th style="width: 7%">JMLH<br>SISWA</th>
                <th style="width: 7%">HADIR</th>
                <th style="width: 8%">TERLAMBAT</th>
                <th style="width: 7%">SAKIT</th>
                <th style="width: 7%">IZIN</th>
                <th style="width: 7%">ALFA</th>
                <th style="width: 7%">BLM<br>ABSEN</th>
            </tr>
        </thead>
        <tbody>
            @php $kelasLvlGroup = collect($classesSummary)->groupBy(function($c) { return substr($c['name'], 0, 1); }); @endphp
            
            @php
                // Sorting per kelas Name to ensure 7A, 7B, 8A, 8B...
                $sortedClasses = collect($classesSummary)->sortBy('name')->values()->all();
                $totalRows = count($sortedClasses);
            @endphp
            
            @foreach($sortedClasses as $index => $row)
            @php
                $ketTotal = $row['sakit'] + $row['izin'] + $row['alpa'] + $row['belum'];
            @endphp
            <tr>
                @if($index === 0)
                <td rowspan="{{ $totalRows }}" style="vertical-align: middle;">1</td>
                <td rowspan="{{ $totalRows }}" style="vertical-align: middle;">{{ $dateFormatted }}</td>
                @endif
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['total'] }}</td>
                <td>{{ $row['hadir'] > 0 ? $row['hadir'] : '-' }}</td>
                <td>{{ $row['telat'] > 0 ? $row['telat'] : '-' }}</td>
                <td>{{ $row['sakit'] > 0 ? $row['sakit'] : '-' }}</td>
                <td>{{ $row['izin'] > 0 ? $row['izin'] : '-' }}</td>
                <td>{{ $row['alpa'] > 0 ? $row['alpa'] : '-' }}</td>
                <td>{{ $row['belum'] > 0 ? $row['belum'] : '-' }}</td>
                @if($index === 0)
                <td rowspan="{{ $totalRows }}" style="vertical-align: middle;"></td>
                @endif
                <td>{{ $ketTotal > 0 ? $ketTotal : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>

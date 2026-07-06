<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Presensi Detail</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; }
        .header p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f4f4f4; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Detail Presensi Siswa</h2>
        <p>Bulan: {{ $monthName }} {{ $year }}</p>
        <p>Kelas: {{ $className }} | Tahun Ajaran: {{ $academicYearName }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="30">No</th>
                <th width="80">Tanggal</th>
                <th width="80">NISN</th>
                <th>Nama Siswa</th>
                <th width="60">Kelas</th>
                <th width="60">Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $row->date->format('d/m/Y') }}</td>
                <td>{{ $row->siswa->nisn ?? '-' }}</td>
                <td>{{ $row->siswa->name ?? '-' }}</td>
                <td>{{ $row->kelas->name ?? '-' }}</td>
                <td>{{ ucfirst($row->status) }}</td>
                <td>{{ $row->note ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data presensi.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

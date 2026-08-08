<table>
    <thead>
        <tr>
            <th colspan="{{ 3 + (count($monthsList) * 4) }}" style="font-weight: bold; font-size: 14px; text-align: center;">
                REKAP PRESENSI TAHUNAN PER KELAS
            </th>
        </tr>
        <tr>
            <th colspan="{{ 3 + (count($monthsList) * 4) }}" style="font-weight: bold; font-size: 12px; text-align: center;">
                {{ strtoupper($sekolah?->school_name ?? 'NAMA SEKOLAH') }}
            </th>
        </tr>
        <tr>
            <th colspan="{{ 3 + (count($monthsList) * 4) }}" style="font-weight: bold; font-size: 11px; text-align: center;">
                TAHUN AJARAN {{ strtoupper($tahunAjaran?->name ?? '') }}
            </th>
        </tr>
        <tr></tr>
        <tr>
            <th rowspan="2" style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">No</th>
            <th rowspan="2" style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Kelas</th>
            <th rowspan="2" style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Jml Siswa</th>
            @foreach($monthsList as $m)
                <th colspan="4" style="font-weight: bold; background-color: #f1f5f9; text-align: center; border: 1px solid #000000;">{{ strtoupper($m['label']) }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach($monthsList as $m)
                <th style="font-weight: bold; background-color: #e2e8f0; color: #166534; text-align: center; border: 1px solid #000000;">H</th>
                <th style="font-weight: bold; background-color: #e2e8f0; color: #4c1d95; text-align: center; border: 1px solid #000000;">S</th>
                <th style="font-weight: bold; background-color: #e2e8f0; color: #1e40af; text-align: center; border: 1px solid #000000;">I</th>
                <th style="font-weight: bold; background-color: #e2e8f0; color: #991b1b; text-align: center; border: 1px solid #000000;">A</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($classesData as $index => $row)
            <tr>
                <td style="text-align: center; border: 1px solid #000000;">{{ $index + 1 }}</td>
                <td style="font-weight: bold; border: 1px solid #000000;">{{ $row['name'] }}</td>
                <td style="text-align: center; border: 1px solid #000000;">{{ $row['student_count'] }}</td>
                @foreach($monthsList as $m)
                    @php
                        $key   = "{$m['year']}-{$m['month']}";
                        $stats = $row['months'][$key] ?? ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];
                    @endphp
                    <td style="text-align: center; font-weight: bold; color: #166534; border: 1px solid #000000;">{{ $stats['hadir'] ?: '-' }}</td>
                    <td style="text-align: center; font-weight: bold; color: #4c1d95; border: 1px solid #000000;">{{ $stats['sakit'] ?: '-' }}</td>
                    <td style="text-align: center; font-weight: bold; color: #1e40af; border: 1px solid #000000;">{{ $stats['izin'] ?: '-' }}</td>
                    <td style="text-align: center; font-weight: bold; color: #991b1b; border: 1px solid #000000;">{{ $stats['alpa'] ?: '-' }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>

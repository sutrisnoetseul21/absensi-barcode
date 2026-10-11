<table>
    <thead>
        <tr>
            <th colspan="21" style="font-weight: bold; font-size: 14px; text-align: center;">
                LAPORAN REKAPITULASI PRESENSI SHOLAT DHUHUR PER KELAS
            </th>
        </tr>
        <tr>
            <th colspan="21" style="font-weight: bold; font-size: 12px; text-align: center;">
                {{ strtoupper($sekolah?->school_name ?? 'SMP NEGERI 1 SAMPANG') }}
            </th>
        </tr>
        <tr>
            <th colspan="21" style="font-size: 10px; text-align: center; color: #475569;">
                Hari / Tanggal: {{ $selectedDateFormatted }} | Tahun Ajaran {{ strtoupper($tahunAjaran?->name ?? '-') }}
            </th>
        </tr>
        <tr></tr>
        <tr>
            <th rowspan="2" style="font-weight: bold; background-color: #064e3b; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">No</th>
            <th rowspan="2" style="font-weight: bold; background-color: #064e3b; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Kelas</th>
            <th rowspan="2" style="font-weight: bold; background-color: #064e3b; color: #ffffff; text-align: left; vertical-align: middle; border: 1px solid #000000;">Wali Kelas</th>
            <th rowspan="2" style="font-weight: bold; background-color: #064e3b; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Total Siswa</th>
            <th colspan="2" style="font-weight: bold; background-color: #064e3b; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Total Siswa (L/P)</th>
            <th colspan="2" style="font-weight: bold; background-color: #312e81; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Non-Muslim</th>
            <th colspan="2" style="font-weight: bold; background-color: #047857; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Hadir Sholat</th>
            <th colspan="2" style="font-weight: bold; background-color: #1d4ed8; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Izin / Halangan</th>
            <th colspan="2" style="font-weight: bold; background-color: #be123c; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Tidak Hadir (Alpa)</th>
            <th colspan="2" style="font-weight: bold; background-color: #475569; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Belum Absen</th>
            <th colspan="2" style="font-weight: bold; background-color: #064e3b; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">% Hadir</th>
            <th rowspan="2" style="font-weight: bold; background-color: #064e3b; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Paraf Wali Kelas / PIC</th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #047857; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">L</th>
            <th style="font-weight: bold; background-color: #047857; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">P</th>
            <th style="font-weight: bold; background-color: #3730a3; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">L</th>
            <th style="font-weight: bold; background-color: #3730a3; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">P</th>
            <th style="font-weight: bold; background-color: #059669; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">L</th>
            <th style="font-weight: bold; background-color: #059669; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">P</th>
            <th style="font-weight: bold; background-color: #2563eb; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">L</th>
            <th style="font-weight: bold; background-color: #2563eb; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">P</th>
            <th style="font-weight: bold; background-color: #e11d48; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">L</th>
            <th style="font-weight: bold; background-color: #e11d48; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">P</th>
            <th style="font-weight: bold; background-color: #64748b; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">L</th>
            <th style="font-weight: bold; background-color: #64748b; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">P</th>
            <th style="font-weight: bold; background-color: #047857; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">L</th>
            <th style="font-weight: bold; background-color: #047857; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">P</th>
        </tr>
    </thead>
    <tbody>
        @foreach($classesSummary as $index => $row)
            <tr>
                <td style="text-align: center; border: 1px solid #000000;">{{ $index + 1 }}</td>
                <td style="font-weight: bold; text-align: center; border: 1px solid #000000;">{{ $row['name'] }}</td>
                <td style="border: 1px solid #000000;">{{ $row['wali_kelas'] }}</td>
                <td style="text-align: center; border: 1px solid #000000; font-weight: bold;">{{ $row['total'] }}</td>
                <td style="text-align: center; border: 1px solid #000000;">{{ $row['total_l'] }}</td>
                <td style="text-align: center; border: 1px solid #000000;">{{ $row['total_p'] }}</td>
                
                <td style="text-align: center; color: #4338ca; border: 1px solid #000000;">{{ $row['non_muslim_l'] ?: '-' }}</td>
                <td style="text-align: center; color: #4338ca; border: 1px solid #000000;">{{ $row['non_muslim_p'] ?: '-' }}</td>
                
                <td style="text-align: center; color: #166534; font-weight: bold; border: 1px solid #000000;">{{ $row['hadir_l'] ?: '-' }}</td>
                <td style="text-align: center; color: #166534; font-weight: bold; border: 1px solid #000000;">{{ $row['hadir_p'] ?: '-' }}</td>
                
                <td style="text-align: center; color: #1d4ed8; border: 1px solid #000000;">{{ $row['ijin_l'] ?: '-' }}</td>
                <td style="text-align: center; color: #1d4ed8; border: 1px solid #000000;">{{ $row['ijin_p'] ?: '-' }}</td>
                
                <td style="text-align: center; color: #dc2626; font-weight: bold; border: 1px solid #000000;">{{ $row['tidak_hadir_l'] ?: '-' }}</td>
                <td style="text-align: center; color: #dc2626; font-weight: bold; border: 1px solid #000000;">{{ $row['tidak_hadir_p'] ?: '-' }}</td>
                
                <td style="text-align: center; font-weight: bold; {{ $row['belum_l'] > 0 ? 'background-color: #ffe4e6; color: #9f1239;' : 'color: #94a3b8;' }} border: 1px solid #000000;">{{ $row['belum_l'] ?: '-' }}</td>
                <td style="text-align: center; font-weight: bold; {{ $row['belum_p'] > 0 ? 'background-color: #ffe4e6; color: #9f1239;' : 'color: #94a3b8;' }} border: 1px solid #000000;">{{ $row['belum_p'] ?: '-' }}</td>
                
                <td style="text-align: center; font-weight: bold; border: 1px solid #000000;">{{ $row['persen_l'] }}%</td>
                <td style="text-align: center; font-weight: bold; border: 1px solid #000000;">{{ $row['persen_p'] }}%</td>
                <td style="border: 1px solid #000000;"></td>
            </tr>
        @endforeach
        <tr style="font-weight: bold; background-color: #f1f5f9;">
            <td colspan="3" style="text-align: right; font-weight: bold; border: 1px solid #000000;">TOTAL SELURUH SEKOLAH:</td>
            <td style="text-align: center; font-weight: bold; border: 1px solid #000000;">{{ $stats['total_students'] ?? 0 }}</td>
            <td style="text-align: center; font-weight: bold; border: 1px solid #000000;">{{ $stats['total_l'] ?? 0 }}</td>
            <td style="text-align: center; font-weight: bold; border: 1px solid #000000;">{{ $stats['total_p'] ?? 0 }}</td>
            <td style="text-align: center; font-weight: bold; color: #4338ca; border: 1px solid #000000;">{{ $stats['non_muslim_l'] ?? 0 }}</td>
            <td style="text-align: center; font-weight: bold; color: #4338ca; border: 1px solid #000000;">{{ $stats['non_muslim_p'] ?? 0 }}</td>
            <td style="text-align: center; font-weight: bold; color: #166534; border: 1px solid #000000;">{{ $stats['hadir_l'] ?? 0 }}</td>
            <td style="text-align: center; font-weight: bold; color: #166534; border: 1px solid #000000;">{{ $stats['hadir_p'] ?? 0 }}</td>
            <td style="text-align: center; font-weight: bold; color: #1d4ed8; border: 1px solid #000000;">{{ $stats['ijin_l'] ?? 0 }}</td>
            <td style="text-align: center; font-weight: bold; color: #1d4ed8; border: 1px solid #000000;">{{ $stats['ijin_p'] ?? 0 }}</td>
            <td style="text-align: center; font-weight: bold; color: #dc2626; border: 1px solid #000000;">{{ $stats['tidak_hadir_l'] ?? 0 }}</td>
            <td style="text-align: center; font-weight: bold; color: #dc2626; border: 1px solid #000000;">{{ $stats['tidak_hadir_p'] ?? 0 }}</td>
            <td style="text-align: center; font-weight: bold; color: #9f1239; border: 1px solid #000000;">{{ $stats['belum_l'] ?? 0 }}</td>
            <td style="text-align: center; font-weight: bold; color: #9f1239; border: 1px solid #000000;">{{ $stats['belum_p'] ?? 0 }}</td>
            <td style="text-align: center; font-weight: bold; border: 1px solid #000000;">{{ $stats['persen_l'] ?? 0 }}%</td>
            <td style="text-align: center; font-weight: bold; border: 1px solid #000000;">{{ $stats['persen_p'] ?? 0 }}%</td>
            <td style="border: 1px solid #000000;"></td>
        </tr>
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th colspan="19" style="font-weight: bold; font-size: 14px; text-align: center;">
                LAPORAN REKAPITULASI KEHADIRAN SISWA PER KELAS
            </th>
        </tr>
        <tr>
            <th colspan="19" style="font-weight: bold; font-size: 12px; text-align: center;">
                {{ strtoupper($sekolah?->school_name ?? 'SMP NEGERI 3 KEDUNGREJA') }}
            </th>
        </tr>
        <tr>
            <th colspan="19" style="font-size: 10px; text-align: center; color: #475569;">
                Hari / Tanggal: {{ $selectedDateFormatted }} | Tahun Ajaran {{ strtoupper($tahunAjaran?->name ?? '-') }}
            </th>
        </tr>
        <tr></tr>
        <tr>
            <th rowspan="2" style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">No</th>
            <th rowspan="2" style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Kelas</th>
            <th rowspan="2" style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: left; vertical-align: middle; border: 1px solid #000000;">Wali Kelas</th>
            <th rowspan="2" style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Total Siswa</th>
            <th colspan="2" style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Total Siswa (L/P)</th>
            <th colspan="2" style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Siswa Hadir</th>
            <th colspan="2" style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Sakit</th>
            <th colspan="2" style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Izin</th>
            <th colspan="2" style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Alpa</th>
            <th colspan="2" style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Belum Presensi</th>
            <th colspan="2" style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">% Hadir</th>
            <th rowspan="2" style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Paraf Wali Kelas / PIC</th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">L</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">P</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">L</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">P</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">L</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">P</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">L</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">P</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">L</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">P</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">L</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">P</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">L</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">P</th>
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
                
                <td style="text-align: center; color: #166534; font-weight: bold; border: 1px solid #000000;">{{ $row['hadir_l'] ?: '-' }}</td>
                <td style="text-align: center; color: #166534; font-weight: bold; border: 1px solid #000000;">{{ $row['hadir_p'] ?: '-' }}</td>
                
                <td style="text-align: center; border: 1px solid #000000;">{{ $row['sakit_l'] ?: '-' }}</td>
                <td style="text-align: center; border: 1px solid #000000;">{{ $row['sakit_p'] ?: '-' }}</td>
                
                <td style="text-align: center; border: 1px solid #000000;">{{ $row['izin_l'] ?: '-' }}</td>
                <td style="text-align: center; border: 1px solid #000000;">{{ $row['izin_p'] ?: '-' }}</td>
                
                <td style="text-align: center; color: #dc2626; font-weight: bold; border: 1px solid #000000;">{{ $row['alpa_l'] ?: '-' }}</td>
                <td style="text-align: center; color: #dc2626; font-weight: bold; border: 1px solid #000000;">{{ $row['alpa_p'] ?: '-' }}</td>
                
                <td style="text-align: center; font-weight: bold; {{ $row['belum_l'] > 0 ? 'background-color: #ffe4e6; color: #9f1239;' : 'color: #166534;' }} border: 1px solid #000000;">{{ $row['belum_l'] ?: '-' }}</td>
                <td style="text-align: center; font-weight: bold; {{ $row['belum_p'] > 0 ? 'background-color: #ffe4e6; color: #9f1239;' : 'color: #166534;' }} border: 1px solid #000000;">{{ $row['belum_p'] ?: '-' }}</td>
                
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
            <td style="text-align: center; font-weight: bold; color: #166534; border: 1px solid #000000;">{{ $stats['hadir_l'] ?? 0 }}</td>
            <td style="text-align: center; font-weight: bold; color: #166534; border: 1px solid #000000;">{{ $stats['hadir_p'] ?? 0 }}</td>
            <td style="text-align: center; font-weight: bold; border: 1px solid #000000;">{{ $stats['sakit_l'] ?? 0 }}</td>
            <td style="text-align: center; font-weight: bold; border: 1px solid #000000;">{{ $stats['sakit_p'] ?? 0 }}</td>
            <td style="text-align: center; font-weight: bold; border: 1px solid #000000;">{{ $stats['izin_l'] ?? 0 }}</td>
            <td style="text-align: center; font-weight: bold; border: 1px solid #000000;">{{ $stats['izin_p'] ?? 0 }}</td>
            <td style="text-align: center; font-weight: bold; color: #dc2626; border: 1px solid #000000;">{{ $stats['alpa_l'] ?? 0 }}</td>
            <td style="text-align: center; font-weight: bold; color: #dc2626; border: 1px solid #000000;">{{ $stats['alpa_p'] ?? 0 }}</td>
            <td style="text-align: center; font-weight: bold; border: 1px solid #000000;">{{ $stats['belum_l'] ?? 0 }}</td>
            <td style="text-align: center; font-weight: bold; border: 1px solid #000000;">{{ $stats['belum_p'] ?? 0 }}</td>
            <td style="text-align: center; font-weight: bold; border: 1px solid #000000;">{{ $stats['persen_l'] ?? 0 }}%</td>
            <td style="text-align: center; font-weight: bold; border: 1px solid #000000;">{{ $stats['persen_p'] ?? 0 }}%</td>
            <td style="border: 1px solid #000000;"></td>
        </tr>
        <tr></tr>
        <tr>
            <td colspan="4" style="text-align: center; font-size: 11px;">
                Mengetahui,<br>
                Kepala Sekolah<br><br><br><br>
                <strong><u>{{ $sekolah?->headmaster_name ?? '....................................' }}</u></strong><br>
                NIP. {{ $sekolah?->headmaster_nip ?? '-' }}
            </td>
            <td colspan="5"></td>
            <td colspan="4" style="text-align: center; font-size: 11px;">
                Kedungreja, {{ $selectedDateFormatted }}<br>
                Petugas Presensi Sekolah<br><br><br><br>
                <strong><u>{{ auth()->user()->name ?? 'Administrator Presensi' }}</u></strong><br>
                Petugas Piket / Presensi
            </td>
        </tr>
    </tbody>
</table>

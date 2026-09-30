<table>
    <thead>
        <tr>
            <th colspan="7" style="font-weight: bold; font-size: 14px; text-align: center;">
                DAFTAR SISWA BELUM PRESENSI
            </th>
        </tr>
        <tr>
            <th colspan="7" style="font-weight: bold; font-size: 12px; text-align: center;">
                {{ strtoupper($sekolah?->school_name ?? 'SMP NEGERI 3 KEDUNGREJA') }}
            </th>
        </tr>
        <tr>
            <th colspan="7" style="font-size: 10px; text-align: center; color: #475569;">
                Hari / Tanggal: {{ $selectedDateFormatted }} | Tahun Ajaran {{ strtoupper($tahunAjaran?->name ?? '-') }}
            </th>
        </tr>
        <tr></tr>
        <tr>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">No</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: left; vertical-align: middle; border: 1px solid #000000;">Nama Siswa</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Kelas</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">NISN</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Status</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">No HP Orang Tua / Wali</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: left; vertical-align: middle; border: 1px solid #000000;">Catatan / Tindak Lanjut</th>
        </tr>
    </thead>
    <tbody>
        @forelse($unattendedStudents as $index => $st)
            <tr>
                <td style="text-align: center; border: 1px solid #000000;">{{ $index + 1 }}</td>
                <td style="font-weight: bold; border: 1px solid #000000;">{{ $st['name'] }}</td>
                <td style="text-align: center; font-weight: bold; border: 1px solid #000000;">Kelas {{ $st['class_name'] }}</td>
                <td style="text-align: center; border: 1px solid #000000;">{{ $st['nisn'] ?? '-' }}</td>
                <td style="text-align: center; font-weight: bold; color: #dc2626; background-color: #ffe4e6; border: 1px solid #000000;">Belum Presensi</td>
                <td style="text-align: center; border: 1px solid #000000;">{{ $st['phone'] ?: '-' }}</td>
                <td style="border: 1px solid #000000;"></td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 10px; font-weight: bold; color: #166534; border: 1px solid #000000;">
                    Alhamdulillah, seluruh siswa sudah terdata hadir/keterangan hari ini.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

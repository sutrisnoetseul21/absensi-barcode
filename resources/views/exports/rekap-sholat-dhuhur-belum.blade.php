<table>
    <thead>
        <tr>
            <th colspan="7" style="font-weight: bold; font-size: 14px; text-align: center;">
                DAFTAR SISWA MUSLIM BELUM PRESENSI SHOLAT DHUHUR
            </th>
        </tr>
        <tr>
            <th colspan="7" style="font-weight: bold; font-size: 12px; text-align: center;">
                {{ strtoupper($sekolah?->school_name ?? 'SMP NEGERI 1 SAMPANG') }}
            </th>
        </tr>
        <tr>
            <th colspan="7" style="font-size: 10px; text-align: center; color: #475569;">
                Hari / Tanggal: {{ $selectedDateFormatted }} | Tahun Ajaran {{ strtoupper($tahunAjaran?->name ?? '-') }}
            </th>
        </tr>
        <tr></tr>
        <tr>
            <th style="font-weight: bold; background-color: #064e3b; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">No</th>
            <th style="font-weight: bold; background-color: #064e3b; color: #ffffff; text-align: left; vertical-align: middle; border: 1px solid #000000;">Nama Siswa</th>
            <th style="font-weight: bold; background-color: #064e3b; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Kelas</th>
            <th style="font-weight: bold; background-color: #064e3b; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">NISN</th>
            <th style="font-weight: bold; background-color: #064e3b; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Status</th>
            <th style="font-weight: bold; background-color: #064e3b; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">No HP Orang Tua / Wali</th>
            <th style="font-weight: bold; background-color: #064e3b; color: #ffffff; text-align: left; vertical-align: middle; border: 1px solid #000000;">Catatan / Tindak Lanjut</th>
        </tr>
    </thead>
    <tbody>
        @forelse($unattendedStudents as $index => $st)
            <tr>
                <td style="text-align: center; border: 1px solid #000000;">{{ $index + 1 }}</td>
                <td style="font-weight: bold; border: 1px solid #000000;">{{ $st['name'] }}</td>
                <td style="text-align: center; font-weight: bold; border: 1px solid #000000;">Kelas {{ $st['class_name'] }}</td>
                <td style="text-align: center; border: 1px solid #000000;">{{ $st['nisn'] ?? '-' }}</td>
                <td style="text-align: center; font-weight: bold; color: #dc2626; background-color: #ffe4e6; border: 1px solid #000000;">Belum Absen Sholat</td>
                <td style="text-align: center; border: 1px solid #000000;">{{ $st['phone'] ?: '-' }}</td>
                <td style="border: 1px solid #000000;"></td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 10px; font-weight: bold; color: #166534; border: 1px solid #000000;">
                    Alhamdulillah, seluruh siswa Muslim sudah terdata hadir atau memiliki keterangan sholat dhuhur hari ini.
                </td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr></tr>
        <tr>
            <td colspan="2" style="text-align: center;">Mengetahui,</td>
            <td colspan="3"></td>
            <td colspan="2" style="text-align: center;">Sampang, {{ $selectedDateFormatted }}</td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center; font-weight: bold;">Kepala {{ $sekolah?->school_name ?? 'SMP Negeri 1 Sampang' }}</td>
            <td colspan="3"></td>
            <td colspan="2" style="text-align: center; font-weight: bold;">Koordinator Sholat Dhuhur</td>
        </tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr>
            <td colspan="2" style="text-align: center; font-weight: bold; text-decoration: underline;">{{ $sekolah?->principal_name ?? 'Drs. DIRWANTO' }}</td>
            <td colspan="3"></td>
            <td colspan="2" style="text-align: center; font-weight: bold; text-decoration: underline;">{{ $koordinator?->name ?? 'ASIS, S.Pd.I.' }}</td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;">NIP. {{ $sekolah?->principal_nip ?? '196705121995121002' }}</td>
            <td colspan="3"></td>
            <td colspan="2" style="text-align: center;">NIP. {{ $koordinator?->nip ?? '197601282007011009' }}</td>
        </tr>
    </tfoot>
</table>

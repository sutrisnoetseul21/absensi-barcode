<table>
    <thead>
        <tr>
            <th colspan="9" style="font-weight: bold; font-size: 14px; text-align: center;">
                DATA KEHADIRAN SHOLAT DHUHUR SELURUH SISWA
            </th>
        </tr>
        <tr>
            <th colspan="9" style="font-weight: bold; font-size: 12px; text-align: center;">
                {{ strtoupper($sekolah?->school_name ?? 'SMP NEGERI 1 SAMPANG') }}
            </th>
        </tr>
        <tr>
            <th colspan="9" style="font-size: 10px; text-align: center; color: #475569;">
                Hari / Tanggal: {{ $selectedDateFormatted }} | Tahun Ajaran {{ strtoupper($tahunAjaran?->name ?? '-') }}
            </th>
        </tr>
        <tr></tr>
        <tr>
            <th style="font-weight: bold; background-color: #064e3b; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">No</th>
            <th style="font-weight: bold; background-color: #064e3b; color: #ffffff; text-align: left; vertical-align: middle; border: 1px solid #000000;">Nama Siswa</th>
            <th style="font-weight: bold; background-color: #064e3b; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Kelas</th>
            <th style="font-weight: bold; background-color: #064e3b; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">NISN</th>
            <th style="font-weight: bold; background-color: #064e3b; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">L/P</th>
            <th style="font-weight: bold; background-color: #064e3b; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Agama</th>
            <th style="font-weight: bold; background-color: #064e3b; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Status Presensi</th>
            <th style="font-weight: bold; background-color: #064e3b; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Waktu Presensi</th>
            <th style="font-weight: bold; background-color: #064e3b; color: #ffffff; text-align: left; vertical-align: middle; border: 1px solid #000000;">Catatan / Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($allStudents as $index => $st)
            @php
                $statusLabels = [
                    'hadir'       => 'Hadir Berjamaah',
                    'ijin'        => 'Izin / Halangan',
                    'tidak_hadir' => 'Tidak Hadir (Alpa)',
                    'belum'       => 'Belum Presensi',
                    'non_muslim'  => 'Non-Muslim (Bebas Kewajiban)',
                ];
                $label = $statusLabels[$st['status']] ?? ucfirst($st['status']);
            @endphp
            <tr>
                <td style="text-align: center; border: 1px solid #000000;">{{ $index + 1 }}</td>
                <td style="font-weight: bold; border: 1px solid #000000;">{{ $st['name'] }}</td>
                <td style="text-align: center; font-weight: bold; border: 1px solid #000000;">Kelas {{ $st['class_name'] }}</td>
                <td style="text-align: center; border: 1px solid #000000;">{{ $st['nisn'] ?? '-' }}</td>
                <td style="text-align: center; border: 1px solid #000000;">{{ $st['gender'] }}</td>
                <td style="text-align: center; border: 1px solid #000000;">{{ $st['religion'] ?: 'Islam' }}</td>
                <td style="text-align: center; font-weight: bold; border: 1px solid #000000;">
                    @if(!empty($st['is_non_muslim']))
                        <span style="color: #4338ca;">{{ $label }}</span>
                    @elseif($st['status'] === 'hadir')
                        <span style="color: #166534;">{{ $label }}</span>
                    @elseif($st['status'] === 'ijin')
                        <span style="color: #1d4ed8;">{{ $label }}</span>
                    @elseif($st['status'] === 'tidak_hadir')
                        <span style="color: #dc2626;">{{ $label }}</span>
                    @else
                        <span style="color: #ea580c;">{{ $label }}</span>
                    @endif
                </td>
                <td style="text-align: center; border: 1px solid #000000;">{{ $st['scan_time'] ? $st['scan_time'] . ' WIB' : '-' }}</td>
                <td style="border: 1px solid #000000;">{{ $st['keterangan'] ?: '-' }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr></tr>
        <tr>
            <td colspan="3" style="text-align: center;">Mengetahui,</td>
            <td colspan="3"></td>
            <td colspan="3" style="text-align: center;">Sampang, {{ $selectedDateFormatted }}</td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: center; font-weight: bold;">Kepala {{ $sekolah?->school_name ?? 'SMP Negeri 1 Sampang' }}</td>
            <td colspan="3"></td>
            <td colspan="3" style="text-align: center; font-weight: bold;">Koordinator Sholat Dhuhur</td>
        </tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr>
            <td colspan="3" style="text-align: center; font-weight: bold; text-decoration: underline;">{{ $sekolah?->principal_name ?? 'Drs. DIRWANTO' }}</td>
            <td colspan="3"></td>
            <td colspan="3" style="text-align: center; font-weight: bold; text-decoration: underline;">{{ $koordinator?->name ?? 'ASIS, S.Pd.I.' }}</td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: center;">NIP. {{ $sekolah?->principal_nip ?? '196705121995121002' }}</td>
            <td colspan="3"></td>
            <td colspan="3" style="text-align: center;">NIP. {{ $koordinator?->nip ?? '197601282007011009' }}</td>
        </tr>
    </tfoot>
</table>

@php $colSpan = !empty($includeCheckout) ? 10 : 8; @endphp
<table>
    <thead>
        <tr>
            <th colspan="{{ $colSpan }}" style="font-weight: bold; font-size: 14px; text-align: center;">
                DATA KEHADIRAN SELURUH SISWA
            </th>
        </tr>
        <tr>
            <th colspan="{{ $colSpan }}" style="font-weight: bold; font-size: 12px; text-align: center;">
                {{ strtoupper($sekolah?->school_name ?? 'SMP NEGERI 3 KEDUNGREJA') }}
            </th>
        </tr>
        <tr>
            <th colspan="{{ $colSpan }}" style="font-size: 10px; text-align: center; color: #475569;">
                Hari / Tanggal: {{ $selectedDateFormatted }} | Tahun Ajaran {{ strtoupper($tahunAjaran?->name ?? '-') }}
            </th>
        </tr>
        <tr></tr>
        <tr>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">No</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: left; vertical-align: middle; border: 1px solid #000000;">Nama Siswa</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Kelas</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">NISN</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Status Kehadiran</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Jam Scan / Datang</th>
            @if(!empty($includeCheckout))
                <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Jam Pulang</th>
                <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Status Pulang</th>
            @endif
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Status Fisik</th>
            <th style="font-weight: bold; background-color: #1e3a5f; color: #ffffff; text-align: left; vertical-align: middle; border: 1px solid #000000;">Catatan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($allStudents as $index => $st)
            @php
                $statusLabels = [
                    'hadir' => 'Hadir Tepat Waktu',
                    'telat' => 'Terlambat',
                    'izin'  => 'Izin',
                    'sakit' => 'Sakit',
                    'alpa'  => 'Alpa',
                    'belum' => 'Belum Presensi',
                ];
                $label = $statusLabels[$st['status']] ?? ucfirst($st['status']);
                $isHadirFisik = in_array($st['status'], ['hadir', 'telat']);
                $pulangLabel = !empty($st['status_pulang'])
                    ? ucwords(str_replace('_', ' ', (string) $st['status_pulang']))
                    : ($isHadirFisik ? 'Belum Pulang' : '-');
            @endphp
            <tr>
                <td style="text-align: center; border: 1px solid #000000;">{{ $index + 1 }}</td>
                <td style="font-weight: bold; border: 1px solid #000000;">{{ $st['name'] }}</td>
                <td style="text-align: center; font-weight: bold; border: 1px solid #000000;">Kelas {{ $st['class_name'] }}</td>
                <td style="text-align: center; border: 1px solid #000000;">{{ $st['nisn'] ?? '-' }}</td>
                <td style="text-align: center; font-weight: bold; border: 1px solid #000000;">{{ $label }}</td>
                <td style="text-align: center; border: 1px solid #000000;">{{ $st['scan_time'] ? $st['scan_time'] . ' WIB' : '-' }}</td>
                @if(!empty($includeCheckout))
                    <td style="text-align: center; border: 1px solid #000000;">{{ !empty($st['scan_out']) ? $st['scan_out'] . ' WIB' : '-' }}</td>
                    <td style="text-align: center; border: 1px solid #000000;">{{ $pulangLabel }}</td>
                @endif
                <td style="text-align: center; font-weight: bold; border: 1px solid #000000; {{ $isHadirFisik ? 'color: #166534;' : 'color: #94a3b8;' }}">
                    {{ $isHadirFisik ? 'Hadir di Sekolah' : '-' }}
                </td>
                <td style="border: 1px solid #000000;">{{ $st['note'] ?: '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

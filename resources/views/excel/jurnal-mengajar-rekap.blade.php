<table>
    <thead>
        <tr>
            <th colspan="12" style="font-size: 14pt; font-weight: bold; text-align: center;">
                {{ $sekolah?->school_name ?? 'SEKOLAH MENENGAH PERTAMA' }}
            </th>
        </tr>
        <tr>
            <th colspan="12" style="font-size: 9pt; text-align: center; color: #475569;">
                {{ $sekolah?->school_address ?? 'Alamat Sekolah Terpadu' }} | Email: {{ $sekolah?->school_email ?? '-' }} | Telp: {{ $sekolah?->school_phone ?? '-' }}
            </th>
        </tr>
        <tr>
            <th colspan="12" style="height: 10px;"></th>
        </tr>
        <tr>
            <th colspan="12" style="font-size: 12pt; font-weight: bold; text-align: center;">
                REKAPITULASI JURNAL AGENDA PEMBELAJARAN (KBM)
            </th>
        </tr>
        <tr>
            <th colspan="12" style="font-size: 10pt; text-align: center;">
                Tahun Ajaran: {{ $tahunAjaran?->name ?? '-' }} | Periode: {{ $periodeLabel ?? 'Semua Periode' }}
            </th>
        </tr>
        <tr>
            <th colspan="12" style="height: 15px;"></th>
        </tr>
        <tr>
            <th colspan="2" style="font-weight: bold;">Guru Pengampu</th>
            <th colspan="4">: {{ $guru?->name ?? 'Semua Guru Pengampu' }}</th>
            <th colspan="2" style="font-weight: bold;">Mata Pelajaran</th>
            <th colspan="4">: {{ $mapel?->nama_mapel ?? 'Semua Mata Pelajaran' }}</th>
        </tr>
        <tr>
            <th colspan="2" style="font-weight: bold;">NIP / NUPTK</th>
            <th colspan="4">: {{ $guru?->nip ?? '-' }}</th>
            <th colspan="2" style="font-weight: bold;">Kelas Rombel</th>
            <th colspan="4">: {{ $kelas?->name ?? 'Semua Kelas' }}</th>
        </tr>
        <tr>
            <th colspan="12" style="height: 10px;"></th>
        </tr>
        <tr style="background-color: #1e3a5f; color: #ffffff;">
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">No</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">Hari &amp; Tanggal</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">Jam Ke (JP)</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">Kelas</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">Mata Pelajaran</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">Materi Pokok / Bahasan</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">Uraian Aktivitas Pembelajaran</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">Total Siswa</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">Siswa Hadir</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">Tidak Hadir</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">% Hadir</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">Refleksi / Catatan Guru</th>
        </tr>
    </thead>
    <tbody>
        @forelse($jurnals as $j)
            <tr>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $loop->iteration }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $j->tanggal->translatedFormat('d/m/Y') }} ({{ $j->tanggal->translatedFormat('l') }})</td>
                <td style="text-align: center; font-weight: bold; border: 1px solid #cbd5e1;">{{ $j->jam_ke_label }}</td>
                <td style="text-align: center; font-weight: bold; border: 1px solid #cbd5e1;">{{ $j->kelas?->name }}</td>
                <td style="border: 1px solid #cbd5e1;">{{ $j->mataPelajaran?->nama_mapel }}</td>
                <td style="font-weight: bold; border: 1px solid #cbd5e1;">{{ $j->materi }}</td>
                <td style="border: 1px solid #cbd5e1;">{{ $j->uraian_materi }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $j->total_siswa }}</td>
                <td style="text-align: center; font-weight: bold; color: #166534; border: 1px solid #cbd5e1;">{{ $j->total_hadir }}</td>
                <td style="text-align: center; color: #dc2626; border: 1px solid #cbd5e1;">{{ $j->total_tidak_mengikuti }}</td>
                <td style="text-align: center; font-weight: bold; border: 1px solid #cbd5e1;">{{ $j->persentase_kehadiran }}%</td>
                <td style="border: 1px solid #cbd5e1;">{{ $j->refleksi ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="12" style="text-align: center; border: 1px solid #cbd5e1; padding: 15px; color: #64748b;">
                    Tidak ada catatan jurnal mengajar pada periode ini.
                </td>
            </tr>
        @endforelse
        <tr>
            <td colspan="12" style="height: 20px;"></td>
        </tr>
        <tr>
            <td colspan="4" style="text-align: center;">
                Mengetahui,<br>
                Kepala Sekolah
                <br><br><br><br>
                <b><u>{{ $sekolah?->principal_name ?? '.........................................' }}</u></b><br>
                NIP. {{ $sekolah?->principal_nip ?? '-' }}
            </td>
            <td colspan="4"></td>
            <td colspan="4" style="text-align: center;">
                {{ $sekolah?->kota ?? 'Tempat' }}, {{ now()->translatedFormat('d F Y') }}<br>
                Guru Mata Pelajaran
                <br><br><br><br>
                <b><u>{{ $guru?->name ?? '.........................................' }}</u></b><br>
                NIP. {{ $guru?->nip ?? '-' }}
            </td>
        </tr>
    </tbody>
</table>

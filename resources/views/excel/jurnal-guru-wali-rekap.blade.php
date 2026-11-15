<table>
    <thead>
        <tr>
            <th colspan="15" style="font-size: 14pt; font-weight: bold; text-align: center;">
                {{ $sekolah?->school_name ?? 'SEKOLAH' }}
            </th>
        </tr>
        <tr>
            <th colspan="15" style="font-size: 9pt; text-align: center; color: #475569;">
                {{ $sekolah?->school_address ?? '-' }} | Email: {{ $sekolah?->school_email ?? '-' }} | Telp: {{ $sekolah?->school_phone ?? '-' }}
            </th>
        </tr>
        <tr>
            <th colspan="15" style="height: 10px;"></th>
        </tr>
        <tr>
            <th colspan="15" style="font-size: 12pt; font-weight: bold; text-align: center;">
                REKAPITULASI JURNAL PENDAMPINGAN GURU WALI (SI-WALI)
            </th>
        </tr>
        <tr>
            <th colspan="15" style="font-size: 10pt; text-align: center;">
                Tahun Ajaran: {{ $tahunAjaran?->name ?? '-' }} | Periode: {{ $periodeLabel }}
            </th>
        </tr>
        <tr>
            <th colspan="15" style="height: 12px;"></th>
        </tr>
        <tr>
            <th colspan="2" style="font-weight: bold;">Guru Wali</th>
            <th colspan="5">: {{ $guru?->name ?? 'Semua Guru Wali' }}</th>
            <th colspan="2" style="font-weight: bold;">Nama Kelompok</th>
            <th colspan="6">: {{ $kelompok?->nama_kelompok ?? 'Semua Kelompok' }}</th>
        </tr>
        <tr>
            <th colspan="2" style="font-weight: bold;">NIP / NUPTK</th>
            <th colspan="5">: {{ $guru?->nip ?? '-' }}</th>
            <th colspan="2" style="font-weight: bold;">Total Catatan</th>
            <th colspan="6">: {{ $jurnals->count() }} Sesi Pendampingan</th>
        </tr>
        <tr>
            <th colspan="15" style="height: 10px;"></th>
        </tr>
        <tr>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">No</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">Tanggal &amp; Waktu</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">Guru Wali</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">Kelompok</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">Nama Siswa</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">NISN</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">Kelas</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">Jenis Pendampingan</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">Kategori 4 Pilar</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">Sentimen</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">Status Sesi</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">Kolaborasi / Rujukan</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">Uraian Pembahasan</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">Rencana Tindak Lanjut</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #1e3a5f; color: #ffffff;">Sifat Catatan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($jurnals as $j)
            @php
                $kelasName = $j->kelas?->name ?? $j->siswa?->getRombelName($tahunAjaran?->id) ?? '-';
                $sentimenValue = $j->kategori_sentimen instanceof \BackedEnum ? $j->kategori_sentimen->value : ($j->kategori_sentimen ?? '-');
            @endphp
            <tr>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $loop->iteration }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $j->tanggal_waktu?->translatedFormat('d/m/Y H:i') ?? '-' }}</td>
                <td style="border: 1px solid #cbd5e1;">{{ $j->guru?->name ?? '-' }}</td>
                <td style="border: 1px solid #cbd5e1;">{{ $j->kelompok?->nama_kelompok ?? '-' }}</td>
                <td style="font-weight: bold; border: 1px solid #cbd5e1;">{{ $j->siswa?->name ?? '-' }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $j->siswa?->nisn ?? '-' }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $kelasName }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $j->jenis_pendampingan ?? '-' }}</td>
                <td style="border: 1px solid #cbd5e1;">{{ $j->kategori_pendampingan ?? '-' }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $sentimenValue }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $j->status_sesi ?? '-' }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $j->rujukan_kolaborasi ?? 'Mandiri' }}</td>
                <td style="border: 1px solid #cbd5e1;">{{ $j->uraian_pembahasan ?? '-' }}</td>
                <td style="border: 1px solid #cbd5e1;">{{ $j->rencana_tindak_lanjut ?? '-' }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $j->is_public_note ? 'Publik (Siswa)' : 'Privat (Guru/Supervisi)' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="15" style="text-align: center; border: 1px solid #cbd5e1; padding: 15px; color: #64748b;">
                    Tidak ada catatan jurnal pendampingan Guru Wali pada periode ini.
                </td>
            </tr>
        @endforelse
        <tr>
            <td colspan="15" style="height: 20px;"></td>
        </tr>
        <tr>
            <td colspan="5" style="text-align: center;">
                Mengetahui,<br>
                Kepala Sekolah
                <br><br><br><br>
                <b><u>{{ $sekolah?->principal_name ?? '.........................................' }}</u></b><br>
                NIP. {{ $sekolah?->principal_nip ?? '-' }}
            </td>
            <td colspan="5"></td>
            <td colspan="5" style="text-align: center;">
                {{ $sekolah?->tempat_rapor ?? ($sekolah?->kota ?? 'Tempat') }}, {{ now()->translatedFormat('d F Y') }}<br>
                {{ $guru ? 'Guru Wali' : 'Koordinator / Supervisi Akademik' }}
                <br><br><br><br>
                <b><u>{{ $guru?->name ?? (auth()->user()?->name ?? '.........................................') }}</u></b><br>
                @if($guru?->nip)
                    NIP. {{ $guru->nip }}
                @endif
            </td>
        </tr>
    </tbody>
</table>


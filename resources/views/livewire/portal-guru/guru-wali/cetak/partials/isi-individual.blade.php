<!-- ================================================================= -->
<!-- FILE ISI: LAPORAN PENDAMPINGAN INDIVIDUAL PESERTA DIDIK           -->
<!-- Digunakan oleh: Cetak Individual & Cetak Masal                    -->
<!-- ================================================================= -->

<!-- JUDUL DOKUMEN -->
<div class="doc-title">
    <h1>Laporan Hasil Pendampingan Individual Peserta Didik</h1>
    <div class="sub-title">GURU WALI &bull; PERMENDIKDASMEN NO. 11 TAHUN 2025</div>
    <div class="period-label">Tahun Ajaran {{ $tahunAjaran->name }} &bull; Periode: {{ $labelPeriode }}</div>
</div>

<!-- BAGIAN I: IDENTITAS PESERTA DIDIK -->
<div class="section-header">I. Identitas Peserta Didik & Guru Wali</div>
<table class="identitas-table">
    <tr>
        <td class="lbl">Nama Peserta Didik</td>
        <td class="sep">:</td>
        <td class="val">{{ $siswa->name }}</td>

        <td class="lbl">Guru Wali</td>
        <td class="sep">:</td>
        <td class="val">{{ $teacher->name }}</td>
    </tr>
    <tr>
        <td class="lbl">NIS / NISN</td>
        <td class="sep">:</td>
        <td class="val">{{ $siswa->nis ?? '—' }} / {{ $siswa->nisn ?? '—' }}</td>

        <td class="lbl">NIP Guru Wali</td>
        <td class="sep">:</td>
        <td class="val">{{ $teacher->nip ?? '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Kelas / Rombel</td>
        <td class="sep">:</td>
        <td class="val">{{ $kelas }}</td>

        <td class="lbl">Kelompok Binaan</td>
        <td class="sep">:</td>
        <td class="val">{{ $kelompok->nama_kelompok }}</td>
    </tr>
</table>

<!-- BAGIAN II: REKAPITULASI SESI PENDAMPINGAN -->
<div class="section-header">II. Rekapitulasi Sesi Pendampingan Guru Wali</div>

@if($periode === 'tahunan')
    <!-- Sub-bagian A: Semester 1 (Ganjil) -->
    <div style="font-weight: bold; font-size: 8.5pt; margin: 3px 0 2px 0;">
        A. Semester 1 (Ganjil: Juli – Desember {{ $tahunAjaran->start_year ?? date('Y') }}) &bull; Total: {{ $semester1Jurnals->count() }} Sesi
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 75px;">Tanggal</th>
                <th style="width: 70px;">Bentuk</th>
                <th style="width: 100px;">Pilar Fokus</th>
                <th>Topik / Uraian Masalah</th>
                <th style="width: 110px;">Tindak Lanjut & Kolaborasi</th>
                <th style="width: 75px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($semester1Jurnals as $s1Idx => $jurnal)
            <tr>
                <td class="center">{{ $s1Idx + 1 }}</td>
                <td class="center">
                    {{ \Carbon\Carbon::parse($jurnal->tanggal_waktu)->translatedFormat('d/m/Y') }}
                    <div style="font-size: 7.5pt; color: #555;">{{ \Carbon\Carbon::parse($jurnal->tanggal_waktu)->format('H:i') }} WIB</div>
                </td>
                <td class="center">{{ $jurnal->jenis_pendampingan }}</td>
                <td>
                    <span class="pilar-badge">{{ $jurnal->kategori_pendampingan }}</span>
                </td>
                <td>{{ $jurnal->uraian_pembahasan }}</td>
                <td>
                    {{ $jurnal->rencana_tindak_lanjut ?? '—' }}
                    @if($jurnal->rujukan_kolaborasi && $jurnal->rujukan_kolaborasi !== 'Mandiri')
                        <div style="font-size: 7.5pt; color: #000; font-weight: bold;">Rujukan: {{ $jurnal->rujukan_kolaborasi }}</div>
                        @if($jurnal->konselingBk)
                            <div style="font-size: 7pt; color: #000; font-style: italic;">(BK: {{ $jurnal->konselingBk->status_kasus }})</div>
                        @endif
                    @endif
                </td>
                <td class="center">
                    <strong>{{ $jurnal->status_sesi }}</strong>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="center" style="font-style: italic; color: #666; padding: 4px;">
                    Tidak ada catatan pendampingan pada Semester 1 (Ganjil).
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Sub-bagian B: Semester 2 (Genap) -->
    <div style="font-weight: bold; font-size: 8.5pt; margin: 4px 0 2px 0;">
        B. Semester 2 (Genap: Januari – Juni {{ $tahunAjaran->end_year ?? (date('Y') + 1) }}) &bull; Total: {{ $semester2Jurnals->count() }} Sesi
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 75px;">Tanggal</th>
                <th style="width: 70px;">Bentuk</th>
                <th style="width: 100px;">Pilar Fokus</th>
                <th>Topik / Uraian Masalah</th>
                <th style="width: 110px;">Tindak Lanjut & Kolaborasi</th>
                <th style="width: 75px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($semester2Jurnals as $s2Idx => $jurnal)
            <tr>
                <td class="center">{{ $s2Idx + 1 }}</td>
                <td class="center">
                    {{ \Carbon\Carbon::parse($jurnal->tanggal_waktu)->translatedFormat('d/m/Y') }}
                    <div style="font-size: 7.5pt; color: #555;">{{ \Carbon\Carbon::parse($jurnal->tanggal_waktu)->format('H:i') }} WIB</div>
                </td>
                <td class="center">{{ $jurnal->jenis_pendampingan }}</td>
                <td>
                    <span class="pilar-badge">{{ $jurnal->kategori_pendampingan }}</span>
                </td>
                <td>{{ $jurnal->uraian_pembahasan }}</td>
                <td>
                    {{ $jurnal->rencana_tindak_lanjut ?? '—' }}
                    @if($jurnal->rujukan_kolaborasi && $jurnal->rujukan_kolaborasi !== 'Mandiri')
                        <div style="font-size: 7.5pt; color: #000; font-weight: bold;">Rujukan: {{ $jurnal->rujukan_kolaborasi }}</div>
                        @if($jurnal->konselingBk)
                            <div style="font-size: 7pt; color: #000; font-style: italic;">(BK: {{ $jurnal->konselingBk->status_kasus }})</div>
                        @endif
                    @endif
                </td>
                <td class="center">
                    <strong>{{ $jurnal->status_sesi }}</strong>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="center" style="font-style: italic; color: #666; padding: 4px;">
                    Tidak ada catatan pendampingan pada Semester 2 (Genap).
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

@else
    <!-- Tampilan 1 Semester -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 75px;">Tanggal</th>
                <th style="width: 70px;">Bentuk</th>
                <th style="width: 100px;">Pilar Fokus</th>
                <th>Topik / Uraian Masalah</th>
                <th style="width: 110px;">Tindak Lanjut & Kolaborasi</th>
                <th style="width: 75px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jurnals as $jIdx => $jurnal)
            <tr>
                <td class="center">{{ $jIdx + 1 }}</td>
                <td class="center">
                    {{ \Carbon\Carbon::parse($jurnal->tanggal_waktu)->translatedFormat('d/m/Y') }}
                    <div style="font-size: 7.5pt; color: #555;">{{ \Carbon\Carbon::parse($jurnal->tanggal_waktu)->format('H:i') }} WIB</div>
                </td>
                <td class="center">{{ $jurnal->jenis_pendampingan }}</td>
                <td>
                    <span class="pilar-badge">{{ $jurnal->kategori_pendampingan }}</span>
                </td>
                <td>{{ $jurnal->uraian_pembahasan }}</td>
                <td>
                    {{ $jurnal->rencana_tindak_lanjut ?? '—' }}
                    @if($jurnal->rujukan_kolaborasi && $jurnal->rujukan_kolaborasi !== 'Mandiri')
                        <div style="font-size: 7.5pt; color: #000; font-weight: bold;">Rujukan: {{ $jurnal->rujukan_kolaborasi }}</div>
                        @if($jurnal->konselingBk)
                            <div style="font-size: 7pt; color: #000; font-style: italic;">(BK: {{ $jurnal->konselingBk->status_kasus }})</div>
                        @endif
                    @endif
                </td>
                <td class="center">
                    <strong>{{ $jurnal->status_sesi }}</strong>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="center" style="font-style: italic; color: #666; padding: 4px;">
                    Tidak ada catatan pendampingan untuk periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
@endif

<!-- BAGIAN III: CATATAN KONSULTASI MANDIRI -->
<div class="section-header">III. Catatan Konsultasi Mandiri Inisiatif Peserta Didik</div>
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 25px;">No</th>
            <th style="width: 75px;">Tgl Pengajuan</th>
            <th style="width: 110px;">Kategori Topik</th>
            <th>Pesan / Uraian Dari Peserta Didik</th>
            <th style="width: 85px;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($konsultasis as $kIdx => $k)
        <tr>
            <td class="center">{{ $kIdx + 1 }}</td>
            <td class="center">
                {{ \Carbon\Carbon::parse($k->created_at)->translatedFormat('d/m/Y') }}
                <div style="font-size: 7.5pt; color: #333;">{{ $k->mode_konsultasi ?? 'Tatap Muka' }}</div>
            </td>
            <td>
                <span class="pilar-badge">{{ $k->kategori_pendampingan ?? 'Umum' }}</span>
            </td>
            <td>
                <div style="font-weight: bold; color: #000;">{{ $k->topik_konsultasi }}</div>
                @if($k->detail_permasalahan)
                    <div style="font-size: 7.5pt; color: #000; margin-top: 1px;">{{ $k->detail_permasalahan }}</div>
                @endif
                @if($k->tanggapan_guru)
                    <div style="font-size: 7.5pt; color: #000; margin-top: 2px;">
                        <strong>Respon Guru:</strong> {{ $k->tanggapan_guru }}
                    </div>
                @endif
            </td>
            <td class="center" style="color: #000;">
                @if(in_array($k->status_pengajuan, ['Dikonversi ke Jurnal', 'Selesai']))
                    <strong>Selesai</strong>
                @elseif($k->status_pengajuan?->value === 'Dijadwalkan')
                    <strong>Dijadwalkan</strong>
                    @if($k->jadwal_pasti)
                        <div style="font-size: 7pt; color: #000;">{{ \Carbon\Carbon::parse($k->jadwal_pasti)->translatedFormat('d/m/Y H:i') }}</div>
                    @endif
                @elseif($k->status_pengajuan?->value === 'Ditolak')
                    <strong>Ditolak</strong>
                @else
                    <strong>{{ $k->status_pengajuan }}</strong>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="center" style="font-style: italic; color: #666; padding: 4px;">
                Tidak ada riwayat pengajuan konsultasi mandiri oleh peserta didik pada periode ini.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

<!-- BAGIAN IV: RINGKASAN CAPAIAN 4 PILAR -->
<div class="section-header">IV. Ringkasan Capaian Berdasarkan 4 Pilar Bimbingan</div>
<table class="pilar-summary-table">
    <thead>
        <tr>
            <th>Pilar Akademik</th>
            <th>Pilar Karakter & Kedisiplinan</th>
            <th>Pilar Minat & Bakat / Ekskul</th>
            <th>Pilar Sosial & Psikologis</th>
            <th>Total Kumulatif Sesi</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>{{ $distribusiPilar['Akademik'] }}</strong> Sesi</td>
            <td><strong>{{ $distribusiPilar['Karakter & Kedisiplinan'] }}</strong> Sesi</td>
            <td><strong>{{ $distribusiPilar['Minat & Bakat / Ekskul'] }}</strong> Sesi</td>
            <td><strong>{{ $distribusiPilar['Sosial & Psikologis'] }}</strong> Sesi</td>
            <td style="background-color: #f1f5f9;"><strong>{{ $jurnals->count() }}</strong> Sesi</td>
        </tr>
    </tbody>
</table>

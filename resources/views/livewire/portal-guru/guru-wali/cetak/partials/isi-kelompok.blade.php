<!-- ================================================================= -->
<!-- FILE ISI: LAPORAN KINERJA PENDAMPINGAN KELOMPOK GURU WALI         -->
<!-- Digunakan oleh: Cetak Kelompok                                    -->
<!-- ================================================================= -->

<!-- JUDUL DOKUMEN -->
<div class="doc-title">
    <h1>Laporan Kinerja Pendampingan Kelompok Guru Wali</h1>
    <div class="sub-title">BUKTI FISIK EKUIVALENSI BEBAN KERJA 2 JP/MINGGU &bull; KEPMENDIKDASMEN NO. 221/P/2025</div>
    <div class="period-label">Tahun Ajaran {{ $tahunAjaran->name }} &bull; Periode: {{ $labelPeriode }}</div>
</div>

<!-- BAGIAN I: IDENTITAS PENUGASAN -->
<div class="section-header">I. Identitas Penugasan Guru Wali</div>
<table class="identitas-table">
    <tr>
        <td class="lbl">Nama Guru Wali</td>
        <td class="sep">:</td>
        <td class="val">{{ $teacher->name }}</td>

        <td class="lbl">Nama Kelompok Binaan</td>
        <td class="sep">:</td>
        <td class="val">{{ $kelompok->nama_kelompok }}</td>
    </tr>
    <tr>
        <td class="lbl">NIP</td>
        <td class="sep">:</td>
        <td class="val">{{ $teacher->nip ?? '—' }}</td>

        <td class="lbl">Jumlah Murid Binaan</td>
        <td class="sep">:</td>
        <td class="val">{{ $totalSiswa }} Peserta Didik</td>
    </tr>
    <tr>
        <td class="lbl">Beban Ekuivalensi Kerja</td>
        <td class="sep">:</td>
        <td class="val">2 Jam Pelajaran (JP) / Minggu</td>

        <td class="lbl">Periode Laporan</td>
        <td class="sep">:</td>
        <td class="val">{{ $labelPeriode }}</td>
    </tr>
</table>

<!-- BAGIAN II: RINGKASAN KINERJA -->
<div class="section-header">II. Ringkasan Kinerja & Tingkat Kepatuhan Pendampingan</div>
<table class="stat-table">
    <thead>
        <tr>
            <th>Total Siswa</th>
            <th>Siswa Telah Didampingi</th>
            <th>Persentase Kepatuhan</th>
            <th>Total Sesi Terlaksana</th>
            <th>Sesi Individu</th>
            <th>Sesi Kelompok / Klasikal</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>{{ $totalSiswa }}</strong> Anak</td>
            <td><strong style="color: #059669;">{{ $siswaPernahSesi }}</strong> Anak</td>
            <td><strong style="font-size: 11pt; color: {{ $persentaseKepatuhan >= 80 ? '#059669' : ($persentaseKepatuhan >= 50 ? '#d97706' : '#dc2626') }}">{{ $persentaseKepatuhan }}%</strong></td>
            <td><strong>{{ $totalSesi }}</strong> Sesi</td>
            <td>{{ $totalIndividu }} Sesi</td>
            <td>{{ $totalKelompokKecil + $totalKlasikal }} Sesi</td>
        </tr>
    </tbody>
</table>

<!-- BAGIAN III: MATRIKS SISWA BINAAN -->
<div class="section-header">III. Matriks Rekapitulasi Capaian Peserta Didik Binaan</div>
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 25px;">No</th>
            <th>Nama Peserta Didik</th>
            <th style="width: 80px;">NIS / NISN</th>
            <th style="width: 45px;">Kelas</th>
            <th style="width: 55px;">Jml Sesi</th>
            <th style="width: 95px;">Pilar Dominan</th>
            <th style="width: 105px;">Status Terakhir</th>
            <th>Catatan & Rencana Tindak Lanjut</th>
        </tr>
    </thead>
    <tbody>
        @forelse($matriksSiswa as $mIdx => $m)
        <tr>
            <td class="center">{{ $mIdx + 1 }}</td>
            <td><strong>{{ $m->nama }}</strong></td>
            <td class="center" style="font-size: 7.5pt;">{{ $m->nis ?? '—' }} / {{ $m->nisn ?? '—' }}</td>
            <td class="center">{{ $m->kelas }}</td>
            <td class="center"><strong>{{ $m->total_sesi }}</strong></td>
            <td class="center">
                @if($m->total_sesi > 0)
                    <span class="pilar-badge">{{ $m->kategori_dominan }}</span>
                @else
                    <span style="color: #999;">—</span>
                @endif
            </td>
            <td class="center">
                @if($m->status_terakhir === 'Tuntas / Selesai')
                    <strong style="color: #059669;">{{ $m->status_terakhir }}</strong>
                @elseif($m->status_terakhir === 'Belum ada sesi')
                    <span style="color: #888;">{{ $m->status_terakhir }}</span>
                @else
                    <strong style="color: #d97706;">{{ $m->status_terakhir }}</strong>
                @endif
                @if($m->tanggal_terakhir !== '—')
                    <div style="font-size: 7pt; color: #555;">{{ $m->tanggal_terakhir }}</div>
                @endif
            </td>
            <td>{{ $m->tindak_lanjut }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="center" style="font-style: italic; color: #666; padding: 6px;">
                Belum ada siswa binaan yang terdaftar pada kelompok ini.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

<!-- BAGIAN IV: DISTRIBUSI PILAR & RUJUKAN -->
<div class="section-header">IV. Distribusi Bimbingan 4 Pilar & Kolaborasi Penanganan</div>
<table style="width: 100%; border-collapse: collapse; border: none; margin-bottom: 6px;">
    <tr>
        <td style="width: 50%; vertical-align: top; padding-right: 4px; border: none;">
            <table class="data-table" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th colspan="2">Distribusi 4 Pilar Pendampingan</th>
                    </tr>
                    <tr>
                        <th>Kategori Pilar</th>
                        <th style="width: 65px;">Total Sesi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Pilar Akademik</td>
                        <td class="center"><strong>{{ $distribusiPilar['Akademik'] }}</strong></td>
                    </tr>
                    <tr>
                        <td>Pilar Karakter & Kedisiplinan</td>
                        <td class="center"><strong>{{ $distribusiPilar['Karakter & Kedisiplinan'] }}</strong></td>
                    </tr>
                    <tr>
                        <td>Pilar Minat & Bakat / Ekskul</td>
                        <td class="center"><strong>{{ $distribusiPilar['Minat & Bakat / Ekskul'] }}</strong></td>
                    </tr>
                    <tr>
                        <td>Pilar Sosial & Psikologis</td>
                        <td class="center"><strong>{{ $distribusiPilar['Sosial & Psikologis'] }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </td>
        <td style="width: 50%; vertical-align: top; padding-left: 4px; border: none;">
            <table class="data-table" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th colspan="2">Rekapitulasi Kolaborasi & Rujukan</th>
                    </tr>
                    <tr>
                        <th>Pihak Kolaborasi</th>
                        <th style="width: 65px;">Jumlah Kasus</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Koordinasi dengan Wali Kelas</td>
                        <td class="center"><strong>{{ $rekapRujukan['Wali Kelas'] }}</strong></td>
                    </tr>
                    <tr>
                        <td>Rujukan ke Guru BK (Konseling)</td>
                        <td class="center"><strong>{{ $rekapRujukan['Guru BK'] }}</strong></td>
                    </tr>
                    <tr>
                        <td>Komunikasi dengan Orang Tua / Wali</td>
                        <td class="center"><strong>{{ $rekapRujukan['Orang Tua / Wali'] }}</strong></td>
                    </tr>
                    <tr>
                        <td>Penanganan Mandiri Guru Wali</td>
                        <td class="center"><strong>{{ $rekapRujukan['Mandiri'] }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>

<!-- BAGIAN V: CATATAN REFLEKSI GURU WALI -->
<div class="section-header">V. Catatan Refleksi & Evaluasi Pelaksanaan Tugas</div>
<div class="refleksi-box">
    @if(!empty(trim($catatanRefleksi)))
        {{ $catatanRefleksi }}
    @else
        Seluruh aktivitas pendampingan kelompok binaan telah dilaksanakan secara berkesinambungan mengacu pada Permendikdasmen No. 11 Tahun 2025. Pendekatan pembinaan mencakup pemantauan berkala 4 pilar (Akademik, Karakter, Minat Bakat, dan Sosial Emosional) guna memberikan dukungan perkembangan belajar peserta didik secara holistik. Koordinasi berkala juga telah dilakukan bersama Wali Kelas dan Guru Bimbingan Konseling (BK) untuk kasus yang memerlukan penanganan khusus.
    @endif
</div>

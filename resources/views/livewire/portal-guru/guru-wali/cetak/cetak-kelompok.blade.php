<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kinerja Kelompok Guru Wali - {{ $kelompok->nama_kelompok }}</title>
    <style>
        /* CSS Reset & Setup Halaman Cetak */
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 15mm 15mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10.5pt;
            line-height: 1.35;
            color: #111;
            margin: 0;
            padding: 0;
            background: #f8fafc;
        }

        /* Toolbar Layar (Khusus Browser) */
        .screen-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            background: #1e293b;
            color: #fff;
            padding: 10px 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 13px;
        }

        .screen-toolbar .btn-group {
            display: flex;
            gap: 10px;
        }

        .screen-toolbar button, .screen-toolbar a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
            border: none;
        }

        .btn-print {
            background: #2563eb;
            color: #fff;
        }

        .btn-print:hover {
            background: #1d4ed8;
        }

        .btn-pdf {
            background: #059669;
            color: #fff;
        }

        .btn-pdf:hover {
            background: #047857;
        }

        .btn-close {
            background: #475569;
            color: #fff;
        }

        .btn-close:hover {
            background: #334155;
        }

        /* Container Kertas A4 */
        .paper-sheet {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 15mm 15mm 15mm 15mm;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.08);
        }

        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
            }
            .screen-toolbar {
                display: none !important;
            }
            .paper-sheet {
                width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
            }
        }

        /* Kop Surat Resmi */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-bottom: 0px;
        }

        .kop-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        .kop-logo {
            width: 80px;
            text-align: center;
        }

        .kop-logo img {
            max-width: 75px;
            max-height: 75px;
            object-fit: contain;
        }

        .kop-text {
            text-align: center;
            padding: 0 10px;
        }

        .kop-instansi {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .kop-sekolah {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 2px 0;
        }

        .kop-alamat {
            font-size: 9pt;
            font-style: normal;
            margin: 0;
            line-height: 1.3;
        }

        /* Garis Ganda Kop */
        .kop-line {
            border-top: 2.5px solid #000;
            border-bottom: 0.8px solid #000;
            height: 3px;
            margin: 6px 0 14px 0;
        }

        /* Judul Dokumen */
        .doc-title {
            text-align: center;
            margin-bottom: 12px;
        }

        .doc-title h1 {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 2px 0;
        }

        .doc-title .sub-title {
            font-size: 9.5pt;
            font-weight: bold;
            color: #222;
            margin: 0;
        }

        .doc-title .period-label {
            font-size: 9.5pt;
            font-style: italic;
            margin-top: 2px;
        }

        /* Section Heading */
        .section-header {
            font-size: 10.5pt;
            font-weight: bold;
            margin: 10px 0 4px 0;
            text-transform: uppercase;
            border-bottom: 1px solid #999;
            padding-bottom: 2px;
        }

        /* Tabel Identitas */
        .identitas-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10pt;
        }

        .identitas-table td {
            padding: 2.5px 4px;
            border: none;
            vertical-align: top;
        }

        .identitas-table .lbl {
            width: 22%;
            color: #222;
        }

        .identitas-table .sep {
            width: 2%;
            text-align: center;
        }

        .identitas-table .val {
            width: 26%;
            font-weight: bold;
        }

        /* Tabel Data */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9pt;
        }

        .data-table th, .data-table td {
            border: 1px solid #444;
            padding: 4px 6px;
            vertical-align: middle;
        }

        .data-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 8.5pt;
        }

        .data-table td.center {
            text-align: center;
        }

        /* Box Kinerja */
        .stat-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9pt;
        }

        .stat-table th, .stat-table td {
            border: 1px solid #555;
            padding: 5px 8px;
            text-align: center;
        }

        .stat-table th {
            background-color: #f8fafc;
            font-size: 8.5pt;
        }

        .refleksi-box {
            border: 1px solid #666;
            padding: 8px 10px;
            background: #fff;
            font-size: 9.5pt;
            line-height: 1.4;
            margin-bottom: 12px;
            text-align: justify;
        }

        /* Lembar Pengesahan Tanda Tangan */
        .ttd-section {
            width: 100%;
            margin-top: 15px;
            page-break-inside: avoid;
        }

        .ttd-date {
            text-align: right;
            font-size: 10pt;
            margin-bottom: 8px;
            padding-right: 30px;
        }

        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .ttd-table td {
            border: none;
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 15px;
            font-size: 10pt;
        }

        .ttd-space {
            height: 60px;
        }

        .ttd-name {
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
        }

        .ttd-nip {
            font-size: 9pt;
            margin-top: 1px;
        }
    </style>
</head>
<body>

    @if(!$isPdf)
    <!-- Screen Toolbar -->
    <div class="screen-toolbar no-print">
        <div style="font-weight: bold;">
            📄 Pratinjau Cetak: Laporan Kinerja {{ $kelompok->nama_kelompok }} ({{ $labelPeriode }})
        </div>
        <div class="btn-group">
            <button type="button" class="btn-print" onclick="window.print()">
                🖨️ Cetak Dokumen (A4)
            </button>
            <a href="{{ route('portal-guru.guru-wali.cetak.kelompok.pdf', ['periode' => $periode, 'academic_year_id' => $tahunAjaran->id, 'catatan_refleksi' => $catatanRefleksi]) }}" class="btn-pdf">
                📥 Unduh PDF
            </a>
            <button type="button" class="btn-close" onclick="window.close()">
                ✖ Tutup
            </button>
        </div>
    </div>
    @endif

    <div class="paper-sheet">
        <!-- KOP SURAT RESMI -->
        <table class="kop-table">
            <tr>
                <td class="kop-logo">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Logo Sekolah">
                    @elseif($settings?->school_logo_path)
                        <img src="{{ public_path('storage/' . $settings->school_logo_path) }}" alt="Logo Sekolah">
                    @endif
                </td>
                <td class="kop-text">
                    <div class="kop-instansi">DINAS PENDIDIKAN DAN KEBUDAYAAN</div>
                    <div class="kop-sekolah">{{ strtoupper($settings?->school_name ?? 'SMP NEGERI 3 KEDUNGREJA') }}</div>
                    <div class="kop-alamat">
                        {{ $settings?->school_address ?? 'Kedungreja' }} &bull; Wilayah {{ $namaKota }}
                        <br>Laman: www.smpn3kedungreja.sch.id &bull; Surel: official@smpn3kedungreja.sch.id
                    </div>
                </td>
            </tr>
        </table>
        <div class="kop-line"></div>

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

        <!-- BAGIAN II: RINGKASAN KINERJA PENDAMPINGAN -->
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
                    <td><strong style="color: #047857;">{{ $siswaPernahSesi }}</strong> Anak</td>
                    <td style="background-color: #f1f5f9;">
                        <strong style="font-size: 11pt;">{{ $persentaseKepatuhan }}%</strong>
                    </td>
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
                    <th style="width: 75px;">NIS / NISN</th>
                    <th style="width: 55px;">Kelas</th>
                    <th style="width: 55px;">Jml Sesi</th>
                    <th style="width: 105px;">Pilar Dominan</th>
                    <th style="width: 95px;">Status Terakhir</th>
                    <th>Catatan & Rencana Tindak Lanjut</th>
                </tr>
            </thead>
            <tbody>
                @forelse($matriksSiswa as $idx => $m)
                <tr>
                    <td class="center">{{ $idx + 1 }}</td>
                    <td><strong>{{ $m->nama }}</strong></td>
                    <td class="center">{{ $m->nis }} / {{ $m->nisn }}</td>
                    <td class="center">{{ $m->kelas }}</td>
                    <td class="center">
                        <strong>{{ $m->total_sesi }}</strong>
                    </td>
                    <td>{{ $m->kategori_dominan }}</td>
                    <td class="center">
                        <span style="font-size: 8pt; font-weight: bold;">{{ $m->status_terakhir }}</span>
                        @if($m->tanggal_terakhir !== '—')
                            <br><span style="font-size: 7.5pt; color: #555;">{{ $m->tanggal_terakhir }}</span>
                        @endif
                    </td>
                    <td>{{ $m->tindak_lanjut }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="center" style="font-style: italic; color: #666; padding: 10px;">
                        Tidak ada siswa yang terdaftar dalam kelompok binaan ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- BAGIAN IV: DISTRIBUSI PILAR & RUJUKAN KOLABORASI -->
        <div class="section-header">IV. Distribusi Bimbingan 4 Pilar & Kolaborasi Penanganan</div>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
            <tr>
                <td style="width: 50%; vertical-align: top; padding-right: 5px; border: none;">
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
                <td style="width: 50%; vertical-align: top; padding-left: 5px; border: none;">
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

        <!-- BAGIAN VI: LEMBAR PENGESAHAN (2 PIHAK) -->
        <div class="ttd-section">
            <div class="ttd-date">
                {{ $namaKota }}, {{ $tanggalCetak }}
            </div>
            <table class="ttd-table">
                <tr>
                    <td>
                        Mengetahui dan Mengesahkan,<br>
                        Kepala Sekolah
                        <div class="ttd-space"></div>
                        <div class="ttd-name">{{ $namaKepsek }}</div>
                        <div class="ttd-nip">NIP. {{ $nipKepsek }}</div>
                    </td>
                    <td>
                        Guru Wali Pengampu,<br>
                        {{ $kelompok->nama_kelompok }}
                        <div class="ttd-space"></div>
                        <div class="ttd-name">{{ $teacher->name }}</div>
                        <div class="ttd-nip">NIP. {{ $teacher->nip ?? '—' }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    @if(!$isPdf && $autoPrint)
    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
    @endif
</body>
</html>

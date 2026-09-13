<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pendampingan Individual - {{ $siswa->name }}</title>
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
            color: #333;
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
            width: 18%;
            color: #222;
        }

        .identitas-table .sep {
            width: 2%;
            text-align: center;
        }

        .identitas-table .val {
            width: 30%;
            font-weight: bold;
        }

        /* Tabel Data / Sesi */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9pt;
        }

        .data-table th, .data-table td {
            border: 1px solid #444;
            padding: 4px 6px;
            vertical-align: top;
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

        /* Pilar Badges in Print */
        .pilar-badge {
            font-weight: bold;
            font-size: 8pt;
            display: inline-block;
        }

        /* 4 Pilar Summary Grid Table */
        .pilar-summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9pt;
        }

        .pilar-summary-table th, .pilar-summary-table td {
            border: 1px solid #555;
            padding: 5px;
            text-align: center;
        }

        .pilar-summary-table th {
            background-color: #f8fafc;
            font-size: 8.5pt;
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
            padding-right: 15px;
        }

        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .ttd-table td {
            border: none;
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 5px;
            font-size: 10pt;
        }

        .ttd-space {
            height: 55px;
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
            📄 Pratinjau Cetak: {{ $siswa->name }} ({{ $labelPeriode }})
        </div>
        <div class="btn-group">
            <button type="button" class="btn-print" onclick="window.print()">
                🖨️ Cetak Dokumen (A4)
            </button>
            <a href="{{ route('portal-guru.guru-wali.cetak.individual.pdf', ['student_id' => $siswa->id, 'periode' => $periode, 'academic_year_id' => $tahunAjaran->id]) }}" class="btn-pdf">
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
            <div style="font-weight: bold; font-size: 9.5pt; margin: 4px 0 3px 0;">
                A. Semester 1 (Ganjil: Juli – Desember {{ $tahunAjaran->start_year ?? date('Y') }}) &bull; Total: {{ $semester1Jurnals->count() }} Sesi
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 25px;">No</th>
                        <th style="width: 85px;">Tanggal</th>
                        <th style="width: 75px;">Bentuk</th>
                        <th style="width: 105px;">Pilar Fokus</th>
                        <th>Topik / Uraian Masalah</th>
                        <th style="width: 120px;">Tindak Lanjut & Kolaborasi</th>
                        <th style="width: 75px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($semester1Jurnals as $idx => $jurnal)
                    <tr>
                        <td class="center">{{ $idx + 1 }}</td>
                        <td class="center">
                            {{ \Carbon\Carbon::parse($jurnal->tanggal_waktu)->translatedFormat('d/m/Y') }}<br>
                            <span style="font-size: 8pt; color: #555;">{{ \Carbon\Carbon::parse($jurnal->tanggal_waktu)->format('H:i') }} WIB</span>
                        </td>
                        <td class="center">{{ $jurnal->jenis_pendampingan }}</td>
                        <td><strong>{{ $jurnal->kategori_pendampingan }}</strong></td>
                        <td>{{ $jurnal->uraian_pembahasan }}</td>
                        <td>
                            {{ $jurnal->rencana_tindak_lanjut ?? '—' }}
                            @if($jurnal->rujukan_kolaborasi && $jurnal->rujukan_kolaborasi !== 'Mandiri')
                                <br><strong style="font-size: 8pt;">Rujukan: {{ $jurnal->rujukan_kolaborasi }}</strong>
                            @endif
                        </td>
                        <td class="center"><strong>{{ $jurnal->status_sesi }}</strong></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="center" style="font-style: italic; color: #666; padding: 8px;">
                            Tidak ada catatan pendampingan pada Semester 1 (Ganjil).
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Sub-bagian B: Semester 2 (Genap) -->
            <div style="font-weight: bold; font-size: 9.5pt; margin: 8px 0 3px 0;">
                B. Semester 2 (Genap: Januari – Juni {{ $tahunAjaran->end_year ?? (date('Y') + 1) }}) &bull; Total: {{ $semester2Jurnals->count() }} Sesi
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 25px;">No</th>
                        <th style="width: 85px;">Tanggal</th>
                        <th style="width: 75px;">Bentuk</th>
                        <th style="width: 105px;">Pilar Fokus</th>
                        <th>Topik / Uraian Masalah</th>
                        <th style="width: 120px;">Tindak Lanjut & Kolaborasi</th>
                        <th style="width: 75px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($semester2Jurnals as $idx => $jurnal)
                    <tr>
                        <td class="center">{{ $idx + 1 }}</td>
                        <td class="center">
                            {{ \Carbon\Carbon::parse($jurnal->tanggal_waktu)->translatedFormat('d/m/Y') }}<br>
                            <span style="font-size: 8pt; color: #555;">{{ \Carbon\Carbon::parse($jurnal->tanggal_waktu)->format('H:i') }} WIB</span>
                        </td>
                        <td class="center">{{ $jurnal->jenis_pendampingan }}</td>
                        <td><strong>{{ $jurnal->kategori_pendampingan }}</strong></td>
                        <td>{{ $jurnal->uraian_pembahasan }}</td>
                        <td>
                            {{ $jurnal->rencana_tindak_lanjut ?? '—' }}
                            @if($jurnal->rujukan_kolaborasi && $jurnal->rujukan_kolaborasi !== 'Mandiri')
                                <br><strong style="font-size: 8pt;">Rujukan: {{ $jurnal->rujukan_kolaborasi }}</strong>
                            @endif
                        </td>
                        <td class="center"><strong>{{ $jurnal->status_sesi }}</strong></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="center" style="font-style: italic; color: #666; padding: 8px;">
                            Tidak ada catatan pendampingan pada Semester 2 (Genap).
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        @else
            <!-- Laporan Spesifik 1 Semester -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 25px;">No</th>
                        <th style="width: 85px;">Tanggal</th>
                        <th style="width: 75px;">Bentuk</th>
                        <th style="width: 105px;">Pilar Fokus</th>
                        <th>Topik / Uraian Masalah</th>
                        <th style="width: 120px;">Tindak Lanjut & Kolaborasi</th>
                        <th style="width: 75px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jurnals as $idx => $jurnal)
                    <tr>
                        <td class="center">{{ $idx + 1 }}</td>
                        <td class="center">
                            {{ \Carbon\Carbon::parse($jurnal->tanggal_waktu)->translatedFormat('d/m/Y') }}<br>
                            <span style="font-size: 8pt; color: #555;">{{ \Carbon\Carbon::parse($jurnal->tanggal_waktu)->format('H:i') }} WIB</span>
                        </td>
                        <td class="center">{{ $jurnal->jenis_pendampingan }}</td>
                        <td><strong>{{ $jurnal->kategori_pendampingan }}</strong></td>
                        <td>{{ $jurnal->uraian_pembahasan }}</td>
                        <td>
                            {{ $jurnal->rencana_tindak_lanjut ?? '—' }}
                            @if($jurnal->rujukan_kolaborasi && $jurnal->rujukan_kolaborasi !== 'Mandiri')
                                <br><strong style="font-size: 8pt;">Rujukan: {{ $jurnal->rujukan_kolaborasi }}</strong>
                            @endif
                        </td>
                        <td class="center"><strong>{{ $jurnal->status_sesi }}</strong></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="center" style="font-style: italic; color: #666; padding: 8px;">
                            Tidak ada catatan pendampingan untuk periode yang dipilih.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        <!-- BAGIAN III: REKAP KONSULTASI MANDIRI (DUA ARAH) -->
        <div class="section-header">III. Catatan Konsultasi Mandiri Inisiatif Peserta Didik</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px;">No</th>
                    <th style="width: 85px;">Tgl Pengajuan</th>
                    <th style="width: 120px;">Kategori Topik</th>
                    <th>Pesan / Uraian dari Peserta Didik</th>
                    <th style="width: 90px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($konsultasis as $kIdx => $k)
                <tr>
                    <td class="center">{{ $kIdx + 1 }}</td>
                    <td class="center">{{ \Carbon\Carbon::parse($k->created_at)->translatedFormat('d/m/Y') }}</td>
                    <td><strong>{{ $k->kategori ?? 'Umum' }}</strong></td>
                    <td>{{ $k->isi_konsultasi ?? $k->topik }}</td>
                    <td class="center"><strong>{{ $k->status_pengajuan }}</strong></td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="center" style="font-style: italic; color: #666; padding: 6px;">
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

        <!-- BAGIAN V: LEMBAR PENGESAHAN (3 PIHAK) -->
        <div class="ttd-section">
            <div class="ttd-date">
                {{ $namaKota }}, {{ $tanggalCetak }}
            </div>
            <table class="ttd-table">
                <tr>
                    <td>
                        Mengetahui,<br>
                        Orang Tua / Wali Murid
                        <div class="ttd-space"></div>
                        <div class="ttd-name">( ............................................ )</div>
                        <div class="ttd-nip">Wali dari {{ $siswa->name }}</div>
                    </td>
                    <td>
                        Guru Wali Pendamping,
                        <div class="ttd-space"></div>
                        <div class="ttd-name">{{ $teacher->name }}</div>
                        <div class="ttd-nip">NIP. {{ $teacher->nip ?? '—' }}</div>
                    </td>
                    <td>
                        Mengetahui,<br>
                        Kepala Sekolah
                        <div class="ttd-space"></div>
                        <div class="ttd-name">{{ $namaKepsek }}</div>
                        <div class="ttd-nip">NIP. {{ $nipKepsek }}</div>
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

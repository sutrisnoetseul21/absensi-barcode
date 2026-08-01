<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Katalog Buku - {{ $settings?->school_name ?? 'Perpustakaan' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1.5cm 1.8cm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8pt;
            color: #1e293b;
            background: #fff;
        }

        /* ===== HEADER KOP ===== */
        .kop-wrapper {
            border-bottom: 2px solid #1e3a5f;
            padding-bottom: 8px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .kop-left .title {
            font-size: 15pt;
            font-weight: bold;
            color: #1e3a5f;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .kop-left .school-name {
            font-size: 9pt;
            font-weight: bold;
            color: #334155;
            margin-top: 3px;
        }
        .kop-left .school-address {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 2px;
        }
        .kop-right {
            text-align: right;
            font-size: 7.5pt;
            color: #64748b;
            padding-top: 4px;
        }
        .kop-right .tanggal-label {
            font-weight: bold;
            color: #334155;
        }

        /* ===== FILTER INFO ===== */
        .filter-info {
            font-size: 7.5pt;
            color: #475569;
            margin-bottom: 8px;
            padding: 4px 8px;
            background: #f1f5f9;
            border-left: 3px solid #1e3a5f;
            border-radius: 0 4px 4px 0;
        }

        /* ===== TABEL ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }
        thead tr th {
            background-color: #1e3a5f;
            color: #ffffff;
            font-weight: bold;
            padding: 5px 4px;
            text-align: center;
            border: 1px solid #1e3a5f;
            white-space: nowrap;
        }
        tbody tr td {
            padding: 4px 4px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        tbody tr:nth-child(even) {
            background-color: #f0f4ff;
        }
        tbody tr:hover {
            background-color: #e0e7ff;
        }
        .col-no     { width: 3%;  text-align: center; }
        .col-judul  { width: 24%; }
        .col-koleksi{ width: 8%;  text-align: center; }
        .col-penulis{ width: 12%; }
        .col-penerbit{ width: 10%; }
        .col-tahun  { width: 5%;  text-align: center; }
        .col-isbn   { width: 10%; font-size: 7pt; font-family: monospace; }
        .col-ddc    { width: 10%; font-size: 7pt; }
        .col-jenjang{ width: 7%;  text-align: center; }
        .col-rak    { width: 5%;  text-align: center; }
        .col-total  { width: 4%;  text-align: center; }
        .col-tersedia { width: 4%; text-align: center; }

        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 4px;
            font-size: 6.5pt;
            font-weight: bold;
            background-color: #dbeafe;
            color: #1d4ed8;
        }
        .judul-buku {
            font-weight: bold;
            color: #0f172a;
        }
        .mapel-tag {
            display: block;
            font-size: 6.5pt;
            color: #64748b;
            margin-top: 1px;
        }

        /* ===== FOOTER ===== */
        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 7pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }
        .footer strong {
            color: #475569;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #94a3b8;
            font-size: 9pt;
        }
    </style>
</head>
<body>

    {{-- ===== KOP SURAT ===== --}}
    <div class="kop-wrapper">
        <div class="kop-left">
            <div class="title">Katalog Buku</div>
            <div class="school-name">{{ $settings?->school_name ?? 'Nama Sekolah' }}</div>
            <div class="school-address">{{ $settings?->school_address ?? '' }}</div>
        </div>
        <div class="kop-right">
            <div class="tanggal-label">Tanggal Cetak</div>
            <div>{{ now()->translatedFormat('d F Y') }}</div>
        </div>
    </div>

    {{-- ===== INFO FILTER ===== --}}
    @if(!empty($filterLabel))
        <div class="filter-info">
            <strong>Filter:</strong> {{ $filterLabel }}
        </div>
    @endif

    {{-- ===== TABEL BUKU ===== --}}
    @if($bukus->isEmpty())
        <div class="empty-state">
            Tidak ada data buku yang sesuai dengan filter yang dipilih.
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-judul">Judul</th>
                    <th class="col-koleksi">Koleksi</th>
                    <th class="col-penulis">Penulis</th>
                    <th class="col-penerbit">Penerbit</th>
                    <th class="col-tahun">Tahun</th>
                    <th class="col-isbn">ISBN</th>
                    <th class="col-ddc">Kode DDC</th>
                    <th class="col-jenjang">Jenjang</th>
                    <th class="col-rak">Lokasi Rak</th>
                    <th class="col-total">Total</th>
                    <th class="col-tersedia">Tersedia</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bukus as $index => $buku)
                    @php
                        $total    = $buku->eksemplarBukus->count();
                        $tersedia = $buku->eksemplarBukus->where('status', 'tersedia')->count();
                        $jenjang  = match((string)$buku->grade_level) {
                            '7'  => 'Kelas 7',
                            '8'  => 'Kelas 8',
                            '9'  => 'Kelas 9',
                            '10' => 'Kelas 10',
                            '11' => 'Kelas 11',
                            '12' => 'Kelas 12',
                            default => 'Semua',
                        };
                    @endphp
                    <tr>
                        <td class="col-no">{{ $index + 1 }}</td>
                        <td class="col-judul">
                            <span class="judul-buku">{{ $buku->judul }}</span>
                            @if($buku->mataPelajaran)
                                <span class="mapel-tag">Mapel: {{ $buku->mataPelajaran->nama_mapel }}</span>
                            @endif
                        </td>
                        <td class="col-koleksi">
                            <span class="badge">{{ $buku->kategoriBuku?->nama_kategori ?? '-' }}</span>
                        </td>
                        <td class="col-penulis">{{ $buku->penulis ?? '-' }}</td>
                        <td class="col-penerbit">{{ $buku->penerbit ?? '-' }}</td>
                        <td class="col-tahun">{{ $buku->tahun_terbit ?? '-' }}</td>
                        <td class="col-isbn">{{ $buku->isbn ?? '-' }}</td>
                        <td class="col-ddc">
                            @if($buku->klasifikasiDdc)
                                {{ $buku->klasifikasiDdc->kode_ddc }}
                                <span class="mapel-tag">{{ $buku->klasifikasiDdc->kategori }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="col-jenjang">{{ $jenjang }}</td>
                        <td class="col-rak">{{ $buku->lokasi_rak ?? '-' }}</td>
                        <td class="col-total">{{ $total }}</td>
                        <td class="col-tersedia">{{ $tersedia }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            Menampilkan <strong>{{ $bukus->count() }}</strong> judul buku &bull;
            Dicetak dari Sistem ERP Perpustakaan &bull; {{ now()->translatedFormat('d F Y, H:i') }} WIB
        </div>
    @endif

</body>
</html>

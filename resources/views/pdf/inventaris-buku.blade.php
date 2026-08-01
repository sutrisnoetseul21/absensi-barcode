<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Inventaris Buku - {{ $settings?->school_name ?? 'Perpustakaan' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1.5cm 1.8cm;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8pt;
            color: #1e293b;
            background: #fff;
        }

        /* ===== KOP ===== */
        .kop-wrapper {
            border-bottom: 2px solid #1e3a5f;
            padding-bottom: 8px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .kop-left .title {
            font-size: 14pt;
            font-weight: bold;
            color: #1e3a5f;
            letter-spacing: 0.5px;
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
        .kop-right .tanggal-label { font-weight: bold; color: #334155; }

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
        tbody tr:nth-child(even) { background-color: #f0f4ff; }

        .col-no      { width: 3%;  text-align: center; }
        .col-tgl     { width: 7%;  text-align: center; white-space: nowrap; }
        .col-inv     { width: 10%; font-family: monospace; white-space: nowrap; }
        .col-judul   { width: 22%; }
        .col-penulis { width: 11%; }
        .col-penerbit{ width: 10%; }
        .col-tahun   { width: 5%;  text-align: center; }
        .col-asal    { width: 7%;  text-align: center; }
        .col-ddc     { width: 8%;  font-family: monospace; }
        .col-harga   { width: 9%;  text-align: right; white-space: nowrap; }
        .col-jml     { width: 5%;  text-align: center; }
        .col-status  { width: 6%;  text-align: center; }

        .badge-aktif {
            display: inline-block;
            padding: 1px 6px;
            background: #dcfce7;
            color: #15803d;
            border-radius: 4px;
            font-size: 6.5pt;
            font-weight: bold;
        }
        .badge-batal {
            display: inline-block;
            padding: 1px 6px;
            background: #fee2e2;
            color: #b91c1c;
            border-radius: 4px;
            font-size: 6.5pt;
            font-weight: bold;
        }
        .judul-buku { font-weight: bold; color: #0f172a; }

        /* ===== FOOTER ===== */
        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 7pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }
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
            <div class="title">Buku Induk Inventaris Perpustakaan</div>
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

    {{-- ===== TABEL ===== --}}
    @if($inventaris->isEmpty())
        <div class="empty-state">
            Tidak ada data inventaris yang sesuai dengan filter yang dipilih.
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-tgl">Tgl Masuk</th>
                    <th class="col-inv">No Inventaris</th>
                    <th class="col-judul">Judul Buku</th>
                    <th class="col-penulis">Pengarang</th>
                    <th class="col-penerbit">Penerbit</th>
                    <th class="col-tahun">Tahun</th>
                    <th class="col-asal">Asal</th>
                    <th class="col-ddc">No Klas.</th>
                    <th class="col-harga">Harga</th>
                    <th class="col-jml">Eks.</th>
                    <th class="col-status">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inventaris as $index => $inv)
                    <tr>
                        <td class="col-no">{{ $index + 1 }}</td>
                        <td class="col-tgl">
                            {{ $inv->tanggal_masuk ? \Carbon\Carbon::parse($inv->tanggal_masuk)->format('d/m/Y') : '-' }}
                        </td>
                        <td class="col-inv">{{ $inv->no_inventaris ?? '-' }}</td>
                        <td class="col-judul">
                            <span class="judul-buku">{{ $inv->buku?->judul ?? '-' }}</span>
                        </td>
                        <td class="col-penulis">{{ $inv->buku?->penulis ?? '-' }}</td>
                        <td class="col-penerbit">{{ $inv->buku?->penerbit ?? '-' }}</td>
                        <td class="col-tahun">{{ $inv->buku?->tahun_terbit ?? '-' }}</td>
                        <td class="col-asal">{{ ucwords(str_replace('_', ' ', $inv->asal ?? '')) }}</td>
                        <td class="col-ddc">{{ $inv->buku?->klasifikasiDdc?->kode_ddc ?? '-' }}</td>
                        <td class="col-harga">
                            {{ $inv->harga > 0 ? 'Rp ' . number_format($inv->harga, 0, ',', '.') : '-' }}
                        </td>
                        <td class="col-jml">{{ number_format($inv->jumlah_eksemplar) }}</td>
                        <td class="col-status">
                            @if($inv->status === 'aktif')
                                <span class="badge-aktif">Aktif</span>
                            @else
                                <span class="badge-batal">Batal</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            Menampilkan <strong>{{ $inventaris->count() }}</strong> entri inventaris &bull;
            Dicetak dari Sistem ERP Perpustakaan &bull; {{ now()->translatedFormat('d F Y, H:i') }} WIB
        </div>
    @endif

</body>
</html>

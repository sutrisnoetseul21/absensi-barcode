<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Peminjaman Buku - {{ $settings?->school_name ?? 'Perpustakaan' }}</title>
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

        .col-no       { width: 3%;  text-align: center; }
        .col-tglpinjam{ width: 9%;  text-align: center; white-space: nowrap; }
        .col-tgljatuh { width: 9%;  text-align: center; white-space: nowrap; }
        .col-peminjam { width: 17%; font-weight: bold; color: #0f172a; }
        .col-tipe     { width: 9%;  text-align: center; }
        .col-judul    { width: 20%; font-weight: bold; color: #0f172a; }
        .col-eksemplar{ width: 10%; font-family: monospace; text-align: center; }
        .col-tglkembali{ width: 9%;  text-align: center; white-space: nowrap; }
        .col-status   { width: 14%; text-align: center; }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 6.5pt;
            font-weight: bold;
        }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-warning { background: #fef08a; color: #a16207; }
        .badge-danger  { background: #fee2e2; color: #b91c1c; }

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
            <div class="title">Data Peminjaman Buku Perpustakaan</div>
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
    @if($peminjaman->isEmpty())
        <div class="empty-state">
            Tidak ada data peminjaman yang sesuai dengan filter yang dipilih.
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-tglpinjam">Tgl Pinjam</th>
                    <th class="col-tgljatuh">Jatuh Tempo</th>
                    <th class="col-peminjam">Peminjam</th>
                    <th class="col-tipe">Tipe</th>
                    <th class="col-judul">Judul Buku</th>
                    <th class="col-eksemplar">Kode Eks.</th>
                    <th class="col-tglkembali">Tgl Kembali</th>
                    <th class="col-status">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($peminjaman as $index => $pem)
                    @php
                        $isTerlambat = $pem->status === 'dipinjam' && $pem->tanggal_jatuh_tempo && $pem->tanggal_jatuh_tempo < \Carbon\Carbon::now()->startOfDay();
                        
                        $statusClass = match(true) {
                            $isTerlambat => 'badge-danger',
                            $pem->status === 'dipinjam' => 'badge-warning',
                            $pem->status === 'hilang' => 'badge-danger',
                            $pem->status === 'dikembalikan' => 'badge-success',
                            default => ''
                        };

                        $statusText = $isTerlambat ? 'Terlambat' : ucfirst($pem->status ?? '-');

                        $tipeAnggota = match ((string) $pem->peminjam_type) {
                            'siswa' => 'Siswa',
                            'guru'  => 'Guru / Staff',
                            default => ucfirst($pem->peminjam_type ?? '-')
                        };
                    @endphp
                    <tr>
                        <td class="col-no">{{ $index + 1 }}</td>
                        <td class="col-tglpinjam">
                            {{ $pem->tanggal_pinjam ? $pem->tanggal_pinjam->format('d/m/Y') : '-' }}
                        </td>
                        <td class="col-tgljatuh">
                            {{ $pem->tanggal_jatuh_tempo ? $pem->tanggal_jatuh_tempo->format('d/m/Y') : '-' }}
                        </td>
                        <td class="col-peminjam">{{ $pem->peminjam?->name ?? '-' }}</td>
                        <td class="col-tipe">{{ $tipeAnggota }}</td>
                        <td class="col-judul">
                            {{ $pem->eksemplarBuku?->buku?->judul ?? '-' }}
                        </td>
                        <td class="col-eksemplar">{{ $pem->eksemplarBuku?->kode_eksemplar ?? '-' }}</td>
                        <td class="col-tglkembali">
                            {{ $pem->tanggal_kembali ? $pem->tanggal_kembali->format('d/m/Y') : '-' }}
                        </td>
                        <td class="col-status">
                            <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            Menampilkan <strong>{{ $peminjaman->count() }}</strong> entri peminjaman &bull;
            Dicetak dari Sistem ERP Perpustakaan &bull; {{ now()->translatedFormat('d F Y, H:i') }} WIB
        </div>
    @endif

</body>
</html>

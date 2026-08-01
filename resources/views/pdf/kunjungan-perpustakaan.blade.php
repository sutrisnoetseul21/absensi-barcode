<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Kunjungan Perpustakaan - {{ $settings?->school_name ?? 'Perpustakaan' }}</title>
    <style>
        @page {
            size: A4 portrait;
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

        .col-no      { width: 4%;  text-align: center; }
        .col-tgl     { width: 12%; text-align: center; white-space: nowrap; }
        .col-waktu   { width: 10%; text-align: center; white-space: nowrap; }
        .col-nama    { width: 30%; font-weight: bold; color: #0f172a; }
        .col-tipe    { width: 12%; text-align: center; }
        .col-tujuan  { width: 17%; }
        .col-catatan { width: 15%; }

        .nama-tambahan { font-size: 6.5pt; color: #64748b; font-weight: normal; }

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
            <div class="title">Rekapitulasi Kunjungan Perpustakaan</div>
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
    @if($kunjungan->isEmpty())
        <div class="empty-state">
            Tidak ada data kunjungan yang sesuai dengan filter yang dipilih.
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-tgl">Tanggal</th>
                    <th class="col-waktu">Waktu Masuk</th>
                    <th class="col-nama">Nama Pengunjung</th>
                    <th class="col-tipe">Tipe Anggota</th>
                    <th class="col-tujuan">Tujuan</th>
                    <th class="col-catatan">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kunjungan as $index => $kun)
                    @php
                        $tipeAnggota = match ((string) $kun->pengunjung_type) {
                            'siswa' => 'Siswa',
                            'guru'  => 'Guru / Staff',
                            default => ucfirst($kun->pengunjung_type ?? '-')
                        };

                        $namaTambahan = '';
                        if ($kun->pengunjung_type === 'siswa' && $kun->pengunjung && $kun->pengunjung->enrollmentAktif) {
                            $namaTambahan = '(Kelas ' . $kun->pengunjung->enrollmentAktif->kelas->name . ')';
                        }
                    @endphp
                    <tr>
                        <td class="col-no">{{ $index + 1 }}</td>
                        <td class="col-tgl">
                            {{ $kun->tanggal ? $kun->tanggal->format('d/m/Y') : '-' }}
                        </td>
                        <td class="col-waktu">
                            {{ $kun->waktu_masuk ?? '-' }}
                        </td>
                        <td class="col-nama">
                            {{ $kun->pengunjung?->name ?? '-' }}
                            @if($namaTambahan)
                                <br><span class="nama-tambahan">{{ $namaTambahan }}</span>
                            @endif
                        </td>
                        <td class="col-tipe">{{ $tipeAnggota }}</td>
                        <td class="col-tujuan">{{ $kun->tujuan_kunjungan ?? '-' }}</td>
                        <td class="col-catatan">{{ $kun->catatan ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            Menampilkan <strong>{{ $kunjungan->count() }}</strong> data kunjungan &bull;
            Dicetak dari Sistem ERP Perpustakaan &bull; {{ now()->translatedFormat('d F Y, H:i') }} WIB
        </div>
    @endif

</body>
</html>

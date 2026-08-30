<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Biodata Peserta Didik</title>
    <style>
        @page {
            size: a4;
            margin: 20mm 20mm 25mm 20mm;
        }
        #footer {
            position: fixed;
            bottom: -15mm;
            left: 0;
            right: 0;
            border-top: 1px solid #000;
            padding-top: 3px;
        }
        
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .footer-table td {
            border: none;
            font-family: 'Times New Roman', Times, serif;
            font-size: 9pt;
            font-weight: bold;
            font-style: italic;
            padding: 0;
        }
        
        .footer-left {
            text-align: left;
            width: 50%;
        }
        
        .footer-right {
            text-align: right;
            width: 50%;
        }
        
        .page-num:before {
            content: counter(page);
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
        }
        .judul {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            letter-spacing: 1px;
            margin-bottom: 20pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }
        td {
            vertical-align: top;
            padding: 2px 0;
            border: none;
        }
        .col-no {
            width: 5%;
        }
        .col-label {
            width: 35%;
        }
        .col-isi {
            width: 60%;
        }
        .ttd-container {
            width: 100%;
            margin-top: 40pt;
        }
        .ttd-box {
            width: 35%;
            float: right;
            text-align: left;
        }
        .ttd-space {
            height: 60pt;
        }
        .nama-kepsek {
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div id="footer">
        <table class="footer-table">
            <tr>
                <td class="footer-left">| {{ strtoupper($siswa->name) }} | {{ $siswa->nis }}</td>
                <td class="footer-right"></td>
            </tr>
        </table>
    </div>

    <div class="judul">IDENTITAS PESERTA DIDIK</div>

    <table>
        <tr>
            <td class="col-no">1.</td>
            <td class="col-label">Nama Peserta Didik (Lengkap)</td>
            <td class="col-isi">: {{ $siswa->name }}</td>
        </tr>
        <tr>
            <td class="col-no">2.</td>
            <td class="col-label">Nomor Induk/NISN</td>
            <td class="col-isi">: {{ $siswa->nis ?? '-' }} / {{ $siswa->nisn }}</td>
        </tr>
        <tr>
            <td class="col-no">3.</td>
            <td class="col-label">Tempat, Tanggal Lahir</td>
            <td class="col-isi">: {{ $siswa->birth_place ?? '-' }}, {{ tanggal_indonesia($siswa->birth_date) }}</td>
        </tr>
        <tr>
            <td class="col-no">4.</td>
            <td class="col-label">Jenis Kelamin</td>
            <td class="col-isi">: {{ $siswa->gender === 'L' ? 'Laki-Laki' : ($siswa->gender === 'P' ? 'Perempuan' : '-') }}</td>
        </tr>
        <tr>
            <td class="col-no">5.</td>
            <td class="col-label">Agama</td>
            <td class="col-isi">: {{ $siswa->religion ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-no">6.</td>
            <td class="col-label">Status dalam Keluarga</td>
            <td class="col-isi">: {{ $siswa->family_status ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-no">7.</td>
            <td class="col-label">Anak ke</td>
            <td class="col-isi">: {{ $siswa->child_order ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-no">8.</td>
            <td class="col-label">Alamat Peserta Didik</td>
            <td class="col-isi">: {{ $siswa->address ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-no">9.</td>
            <td class="col-label">Nomor Telepon Rumah</td>
            <td class="col-isi">: {{ $siswa->no_hp ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-no">10.</td>
            <td class="col-label">Sekolah Asal</td>
            <td class="col-isi">: {{ $siswa->previous_school ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-no">11.</td>
            <td class="col-label">Diterima di sekolah ini</td>
            <td class="col-isi"></td>
        </tr>
        <tr>
            <td class="col-no"></td>
            <td class="col-label" style="padding-left: 15px;">Di kelas</td>
            <td class="col-isi">: {{ $siswa->admission_class ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-no"></td>
            <td class="col-label" style="padding-left: 15px;">Pada tanggal</td>
            <td class="col-isi">: {{ tanggal_indonesia($siswa->admission_date) }}</td>
        </tr>
        <tr>
            <td class="col-no">12.</td>
            <td class="col-label">Nama Orang Tua</td>
            <td class="col-isi"></td>
        </tr>
        <tr>
            <td class="col-no"></td>
            <td class="col-label" style="padding-left: 15px;">a. Ayah</td>
            <td class="col-isi">: {{ $siswa->nama_ayah ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-no"></td>
            <td class="col-label" style="padding-left: 15px;">b. Ibu</td>
            <td class="col-isi">: {{ $siswa->nama_ibu ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-no">13.</td>
            <td class="col-label">Nomor Telepon Rumah (Orang Tua)</td>
            <td class="col-isi">: {{ $siswa->no_hp_orang_tua ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-no">14.</td>
            <td class="col-label">Pekerjaan Orang Tua</td>
            <td class="col-isi"></td>
        </tr>
        <tr>
            <td class="col-no"></td>
            <td class="col-label" style="padding-left: 15px;">a. Ayah</td>
            <td class="col-isi">: {{ $siswa->pekerjaan_ayah ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-no"></td>
            <td class="col-label" style="padding-left: 15px;">b. Ibu</td>
            <td class="col-isi">: {{ $siswa->pekerjaan_ibu ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-no">15.</td>
            <td class="col-label">Nama Wali Siswa</td>
            <td class="col-isi">: {{ $siswa->nama_wali ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-no">16.</td>
            <td class="col-label">Pekerjaan Wali Peserta Didik</td>
            <td class="col-isi">: {{ $siswa->pekerjaan_wali ?? '-' }}</td>
        </tr>
    </table>

    <div class="ttd-container">
        <div class="ttd-box">
            {{ $namaKota ?? 'Kota' }}, {{ tanggal_indonesia($tanggalRapor) }}<br>
            Kepala Sekolah,
            <div class="ttd-space"></div>
            <span class="nama-kepsek">{{ $namaKepsek }}</span><br>
            NIP. {{ $nipKepsek }}
        </div>
    </div>

</body>
</html>

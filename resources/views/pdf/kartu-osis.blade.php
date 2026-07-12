<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Kartu OSIS</title>
    <style>
        @page {
            size: 90mm 60mm;
            margin: 0;
        }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            width: 90mm;
            height: 60mm;
            background-color: #ffffff;
            color: #000;
            position: relative;
            overflow: hidden;
        }

        /* ===== HEADER ===== */
        .header {
            width: 100%;
            text-align: center;
            padding-top: 2.2mm; /* Pushed down a bit as requested */
            height: 17.5mm;
            position: relative;
            background-color: #3182ce;
            border-bottom: 0.4mm solid #2b6cb0;
        }
        .logo-left {
            position: absolute;
            left: 3mm;
            top: 2.5mm;
            width: 12mm;
            height: 12mm;
            object-fit: contain;
        }
        .logo-right {
            position: absolute;
            right: 3mm;
            top: 2.5mm;
            width: 12mm;
            height: 12mm;
            object-fit: contain;
        }
        .header-text-container {
            padding: 0 16mm;
            color: #ffffff;
        }
        .header-line {
            font-size: 10px;
            font-weight: bold;
            line-height: 1.2;
            margin: 0.2px 0;
            color: #ffffff;
            letter-spacing: 0.1px;
        }

        /* ===== FOTO SISWA ===== */
        .photo-container {
            position: absolute;
            left: 4mm;
            top: 19mm;
            width: 20mm;
            height: 26mm;
            border: 0.3mm solid #333;
            background: #fcfcfc;
            overflow: hidden;
        }
        .photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .photo-no-photo {
            width: 100%;
            height: 100%;
            text-align: center;
            padding-top: 10px;
            font-size: 6px;
            color: #aaa;
            background: #f5f5f5;
        }

        /* ===== BIODATA ===== */
        .biodata {
            position: absolute;
            left: 27mm;
            top: 19mm;
            width: 59mm;
            font-size: 6.2px;
            line-height: 1.2;
        }
        .biodata table {
            width: 100%;
            border-collapse: collapse;
        }
        .biodata td {
            vertical-align: top;
            padding-bottom: 1mm;
        }
        .biodata td.label {
            width: 15mm;
            font-weight: bold;
            color: #333;
        }
        .biodata td.colon {
            width: 2mm;
            text-align: center;
            color: #333;
        }
        .biodata td.value {
            color: #000;
        }
        .student-name {
            font-weight: bold;
            font-size: 7.2px;
        }

        /* ===== BARCODE (Bawah Kiri) ===== */
        .barcode-container {
            position: absolute;
            left: 4mm;
            bottom: 2mm;
            width: 32mm;
            text-align: center;
        }
        .barcode-container img {
            width: 32mm;
            height: 7.5mm;
            display: block;
        }
        .barcode-text {
            font-size: 5px;
            letter-spacing: 0.8px;
            margin-top: 0.5px;
            color: #333;
        }

        /* ===== TTD KEPALA SEKOLAH (Bawah Kanan) ===== */
        .signature-container {
            position: absolute;
            right: 4mm;
            bottom: 2mm;
            width: 32mm;
            text-align: center;
            font-size: 5.2px;
            line-height: 1.2;
        }
        .signature-title {
            font-weight: bold;
        }
        .signature-img {
            height: 9mm;
            margin: 0.5px auto;
            display: block;
            object-fit: contain;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        @if($settings?->district_logo_path && file_exists(public_path('storage/' . $settings->district_logo_path)))
            <img class="logo-left" src="{{ public_path('storage/' . $settings->district_logo_path) }}" alt="Logo Kabupaten">
        @elseif($settings?->school_logo_path && file_exists(public_path('storage/' . $settings->school_logo_path)))
            <img class="logo-left" src="{{ public_path('storage/' . $settings->school_logo_path) }}" alt="Logo">
        @endif

        @if($settings?->school_logo_path && file_exists(public_path('storage/' . $settings->school_logo_path)))
            <img class="logo-right" src="{{ public_path('storage/' . $settings->school_logo_path) }}" alt="Logo Sekolah">
        @endif

        <div class="header-text-container">
            <div class="header-line">PEMERINTAH KABUPATEN CILACAP</div>
            <div class="header-line">DINAS PENDIDIKAN DAN KEBUDAYAAN</div>
            <div class="header-line">{{ strtoupper($settings->school_name ?? 'NAMA SEKOLAH') }}</div>
            <div class="header-line" style="font-weight: normal; font-size: 7.5px; margin-top: 0.5px;">{{ \Illuminate\Support\Str::limit($settings->school_address ?? 'Alamat Sekolah', 85) }}</div>
        </div>
    </div>

    <!-- Foto Siswa (Kiri Tengah) -->
    <div class="photo-container">
        @if($student->photo_path && file_exists(public_path('storage/' . $student->photo_path)))
            <img src="{{ public_path('storage/' . $student->photo_path) }}" alt="Foto Siswa">
        @else
            <div class="photo-no-photo">FOTO</div>
        @endif
    </div>

    <!-- Biodata (Kanan Tengah) -->
    <div class="biodata">
        <table>
            <tr>
                <td class="label">Nama</td>
                <td class="colon">:</td>
                <td class="value student-name">{{ $student->name }}</td>
            </tr>
            <tr>
                <td class="label">NISN</td>
                <td class="colon">:</td>
                <td class="value">{{ $student->nisn }}</td>
            </tr>
            <tr>
                <td class="label">TTL</td>
                <td class="colon">:</td>
                <td class="value">{{ $student->birth_place ?? '-' }}, {{ $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Kelas</td>
                <td class="colon">:</td>
                @php
                    $enrollment = $student->enrollmentAktif;
                    $className = $enrollment?->kelas?->name ?? '-';
                @endphp
                <td class="value">{{ $className }}</td>
            </tr>
            <tr>
                <td class="label">Alamat</td>
                <td class="colon">:</td>
                <td class="value">{{ \Illuminate\Support\Str::limit($student->address ?? '-', 45) }}</td>
            </tr>
        </table>
    </div>

    <!-- Barcode (Bawah Kiri) -->
    <div class="barcode-container">
        @php
            $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
            $barcodeImage = base64_encode($generator->getBarcode($student->barcode_code, $generator::TYPE_CODE_128, 2, 45));
        @endphp
        <img src="data:image/png;base64,{{ $barcodeImage }}" alt="Barcode">
        <div class="barcode-text">{{ $student->barcode_code }}</div>
    </div>

    <!-- TTD Kepala Sekolah (Bawah Kanan) -->
    <div class="signature-container">
        <div>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
        <div class="signature-title">Kepala Sekolah,</div>
        @if($settings?->principal_signature_path && file_exists(public_path('storage/' . $settings->principal_signature_path)))
            <img class="signature-img" src="{{ public_path('storage/' . $settings->principal_signature_path) }}" alt="TTD">
        @else
            <div style="height:9mm;"></div>
        @endif
        <div class="signature-name">{{ $settings?->principal_name ?? '____________________' }}</div>
    </div>

</body>
</html>

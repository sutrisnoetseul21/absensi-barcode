<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Kartu OSIS</title>
    <style>
        @page {
            size: 85.6mm 54mm; /* Landscape CR80 */
            margin: 0;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            width: 85.6mm;
            height: 54mm;
            position: relative;
            background-color: #f0f9ff; /* Light blue gradient effect in CSS later */
            color: #000;
        }
        /* Gradien biru di bagian header meniru contoh */
        .bg-top {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 16mm;
            background-color: #dbeafe;
            border-bottom: 2px solid #93c5fd;
            z-index: -1;
        }
        .header {
            width: 100%;
            text-align: center;
            padding-top: 1mm;
            height: 15mm;
            position: relative;
        }
        .header-text {
            font-size: 6px;
            font-weight: bold;
            line-height: 1.1;
        }
        .school-name {
            font-size: 11px;
            font-weight: bold;
            margin: 1px 0;
            letter-spacing: 0.5px;
        }
        .address-text {
            font-size: 5px;
            font-weight: normal;
        }
        .logo-left {
            position: absolute;
            left: 3mm;
            top: 2mm;
            width: 11mm;
            height: 11mm;
        }
        .logo-right {
            position: absolute;
            right: 3mm;
            top: 2mm;
            width: 11mm;
            height: 11mm;
        }
        
        /* Foto Siswa */
        .photo-container {
            position: absolute;
            left: 3mm;
            top: 18mm;
            width: 18mm;
            height: 24mm;
            border: 1px solid #333;
            background: #fff;
            padding: 1px;
            box-sizing: border-box;
            border-radius: 2px;
        }
        .photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* Biodata */
        .biodata {
            position: absolute;
            left: 24mm;
            top: 18mm;
            width: 58mm;
            font-size: 6px;
            line-height: 1.3;
        }
        .biodata table {
            width: 100%;
            border-collapse: collapse;
        }
        .biodata td {
            vertical-align: top;
            padding-bottom: 0.8mm;
        }
        .biodata td.label {
            width: 18mm;
            font-weight: bold;
        }
        .biodata td.colon {
            width: 2mm;
            text-align: center;
        }
        
        /* Barcode di posisi bawah kiri */
        .barcode-container {
            position: absolute;
            left: 24mm;
            bottom: 3mm;
            width: 25mm;
            text-align: center;
        }
        .barcode-container img {
            width: 25mm;
            height: 6mm;
        }
        .barcode-text {
            font-size: 5px;
            letter-spacing: 1px;
            margin-top: 1px;
        }
        
        /* TTD Kepala Sekolah */
        .signature-container {
            position: absolute;
            right: 3mm;
            bottom: 2mm;
            width: 25mm;
            text-align: center;
            font-size: 5px;
        }
        .signature-date {
            margin-bottom: 1px;
        }
        .signature-title {
            font-weight: bold;
        }
        .signature-img {
            height: 9mm;
            margin: 1px 0;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }
        .signature-nip {
            margin-top: 1px;
        }
    </style>
</head>
<body>
    <div class="bg-top"></div>
    
    <div class="header">
        @if($settings?->school_logo_path && file_exists(public_path('storage/' . $settings->school_logo_path)))
            <img class="logo-left" src="{{ public_path('storage/' . $settings->school_logo_path) }}" alt="Logo">
        @else
            <!-- Placeholder Logo Kiri (misal logo pemprov) -->
            <img class="logo-left" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=" alt="Logo">
        @endif
        
        <!-- Karena biasanya ada 2 logo (kiri-kanan), kita tampilkan logo sekolah di kanan jika ada -->
        @if($settings?->school_logo_path && file_exists(public_path('storage/' . $settings->school_logo_path)))
            <img class="logo-right" src="{{ public_path('storage/' . $settings->school_logo_path) }}" alt="Logo">
        @else
            <img class="logo-right" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=" alt="Logo">
        @endif

        <div class="header-text">
            <div>KEMENTERIAN PENDIDIKAN DAN KEBUDAYAAN</div>
            <div>DINAS PENDIDIKAN</div>
        </div>
        <div class="school-name">{{ strtoupper($settings->school_name ?? 'NAMA SEKOLAH') }}</div>
        <div class="address-text">{{ \Illuminate\Support\Str::limit($settings->school_address ?? 'Alamat Sekolah', 90) }}</div>
    </div>
    
    <div class="photo-container">
        @if($student->photo_path && file_exists(public_path('storage/' . $student->photo_path)))
            <img src="{{ public_path('storage/' . $student->photo_path) }}" alt="Foto Siswa">
        @else
            <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=" alt="Avatar">
        @endif
    </div>
    
    <div class="biodata">
        <table>
            <tr>
                <td class="label">Nama</td>
                <td class="colon">:</td>
                <td style="font-weight: bold;">{{ $student->name }}</td>
            </tr>
            <tr>
                <td class="label">NISN</td>
                <td class="colon">:</td>
                <td>{{ $student->nisn }}</td>
            </tr>
            <tr>
                <td class="label">Tempat, Tgl Lahir</td>
                <td class="colon">:</td>
                <td>{{ $student->birth_place ?? '-' }}, {{ $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->format('d-m-Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Kelas / Rombel</td>
                <td class="colon">:</td>
                @php
                    $enrollment = $student->enrollmentAktif;
                    $className = $enrollment?->kelas?->name ?? '-';
                @endphp
                <td>{{ $className }}</td>
            </tr>
            <tr>
                <td class="label">Alamat</td>
                <td class="colon">:</td>
                <td>{{ \Illuminate\Support\Str::limit($student->address ?? '-', 60) }}</td>
            </tr>
        </table>
    </div>
    
    <div class="barcode-container">
        @php
            $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
            $barcodeImage = base64_encode($generator->getBarcode($student->barcode_code, $generator::TYPE_CODE_128, 2, 40));
        @endphp
        <img src="data:image/png;base64,{{ $barcodeImage }}" alt="Barcode">
        <div class="barcode-text">{{ $student->barcode_code }}</div>
    </div>
    
    <div class="signature-container">
        <div class="signature-date">Tasikmalaya, {{ date('F Y') }}</div>
        <div class="signature-title">Kepala Sekolah</div>
        @if($settings?->principal_signature_path && file_exists(public_path('storage/' . $settings->principal_signature_path)))
            <img class="signature-img" src="{{ public_path('storage/' . $settings->principal_signature_path) }}" alt="TTD">
        @else
            <div style="height: 9mm;"></div>
        @endif
        <div class="signature-name">{{ $settings?->principal_name ?? '____________________' }}</div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Siswa - {{ $student->name }}</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
            size: 153.07pt 243.78pt portrait;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        html, body {
            margin: 0;
            padding: 0;
            width: 153.07pt;
            height: 243.78pt;
            overflow: hidden;
            background-color: #ffffff;
            font-family: 'Helvetica', 'Arial', sans-serif;
            position: relative;
        }

        /* ====== STRIP ATAS (simulasi gradien: biru → amber) ====== */
        .strip-top {
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3pt;
        }
        .strip-top-left {
            position: absolute;
            top: 0; left: 0;
            width: 102pt; height: 3pt;
            background-color: #1e3a8a;
        }
        .strip-top-right {
            position: absolute;
            top: 0; right: 0;
            width: 51pt; height: 3pt;
            background-color: #f59e0b;
        }

        /* ====== STRIP BAWAH (simulasi gradien: amber → biru) ====== */
        .strip-bottom {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 3pt;
        }
        .strip-bottom-left {
            position: absolute;
            bottom: 0; left: 0;
            width: 51pt; height: 3pt;
            background-color: #f59e0b;
        }
        .strip-bottom-right {
            position: absolute;
            bottom: 0; right: 0;
            width: 102pt; height: 3pt;
            background-color: #1e3a8a;
        }

        /* ====== DOMAIN URL (tepat di atas strip bawah) ====== */
        .footer-url-box {
            position: absolute;
            bottom: 3pt;
            left: 0; right: 0;
            text-align: center;
            padding: 2pt 0;
            z-index: 10;
        }
        .footer-url {
            font-size: 5pt;
            color: #3b82f6;
            font-weight: bold;
        }

        /* ====== DEKORASI BACKGROUND (seperti di versi cetak langsung) ====== */
        .bg-arc-top-right {
            position: absolute;
            top: -20pt; right: -20pt;
            width: 80pt; height: 80pt;
            border-radius: 50%;
            background-color: #e8f2ff; /* biru sangat muda */
            z-index: 1;
        }
        .bg-arc-bottom-left {
            position: absolute;
            bottom: -15pt; left: -15pt;
            width: 70pt; height: 70pt;
            border-radius: 50%;
            background-color: #fff4e0; /* oranye/kuning sangat muda */
            z-index: 1;
        }

        /* ====== KONTEN UTAMA (mulai dari bawah strip atas) ====== */
        .content {
            position: absolute;
            top: 3pt;
            left: 0; right: 0;
            /* batas bawah: di atas url box (sekitar 10pt) + strip (3pt) = 13pt dari bawah */
            bottom: 13pt;
            text-align: center;
            padding: 6pt 5pt 2pt 5pt;
            overflow: hidden;
            z-index: 10;
        }

        /* Logo */
        .logo-img {
            height: 23pt;
            width: auto;
        }

        /* Nama Sekolah */
        .school-name {
            font-size: 6.5pt;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            line-height: 1.15;
            margin-top: 1.5pt;
        }

        /* Badge Kartu */
        .card-badge {
            font-size: 4.8pt;
            font-weight: bold;
            color: #f59e0b;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
            background-color: rgba(255,255,255,0.7);
            border: 0.5pt solid #fcd34d;
            border-radius: 6pt;
            padding: 1pt 5pt;
            display: inline-block;
            margin-top: 1.5pt;
            margin-bottom: 4pt;
        }

        /* Foto */
        .photo-frame {
            width: 46pt;
            height: 58pt;
            border-radius: 4pt;
            border: 2pt solid #ffffff;
            background-color: #cbd5e1;
            overflow: hidden;
            margin: 0 auto 3pt auto;
        }
        .photo-frame img {
            width: 46pt;
            height: 58pt;
        }
        .photo-ph {
            font-size: 7pt;
            font-weight: bold;
            color: #64748b;
            line-height: 58pt;
            text-align: center;
        }

        /* Nama & Kelas */
        .student-name {
            font-size: 8.5pt;
            font-weight: bold;
            color: #1e3a8a;
            line-height: 1.1;
            margin-bottom: 1pt;
        }
        .student-class {
            font-size: 6pt;
            color: #475569;
            margin-bottom: 3.5pt;
        }

        /* Login Box */
        .login-box {
            background-color: rgba(255,255,255,0.75);
            border: 0.8pt solid #bfdbfe;
            border-radius: 5pt;
            padding: 2.5pt 3pt;
            margin: 0 3pt 3pt 3pt;
        }
        .login-label {
            font-size: 5pt;
            color: #3b82f6;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3pt;
        }
        .login-val {
            font-size: 10.5pt;
            font-weight: bold;
            color: #1e3a8a;
            letter-spacing: 1.5pt;
            margin-top: 0.5pt;
        }

        /* Barcode */
        .barcode-wrap {
            margin: 0 3pt;
        }
        .barcode-img {
            width: 130pt;
            height: 19pt;
        }
    </style>
</head>
<body>

    {{-- Dekorasi Background --}}
    <div class="bg-arc-top-right"></div>
    <div class="bg-arc-bottom-left"></div>

    {{-- Strip Atas --}}
    <div class="strip-top">
        <div class="strip-top-left"></div>
        <div class="strip-top-right"></div>
    </div>

    {{-- Konten Utama --}}
    <div class="content">
        @if($logoBase64)
            <div><img src="{{ $logoBase64 }}" class="logo-img" alt="Logo"></div>
        @endif

        <div class="school-name">{{ strtoupper($settings->school_name ?? 'NAMA SEKOLAH') }}</div>
        <div><span class="card-badge">KARTU SISWA</span></div>

        <div class="photo-frame">
            @if($photoBase64)
                <img src="{{ $photoBase64 }}" alt="Foto">
            @else
                <div class="photo-ph">FOTO</div>
            @endif
        </div>

        <div class="student-name">{{ $student->name }}</div>
        <div class="student-class">Kelas: {{ $className }}</div>

        <div class="login-box">
            <div class="login-label">Username ({{ $identifierLabel }})</div>
            <div class="login-val">{{ $identifierValue }}</div>
        </div>

        <div class="barcode-wrap">
            <img src="data:image/png;base64,{{ $barcodeImage }}" class="barcode-img" alt="Barcode">
        </div>
    </div>

    {{-- Domain URL (menempel tepat di atas strip bawah) --}}
    <div class="footer-url-box">
        <span class="footer-url">{{ env('SCHOOL_EMAIL_DOMAIN', 'smpn1majenang.sch.id') }}</span>
    </div>

    {{-- Strip Bawah (menempel di batas paling bawah) --}}
    <div class="strip-bottom">
        <div class="strip-bottom-left"></div>
        <div class="strip-bottom-right"></div>
    </div>

</body>
</html>

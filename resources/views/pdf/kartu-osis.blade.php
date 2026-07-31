<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pratinjau Kartu OSIS / Presensi - {{ $student->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1e3a8a; /* Deep Blue */
            --secondary: #3b82f6; /* Bright Blue */
            --accent: #f59e0b; /* Amber/Yellow */
            --bg-light: #f8fafc;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            background-color: #0f172a; /* Sleek dark background for preview */
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* ===== PAGE WRAPPER & SPLIT PREVIEW ===== */
        .page-wrapper {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            gap: 36px;
            max-width: 960px;
            width: 100%;
            margin: 0 auto;
        }

        /* Left: Preview Container */
        .preview-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #1e293b;
            padding: 28px 32px;
            border-radius: 28px;
            border: 1px solid #334155;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
        }

        .preview-header {
            margin-bottom: 20px;
        }

        .badge-preview {
            background: rgba(99, 102, 241, 0.15);
            color: #a5b4fc;
            border: 1px solid rgba(129, 140, 248, 0.3);
            padding: 6px 16px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* Right: Print & PDF Control Panel */
        .print-panel {
            flex: 1;
            background: white;
            padding: 32px;
            border-radius: 28px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            max-width: 440px;
            width: 100%;
        }

        .panel-title {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 6px 0;
            letter-spacing: -0.5px;
        }

        .panel-subtitle {
            font-size: 13px;
            color: #64748b;
            margin: 0 0 22px 0;
            line-height: 1.5;
        }

        .student-summary {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
            font-size: 13px;
        }

        .summary-label {
            color: #64748b;
            font-weight: 500;
        }

        .summary-val {
            color: #0f172a;
            font-weight: 700;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 24px;
        }

        .btn-action {
            width: 100%;
            padding: 14px 20px;
            border-radius: 16px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-print-direct {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
        }

        .btn-print-direct:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.4);
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
        }

        .btn-pdf-download {
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            box-shadow: 0 8px 20px rgba(5, 150, 105, 0.3);
        }

        .btn-pdf-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(5, 150, 105, 0.4);
            background: linear-gradient(135deg, #047857, #065f46);
        }

        .print-tips {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 18px;
            padding: 16px;
            font-size: 12px;
            color: #1e40af;
            line-height: 1.5;
        }

        .print-tips h4 {
            margin: 0 0 8px 0;
            font-size: 13px;
            font-weight: 700;
            color: #1e3a8a;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .print-tips ul {
            margin: 0;
            padding-left: 18px;
        }

        .print-tips li {
            margin-bottom: 6px;
        }

        /* Responsive Breakpoint */
        @media (max-width: 768px) {
            body {
                padding: 16px;
            }
            .page-wrapper {
                flex-direction: column;
                gap: 24px;
            }
            .print-panel {
                max-width: 100%;
            }
        }

        /* ===== CARD CONTAINER (54x86mm) ===== */
        .card {
            width: 54mm;
            height: 86mm;
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border: 1px solid #e2e8f0;
            box-sizing: border-box;
            flex-shrink: 0;
        }

        /* ===== BACKGROUND GRAPHICS ===== */
        .card-bg {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: 0;
            background: linear-gradient(160deg, #f0f7ff 0%, #ffffff 60%, #fff8ed 100%);
            overflow: hidden;
        }
        .bg-top-strip {
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--secondary), var(--accent));
        }
        .bg-bottom-strip {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--accent), var(--secondary), var(--primary));
        }
        .bg-lines {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 12px,
                rgba(59, 130, 246, 0.03) 12px,
                rgba(59, 130, 246, 0.03) 14px
            );
        }
        .bg-arc {
            position: absolute;
            top: -60px; right: -60px;
            width: 130px; height: 130px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(59,130,246,0.07) 0%, rgba(30,58,138,0.05) 100%);
        }
        .bg-arc-2 {
            position: absolute;
            bottom: -50px; left: -50px;
            width: 110px; height: 110px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(245,158,11,0.07) 0%, rgba(217,119,6,0.05) 100%);
        }

        /* ===== CONTENT WRAPPER ===== */
        .card-content {
            position: relative;
            z-index: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        /* ===== HEADER ===== */
        .header {
            padding: 12px 12px 4px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            z-index: 10;
        }
        .logo {
            width: 38px;
            height: 38px;
            object-fit: contain;
            margin-bottom: 6px;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
        }
        .school-name {
            font-size: 8px;
            font-weight: 800;
            line-height: 1.2;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .card-title {
            font-size: 6px;
            color: var(--accent);
            font-weight: 700;
            letter-spacing: 1px;
            margin-top: 2px;
            background: rgba(255, 255, 255, 0.5);
            padding: 2px 8px;
            border-radius: 10px;
        }

        /* ===== PHOTO AREA ===== */
        .photo-area {
            display: flex;
            justify-content: center;
            margin-top: 8px;
            position: relative;
            z-index: 10;
        }
        .photo-frame {
            width: 17mm;
            height: 22mm;
            border-radius: 6px;
            background: #cbd5e1;
            border: 2.5px solid white;
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.2), 0 0 0 1px rgba(59,130,246,0.15);
            overflow: hidden;
            position: relative;
        }
        .photo-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .photo-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: #64748b;
            font-weight: bold;
            background: #f1f5f9;
        }

        /* ===== STUDENT INFO ===== */
        .student-info {
            text-align: center;
            margin-top: 7px;
            padding: 0 10px;
        }
        .student-name {
            font-size: 10px;
            font-weight: 800;
            color: var(--primary);
            line-height: 1.2;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .student-class {
            font-size: 7px;
            color: #64748b;
            font-weight: 600;
            text-align: center;
            margin-top: 2px;
        }

        /* ===== LOGIN CREDENTIALS ===== */
        .login-box {
            margin: 6px 12px 0;
            background: rgba(255, 255, 255, 0.75);
            border: 1px solid rgba(191, 219, 254, 0.9);
            border-radius: 8px;
            padding: 5px 4px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.08);
            backdrop-filter: blur(8px);
        }
        .login-label {
            font-size: 6.5px;
            color: var(--secondary);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 3px;
        }
        .login-value {
            font-size: 15px;
            color: var(--primary);
            font-weight: 900;
            letter-spacing: 2px;
            margin-top: 2px;
        }

        /* ===== BARCODE AREA ===== */
        .barcode-area {
            margin-top: 8px;
            text-align: center;
            padding: 0 10px 0;
        }
        .barcode-area img {
            width: 100%;
            height: 9mm;
            mix-blend-mode: multiply;
        }

        /* ===== FOOTER ===== */
        .footer {
            margin-top: auto;
            padding: 5px 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 6px;
            font-weight: 600;
            color: #64748b;
        }
        .footer-url {
            color: var(--secondary);
        }
        
        /* ===== STRICT PRINT STYLES ===== */
        @media print {
            @page {
                size: 54mm 86mm; /* Exact card size */
                margin: 0;
            }
            html, body {
                background: none !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
                min-height: auto !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .page-wrapper {
                display: block !important;
                max-width: none !important;
                margin: 0 !important;
                gap: 0 !important;
            }
            .preview-area {
                background: none !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
            .preview-header, .print-panel {
                display: none !important;
            }
            .card {
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                width: 54mm !important;
                height: 86mm !important;
                page-break-after: always;
                margin: 0 !important;
            }
            .card-bg {
                background: linear-gradient(135deg, #ffffff 0%, #f0f7ff 100%) !important;
            }
        }
    </style>
</head>
<body>

    @php
        $enrollment = $student->enrollmentAktif;
        $className = $enrollment?->kelas?->name ?? '-';
        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
        $barcodeData = $student->barcode_code ?? $student->nisn ?? 'NO-BARCODE';
        $barcodeImage = base64_encode($generator->getBarcode($barcodeData, $generator::TYPE_CODE_128, 2, 50));

        $logoPath = null;
        if ($settings?->school_logo_path && file_exists(public_path('storage/' . $settings->school_logo_path))) {
            $logoPath = asset('storage/' . $settings->school_logo_path);
        } elseif ($settings?->district_logo_path && file_exists(public_path('storage/' . $settings->district_logo_path))) {
            $logoPath = asset('storage/' . $settings->district_logo_path);
        }

        $photoPath = null;
        if ($student->photo_path && file_exists(public_path('storage/' . $student->photo_path))) {
            $photoPath = asset('storage/' . $student->photo_path);
        }
    @endphp

    <div class="page-wrapper">
        
        <!-- Left: Preview Box -->
        <div class="preview-area">
            <div class="preview-header">
                <span class="badge-preview">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Pratinjau Tampilan Kartu Presensi
                </span>
            </div>

            <!-- The Card -->
            <div class="card">
                <div class="card-bg">
                    <div class="bg-top-strip"></div>
                    <div class="bg-bottom-strip"></div>
                    <div class="bg-lines"></div>
                    <div class="bg-arc"></div>
                    <div class="bg-arc-2"></div>
                </div>

                <div class="card-content">
                    <div class="header">
                        @if($logoPath)
                            <img class="logo" src="{{ $logoPath }}" alt="Logo">
                        @endif
                        <div class="school-name">{{ strtoupper($settings->school_name ?? 'NAMA SEKOLAH') }}</div>
                        <div class="card-title">KARTU PRESENSI</div>
                    </div>

                    <div class="photo-area">
                        <div class="photo-frame">
                            @if($photoPath)
                                <img src="{{ $photoPath }}" alt="Foto">
                            @else
                                <div class="photo-placeholder">FOTO</div>
                            @endif
                        </div>
                    </div>

                    <div class="student-info">
                        <div class="student-name">{{ $student->name }}</div>
                        <div class="student-class">Kelas: {{ $className }}</div>
                    </div>

                    <div class="login-box">
                        <div class="login-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            Username (NISN)
                        </div>
                        <div class="login-value">{{ $student->nisn }}</div>
                    </div>

                    <div class="barcode-area">
                        <img src="data:image/png;base64,{{ $barcodeImage }}" alt="Barcode">
                    </div>

                    <div class="footer">
                        <span class="footer-url">presensi.smpn1majenang.sch.id</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Control & Action Panel -->
        <div class="print-panel">
            <h2 class="panel-title">Cetak Kartu Presensi</h2>
            <p class="panel-subtitle">Periksa pratinjau kartu siswa di sebelah kiri sebelum mencetak atau menyimpan berkas.</p>

            <div class="student-summary">
                <div class="summary-row">
                    <span class="summary-label">Nama Lengkap</span>
                    <span class="summary-val">{{ $student->name }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">NISN</span>
                    <span class="summary-val">{{ $student->nisn }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Kelas & TA</span>
                    <span class="summary-val">{{ $className }}</span>
                </div>
            </div>

            <div class="action-buttons">
                <button onclick="window.print()" class="btn-action btn-print-direct">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    Cetak Langsung (Print)
                </button>

                <button onclick="window.print()" class="btn-action btn-pdf-download">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Simpan Sebagai PDF
                </button>
            </div>

            <div class="print-tips">
                <h4>
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Petunjuk Simpan PDF / Cetak:
                </h4>
                <ul>
                    <li>Untuk <strong>Simpan PDF</strong>: Klik tombol <em>Simpan Sebagai PDF</em>, lalu ubah <strong>Tujuan / Destination</strong> menjadi <strong>"Simpan sebagai PDF"</strong> di dialog browser.</li>
                    <li>Ukuran Kertas: Pilih <strong>Custom / 54mm x 86mm</strong> (Ukuran Kartu Standard ID CR80).</li>
                    <li>Skala / Scale: Pilih <strong>Default / 100%</strong>.</li>
                </ul>
            </div>
        </div>

    </div>

</body>
</html>

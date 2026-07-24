<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Massal Kartu Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1e3a8a; /* Deep Blue */
            --secondary: #3b82f6; /* Bright Blue */
            --accent: #f59e0b; /* Amber/Yellow */
            --bg-light: #f8fafc;
        }

        body {
            margin: 0;
            padding: 20px;
            background-color: #cbd5e1;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        /* ===== PRINT CONTROLS (SCREEN ONLY) ===== */
        .print-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
            background: white;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            text-align: center;
        }
        .btn-print {
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(59, 130, 246, 0.4);
        }
        .print-hint {
            display: block;
            margin-top: 8px;
            font-size: 12px;
            color: #64748b;
        }

        /* ===== A4 PAGE CONTAINER ===== */
        .a4-page {
            width: 210mm;
            min-height: 297mm;
            background: white;
            margin: 0 auto 20px;
            padding: 12mm 15mm;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            box-sizing: border-box;
            display: grid;
            grid-template-columns: repeat(3, 54mm);
            grid-auto-rows: 86mm;
            grid-gap: 5mm;
            justify-content: center;
            align-content: start;
        }

        /* ===== CARD CONTAINER (54x86mm) ===== */
        .card {
            width: 54mm;
            height: 86mm;
            background: white;
            border-radius: 10px; 
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border: 1px dashed #cbd5e1; /* Dashed line to help with cutting */
            box-sizing: border-box;
        }

        /* ===== BACKGROUND GRAPHICS ===== */
        .card-bg {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
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

        /* ===== NAME ===== */
        .name-section {
            text-align: center;
            padding: 5px 10px 0;
        }
        .student-name {
            font-size: 9.5px;
            font-weight: 800;
            color: var(--primary);
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
        }
        .student-class {
            font-size: 6.5px;
            color: #6b7280;
            text-align: center;
            margin-top: 2px;
        }

        /* ===== LOGIN BOX ===== */
        .login-box {
            margin: 5px 10px 0;
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
        .barcode-section {
            margin-top: 8px;
            text-align: center;
            padding: 0 10px;
        }
        .barcode-section img {
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
        
        /* ===== PRINT STYLES ===== */
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }
            body {
                background: none;
                padding: 0;
            }
            .print-controls {
                display: none !important;
            }
            .a4-page {
                box-shadow: none;
                margin: 0;
                padding: 0;
                page-break-after: always;
            }
            /* Override background for printing to ensure exact colors */
            .card-bg {
                background: linear-gradient(135deg, #ffffff 0%, #f0f7ff 100%) !important;
            }
            .card {
                border: 0.5px dashed #94a3b8; /* Keep border to guide scissors */
                border-radius: 0; /* No radius for easier cutting */
            }
            
            /* Hide the last empty page break if present */
            .a4-page:last-child {
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>

    <!-- Print Controls UI (Hidden in Print) -->
    <div class="print-controls">
        <button onclick="window.print()" class="btn-print">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Cetak Massal
        </button>
        <span class="print-hint">Gunakan Kertas A4 (Portrait)</span>
    </div>

    @php
        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();

        $logoPath = null;
        if ($settings?->school_logo_path && file_exists(public_path('storage/' . $settings->school_logo_path))) {
            $logoPath = asset('storage/' . $settings->school_logo_path);
        } elseif ($settings?->district_logo_path && file_exists(public_path('storage/' . $settings->district_logo_path))) {
            $logoPath = asset('storage/' . $settings->district_logo_path);
        }
        
        // Chunk into 9 cards per page (3x3 grid)
        $pages = $students->chunk(9);
    @endphp

    @foreach($pages as $pageStudents)
    <div class="a4-page">
        @foreach($pageStudents as $student)
            @php
                $enrollment = $student->enrollmentAktif;
                $className = $enrollment?->kelas?->name ?? '-';
                $barcodeData = $student->barcode_code ?? $student->nisn ?? 'NO-BARCODE';
                $barcodeImage = base64_encode($generator->getBarcode($barcodeData, $generator::TYPE_CODE_128, 2, 50));

                $photoPath = null;
                if ($student->photo_path && file_exists(public_path('storage/' . $student->photo_path))) {
                    $photoPath = asset('storage/' . $student->photo_path);
                }
            @endphp
            <!-- The Card -->
            <div class="card">
                
                <!-- Beautiful Background -->
                <div class="card-bg">
                    <div class="bg-top-strip"></div>
                    <div class="bg-bottom-strip"></div>
                    <div class="bg-lines"></div>
                    <div class="bg-arc"></div>
                    <div class="bg-arc-2"></div>
                </div>

                <div class="card-content">
                    
                    <!-- Modern Header Centered -->
                    <div class="header">
                        @if($logoPath)
                            <img class="logo" src="{{ $logoPath }}" alt="Logo">
                        @endif
                        <div class="school-name">{{ strtoupper($settings->school_name ?? 'NAMA SEKOLAH') }}</div>
                        <div class="card-title">KARTU PRESENSI</div>
                    </div>

                    <!-- Soft Rounded Photo -->
                    <div class="photo-area">
                        <div class="photo-frame">
                            @if($photoPath)
                                <img src="{{ $photoPath }}" alt="Foto">
                            @else
                                <div class="photo-placeholder">FOTO</div>
                            @endif
                        </div>
                    </div>

                    <!-- Student Info -->
                    <div class="name-section">
                        <div class="student-name">{{ $student->name }}</div>
                        <div class="student-class">Kelas: {{ $className }}</div>
                    </div>

                    <!-- Glassmorphism Login Box -->
                    <div class="login-box">
                        <div class="login-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            Username (NISN)
                        </div>
                        <div class="login-value">{{ $student->nisn }}</div>
                    </div>

                    <!-- Barcode -->
                    <div class="barcode-section">
                        <img src="data:image/png;base64,{{ $barcodeImage }}" alt="Barcode">
                    </div>

                    <!-- Modern Footer Centered -->
                    <div class="footer">
                        <span class="footer-url">presensi.smpn1majenang.sch.id</span>
                    </div>

                </div>
            </div>
        @endforeach
    </div>
    @endforeach

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>

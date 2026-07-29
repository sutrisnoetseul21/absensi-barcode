<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label Spine - {{ isset($buku) && $buku ? $buku->judul : 'Massal' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1e3a8a;
            --secondary: #3b82f6;
            --text-main: #1f2937;
            --text-muted: #4b5563;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #cbd5e1;
            font-family: Arial, Helvetica, 'Inter', system-ui, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            padding-top: 20px;
            padding-bottom: 20px;
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

        /* ===== PAGE CONTAINER ===== */
        .page {
            width: 210mm; /* A4 Width */
            min-height: 297mm; /* A4 Height */
            background: white;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            margin-bottom: 20px;
            box-sizing: border-box;
            padding: 0.5cm; /* Margin halaman di layar */
            
            /* CSS Grid untuk menata label: 3 kolom */
            display: grid;
            grid-template-columns: repeat(3, 6cm); 
            grid-auto-rows: 3.5cm;
            gap: 4mm;
            align-content: start;
            justify-content: center;
        }

        /* ===== LABEL SPINE (SLiMS Style) ===== */
        .label-container {
            width: 6cm;
            height: 3.5cm;
            border: 1px solid #000;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #ffffff;
            box-sizing: border-box;
            padding: 0;
            page-break-inside: avoid;
            overflow: hidden;
        }

        .label-header {
            width: 100%;
            background-color: #CCCCCC !important;
            font-weight: bold;
            padding: 4px;
            font-size: 9pt;
            border-bottom: 1px solid #000;
            text-align: center;
            box-sizing: border-box;
            text-transform: uppercase;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .call-number-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-grow: 1;
            width: 100%;
        }

        .call-number-line {
            font-size: 11pt;
            color: #000;
            text-align: center;
            line-height: 1.3;
            width: 100%;
            word-wrap: break-word;
        }

        /* ===== PRINT STYLES ===== */
        @media print {
            @page {
                size: A4;
                margin: 0.5cm;
            }
            body {
                background: none;
                padding: 0;
                display: block;
            }
            .print-controls {
                display: none !important;
            }
            .page {
                box-shadow: none;
                margin: 0;
                padding: 0;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>

    @php
        $sekolah = \App\Models\PengaturanSekolah::first();
        $namaSekolah = $sekolah && $sekolah->school_name ? $sekolah->school_name : 'PERPUSTAKAAN SMPN 3 KEDUNGREJA';
    @endphp

    <div class="print-controls">
        <button onclick="window.print()" class="btn-print">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Cetak Label Spine
        </button>
        <span class="print-hint">Gunakan kertas A4 untuk hasil optimal</span>
    </div>

    {{-- A4 portrait 3 kolom x 7 baris = 21 label per halaman --}}
    @foreach ($eksemplars->chunk(21) as $pageEksemplars)
    <div class="page">
        @foreach ($pageEksemplars as $eks)
            @php
                $bukuTerkait = $eks->buku;
                $callNumber = $bukuTerkait ? $bukuTerkait->call_number : '';
                $lines = explode("\n", str_replace("\r", "", $callNumber));
                $line1 = $lines[0] ?? '';
                $line2 = $lines[1] ?? '';
                $line3 = $lines[2] ?? '';
            @endphp
            <div class="label-container">
                <div class="label-header">{{ Str::limit($namaSekolah, 40) }}</div>
                <div class="call-number-container">
                    <div class="call-number-line">{{ $line1 }}</div>
                    <div class="call-number-line">{{ $line2 }}</div>
                    <div class="call-number-line">{{ $line3 }}</div>
                </div>
            </div>
        @endforeach
    </div>
    @endforeach

    <script>
        window.onload = function() {
            setTimeout(function() {
                // window.print();
            }, 500);
        };
    </script>
</body>
</html>

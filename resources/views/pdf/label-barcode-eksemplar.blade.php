<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Barcode Eksemplar - {{ $buku ? $buku->judul : 'Massal' }}</title>
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
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
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
            padding: 10mm; /* Margin halaman cetak */
            
            /* CSS Grid untuk menata label */
            display: grid;
            grid-template-columns: repeat(4, 1fr); /* 4 kolom label */
            grid-auto-rows: min-content;
            gap: 5mm; /* Jarak antar label */
            align-content: start;
        }

        /* ===== LABEL BARCODE ===== */
        .label-container {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 6px 4px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            box-sizing: border-box;
            width: 100%;
            page-break-inside: avoid;
        }

        .book-title {
            font-size: 8px;
            font-weight: 600;
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
        }

        .barcode-image {
            width: 100%;
            height: 12mm; /* Tinggi ideal barcode Code128 */
            object-fit: fill; /* Supaya barcode membentang lebar penuh (proporsional) */
        }

        .barcode-code {
            font-size: 11px;
            font-weight: 800;
            color: var(--text-main);
            margin-top: 3px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        /* ===== PRINT STYLES ===== */
        @media print {
            @page {
                size: A4;
                margin: 0;
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
                page-break-after: always;
            }
        }
    </style>
</head>
<body>

    @php
        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
    @endphp

    <div class="print-controls">
        <button onclick="window.print()" class="btn-print">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Cetak Barcode
        </button>
        <span class="print-hint">Gunakan kertas A4 untuk hasil optimal</span>
    </div>

    {{-- Kita membagi data per halaman secara manual agar tidak terpotong di tengah baris jika menggunakan Grid --}}
    {{-- A4 portrait 4 kolom x 12 baris = 48 label per halaman kurang lebih --}}
    @foreach ($eksemplars->chunk(48) as $pageEksemplars)
    <div class="page">
        @foreach ($pageEksemplars as $eks)
            @php
                $judulBuku = $buku ? $buku->judul : ($eks->buku ? $eks->buku->judul : 'Tanpa Judul');
                $barcodeData = $eks->kode_eksemplar;
                // Generate base64 PNG
                $barcodeImage = base64_encode($generator->getBarcode($barcodeData, $generator::TYPE_CODE_128, 2, 50));
            @endphp
            <div class="label-container">
                <div class="book-title">{{ Str::limit($judulBuku, 30) }}</div>
                <img class="barcode-image" src="data:image/png;base64,{{ $barcodeImage }}" alt="Barcode">
                <div class="barcode-code">{{ $barcodeData }}</div>
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

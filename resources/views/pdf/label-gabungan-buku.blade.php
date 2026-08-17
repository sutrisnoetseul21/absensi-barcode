<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label Gabungan - {{ isset($buku) && $buku ? $buku->judul : 'Massal' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1e3a8a;
            --secondary: #3b82f6;
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

        .page {
            width: 210mm;
            min-height: 297mm;
            background: white;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            margin-bottom: 20px;
            box-sizing: border-box;
            padding: 0.5cm;
            
            /* Grid 2 Kolom untuk label gabungan */
            display: grid;
            grid-template-columns: repeat(2, 9.0cm);
            grid-auto-rows: 3.5cm;
            gap: 5mm 6mm;
            align-content: start;
            justify-content: center;
        }

        .label-container {
            width: 9.0cm;
            height: 3.5cm;
            border: 1px solid #000;
            display: flex;
            flex-direction: row;
            background: #ffffff;
            box-sizing: border-box;
            page-break-inside: avoid;
            overflow: hidden;
        }

        /* Sisi Kiri (Barcode & Info Buku) - Sangat ringkas, rapat & proporsional */
        .barcode-section {
            width: 2.4cm;
            height: 100%;
            border-right: 1px solid #000;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            gap: 1.5mm;
            padding: 1mm 1.5mm;
            box-sizing: border-box;
            overflow: hidden;
        }

        /* 1. Judul Buku di sebelah kiri barcode (menghadap ke luar) */
        .barcode-title-vertical {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            transform: rotate(180deg);
            font-size: 7pt;
            font-weight: 600;
            color: #000;
            max-height: 3.2cm;
            line-height: 1.1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            text-align: center;
        }

        /* 2. Gambar Barcode di tengah (lebih tinggi/lebar menutupi area) */
        .barcode-img-wrapper {
            width: 1.4cm;
            height: 3.2cm;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .barcode-img-wrapper img {
            width: 3.1cm;
            height: 1.4cm;
            transform: rotate(90deg);
            transform-origin: center center;
            display: block;
        }

        /* 3. Kode Angka Barcode di sebelah kanan barcode (menghadap ke luar) */
        .barcode-code-vertical {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            transform: rotate(180deg);
            font-size: 8pt;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #000;
            text-align: center;
            white-space: nowrap;
        }

        /* Sisi Kanan (Spine Label) - Proporsi lebih luas */
        .spine-section {
            flex: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        .spine-header {
            width: 100%;
            background-color: #80f4f4 !important; /* Warna cyan/tosca sesuai Gambar 2 SLiMS */
            font-weight: 700;
            font-size: 8.5pt;
            text-align: center;
            padding: 3px 6px;
            border-bottom: 1px solid #000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            height: 1.1cm;
            display: flex;
            align-items: center;
            justify-content: center;
            text-transform: uppercase;
            box-sizing: border-box;
            line-height: 1.2;
            color: #000;
        }

        .call-number-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2mm;
        }

        .call-number-line {
            font-size: 12pt;
            font-weight: 700;
            color: #000;
            text-align: center;
            line-height: 1.25;
            width: 100%;
        }

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
        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
        $sekolah = \App\Models\PengaturanSekolah::current() ?? \App\Models\PengaturanSekolah::first();
        $namaSekolah = $sekolah && $sekolah->school_name ? $sekolah->school_name : 'PERPUSTAKAAN SEKOLAH';
    @endphp

    <div class="print-controls">
        <button onclick="window.print()" class="btn-print">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2-2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Cetak Label Gabungan
        </button>
        <span class="print-hint">Gunakan kertas A4 untuk hasil optimal</span>
    </div>

    {{-- A4 portrait 2 kolom x 7 baris = 14 label per halaman --}}
    @foreach ($eksemplars->chunk(14) as $pageEksemplars)
    <div class="page">
        @foreach ($pageEksemplars as $eks)
            @php
                $bukuTerkait = $eks->buku;
                
                // Data Barcode
                $judulBuku = $bukuTerkait ? $bukuTerkait->judul : 'Tanpa Judul';
                $barcodeData = $eks->kode_eksemplar;
                $barcodeImage = base64_encode($generator->getBarcode($barcodeData, $generator::TYPE_CODE_128, 2, 55));

                // Data Spine
                $callNumber = $bukuTerkait ? $bukuTerkait->call_number : '';
                $rawLines = array_values(array_filter(explode("\n", str_replace("\r", "", $callNumber)), fn($l) => trim($l) !== ''));

                if (count($rawLines) >= 4) {
                    // Jika baris pertama berisi prefix (SR/RF dsb di-skip)
                    $ddcLine = $rawLines[1];
                    $authorLine = $rawLines[2];
                    $titleLine = $rawLines[3];
                } elseif (count($rawLines) === 3 && in_array(strtoupper(trim($rawLines[0])), ['SR', 'RF', 'R'])) {
                    $ddcLine = $rawLines[1];
                    $authorLine = $rawLines[2];
                    $titleLine = '';
                } else {
                    $ddcLine = $rawLines[0] ?? '';
                    $authorLine = $rawLines[1] ?? '';
                    $titleLine = $rawLines[2] ?? '';
                }
            @endphp
            <div class="label-container">
                <!-- Sisi Kiri: Barcode Section -->
                <div class="barcode-section">
                    <!-- 1. Judul Buku Vertikal di Paling Kiri -->
                    <div class="barcode-title-vertical">
                        {{ Str::limit($judulBuku, 35) }}
                    </div>

                    <!-- 2. Gambar Barcode di Tengah -->
                    <div class="barcode-img-wrapper">
                        <img src="data:image/png;base64,{{ $barcodeImage }}" alt="Barcode">
                    </div>

                    <!-- 3. Kode Angka Barcode di Sebelah Kanan Barcode -->
                    <div class="barcode-code-vertical">
                        {{ $barcodeData }}
                    </div>
                </div>
                
                <!-- Sisi Kanan: Spine Section -->
                <div class="spine-section">
                    <div class="spine-header">{{ Str::limit($namaSekolah, 35) }}</div>
                    <div class="call-number-container">
                        <div class="call-number-line">{{ $ddcLine }}</div>
                        <div class="call-number-line">{{ $authorLine }}</div>
                        <div class="call-number-line">{{ $titleLine }}</div>
                    </div>
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

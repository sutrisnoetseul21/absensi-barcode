<style>
    /* CSS Reset & Setup Halaman Cetak */
    @page {
        size: A4 portrait;
        @if($isPdf)
        margin: 8mm 12mm 8mm 12mm;
        @else
        margin: 10mm 15mm 10mm 15mm;
        @endif
    }

    * {
        box-sizing: border-box;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    body {
        font-family: @if($isPdf) 'Times-Roman', Times, serif @else 'Times New Roman', Times, serif @endif;
        font-size: @if($isPdf) 8.5pt @else 9.5pt @endif;
        line-height: @if($isPdf) 1.25 @else 1.3 @endif;
        color: #111;
        margin: 0;
        padding: 0;
        background: @if($isPdf) #fff @else #f8fafc @endif;
    }

    /* Toolbar Layar (Khusus Browser) */
    .screen-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 50;
        background: #1e293b;
        color: #fff;
        padding: 10px 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        font-family: system-ui, -apple-system, sans-serif;
        font-size: 13px;
    }

    .screen-toolbar .btn-group {
        display: flex;
        gap: 10px;
    }

    .screen-toolbar button, .screen-toolbar a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        font-size: 12px;
        border: none;
    }

    .btn-print {
        background: #2563eb;
        color: #fff;
    }

    .btn-print:hover {
        background: #1d4ed8;
    }

    .btn-pdf {
        background: #059669;
        color: #fff;
    }

    .btn-pdf:hover {
        background: #047857;
    }

    .btn-close {
        background: #475569;
        color: #fff;
    }

    .btn-close:hover {
        background: #334155;
    }

    /* Container Kertas A4 */
    .paper-sheet {
        @if($isPdf)
        width: 100% !important;
        min-height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
        @else
        background: #fff;
        width: 210mm;
        min-height: 297mm;
        margin: 20px auto;
        padding: 12mm 15mm 12mm 15mm;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.08);
        @endif
    }

    @media print {
        body {
            background: #fff !important;
            padding: 0 !important;
            font-size: 8.5pt !important;
            line-height: 1.25 !important;
        }
        .screen-toolbar {
            display: none !important;
        }
        .paper-sheet {
            width: 100% !important;
            min-height: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
        }
    }

    /* Kop Surat Resmi */
    .kop-table {
        width: 100%;
        border-collapse: collapse;
        border: none;
        margin-bottom: 0px;
    }

    .kop-table td {
        border: none;
        padding: 0;
        vertical-align: middle;
    }

    .kop-logo {
        width: @if($isPdf) 65px @else 72px @endif;
        text-align: center;
    }

    .kop-logo img {
        max-width: @if($isPdf) 58px @else 68px @endif;
        max-height: @if($isPdf) 58px @else 68px @endif;
        object-fit: contain;
    }

    .kop-text {
        text-align: center;
        padding: 0 10px;
    }

    .kop-instansi {
        font-size: @if($isPdf) 10pt @else 10.5pt @endif;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0;
    }

    .kop-sekolah {
        font-size: @if($isPdf) 13pt @else 13.5pt @endif;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 1px 0;
    }

    .kop-alamat {
        font-size: @if($isPdf) 8pt @else 8.5pt @endif;
        font-style: normal;
        margin: 0;
        line-height: 1.2;
    }

    /* Garis Ganda Kop */
    .kop-line {
        border-top: 2px solid #000;
        border-bottom: 0.6px solid #000;
        height: 2px;
        margin: 4px 0 @if($isPdf) 6px @else 10px @endif 0;
    }

    /* Judul Dokumen */
    .doc-title {
        text-align: center;
        margin-bottom: @if($isPdf) 6px @else 10px @endif;
    }

    .doc-title h1 {
        font-size: @if($isPdf) 11pt @else 11.5pt @endif;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0 0 2px 0;
    }

    .doc-title .sub-title {
        font-size: @if($isPdf) 8.5pt @else 9pt @endif;
        font-weight: bold;
        color: #333;
        margin: 0;
    }

    .doc-title .period-label {
        font-size: @if($isPdf) 8.5pt @else 9pt @endif;
        font-style: italic;
        margin-top: 1px;
    }

    /* Section Heading */
    .section-header {
        font-size: @if($isPdf) 9pt @else 9.5pt @endif;
        font-weight: bold;
        margin: @if($isPdf) 5px 0 2.5px 0 @else 7px 0 3px 0 @endif;
        text-transform: uppercase;
        border-bottom: 1px solid #999;
        padding-bottom: 1px;
    }

    /* Tabel Identitas */
    .identitas-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: @if($isPdf) 5px @else 8px @endif;
        font-size: @if($isPdf) 8.5pt @else 9pt @endif;
    }

    .identitas-table td {
        padding: @if($isPdf) 1.5px 3px @else 2px 4px @endif;
        border: none;
        vertical-align: top;
    }

    .identitas-table .lbl {
        width: 20%;
        color: #222;
    }

    .identitas-table .sep {
        width: 2%;
        text-align: center;
    }

    .identitas-table .val {
        width: 28%;
        font-weight: bold;
    }

    /* Tabel Data / Sesi */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: @if($isPdf) 5px @else 8px @endif;
        font-size: @if($isPdf) 8pt @else 8.5pt @endif;
    }

    .data-table th, .data-table td {
        border: 1px solid #444;
        padding: @if($isPdf) 2.5px 4px @else 3.5px 5px @endif;
        vertical-align: top;
    }

    .data-table th {
        background-color: #f1f5f9;
        font-weight: bold;
        text-align: center;
        text-transform: uppercase;
        font-size: @if($isPdf) 7.5pt @else 8pt @endif;
    }

    .data-table td.center {
        text-align: center;
    }

    /* Pilar Badges in Print */
    .pilar-badge {
        font-weight: bold;
        font-size: @if($isPdf) 7.5pt @else 8pt @endif;
        display: inline-block;
    }

    /* 4 Pilar Summary Grid Table */
    .pilar-summary-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: @if($isPdf) 5px @else 8px @endif;
        font-size: @if($isPdf) 8pt @else 8.5pt @endif;
    }

    .pilar-summary-table th, .pilar-summary-table td {
        border: 1px solid #555;
        padding: @if($isPdf) 2.5px @else 3.5px @endif;
        text-align: center;
    }

    .pilar-summary-table th {
        background-color: #f8fafc;
        font-size: @if($isPdf) 7.5pt @else 8pt @endif;
    }

    /* Box Kinerja (Kelompok) */
    .stat-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: @if($isPdf) 5px @else 8px @endif;
        font-size: @if($isPdf) 8pt @else 8.5pt @endif;
    }

    .stat-table th, .stat-table td {
        border: 1px solid #555;
        padding: @if($isPdf) 2.5px 5px @else 4px 6px @endif;
        text-align: center;
    }

    .stat-table th {
        background-color: #f8fafc;
        font-size: @if($isPdf) 7.5pt @else 8pt @endif;
    }

    .refleksi-box {
        border: 1px solid #666;
        padding: @if($isPdf) 5px 8px @else 8px 10px @endif;
        background: #fff;
        font-size: @if($isPdf) 8pt @else 9pt @endif;
        line-height: @if($isPdf) 1.25 @else 1.35 @endif;
        margin-bottom: @if($isPdf) 6px @else 10px @endif;
        text-align: justify;
    }

    /* Lembar Pengesahan Tanda Tangan */
    .ttd-section {
        width: 100%;
        margin-top: @if($isPdf) 6px @else 10px @endif;
        page-break-inside: avoid;
    }

    .ttd-date {
        text-align: right;
        font-size: @if($isPdf) 8.5pt @else 9pt @endif;
        margin-bottom: @if($isPdf) 3px @else 5px @endif;
        padding-right: 15px;
    }

    .ttd-table {
        width: 100%;
        border-collapse: collapse;
        border: none;
    }

    .ttd-table td {
        border: none;
        text-align: center;
        vertical-align: top;
        padding: 0 4px;
        font-size: @if($isPdf) 8.5pt @else 9pt @endif;
    }

    .ttd-space {
        height: @if($isPdf) 38px @else 48px @endif;
    }

    .ttd-name {
        font-weight: bold;
        text-decoration: underline;
        margin: 0;
        font-size: @if($isPdf) 8.5pt @else 9pt @endif;
    }

    .ttd-nip {
        font-size: @if($isPdf) 7.5pt @else 8pt @endif;
        margin-top: 1px;
    }
</style>

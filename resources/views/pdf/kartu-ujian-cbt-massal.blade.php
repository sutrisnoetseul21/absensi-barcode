<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu Ujian CBT - {{ $ujian->nama_ujian }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1e3a8a;
            --primary-dark: #0f172a;
            --primary-light: #3b82f6;
            --accent: #f59e0b;
            --success: #10b981;
            --border-color: #cbd5e1;
            --bg-card: #ffffff;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #0f172a;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #1e293b;
            padding: 24px;
            min-height: 100vh;
        }

        /* ===== SCREEN PRINT CONTROLS ===== */
        .controls-panel {
            max-width: 1050px;
            margin: 0 auto 24px auto;
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
            color: white;
        }

        .controls-info h2 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #f8fafc;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .controls-info p {
            font-size: 0.85rem;
            color: #94a3b8;
            margin-top: 4px;
        }

        .controls-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .filter-select {
            background: #1e293b;
            color: #f8fafc;
            border: 1px solid #475569;
            padding: 9px 14px;
            border-radius: 8px;
            font-size: 0.85rem;
            outline: none;
            cursor: pointer;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
        }

        .btn-print {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);
        }

        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.5);
        }

        .btn-back {
            background: #334155;
            color: #f1f5f9;
        }

        .btn-back:hover {
            background: #475569;
        }

        /* ===== PRINT CONTAINER & SHEET (A4) ===== */
        .print-canvas {
            max-width: 1050px;
            margin: 0 auto;
            background: transparent;
        }

        .sheet-page {
            background: white;
            padding: 14mm 10mm;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12mm 10mm;
            page-break-after: always;
            break-after: page;
        }

        /* ===== CBT EXAM CARD ITEM ===== */
        .exam-card {
            border: 1.5px solid #0f172a;
            border-radius: 12px;
            background: #ffffff;
            position: relative;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            break-inside: avoid;
            page-break-inside: avoid;
        }

        /* Card Header / Kop Sekolah */
        .card-kop {
            background: #f8fafc;
            border-bottom: 1.5px solid #0f172a;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .kop-logo {
            width: 42px;
            height: 42px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .kop-text {
            flex: 1;
            text-align: center;
        }

        .kop-gov {
            font-size: 7.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            line-height: 1.1;
        }

        .kop-school {
            font-size: 9.5pt;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            line-height: 1.2;
            margin: 1px 0;
        }

        .kop-address {
            font-size: 6.5pt;
            color: #64748b;
            line-height: 1.1;
        }

        /* Title Ribbon */
        .card-ribbon {
            background: linear-gradient(90deg, #1e3a8a, #2563eb);
            color: white;
            text-align: center;
            padding: 4px 8px;
            border-bottom: 1px solid #1e293b;
        }

        .ribbon-title {
            font-size: 8pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .ribbon-subtitle {
            font-size: 7pt;
            font-weight: 600;
            color: #fed7aa;
        }

        /* Card Body */
        .card-body {
            padding: 10px 12px;
            display: flex;
            gap: 12px;
            flex: 1;
        }

        .student-photo-box {
            width: 78px;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex-shrink: 0;
        }

        .student-photo {
            width: 76px;
            height: 98px;
            object-fit: cover;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background: #f1f5f9;
        }

        .photo-placeholder {
            width: 76px;
            height: 98px;
            border: 1px dashed #94a3b8;
            border-radius: 6px;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 7.5pt;
            text-align: center;
            padding: 4px;
        }

        .cbt-role-badge {
            margin-top: 6px;
            background: #dbeafe;
            color: #1e40af;
            font-size: 6.5pt;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Student Details Table */
        .student-details {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }

        .meta-table td {
            padding: 2.5px 0;
            vertical-align: top;
        }

        .meta-label {
            width: 72px;
            color: #475569;
            font-weight: 600;
        }

        .meta-colon {
            width: 8px;
            color: #94a3b8;
            text-align: center;
        }

        .meta-value {
            color: #0f172a;
            font-weight: 700;
        }

        .cred-highlight {
            font-family: 'JetBrains Mono', monospace;
            background: #f1f5f9;
            padding: 1px 6px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            font-size: 8.5pt;
            color: #0f172a;
            display: inline-block;
        }

        .pwd-highlight {
            font-family: 'JetBrains Mono', monospace;
            background: #fef3c7;
            color: #92400e;
            padding: 1px 6px;
            border-radius: 4px;
            border: 1px solid #fde68a;
            font-size: 8.5pt;
            font-weight: 800;
            display: inline-block;
        }

        .session-badge {
            background: #ecfdf5;
            color: #065f46;
            padding: 1px 6px;
            border-radius: 4px;
            border: 1px solid #a7f3d0;
            font-size: 7.5pt;
            font-weight: 700;
            display: inline-block;
        }

        /* Card Footer: Barcode & TTD */
        .card-footer {
            border-top: 1px dashed #cbd5e1;
            padding: 8px 12px;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            background: #fafafa;
        }

        .barcode-section {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .barcode-img {
            height: 28px;
            max-width: 140px;
            object-fit: contain;
        }

        .barcode-text {
            font-family: 'JetBrains Mono', monospace;
            font-size: 7pt;
            color: #64748b;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        .ttd-section {
            text-align: center;
            font-size: 6.5pt;
            color: #334155;
            line-height: 1.2;
            width: 120px;
        }

        .ttd-space {
            height: 24px;
        }

        .ttd-name {
            font-weight: 700;
            text-decoration: underline;
            color: #0f172a;
        }

        /* ===== PRINT MEDIA OPTIMIZATION ===== */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }

            .controls-panel {
                display: none !important;
            }

            .print-canvas {
                max-width: 100%;
                margin: 0;
            }

            .sheet-page {
                box-shadow: none;
                border-radius: 0;
                padding: 10mm 8mm;
                margin: 0;
                page-break-after: always;
                break-after: page;
            }

            .exam-card {
                box-shadow: none;
                border: 1.5px solid #000 !important;
            }

            .card-ribbon {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .pwd-highlight, .session-badge, .cred-highlight {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <!-- ─── SCREEN PRINT CONTROLS ──────────────────────────────────────── -->
    <div class="controls-panel">
        <div class="controls-info">
            <h2>
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Cetak Kartu Peserta Ujian CBT
            </h2>
            <p>{{ $ujian->nama_ujian }} &bull; Total Terdata: <strong>{{ count($cardsData) }} Peserta</strong></p>
        </div>

        <div class="controls-actions">
            <!-- Filter Rombel Kelas -->
            <select class="filter-select" onchange="location.href='?class_id=' + this.value">
                <option value="">-- Semua Rombel Peserta --</option>
                @foreach($allClasses as $c)
                    <option value="{{ $c->id }}" {{ $selectedClassId === $c->id ? 'selected' : '' }}>
                        Kelas {{ $c->name }}
                    </option>
                @endforeach
            </select>

            <button onclick="window.print()" class="btn-action btn-print">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Cetak Dokumen (A4)
            </button>

            <a href="javascript:window.close()" class="btn-action btn-back">Tutup</a>
        </div>
    </div>

    <!-- ─── CARDS PRINT CANVAS ─────────────────────────────────────────── -->
    <div class="print-canvas">
        @php
            $chunks = array_chunk($cardsData, 4); // 4 kartu per lembar A4
            $schoolLogo = $settings?->school_logo_path && file_exists(public_path('storage/' . $settings->school_logo_path))
                ? asset('storage/' . $settings->school_logo_path)
                : null;
            $districtLogo = $settings?->district_logo_path && file_exists(public_path('storage/' . $settings->district_logo_path))
                ? asset('storage/' . $settings->district_logo_path)
                : null;
        @endphp

        @forelse($chunks as $pageCards)
            <div class="sheet-page">
                @foreach($pageCards as $card)
                    <div class="exam-card">
                        <!-- Kop Sekolah -->
                        <div class="card-kop">
                            @if($districtLogo)
                                <img src="{{ $districtLogo }}" class="kop-logo" alt="Logo Dinas">
                            @elseif($schoolLogo)
                                <img src="{{ $schoolLogo }}" class="kop-logo" alt="Logo Sekolah">
                            @else
                                <div style="width: 40px; height: 40px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 8pt; color: #64748b;">CBT</div>
                            @endif

                            <div class="kop-text">
                                <div class="kop-gov">PEMERINTAH KABUPATEN / KOTA</div>
                                <div class="kop-school">{{ $settings?->school_name ?? 'SMP NEGERI 1' }}</div>
                                <div class="kop-address">{{ $settings?->school_address ?? 'Jl. Pendidikan No. 1' }} &bull; NPSN: {{ $settings?->school_npsn ?? '-' }}</div>
                            </div>

                            @if($schoolLogo && $districtLogo)
                                <img src="{{ $schoolLogo }}" class="kop-logo" alt="Logo Sekolah">
                            @else
                                <div style="width: 40px;"></div>
                            @endif
                        </div>

                        <!-- Ribbon Asesmen -->
                        <div class="card-ribbon">
                            <div class="ribbon-title">KARTU PESERTA UJIAN BERBASIS KOMPUTER</div>
                            <div class="ribbon-subtitle">
                                {{ strtoupper($ujian->nama_ujian) }} &bull; T.A. {{ $ujian->tahunAjaran?->name ?? '2025/2026' }} ({{ ucfirst($ujian->semester ?? 'Ganjil') }})
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body">
                            <!-- Foto Siswa -->
                            <div class="student-photo-box">
                                @if(!empty($card['photo_base64']))
                                    <img src="{{ $card['photo_base64'] }}" class="student-photo" alt="{{ $card['student']->name }}">
                                @else
                                    <div class="photo-placeholder">
                                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span>FOTO 3x4</span>
                                    </div>
                                @endif
                                <span class="cbt-role-badge">PESERTA CBT</span>
                            </div>

                            <!-- Data Siswa -->
                            <div class="student-details">
                                <table class="meta-table">
                                    <tr>
                                        <td class="meta-label">Nama Siswa</td>
                                        <td class="meta-colon">:</td>
                                        <td class="meta-value">{{ Str::limit($card['student']->name, 26) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="meta-label">Username / NISN</td>
                                        <td class="meta-colon">:</td>
                                        <td class="meta-value"><span class="cred-highlight">{{ $card['student']->nisn }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="meta-label">Password CBT</td>
                                        <td class="meta-colon">:</td>
                                        <td class="meta-value"><span class="pwd-highlight">{{ $card['password'] }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="meta-label">Kelas / Rombel</td>
                                        <td class="meta-colon">:</td>
                                        <td class="meta-value">{{ $card['class_name'] }}</td>
                                    </tr>
                                    <tr>
                                        <td class="meta-label">Sesi & Ruang</td>
                                        <td class="meta-colon">:</td>
                                        <td class="meta-value">
                                            <span class="session-badge">{{ $card['sesi'] }}</span>
                                            <span style="font-size: 7pt; color: #64748b; margin-left: 4px;">({{ $card['ruang'] }})</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="card-footer">
                            <div class="barcode-section">
                                @if(!empty($card['barcode_base64']))
                                    <img src="{{ $card['barcode_base64'] }}" class="barcode-img" alt="Barcode {{ $card['student']->nisn }}">
                                @endif
                                <span class="barcode-text">* {{ $card['student']->nisn }} *</span>
                            </div>

                            <div class="ttd-section">
                                <div>Panitia Ujian CBT,</div>
                                <div class="ttd-space"></div>
                                <div class="ttd-name">{{ $settings?->principal_name ?? 'Ketua Panitia CBT' }}</div>
                                <div>NIP. {{ $settings?->principal_nip ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <div style="background: #1e293b; color: white; padding: 40px; border-radius: 12px; text-align: center;">
                <p>Tidak ada siswa aktif yang terdaftar pada rombel sasaran ujian ini.</p>
            </div>
        @endforelse
    </div>

</body>
</html>

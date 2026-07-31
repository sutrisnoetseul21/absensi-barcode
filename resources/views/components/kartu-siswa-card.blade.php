@props(['student'])

@php
    $settings = \App\Models\PengaturanSekolah::current();
    $enrollment = $student?->enrollmentAktif;
    $className = $enrollment?->kelas?->name ?? '-';
    
    $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
    $mode = $settings?->barcode_scan_mode ?? 'nisn';
    $isNisMode = $mode === 'nis';
    
    $identifierLabel = $isNisMode ? 'NIS' : 'NISN';
    $identifierValue = $isNisMode ? $student->nis : $student->nisn;
    
    $barcodeData = $student->barcode_code ?? $identifierValue ?? 'NO-BARCODE';
    $barcodeImage = base64_encode($generator->getBarcode($barcodeData, $generator::TYPE_CODE_128, 2, 50));

    $logoPath = null;
    if ($settings?->school_logo_path && file_exists(public_path('storage/' . $settings->school_logo_path))) {
        $logoPath = asset('storage/' . $settings->school_logo_path);
    } elseif ($settings?->district_logo_path && file_exists(public_path('storage/' . $settings->district_logo_path))) {
        $logoPath = asset('storage/' . $settings->district_logo_path);
    }

    $photoPath = null;
    if ($student?->photo_path && file_exists(public_path('storage/' . $student->photo_path))) {
        $photoPath = asset('storage/' . $student->photo_path);
    }
@endphp

<!-- Printable Card Wrapper -->
<div id="student-card-to-print" class="print-card-wrapper">
    <div class="profile-card">
        <div class="profile-card-bg">
            <div class="p-bg-top-strip"></div>
            <div class="p-bg-bottom-strip"></div>
            <div class="p-bg-lines"></div>
            <div class="p-bg-arc"></div>
            <div class="p-bg-arc-2"></div>
        </div>

        <div class="profile-card-content">
            <div class="p-header">
                @if($logoPath)
                    <img class="p-logo" src="{{ $logoPath }}" alt="Logo">
                @endif
                <div class="p-school-name">{{ strtoupper($settings->school_name ?? 'NAMA SEKOLAH') }}</div>
                <div class="p-card-title">KARTU SISWA</div>
            </div>

            <div class="p-photo-area">
                <div class="p-photo-frame">
                    @if($photoPath)
                        <img src="{{ $photoPath }}" alt="Foto">
                    @else
                        <div class="p-photo-placeholder">FOTO</div>
                    @endif
                </div>
            </div>

            <div class="p-student-info">
                <div class="p-student-name">{{ $student->name }}</div>
                <div class="p-student-class">Kelas: {{ $className }}</div>
            </div>

            <div class="p-login-box">
                <div class="p-login-label">
                    <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    Username ({{ $identifierLabel }})
                </div>
                <div class="p-login-value">{{ $identifierValue }}</div>
            </div>

            <div class="p-barcode-area">
                <img src="data:image/png;base64,{{ $barcodeImage }}" alt="Barcode">
            </div>

            <div class="p-footer">
                <span class="p-footer-url">{{ env('SCHOOL_EMAIL_DOMAIN', 'smpn1majenang.sch.id') }}</span>
            </div>
        </div>
    </div>
</div>

<style>
    /* Physical Card Dimensions & Graphics */
    .profile-card {
        width: 54mm;
        height: 86mm;
        background: white;
        border-radius: 10px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.35);
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        border: 1px solid #e2e8f0;
        box-sizing: border-box;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    .profile-card-bg {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        z-index: 0;
        background: linear-gradient(160deg, #f0f7ff 0%, #ffffff 60%, #fff8ed 100%);
        overflow: hidden;
    }
    .p-bg-top-strip {
        position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: linear-gradient(90deg, #1e3a8a, #3b82f6, #f59e0b);
    }
    .p-bg-bottom-strip {
        position: absolute; bottom: 0; left: 0; right: 0; height: 3px;
        background: linear-gradient(90deg, #f59e0b, #3b82f6, #1e3a8a);
    }
    .p-bg-lines {
        position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background-image: repeating-linear-gradient(45deg, transparent, transparent 12px, rgba(59, 130, 246, 0.03) 12px, rgba(59, 130, 246, 0.03) 14px);
    }
    .p-bg-arc {
        position: absolute; top: -60px; right: -60px; width: 130px; height: 130px; border-radius: 50%;
        background: linear-gradient(135deg, rgba(59,130,246,0.07) 0%, rgba(30,58,138,0.05) 100%);
    }
    .p-bg-arc-2 {
        position: absolute; bottom: -50px; left: -50px; width: 110px; height: 110px; border-radius: 50%;
        background: linear-gradient(135deg, rgba(245,158,11,0.07) 0%, rgba(217,119,6,0.05) 100%);
    }
    .profile-card-content {
        position: relative; z-index: 1; height: 100%; display: flex; flex-direction: column; box-sizing: border-box;
    }
    .p-header {
        padding: 12px 12px 4px; display: flex; flex-direction: column; align-items: center; text-align: center; position: relative; z-index: 10;
    }
    .p-logo {
        width: 38px; height: 38px; object-fit: contain; margin-bottom: 6px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
    }
    .p-school-name {
        font-size: 8px; font-weight: 800; line-height: 1.2; color: #1e3a8a; text-transform: uppercase; letter-spacing: 0.5px;
    }
    .p-card-title {
        font-size: 6px; color: #f59e0b; font-weight: 700; letter-spacing: 1px; margin-top: 2px; background: rgba(255, 255, 255, 0.5); padding: 2px 8px; border-radius: 10px;
    }
    .p-photo-area {
        display: flex; justify-content: center; margin-top: 8px; position: relative; z-index: 10;
    }
    .p-photo-frame {
        width: 17mm; height: 22mm; border-radius: 6px; background: #cbd5e1; border: 2.5px solid white; box-shadow: 0 4px 12px rgba(30, 58, 138, 0.2), 0 0 0 1px rgba(59,130,246,0.15); overflow: hidden; position: relative;
    }
    .p-photo-frame img { width: 100%; height: 100%; object-fit: cover; }
    .p-photo-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 8px; color: #64748b; font-weight: bold; background: #f1f5f9; }
    .p-student-info { text-align: center; margin-top: 7px; padding: 0 10px; }
    .p-student-name { font-size: 10px; font-weight: 800; color: #1e3a8a; line-height: 1.2; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .p-student-class { font-size: 7px; color: #64748b; font-weight: 600; text-align: center; margin-top: 2px; }
    .p-login-box {
        margin: 6px 12px 0; background: rgba(255, 255, 255, 0.75); border: 1px solid rgba(191, 219, 254, 0.9); border-radius: 8px; padding: 5px 4px; text-align: center; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.08); backdrop-filter: blur(8px);
    }
    .p-login-label {
        font-size: 6.5px; color: #3b82f6; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; justify-content: center; gap: 3px;
    }
    .p-login-value { font-size: 15px; color: #1e3a8a; font-weight: 900; letter-spacing: 2px; margin-top: 2px; }
    .p-barcode-area { margin-top: 8px; text-align: center; padding: 0 10px 0; }
    .p-barcode-area img { width: 100%; height: 9mm; mix-blend-mode: multiply; }
    .p-footer { margin-top: auto; padding: 5px 12px; display: flex; justify-content: center; align-items: center; font-size: 6px; font-weight: 600; color: #64748b; }
    .p-footer-url { color: #3b82f6; }

    /* Strict Print Rules */
    @media print {
        @page {
            size: 54mm 86mm;
            margin: 0;
        }
        body * {
            visibility: hidden !important;
        }
        #student-card-to-print, #student-card-to-print * {
            visibility: visible !important;
        }
        #student-card-to-print {
            position: fixed !important;
            left: 0 !important;
            top: 0 !important;
            width: 54mm !important;
            height: 86mm !important;
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
        }
        .profile-card {
            box-shadow: none !important;
            border: none !important;
            border-radius: 0 !important;
            width: 54mm !important;
            height: 86mm !important;
        }
    }
</style>

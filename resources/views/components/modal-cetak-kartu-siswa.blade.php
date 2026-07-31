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

<!-- Modal Backdrop -->
<div x-show="showCardModal" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @keydown.escape.window="showCardModal = false"
     class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/80 backdrop-blur-md p-4 sm:p-6 md:p-10 flex items-center justify-center"
     style="display: none;">

    <!-- Modal Content Card -->
    <div @click.outside="showCardModal = false"
         x-show="showCardModal"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-4xl overflow-hidden flex flex-col max-h-[90vh]">

        <!-- Modal Header -->
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-500/20 text-indigo-400 rounded-xl border border-indigo-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-white">Pratinjau Kartu Siswa</h3>
                    <p class="text-xs text-slate-400 font-medium">Cetak atau simpan kartu siswa langsung dari halaman ini.</p>
                </div>
            </div>
            <button @click="showCardModal = false" class="p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Modal Body (2 Columns Split) -->
        <div class="p-6 sm:p-8 overflow-y-auto flex-1 grid grid-cols-1 md:grid-cols-12 gap-8 items-center bg-slate-50/50">
            
            <!-- Left: Card Preview Area (54x86mm ID Card Frame) -->
            <div class="md:col-span-5 flex flex-col items-center justify-center p-6 bg-slate-900/90 rounded-3xl border border-slate-800 shadow-inner">
                <div class="mb-4">
                    <span class="px-3.5 py-1 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-[11px] font-bold uppercase tracking-wider">
                        Ukuran ID Card (54x86mm)
                    </span>
                </div>

                <!-- Printable Card Element -->
                <div id="student-card-to-print" class="print-card-wrapper">
                    <div class="modal-card">
                        <div class="modal-card-bg">
                            <div class="m-bg-top-strip"></div>
                            <div class="m-bg-bottom-strip"></div>
                            <div class="m-bg-lines"></div>
                            <div class="m-bg-arc"></div>
                            <div class="m-bg-arc-2"></div>
                        </div>

                        <div class="modal-card-content">
                            <div class="m-header">
                                @if($logoPath)
                                    <img class="m-logo" src="{{ $logoPath }}" alt="Logo">
                                @endif
                                <div class="m-school-name">{{ strtoupper($settings->school_name ?? 'NAMA SEKOLAH') }}</div>
                                <div class="m-card-title">KARTU SISWA</div>
                            </div>

                            <div class="m-photo-area">
                                <div class="m-photo-frame">
                                    @if($photoPath)
                                        <img src="{{ $photoPath }}" alt="Foto">
                                    @else
                                        <div class="m-photo-placeholder">FOTO</div>
                                    @endif
                                </div>
                            </div>

                            <div class="m-student-info">
                                <div class="m-student-name">{{ $student->name }}</div>
                                <div class="m-student-class">Kelas: {{ $className }}</div>
                            </div>

                            <div class="m-login-box">
                                <div class="m-login-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                    Username ({{ $identifierLabel }})
                                </div>
                                <div class="m-login-value">{{ $identifierValue }}</div>
                            </div>

                            <div class="m-barcode-area">
                                <img src="data:image/png;base64,{{ $barcodeImage }}" alt="Barcode">
                            </div>

                            <div class="m-footer">
                                <span class="m-footer-url">{{ env('SCHOOL_EMAIL_DOMAIN', 'smpn1majenang.sch.id') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Details & Action Controls -->
            <div class="md:col-span-7 space-y-6">
                <div>
                    <h4 class="text-xl font-extrabold text-slate-900">Opsi Cetak & Unduh Kartu</h4>
                    <p class="text-xs text-slate-500 font-medium mt-1">Kartu ini dapat dicetak langsung ke printer atau disimpan sebagai berkas PDF.</p>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs space-y-2.5 text-xs">
                    <div class="flex justify-between items-center border-b border-slate-100 pb-2">
                        <span class="text-slate-400 font-medium">Nama Siswa</span>
                        <strong class="text-slate-900 font-bold">{{ $student->name }}</strong>
                    </div>
                    <div class="flex justify-between items-center border-b border-slate-100 pb-2">
                        <span class="text-slate-400 font-medium">{{ $identifierLabel }}</span>
                        <strong class="text-slate-900 font-bold">{{ $identifierValue }}</strong>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-medium">Kelas</span>
                        <strong class="text-slate-900 font-bold">{{ $className }}</strong>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <button type="button" @click="window.print()" class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-brand-primary hover:bg-indigo-700 text-white font-bold text-xs rounded-2xl shadow-lg shadow-brand-primary/30 transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Cetak Langsung
                    </button>

                    <a href="{{ route('portal-siswa.cetak-kartu', ['download' => 1]) }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-2xl shadow-lg shadow-emerald-600/30 transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download PDF
                    </a>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <a href="{{ route('portal-siswa.cetak-kartu') }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-indigo-600 font-bold hover:text-indigo-800 hover:underline transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Buka Halaman Cetak Terpisah
                    </a>
                </div>

                <div class="p-4 bg-indigo-50/70 border border-indigo-100 rounded-2xl text-indigo-900 text-xs space-y-1.5">
                    <h5 class="font-bold flex items-center gap-1.5 text-indigo-900">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Petunjuk Simpan / Cetak:
                    </h5>
                    <p class="text-[11px] text-indigo-700 leading-relaxed">
                        - Untuk <strong>Download PDF</strong>: Pilih <em>Destination / Tujuan</em>: <strong>"Save as PDF" (Simpan sebagai PDF)</strong>.<br>
                        - Untuk <strong>Cetak Langsung</strong>: Pilih mesin printer Anda dan klik <strong>Print</strong>.
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    /* Modal Card Dimensions & Graphics */
    .modal-card {
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
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    .modal-card-bg {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        z-index: 0;
        background: linear-gradient(160deg, #f0f7ff 0%, #ffffff 60%, #fff8ed 100%);
        overflow: hidden;
    }
    .m-bg-top-strip {
        position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: linear-gradient(90deg, #1e3a8a, #3b82f6, #f59e0b);
    }
    .m-bg-bottom-strip {
        position: absolute; bottom: 0; left: 0; right: 0; height: 3px;
        background: linear-gradient(90deg, #f59e0b, #3b82f6, #1e3a8a);
    }
    .m-bg-lines {
        position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background-image: repeating-linear-gradient(45deg, transparent, transparent 12px, rgba(59, 130, 246, 0.03) 12px, rgba(59, 130, 246, 0.03) 14px);
    }
    .m-bg-arc {
        position: absolute; top: -60px; right: -60px; width: 130px; height: 130px; border-radius: 50%;
        background: linear-gradient(135deg, rgba(59,130,246,0.07) 0%, rgba(30,58,138,0.05) 100%);
    }
    .m-bg-arc-2 {
        position: absolute; bottom: -50px; left: -50px; width: 110px; height: 110px; border-radius: 50%;
        background: linear-gradient(135deg, rgba(245,158,11,0.07) 0%, rgba(217,119,6,0.05) 100%);
    }
    .modal-card-content {
        position: relative; z-index: 1; height: 100%; display: flex; flex-direction: column; box-sizing: border-box;
    }
    .m-header {
        padding: 12px 12px 4px; display: flex; flex-direction: column; align-items: center; text-align: center; position: relative; z-index: 10;
    }
    .m-logo {
        width: 38px; height: 38px; object-fit: contain; margin-bottom: 6px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
    }
    .m-school-name {
        font-size: 8px; font-weight: 800; line-height: 1.2; color: #1e3a8a; text-transform: uppercase; letter-spacing: 0.5px;
    }
    .m-card-title {
        font-size: 6px; color: #f59e0b; font-weight: 700; letter-spacing: 1px; margin-top: 2px; background: rgba(255, 255, 255, 0.5); padding: 2px 8px; border-radius: 10px;
    }
    .m-photo-area {
        display: flex; justify-content: center; margin-top: 8px; position: relative; z-index: 10;
    }
    .m-photo-frame {
        width: 17mm; height: 22mm; border-radius: 6px; background: #cbd5e1; border: 2.5px solid white; box-shadow: 0 4px 12px rgba(30, 58, 138, 0.2), 0 0 0 1px rgba(59,130,246,0.15); overflow: hidden; position: relative;
    }
    .m-photo-frame img { width: 100%; height: 100%; object-fit: cover; }
    .m-photo-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 8px; color: #64748b; font-weight: bold; background: #f1f5f9; }
    .m-student-info { text-align: center; margin-top: 7px; padding: 0 10px; }
    .m-student-name { font-size: 10px; font-weight: 800; color: #1e3a8a; line-height: 1.2; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .m-student-class { font-size: 7px; color: #64748b; font-weight: 600; text-align: center; margin-top: 2px; }
    .m-login-box {
        margin: 6px 12px 0; background: rgba(255, 255, 255, 0.75); border: 1px solid rgba(191, 219, 254, 0.9); border-radius: 8px; padding: 5px 4px; text-align: center; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.08); backdrop-filter: blur(8px);
    }
    .m-login-label {
        font-size: 6.5px; color: #3b82f6; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; justify-content: center; gap: 3px;
    }
    .m-login-value { font-size: 15px; color: #1e3a8a; font-weight: 900; letter-spacing: 2px; margin-top: 2px; }
    .m-barcode-area { margin-top: 8px; text-align: center; padding: 0 10px 0; }
    .m-barcode-area img { width: 100%; height: 9mm; mix-blend-mode: multiply; }
    .m-footer { margin-top: auto; padding: 5px 12px; display: flex; justify-content: center; align-items: center; font-size: 6px; font-weight: 600; color: #64748b; }
    .m-footer-url { color: #3b82f6; }

    /* Strict Print Rules for In-Page Modal Print */
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
        .modal-card {
            box-shadow: none !important;
            border: none !important;
            border-radius: 0 !important;
            width: 54mm !important;
            height: 86mm !important;
        }
    }
</style>

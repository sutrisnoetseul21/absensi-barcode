{{-- 
    Kop Surat Partial (2 Logo Resmi Kedinasan)
    Kompatibel dengan format SPMB ($school) dan Portal Guru ($settings)
    Logo Kiri: Logo Pemda (Pemerintah Kabupaten Cilacap)
    Logo Kanan: Logo Sekolah (SMP Negeri 3 Kedungreja)
    Domain: Otomatis mengikuti APP_URL di .env
    Email: Diambil dari inputan Email Resmi Sekolah di Pengaturan Sekolah
--}}
@php
    $schoolObj = $school ?? $settings ?? null;
    $value = fn ($item) => filled($item) ? $item : '';

    // 1. Logo Kiri (Logo Pemkab Cilacap / Pemda)
    $logoUrl = $logoUrl 
        ?? $logoPemdaBase64 
        ?? ($schoolObj && !empty($schoolObj->logo) ? asset('storage/' . $schoolObj->logo) : null)
        ?? ($schoolObj && !empty($schoolObj->district_logo_path) ? asset('storage/' . $schoolObj->district_logo_path) : null);

    // 2. Logo Kanan (Logo Sekolah)
    $logoKananUrl = $logoKananUrl 
        ?? $logoSekolahBase64 
        ?? ($schoolObj && !empty($schoolObj->logo_kanan) ? asset('storage/' . $schoolObj->logo_kanan) : null)
        ?? ($schoolObj && !empty($schoolObj->school_logo_path) ? asset('storage/' . $schoolObj->school_logo_path) : null);

    // 3. Nama Sekolah
    $namaSekolah = $schoolObj 
        ? ($schoolObj->nama_sekolah ?? $schoolObj->school_name ?? 'SMP NEGERI 3 KEDUNGREJA')
        : 'SMP NEGERI 3 KEDUNGREJA';

    // 4. Alamat Baris 1
    $alamatLine1Parts = $schoolObj ? array_filter([
        $value($schoolObj->alamat ?? $schoolObj->school_address ?? 'Jalan Raya Tambaksari'),
        $value($schoolObj->kelurahan ?? '') ? 'Desa ' . $value($schoolObj->kelurahan) : '',
        $value($schoolObj->kecamatan ?? 'Kedungreja') ? 'Kec. ' . $value($schoolObj->kecamatan ?? 'Kedungreja') : '',
        $value($schoolObj->kabupaten_kota ?? 'Cilacap') ? 'Kab. ' . $value($schoolObj->kabupaten_kota ?? 'Cilacap') : '',
        $value($schoolObj->provinsi ?? ''),
    ]) : [];
    $alamatLine1 = implode(', ', $alamatLine1Parts);
    if (empty($alamatLine1)) {
        $alamatLine1 = 'Jalan Raya Tambaksari, Kec. Kedungreja, Kab. Cilacap 53263';
    }

    // 5. Kontak Baris 2: Domain otomatis dari .env & Email dari input Pengaturan Sekolah
    $rawAppUrl = config('app.url', '');
    $domainEnv = parse_url($rawAppUrl, PHP_URL_HOST) ?: request()->getHost();
    // Hapus www. jika ada agar rapi
    $displayDomain = preg_replace('/^www\./i', '', $domainEnv);

    $emailSekolah = $value($schoolObj->school_email ?? $schoolObj->email ?? '');
    $telpSekolah  = $value($schoolObj->school_phone ?? $schoolObj->telepon ?? '');

    $kontakParts = array_filter([
        $telpSekolah ? 'Telp. ' . $telpSekolah : '',
        $displayDomain ? 'Laman: ' . $displayDomain : '',
        $emailSekolah ? 'Email: ' . $emailSekolah : '',
    ]);
    $alamatLine2 = implode(' | ', $kontakParts);
@endphp

@if($schoolObj)
<table class="kop-surat kop-table" style="width: 100%; border-collapse: collapse; border: none; margin-bottom: 0;">
    <tr>
        <!-- LOGO KIRI (PEMDA CILACAP) -->
        <td class="kop-logo" style="width: 70px; text-align: center; vertical-align: middle; padding: 0;">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo Pemda" style="max-width: @if($isPdf ?? false) 56px @else 66px @endif; max-height: @if($isPdf ?? false) 56px @else 66px @endif; object-fit: contain;">
            @else
                <div style="width: 56px;"></div>
            @endif
        </td>

        <!-- TEKS KOP KEDINASAN RESMI -->
        <td class="kop-text" style="text-align: center; vertical-align: middle; padding: 0 6px;">
            <div class="line1" style="font-size: @if($isPdf ?? false) 9.5pt @else 10.5pt @endif; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; line-height: 1.2;">
                PEMERINTAH KABUPATEN CILACAP
            </div>
            <div class="line2" style="font-size: @if($isPdf ?? false) 9.5pt @else 10.5pt @endif; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; line-height: 1.2;">
                DINAS PENDIDIKAN DAN KEBUDAYAAN
            </div>
            <div class="line3" style="font-size: @if($isPdf ?? false) 12.5pt @else 13.5pt @endif; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin: 1px 0; line-height: 1.2;">
                {{ strtoupper($namaSekolah) }}
            </div>
            <div class="line-alamat" style="font-size: @if($isPdf ?? false) 8pt @else 8.5pt @endif; font-style: normal; margin: 0; line-height: 1.2;">
                {{ $alamatLine1 }}
            </div>
            @if($alamatLine2)
            <div class="line-alamat" style="font-size: @if($isPdf ?? false) 7.5pt @else 8pt @endif; font-style: normal; margin: 0; line-height: 1.2;">
                {{ $alamatLine2 }}
            </div>
            @endif
        </td>

        <!-- LOGO KANAN (SEKOLAH) -->
        <td class="kop-logo" style="width: 70px; text-align: center; vertical-align: middle; padding: 0;">
            @if($logoKananUrl)
                <img src="{{ $logoKananUrl }}" alt="Logo Sekolah Kanan" style="max-width: @if($isPdf ?? false) 56px @else 66px @endif; max-height: @if($isPdf ?? false) 56px @else 66px @endif; object-fit: contain;">
            @else
                <div style="width: 56px;"></div>
            @endif
        </td>
    </tr>
</table>
<div class="kop-line"></div>
@endif

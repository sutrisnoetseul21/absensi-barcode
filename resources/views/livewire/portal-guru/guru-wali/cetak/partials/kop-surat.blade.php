<!-- ================================================================= -->
<!-- FILE KOP SURAT RESMI SEKOLAH                                      -->
<!-- Mendukung 1 Logo atau 2 Logo (Pemda di Kiri, Sekolah di Kanan)   -->
<!-- ================================================================= -->
@php
    $hasLogoPemda = !empty($logoPemdaBase64) || (!empty($settings?->district_logo_path) && file_exists(public_path('storage/' . $settings->district_logo_path)));
    $hasLogoSekolah = !empty($logoSekolahBase64) || !empty($logoBase64) || (!empty($settings?->school_logo_path) && file_exists(public_path('storage/' . $settings->school_logo_path)));
    $hasBothLogos = $hasLogoPemda && $hasLogoSekolah;
@endphp

<table class="kop-table">
    <tr>
        @if($hasLogoPemda)
            <td class="kop-logo" style="text-align: center; vertical-align: middle;">
                <img src="{{ $logoPemdaBase64 ?? public_path('storage/' . $settings->district_logo_path) }}" alt="Logo Pemda">
            </td>
        @elseif($hasLogoSekolah)
            <td class="kop-logo" style="text-align: center; vertical-align: middle;">
                <img src="{{ $logoSekolahBase64 ?? $logoBase64 ?? public_path('storage/' . $settings->school_logo_path) }}" alt="Logo Sekolah">
            </td>
        @endif

        <td class="kop-text">
            <div class="kop-instansi">DINAS PENDIDIKAN DAN KEBUDAYAAN</div>
            <div class="kop-sekolah">{{ strtoupper($settings?->school_name ?? 'SMP NEGERI 3 KEDUNGREJA') }}</div>
            <div class="kop-alamat">
                {{ $settings?->school_address ?? 'Kedungreja' }} &bull; Wilayah {{ $namaKota }}
                <br>Laman: www.smpn3kedungreja.sch.id &bull; Surel: official@smpn3kedungreja.sch.id
            </div>
        </td>

        @if($hasBothLogos)
            <td class="kop-logo" style="text-align: center; vertical-align: middle;">
                <img src="{{ $logoSekolahBase64 ?? public_path('storage/' . $settings->school_logo_path) }}" alt="Logo Sekolah">
            </td>
        @elseif($hasLogoSekolah || $hasLogoPemda)
            <!-- Penyeimbang simetris agar teks kop presisi tepat di tengah -->
            <td class="kop-logo" style="visibility: hidden; width: @if($isPdf) 65px @else 72px @endif;"></td>
        @endif
    </tr>
</table>
<div class="kop-line"></div>

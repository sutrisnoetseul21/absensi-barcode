<!-- ================================================================= -->
<!-- FILE KOP SURAT RESMI SEKOLAH                                      -->
<!-- Digunakan oleh: Cetak Individual, Cetak Masal, & Cetak Kelompok   -->
<!-- ================================================================= -->
<table class="kop-table">
    <tr>
        <td class="kop-logo">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo Sekolah">
            @elseif($settings?->school_logo_path)
                <img src="{{ public_path('storage/' . $settings->school_logo_path) }}" alt="Logo Sekolah">
            @endif
        </td>
        <td class="kop-text">
            <div class="kop-instansi">DINAS PENDIDIKAN DAN KEBUDAYAAN</div>
            <div class="kop-sekolah">{{ strtoupper($settings?->school_name ?? 'SMP NEGERI 3 KEDUNGREJA') }}</div>
            <div class="kop-alamat">
                {{ $settings?->school_address ?? 'Kedungreja' }} &bull; Wilayah {{ $namaKota }}
                <br>Laman: www.smpn3kedungreja.sch.id &bull; Surel: official@smpn3kedungreja.sch.id
            </div>
        </td>
    </tr>
</table>
<div class="kop-line"></div>

<!-- ================================================================= -->
<!-- FILE BAWAH TTD: LEMBAR PENGESAHAN 3 PIHAK                         -->
<!-- Digunakan oleh: Cetak Individual & Cetak Masal                    -->
<!-- Format: Orang Tua (Kiri), Guru Wali (Kanan), Kepsek (Bawah Tengah)-->
<!-- ================================================================= -->
@php
    $namaOrtu = $siswa->nama_ayah ?: ($siswa->nama_ibu ?: $siswa->nama_wali);
    $spaceHeight = ($isPdf ?? false) ? '44px' : '52px';
@endphp

<div class="ttd-section" style="margin-top: @if($isPdf ?? false) 10px @else 16px @endif;">
    <!-- BARIS 1: ORANG TUA MURID (KIRI) & GURU WALI (KANAN) -->
    <table class="ttd-table" style="width: 100%; border-collapse: collapse; border: none; margin-bottom: 0;">
        <tr>
            <!-- KIRI: ORANG TUA MURID -->
            <td style="width: 50%; text-align: center; vertical-align: top; border: none; padding: 0 15px;">
                <div style="font-size: @if($isPdf ?? false) 8.5pt @else 9pt @endif; line-height: 1.3; font-weight: normal;">
                    Orang Tua Murid
                </div>
                <div style="height: {{ $spaceHeight }};"></div>
                <div class="ttd-dots" style="font-size: @if($isPdf ?? false) 8.5pt @else 9pt @endif; letter-spacing: 1px; margin: 0;">
                    ................................................
                </div>
                @if(!empty($namaOrtu))
                    <div class="ttd-nip" style="margin-top: 2px; color: #334155;">
                        ( {{ $namaOrtu }} )
                    </div>
                @else
                    <div class="ttd-nip" style="margin-top: 2px; color: #64748b;">
                        ( Nama Terang & TTD )
                    </div>
                @endif
            </td>

            <!-- KANAN: TEMPAT, TANGGAL & GURU WALI -->
            <td style="width: 50%; text-align: center; vertical-align: top; border: none; padding: 0 15px;">
                <div style="font-size: @if($isPdf ?? false) 8.5pt @else 9pt @endif; line-height: 1.25;">
                    {{ $namaKota }}, {{ $tanggalCetak }}
                </div>
                <div style="font-size: @if($isPdf ?? false) 8.5pt @else 9pt @endif; line-height: 1.25; font-weight: normal; margin-top: 1px;">
                    Guru Wali
                </div>
                <div style="height: calc({{ $spaceHeight }} - 14px);"></div>
                <div class="ttd-name" style="font-size: @if($isPdf ?? false) 8.5pt @else 9pt @endif;">
                    {{ $teacher->name }}
                </div>
                <div class="ttd-nip" style="margin-top: 2px;">
                    NIP. {{ $teacher->nip ?? '—' }}
                </div>
            </td>
        </tr>
    </table>

    <!-- BARIS 2: KEPALA SEKOLAH DI BAWAH TENGAH -->
    <table style="width: 100%; border-collapse: collapse; border: none; margin-top: @if($isPdf ?? false) 10px @else 16px @endif;">
        <tr>
            <td style="width: 25%; border: none; padding: 0;"></td>
            <td style="width: 50%; text-align: center; vertical-align: top; border: none; padding: 0 10px;">
                <div style="font-size: @if($isPdf ?? false) 8.5pt @else 9pt @endif; line-height: 1.3; font-weight: normal;">
                    Kepala Sekolah
                </div>
                <div style="height: {{ $spaceHeight }};"></div>
                <div class="ttd-name" style="font-size: @if($isPdf ?? false) 8.5pt @else 9pt @endif;">
                    {{ $namaKepsek }}
                </div>
                <div class="ttd-nip" style="margin-top: 2px;">
                    NIP. {{ $nipKepsek }}
                </div>
            </td>
            <td style="width: 25%; border: none; padding: 0;"></td>
        </tr>
    </table>
</div>

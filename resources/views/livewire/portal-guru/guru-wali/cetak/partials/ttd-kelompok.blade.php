<!-- ================================================================= -->
<!-- FILE BAWAH TTD: LEMBAR PENGESAHAN 2 PIHAK (KELOMPOK)              -->
<!-- Digunakan oleh: Cetak Kelompok                                    -->
<!-- ================================================================= -->
<div class="ttd-section">
    <div class="ttd-date">
        {{ $namaKota }}, {{ $tanggalCetak }}
    </div>
    <table class="ttd-table">
        <tr>
            <td style="width: 50%;">
                Mengetahui dan Mengesahkan,<br>
                Kepala Sekolah
                <div class="ttd-space"></div>
                <div class="ttd-name">{{ $namaKepsek }}</div>
                <div class="ttd-nip">NIP. {{ $nipKepsek }}</div>
            </td>
            <td style="width: 50%;">
                Guru Wali Pengampu,<br>
                {{ $kelompok->nama_kelompok }}
                <div class="ttd-space"></div>
                <div class="ttd-name">{{ $teacher->name }}</div>
                <div class="ttd-nip">NIP. {{ $teacher->nip ?? '—' }}</div>
            </td>
        </tr>
    </table>
</div>

<!-- ================================================================= -->
<!-- FILE BAWAH TTD: LEMBAR PENGESAHAN 3 PIHAK                         -->
<!-- Digunakan oleh: Cetak Individual & Cetak Masal                    -->
<!-- ================================================================= -->
<div class="ttd-section">
    <div class="ttd-date">
        {{ $namaKota }}, {{ $tanggalCetak }}
    </div>
    <table class="ttd-table">
        <tr>
            <td style="width: 33.33%;">
                Mengetahui,<br>
                Orang Tua / Wali Murid
                <div class="ttd-space"></div>
                <div class="ttd-name">( ............................................ )</div>
                <div class="ttd-nip">Wali dari {{ $siswa->name }}</div>
            </td>
            <td style="width: 33.33%;">
                Guru Wali Pendamping,
                <div class="ttd-space"></div>
                <div class="ttd-name">{{ $teacher->name }}</div>
                <div class="ttd-nip">NIP. {{ $teacher->nip ?? '—' }}</div>
            </td>
            <td style="width: 33.33%;">
                Mengetahui,<br>
                Kepala Sekolah
                <div class="ttd-space"></div>
                <div class="ttd-name">{{ $namaKepsek }}</div>
                <div class="ttd-nip">NIP. {{ $nipKepsek }}</div>
            </td>
        </tr>
    </table>
</div>

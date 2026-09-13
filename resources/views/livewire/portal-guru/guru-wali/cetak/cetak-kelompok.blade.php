<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kinerja Kelompok Guru Wali - {{ $kelompok->nama_kelompok }}</title>

    <!-- CSS Cetak & PDF Presisi -->
    @include('livewire.portal-guru.guru-wali.cetak.partials.styles')
</head>
<body>

    @if(!$isPdf)
    <!-- Screen Toolbar (Hanya Tampil di Browser) -->
    <div class="screen-toolbar no-print">
        <div style="font-weight: bold;">
            📄 Pratinjau Cetak: {{ $kelompok->nama_kelompok }} ({{ $labelPeriode }})
        </div>
        <div class="btn-group">
            <button type="button" class="btn-print" onclick="window.print()">
                🖨️ Cetak Dokumen (A4)
            </button>
            <a href="{{ route('portal-guru.guru-wali.cetak.kelompok.pdf', ['periode' => $periode, 'academic_year_id' => $tahunAjaran->id, 'catatan_refleksi' => $catatanRefleksi]) }}" class="btn-pdf">
                📥 Unduh PDF
            </a>
            <button type="button" class="btn-close" onclick="window.close()">
                ✖ Tutup
            </button>
        </div>
    </div>
    @endif

    <div class="paper-sheet">
        <!-- 1. FILE KOP SURAT -->
        @include('livewire.portal-guru.guru-wali.cetak.partials.kop-surat')

        <!-- 2. FILE ISI LAPORAN KINERJA KELOMPOK -->
        @include('livewire.portal-guru.guru-wali.cetak.partials.isi-kelompok')

        <!-- 3. FILE BAWAH TTD PENGESAHAN (2 PIHAK) -->
        @include('livewire.portal-guru.guru-wali.cetak.partials.ttd-kelompok')
    </div>

    @if(!$isPdf && $autoPrint)
    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
    @endif
</body>
</html>

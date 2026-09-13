<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pendampingan Individual - {{ $siswa->name }}</title>
    
    <!-- CSS Cetak & PDF Presisi -->
    @include('livewire.portal-guru.guru-wali.cetak.partials.styles')
</head>
<body>

    @if(!$isPdf)
    <!-- Screen Toolbar (Hanya Tampil di Browser) -->
    <div class="screen-toolbar no-print">
        <div style="font-weight: bold;">
            📄 Pratinjau Cetak: {{ $siswa->name }} ({{ $labelPeriode }})
        </div>
        <div class="btn-group">
            <button type="button" class="btn-print" onclick="window.print()">
                🖨️ Cetak Dokumen (A4)
            </button>
            <a href="{{ route('portal-guru.guru-wali.cetak.individual.pdf', ['student_id' => $siswa->id, 'periode' => $periode, 'academic_year_id' => $tahunAjaran->id]) }}" class="btn-pdf">
                📥 Unduh PDF
            </a>
            <button type="button" class="btn-close" onclick="window.close()">
                ✖ Tutup
            </button>
        </div>
    </div>
    @endif

    <!-- Container Lembar Kertas A4 -->
    <div class="paper-sheet">
        <!-- 1. FILE KOP SURAT -->
        @include('livewire.portal-guru.guru-wali.cetak.partials.kop-surat')

        <!-- 2. FILE ISI LAPORAN INDIVIDUAL -->
        @include('livewire.portal-guru.guru-wali.cetak.partials.isi-individual')

        <!-- 3. FILE BAWAH TTD PENGESAHAN -->
        @include('livewire.portal-guru.guru-wali.cetak.partials.ttd-individual')
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

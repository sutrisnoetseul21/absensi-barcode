<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Individual Masal - {{ $kelompok->nama_kelompok }} ({{ $labelPeriode }})</title>

    <!-- CSS Cetak & PDF Presisi -->
    @include('livewire.portal-guru.guru-wali.cetak.partials.styles')
</head>
<body>

    @if(!$isPdf)
    <!-- Screen Toolbar (Hanya Tampil di Browser) -->
    <div class="screen-toolbar no-print">
        <div style="font-weight: bold;">
            📄 Pratinjau Cetak Masal: {{ $kelompok->nama_kelompok }} &bull; {{ count($studentsData) }} Murid ({{ $labelPeriode }})
        </div>
        <div class="btn-group">
            <button type="button" class="btn-print" onclick="window.print()">
                🖨️ Cetak Semua Murid (A4)
            </button>
            <a href="{{ route('portal-guru.guru-wali.cetak.individual.massal.pdf', ['periode' => $periode]) }}" class="btn-pdf">
                📥 Unduh Masal PDF
            </a>
            <button type="button" class="btn-close" onclick="window.close()">
                ✖ Tutup
            </button>
        </div>
    </div>
    @endif

    @foreach($studentsData as $studentIndex => $st)
    <!-- Lembar Tiap Murid (Otomatis Page-Break Kecuali Terakhir) -->
    <div class="paper-sheet" @if(!$loop->last) style="page-break-after: always; break-after: page;" @endif>
        <!-- 1. FILE KOP SURAT -->
        @include('livewire.portal-guru.guru-wali.cetak.partials.kop-surat')

        <!-- 2. FILE ISI LAPORAN INDIVIDUAL -->
        @include('livewire.portal-guru.guru-wali.cetak.partials.isi-individual', [
            'siswa'            => $st->siswa,
            'kelas'            => $st->kelas,
            'jurnals'          => $st->jurnals,
            'semester1Jurnals' => $st->semester1Jurnals,
            'semester2Jurnals' => $st->semester2Jurnals,
            'konsultasis'      => $st->konsultasis,
            'distribusiPilar'  => $st->distribusiPilar,
        ])

        <!-- 3. FILE BAWAH TTD PENGESAHAN -->
        @include('livewire.portal-guru.guru-wali.cetak.partials.ttd-individual', [
            'siswa' => $st->siswa,
        ])
    </div>
    @endforeach

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

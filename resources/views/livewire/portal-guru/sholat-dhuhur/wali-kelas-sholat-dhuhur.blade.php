<div class="min-h-full bg-slate-50 font-jakarta pb-12" x-data="{ showInputModal: @entangle('showInputModal').live, showCetakModal: @entangle('showCetakModal').live }">
    @include('livewire.portal-guru.sholat-dhuhur.header')

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10 space-y-6">
        @include('livewire.portal-guru.sholat-dhuhur.filters')

        @if(!$selectedAcademicYearId || !$selectedClassId)
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl p-12 text-center border border-white/40 shadow-xl">
                <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-400">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-700">Pilih Kelas dan Tahun Ajaran</h3>
                <p class="text-xs text-slate-400 mt-1">Silakan pilih kelas binaan Anda untuk menampilkan matriks presensi sholat dhuhur.</p>
            </div>
        @else
            @include('livewire.portal-guru.sholat-dhuhur.stats-monthly')
            @include('livewire.portal-guru.sholat-dhuhur.table')
        @endif
    </div>

    @include('livewire.portal-guru.sholat-dhuhur.modal-input')
    @include('livewire.portal-guru.sholat-dhuhur.modal-cetak')
    
    <!-- Toast Notification Listener -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('notify', (event) => {
                const data = event[0] || event;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: data.type || 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                    });
                } else {
                    alert(data.message);
                }
            });
        });
    </script>
</div>

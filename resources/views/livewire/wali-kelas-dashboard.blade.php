<div class="min-h-screen bg-slate-50 font-jakarta pb-12" x-data="{ showInputModal: @entangle('showInputModal').live, showCetakModal: @entangle('showCetakModal').live }">
    @include('livewire.wali-kelas.header')

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10">
        @include('livewire.wali-kelas.filters')

        @if(!$selectedAcademicYearId || !$selectedClassId)
            @include('livewire.wali-kelas.empty-states')
        @else
            @include('livewire.wali-kelas.stats')
            @include('livewire.wali-kelas.table')
        @endif
    </div>

    @include('livewire.wali-kelas.modal-input')
    @include('livewire.wali-kelas.modal-cetak')
    
    <!-- Toast Notification (SweetAlert integration) -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('notify', (event) => {
                const data = event[0];
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

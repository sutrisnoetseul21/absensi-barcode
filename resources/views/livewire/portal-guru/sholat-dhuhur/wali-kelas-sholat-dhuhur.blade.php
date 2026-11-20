<div class="min-h-full bg-slate-50 font-jakarta pb-12" x-data="{ 
    showInputModal: @entangle('showInputModal').live, 
    showCetakModal: @entangle('showCetakModal').live,
    rekapMode: 'harian',
    dailyFilter: 'all',
    searchQuery: '',
    showWaModal: false,
    waStudentId: '',
    waStudentName: '',
    waStudentClass: '',
    waStudentNisn: '',
    waRecipientPhone: '',
    waOriginalMessage: '',
    waMessage: '',
    waStatusType: 'sholat_dhuhur',
    waStatusLabel: '',
    isSending: false,
    openWaModal(data) {
        this.waStudentId = data.id;
        this.waStudentName = data.name;
        this.waStudentClass = data.className;
        this.waStudentNisn = data.nisn || '-';
        this.waRecipientPhone = data.cleanPhone || data.phone || '';
        this.waOriginalMessage = data.rawText || '';
        this.waMessage = data.rawText || '';
        this.waStatusType = data.type || 'sholat_dhuhur';
        this.waStatusLabel = data.statusLabel || 'Belum Presensi Sholat';
        this.isSending = false;
        this.showWaModal = true;
    },
    resetTemplate() {
        this.waMessage = this.waOriginalMessage;
    },
    submitSend() {
        if (!this.waRecipientPhone.trim()) {
            alert('Silakan masukkan nomor WhatsApp tujuan!');
            return;
        }
        if (!this.waMessage.trim()) {
            alert('Isi pesan tidak boleh kosong!');
            return;
        }
        this.isSending = true;
        const wire = this.$wire || (typeof $wire !== 'undefined' ? $wire : null) || (window.Livewire ? window.Livewire.find(this.$el.closest('[wire\\:id]')?.getAttribute('wire:id')) : null);
        if (!wire) {
            alert('Livewire controller tidak ditemukan.');
            this.isSending = false;
            return;
        }
        wire.sendWaDirect(this.waStudentId, this.waRecipientPhone, this.waMessage, this.waStatusType)
            .catch(err => {
                console.error('Error sendWaDirect:', err);
                alert('Terjadi kesalahan saat memproses pengiriman: ' + (err?.message || err));
            })
            .finally(() => {
                this.isSending = false;
            });
    },
    openWhatsAppWeb() {
        if (!this.waRecipientPhone.trim()) {
            alert('Silakan masukkan nomor WhatsApp tujuan!');
            return;
        }
        if (!this.waMessage.trim()) {
            alert('Isi pesan tidak boleh kosong!');
            return;
        }
        let cleanPhone = this.waRecipientPhone.replace(/[^0-9]/g, '');
        if (cleanPhone.startsWith('0')) {
            cleanPhone = '62' + cleanPhone.substring(1);
        } else if (cleanPhone.startsWith('8')) {
            cleanPhone = '62' + cleanPhone;
        }
        const url = 'https://wa.me/' + cleanPhone + '?text=' + encodeURIComponent(this.waMessage);
        window.open(url, '_blank');
    }
}"
@wa-modal-close.window="showWaModal = false">
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

    <x-portal.wa-ortu-modal
        title="Kirim Pesan WhatsApp Sholat Dhuhur"
        subtitle="Kirim via WhatsApp Web/HP atau langsung lewat gateway aplikasi"
        statusHeading="Status Sholat"
        :canSendGateway="$isNotifSholatActive ?? false"
        :templateNote="'Pesan di atas diambil otomatis dari template presensi sholat dhuhur ' . (\App\Models\PengaturanSekolah::current()?->school_name ?? 'Sekolah') . '.'"
    />

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
                }
            });
        });
    </script>
</div>

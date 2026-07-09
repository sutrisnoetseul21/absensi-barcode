<div class="min-h-screen bg-slate-50 flex flex-col font-jakarta"
    x-data="{
        selectedMonthYear: @entangle('selectedMonthYear').live,
        
        get monthName() {
            if (!this.selectedMonthYear) return '';
            const parts = this.selectedMonthYear.split('-');
            const monthStr = parts[0];
            const yearStr = parts[1];
            
            const months = {
                '01':'Januari','02':'Februari','03':'Maret','04':'April','05':'Mei','06':'Juni',
                '07':'Juli','08':'Agustus','09':'September','10':'Oktober','11':'November','12':'Desember'
            };
            return (months[monthStr] || '') + ' ' + yearStr;
        }
    }">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        
        @include('livewire.wali-kelas-student-detail.profile')

        @if(!$enrollment)
            <!-- State Tidak Ada Enrollment -->
            <div class="bg-yellow-50 border border-yellow-200 p-6 rounded-2xl flex items-start shadow-sm">
                <div class="flex-shrink-0 bg-yellow-100 p-3 rounded-full">
                    <svg class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-xl font-bold text-yellow-800">Siswa Belum Aktif</h3>
                    <p class="mt-2 text-yellow-700 text-sm">Sistem mendeteksi bahwa siswa ini saat ini tidak didaftarkan (di-*enroll*) pada kelas manapun untuk tahun ajaran yang sedang berlangsung.</p>
                </div>
            </div>
        @else

        @include('livewire.wali-kelas-student-detail.stats')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8" wire:loading.class="opacity-50">
            @include('livewire.wali-kelas-student-detail.calendar')

            @include('livewire.wali-kelas-student-detail.activity')
        </div>
        
        @endif
    </div>

    <style>
        .font-jakarta { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob { animation: blob 7s infinite; }
        .animation-delay-2000 { animation-delay: 2s; }
        .animation-delay-4000 { animation-delay: 4s; }
    </style>
</div>

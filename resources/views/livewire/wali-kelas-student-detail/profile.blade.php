<!-- Navigation Back -->
<div class="mb-6">
    <a href="{{ route('wali-kelas.dashboard') }}" class="inline-flex items-center text-sm font-bold text-slate-500 hover:text-indigo-600 transition-colors bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-sm">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Kembali ke Dashboard Wali Kelas
    </a>
</div>

<!-- SEKSI 2: Header Profil -->
<div class="relative bg-emerald-700 rounded-3xl overflow-hidden shadow-xl border border-emerald-600 mb-8">
    <!-- Background Blobs -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-teal-500 mix-blend-multiply filter blur-3xl opacity-50 animate-blob"></div>
        <div class="absolute top-12 -right-24 w-96 h-96 rounded-full bg-emerald-500 mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-24 left-1/3 w-96 h-96 rounded-full bg-cyan-600 mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-4000"></div>
    </div>

    <div class="relative z-10 p-8 flex flex-col md:flex-row items-center justify-between gap-6">
        <!-- Info Siswa -->
        <div class="flex items-center gap-6 w-full md:w-auto">
            <div class="relative">
                <!-- Persentase Kehadiran Circular Ring -->
                <svg class="w-32 h-32 transform -rotate-90">
                    <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="8" fill="transparent" class="text-emerald-900/40" />
                    <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="8" fill="transparent" 
                            :stroke-dasharray="2 * Math.PI * 56" 
                            :stroke-dashoffset="(2 * Math.PI * 56) * (1 - ({{ $attendancePercentage }} / 100))"
                            class="text-green-400 transition-all duration-1000 ease-out" stroke-linecap="round" />
                </svg>
                
                <div class="absolute inset-0 p-3">
                    @if($student->photo_path)
                        <img src="{{ asset('storage/'.$student->photo_path) }}" alt="Foto Siswa" class="w-full h-full rounded-full border-4 border-white shadow-lg object-cover">
                    @else
                        <div class="w-full h-full rounded-full border-4 border-white shadow-lg bg-emerald-100 flex items-center justify-center text-emerald-700 text-4xl font-bold">
                            {{ substr($student->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                
                <!-- Badge Kehadiran -->
                <div class="absolute -bottom-2 -right-2 bg-white text-emerald-700 text-xs font-bold px-3 py-1.5 rounded-full shadow-lg border border-emerald-100 flex items-center">
                    {{ $attendancePercentage }}%
                </div>
            </div>
            
            <div class="text-white">
                <h1 class="text-3xl font-extrabold tracking-tight drop-shadow-md mb-1">{{ $student->name }}</h1>
                <p class="text-emerald-100 font-medium flex items-center text-lg drop-shadow">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                    NISN: {{ $student->nisn }}
                </p>
                @if($enrollment)
                <div class="inline-flex mt-3 px-4 py-1.5 bg-white/20 rounded-full text-sm font-semibold backdrop-blur-md border border-white/30 shadow-sm">
                    Kelas: {{ $enrollment->kelas->name ?? '-' }} | {{ $enrollment->tahunAjaran->name ?? '-' }}
                </div>
                @endif
            </div>
        </div>
        
        <!-- Pilih Bulan -->
        <div class="flex flex-col items-end gap-4 w-full md:w-auto">
            <div class="relative w-full md:w-56">
                <select wire:model.live="selectedMonthYear" class="block w-full pl-4 pr-10 py-2.5 text-slate-800 border-white focus:outline-none focus:ring-2 focus:ring-white sm:text-sm rounded-xl shadow-lg bg-white/90 backdrop-blur-md cursor-pointer appearance-none font-bold">
                    @foreach($availableMonths as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>
        </div>
    </div>
</div>

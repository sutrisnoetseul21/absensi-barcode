@props(['pengaturanSekolah'])

<footer class="relative bg-[#0f0f1a] text-slate-400 border-t border-white/5 mt-auto overflow-hidden">
    <!-- Subtle gradient accent -->
    <div class="absolute inset-0 bg-gradient-to-r from-indigo-950/30 via-transparent to-violet-950/20 pointer-events-none"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col sm:flex-row justify-between items-start gap-8">
            <!-- Left: School Info -->
            <div class="flex items-start gap-4">
                @if($pengaturanSekolah && $pengaturanSekolah->school_logo_path)
                    <img src="{{ asset('storage/' . $pengaturanSekolah->school_logo_path) }}" alt="Logo"
                        class="h-12 w-auto object-contain opacity-80 flex-shrink-0">
                @endif
                <div>
                    <h4 class="text-white font-extrabold text-lg leading-tight mb-1">
                        {{ $pengaturanSekolah ? $pengaturanSekolah->school_name : 'Sistem Presensi' }}
                    </h4>
                    <p class="text-sm text-slate-500 max-w-sm">
                        {{ $pengaturanSekolah ? $pengaturanSekolah->school_address : '' }}
                    </p>
                    <div class="flex items-center gap-2 mt-3">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-xs text-emerald-500 font-semibold">Sistem aktif & berjalan normal</span>
                    </div>
                </div>
            </div>
            <!-- Right: Links -->
            <div class="flex flex-col items-start sm:items-end gap-3">
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('siswa.login') }}" class="text-sm text-slate-400 hover:text-white transition-colors">Portal Siswa</a>
                    <span class="text-slate-700">·</span>
                    <a href="{{ route('wali-kelas.login') }}" class="text-sm text-slate-400 hover:text-white transition-colors">Wali Kelas</a>
                    <span class="text-slate-700">·</span>
                    <a href="/admin" class="text-sm text-slate-400 hover:text-white transition-colors">Admin</a>
                    <span class="text-slate-700">·</span>
                    <a href="{{ route('kiosk.scan') }}" class="text-sm text-indigo-400 hover:text-indigo-300 font-semibold transition-colors">Presensi Digital</a>
                </div>
                <p class="text-xs text-slate-600">
                    &copy; {{ date('Y') }} {{ $pengaturanSekolah ? $pengaturanSekolah->school_name : 'Sistem Presensi' }}. Hak Cipta Dilindungi.
                </p>
                <p class="text-xs text-slate-700">Sistem Presensi Berbasis Barcode</p>
            </div>
        </div>
        <!-- Divider -->
        <div class="border-t border-white/5 mt-8 pt-5 text-center">
            <p class="text-xs text-slate-700">Dibangun dengan ❤️ untuk transparansi dan kedisiplinan pendidikan.</p>
        </div>
    </div>
</footer>

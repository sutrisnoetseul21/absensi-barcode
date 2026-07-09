@if(!$selectedAcademicYearId)
    <div class="bg-amber-50/80 backdrop-blur-md border border-amber-200/60 rounded-3xl p-8 flex flex-col md:flex-row items-center md:items-start gap-6 shadow-xl relative z-20 text-center md:text-left">
        <div class="flex-shrink-0 w-16 h-16 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl flex items-center justify-center shadow-lg shadow-amber-500/30">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        </div>
        <div>
            <h3 class="text-xl font-extrabold text-amber-900 mb-2">Tahun Ajaran Belum Aktif</h3>
            <p class="text-base text-amber-800/80 leading-relaxed max-w-2xl">Sistem mendeteksi belum ada Tahun Ajaran yang diatur menjadi <strong class="text-amber-900">Aktif</strong>. Silakan hubungi Administrator untuk membuat atau mengaktifkan Tahun Ajaran terlebih dahulu di menu Data Master.</p>
        </div>
    </div>
@elseif(!$selectedClassId)
    <div class="bg-white/80 backdrop-blur-md border border-slate-200/60 rounded-3xl p-10 flex flex-col items-center justify-center gap-4 shadow-xl relative z-20 text-center">
        <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-2">
            <svg class="w-10 h-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
        </div>
        <div>
            <h3 class="text-2xl font-extrabold text-slate-800 mb-2">Belum Memiliki Kelas</h3>
            <p class="text-base text-slate-500 max-w-md mx-auto">Anda belum terdaftar mengampu kelas manapun sebagai Wali Kelas pada tahun ajaran aktif ini.</p>
        </div>
    </div>
@endif

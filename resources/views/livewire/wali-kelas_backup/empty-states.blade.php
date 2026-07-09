@if(!$selectedAcademicYearId)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 flex items-start gap-4 shadow-sm">
        <div class="flex-shrink-0 w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        </div>
        <div>
            <h3 class="text-lg font-bold text-amber-800">Tahun Ajaran Belum Aktif</h3>
            <p class="mt-1 text-sm text-amber-700">Sistem mendeteksi belum ada Tahun Ajaran yang diatur menjadi <strong>Aktif</strong>. Silakan buat atau aktifkan Tahun Ajaran terlebih dahulu di menu Data Master.</p>
        </div>
    </div>
@elseif(!$selectedClassId)
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 flex items-start gap-4 shadow-sm">
        <div class="flex-shrink-0 w-12 h-12 bg-slate-200 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
        </div>
        <div>
            <h3 class="text-lg font-bold text-slate-800">Tidak Ada Kelas</h3>
            <p class="mt-1 text-sm text-slate-600">Anda belum terdaftar mengampu kelas manapun pada tahun ajaran aktif ini.</p>
        </div>
    </div>
@endif

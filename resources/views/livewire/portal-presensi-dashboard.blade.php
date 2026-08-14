<div class="min-h-screen bg-slate-50 p-8 flex flex-col items-center justify-center font-jakarta">
    <div class="text-center">
        <h1 class="text-3xl font-extrabold text-slate-800 mb-4">Portal Absensi</h1>
        <p class="text-slate-500 mb-6">Halaman dashboard Portal Absensi (Petugas Presensi) sedang dalam tahap pengembangan.</p>
        <a href="{{ url('/') }}" class="inline-block bg-brand-primary text-white font-bold py-2 px-6 rounded-xl hover:bg-brand-primary-dark transition-colors">Kembali ke Home</a>
        <form action="{{ route('portal-presensi.logout') }}" method="POST" class="inline-block mt-4 ml-4">
            @csrf
            <button type="submit" class="text-rose-500 font-bold hover:text-rose-600 transition-colors">Logout</button>
        </form>
    </div>
</div>

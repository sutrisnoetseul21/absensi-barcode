<div class="min-h-screen bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/30 text-white font-bold text-xl">
                            S
                        </div>
                        <span class="font-bold text-xl text-gray-900 tracking-tight">Portal Siswa</span>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="hidden md:block text-sm text-gray-600 font-medium">
                        Halo, {{ Auth::guard('siswa')->user()->nama ?? 'Siswa' }}
                    </div>
                    <form action="{{ route('siswa.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
            <div class="p-8 sm:p-12 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-white">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                    Selamat Datang di Dashboard Siswa
                </h1>
                <p class="mt-4 text-lg text-gray-600 max-w-3xl">
                    Ini adalah halaman awal untuk Anda. Fitur untuk melihat absensi, jadwal kelas, dan informasi lainnya akan segera tersedia pada tahap pengembangan berikutnya.
                </p>
            </div>
            
            <div class="bg-gray-50 bg-opacity-50 grid grid-cols-1 md:grid-cols-2 gap-6 p-8">
                <!-- Placeholder card 1 -->
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex items-start gap-4">
                    <div class="p-3 bg-emerald-100 text-emerald-600 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Kehadiran Hari Ini</h3>
                        <p class="mt-1 text-gray-500">Belum ada data kehadiran yang tercatat untuk hari ini.</p>
                    </div>
                </div>

                <!-- Placeholder card 2 -->
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex items-start gap-4">
                    <div class="p-3 bg-blue-100 text-blue-600 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Rekap Absensi</h3>
                        <p class="mt-1 text-gray-500">Fitur rekap kehadiran mingguan dan bulanan sedang dalam pengerjaan.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

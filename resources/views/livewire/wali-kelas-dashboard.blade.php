<div class="min-h-screen bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/30 text-white font-bold text-xl">
                            W
                        </div>
                        <span class="font-bold text-xl text-gray-900 tracking-tight">Portal Wali Kelas</span>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="hidden md:block text-sm text-gray-600 font-medium">
                        Halo, {{ Auth::guard('wali_kelas')->user()->nama ?? 'Bapak/Ibu Guru' }}
                    </div>
                    <form action="{{ route('wali-kelas.logout') }}" method="POST">
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
            <div class="p-8 sm:p-12 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-white">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                    Selamat Datang di Dashboard Wali Kelas
                </h1>
                <p class="mt-4 text-lg text-gray-600 max-w-3xl">
                    Anda berhasil masuk. Melalui portal ini, Anda nantinya dapat memantau data absensi siswa di kelas perwalian Anda secara *real-time*.
                </p>
            </div>
            
            <div class="bg-gray-50 bg-opacity-50 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-8">
                <!-- Placeholder card 1 -->
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex flex-col items-start">
                    <div class="p-3 bg-indigo-100 text-indigo-600 rounded-lg mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Siswa Kelas Anda</h3>
                    <p class="mt-2 text-gray-500 flex-grow">Kelola data siswa, pantau status kehadiran harian, dan buat rekap absensi kelas dengan mudah.</p>
                    <span class="mt-4 inline-flex items-center text-sm font-semibold text-indigo-600">Fitur Segera Hadir &rarr;</span>
                </div>

                <!-- Placeholder card 2 -->
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex flex-col items-start">
                    <div class="p-3 bg-purple-100 text-purple-600 rounded-lg mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Input Manual</h3>
                    <p class="mt-2 text-gray-500 flex-grow">Tandai siswa yang izin atau sakit tanpa perlu pemindaian barcode dari form ini.</p>
                    <span class="mt-4 inline-flex items-center text-sm font-semibold text-purple-600">Fitur Segera Hadir &rarr;</span>
                </div>
            </div>
        </div>
    </main>
</div>

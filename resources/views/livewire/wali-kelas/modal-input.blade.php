<!-- Modal Input Absen Manual -->
<div x-show="showInputModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <!-- Backdrop -->
        <div x-show="showInputModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" aria-hidden="true" @click="showInputModal = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Panel -->
        <div x-show="showInputModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-10 inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full border border-white/20">
            
            <div class="bg-gradient-to-r from-indigo-600 to-violet-500 px-8 py-5 flex items-center justify-between">
                <h3 class="text-xl font-extrabold text-white flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl backdrop-blur-sm flex items-center justify-center shadow-inner">
                        <svg class="w-6 h-6 text-indigo-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    </div>
                    Input Absensi Manual
                </h3>
                <button @click="showInputModal = false" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-indigo-100 hover:text-white hover:bg-white/20 transition-all focus:outline-none">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="px-6 py-6 max-h-[60vh] overflow-y-auto">
                <!-- Date Picker -->
                <div class="mb-6 flex items-center gap-4 bg-indigo-50 p-4 rounded-xl border border-indigo-100">
                    <label class="font-bold text-indigo-900 whitespace-nowrap">Pilih Tanggal:</label>
                    <input type="date" wire:model.live="inputDate" class="block w-full max-w-xs pl-3 pr-10 py-2 text-indigo-900 border-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:text-sm rounded-lg shadow-sm font-semibold">
                </div>

                @if(count($inputStudents) > 0)
                    <div class="overflow-hidden rounded-xl border border-slate-200 shadow-sm">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    <th class="py-3 px-4 w-1/3">Nama Siswa</th>
                                    <th class="py-3 px-4 text-center">Status Kehadiran</th>
                                    <th class="py-3 px-4 w-1/4">Telat (Menit)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @foreach($inputStudents as $id => $data)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="py-3 px-4 font-bold text-slate-800">{{ $data['name'] }}</td>
                                        <td class="py-3 px-4">
                                            <select wire:model.live="inputStudents.{{ $id }}.status" class="block w-full pl-3 pr-8 py-1.5 text-sm font-bold rounded-lg border-slate-200 focus:ring-2 focus:ring-indigo-500 cursor-pointer shadow-sm
                                                {{ $data['status'] === 'hadir' ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : '' }}
                                                {{ $data['status'] === 'telat' ? 'text-amber-700 bg-amber-50 border-amber-200' : '' }}
                                                {{ $data['status'] === 'izin' ? 'text-blue-700 bg-blue-50 border-blue-200' : '' }}
                                                {{ $data['status'] === 'sakit' ? 'text-indigo-700 bg-indigo-50 border-indigo-200' : '' }}
                                                {{ $data['status'] === 'alpa' ? 'text-red-700 bg-red-50 border-red-200' : '' }}
                                            ">
                                                <option value="">-- Pilih --</option>
                                                <option value="hadir">Hadir</option>
                                                <option value="telat">Terlambat</option>
                                                <option value="izin">Izin</option>
                                                <option value="sakit">Sakit</option>
                                                <option value="alpa">Alpa</option>
                                            </select>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($data['status'] === 'telat')
                                                <div class="relative">
                                                    <input type="number" wire:model="inputStudents.{{ $id }}.late_minutes" min="1" placeholder="0" class="block w-full pl-3 pr-8 py-1.5 text-sm font-bold text-amber-700 bg-amber-50 border-amber-200 rounded-lg focus:ring-2 focus:ring-amber-500 shadow-sm">
                                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                        <span class="text-amber-500 text-xs font-bold">mnt</span>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-slate-300 text-sm font-medium italic">Tidak relevan</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-10 bg-slate-50 rounded-xl border border-slate-200">
                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        <p class="mt-2 text-slate-500 font-semibold">Tidak ada data siswa untuk diinput.</p>
                    </div>
                @endif
            </div>

            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex-1 w-full text-left">
                    @if($isInputDateHoliday)
                        <span class="inline-flex items-center text-sm font-bold text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-1.5 w-full sm:w-auto">
                            <svg class="w-5 h-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Input ditolak: Tanggal ini adalah hari libur
                        </span>
                    @endif
                </div>
                <div class="flex gap-3 w-full sm:w-auto justify-end mt-3 sm:mt-0">
                    <button type="button" @click="showInputModal = false" class="inline-flex justify-center px-4 py-2 border border-slate-300 shadow-sm text-sm font-bold rounded-xl text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Batal
                    </button>
                    <button type="button" wire:click="saveManualInput" {{ $isInputDateHoliday ? 'disabled' : '' }} class="inline-flex justify-center items-center px-6 py-2 border border-transparent shadow-sm text-sm font-bold rounded-xl text-white {{ $isInputDateHoliday ? 'bg-slate-400 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        <svg wire:loading wire:target="saveManualInput" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Simpan Absensi
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

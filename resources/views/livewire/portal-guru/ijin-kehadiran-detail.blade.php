<div>
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('portal-guru.ijin') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-slate-900 hover:bg-slate-50 shadow-sm transition-all">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Detail Pengajuan</h1>
            <p class="text-slate-500 text-sm mt-1">Tinjau informasi dan riwayat pengajuan ijin/sakit siswa.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Info Utama -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center text-brand-primary font-bold text-lg">
                            {{ substr($request->student->name, 0, 1) }}
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">{{ $request->student->name }}</h2>
                            <p class="text-sm text-slate-500">NISN: {{ $request->student->nisn }} • Kelas {{ $request->student->enrollmentAktif->kelas->name ?? '-' }}</p>
                        </div>
                    </div>
                    <div>
                        @if($request->status === 'pending')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                <span class="w-2 h-2 rounded-full bg-amber-500 mr-2 animate-pulse"></span>
                                Pending
                            </span>
                        @elseif($request->status === 'approved')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-emerald-100 text-emerald-800">
                                Disetujui
                            </span>
                        @elseif($request->status === 'rejected')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-rose-100 text-rose-800">
                                Ditolak
                            </span>
                        @endif
                    </div>
                </div>

                <hr class="border-slate-100 mb-6">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Tipe Pengajuan</div>
                        <div class="font-bold text-slate-900 uppercase">{{ $request->type }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Durasi</div>
                        <div class="font-medium text-slate-900">{{ $request->duration_days }} Hari</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Tanggal Mulai</div>
                        <div class="font-medium text-slate-900">{{ $request->start_date->translatedFormat('l, d F Y') }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Tanggal Selesai</div>
                        <div class="font-medium text-slate-900">{{ $request->end_date->translatedFormat('l, d F Y') }}</div>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Alasan</div>
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 text-slate-700 text-sm whitespace-pre-wrap">{{ $request->reason }}</div>
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Lampiran Bukti</div>
                    @if(count($request->attachments) > 0)
                        <div class="flex flex-col sm:flex-row flex-wrap gap-3">
                            @foreach($request->attachments as $index => $path)
                            <div class="inline-flex items-center gap-3 p-3 bg-indigo-50/50 rounded-xl border border-indigo-100 min-w-[250px]">
                                <div class="p-2 bg-white rounded-lg shadow-sm text-brand-primary flex-shrink-0">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                </div>
                                <div class="truncate">
                                    <a href="{{ asset('storage/' . $path) }}" target="_blank" class="text-sm font-semibold text-brand-primary hover:underline block truncate">Lihat Lampiran {{ count($request->attachments) > 1 ? ($index + 1) : '' }}</a>
                                    <span class="text-xs text-slate-500 block truncate">Buka di tab baru</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-500 italic">Tidak ada lampiran.</p>
                    @endif
                </div>
            </div>

            <!-- Panel Aksi -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Aksi</h3>
                
                @if($request->status === 'pending')
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button wire:click="approve" onclick="confirm('Apakah Anda yakin ingin MENYETUJUI pengajuan ini? Data presensi akan otomatis digenerate.') || event.stopImmediatePropagation()" class="flex-1 px-4 py-2.5 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 transition-colors shadow-sm flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Setujui (Approve)
                        </button>
                        <button wire:click="$set('showRejectForm', true)" class="flex-1 px-4 py-2.5 bg-rose-600 text-white font-semibold rounded-xl hover:bg-rose-700 transition-colors shadow-sm flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Tolak (Reject)
                        </button>
                    </div>

                    @if($showRejectForm)
                        <div class="mt-4 p-4 bg-rose-50 border border-rose-100 rounded-xl">
                            <label class="block text-sm font-semibold text-rose-900 mb-2">Alasan Penolakan (Wajib)</label>
                            <textarea wire:model="reason" rows="3" class="w-full rounded-lg border-rose-200 focus:ring-rose-500 focus:border-rose-500 text-sm mb-3" placeholder="Berikan alasan mengapa pengajuan ditolak..."></textarea>
                            @error('reason') <span class="text-rose-500 text-xs mt-1 block mb-3">{{ $message }}</span> @enderror
                            <div class="flex gap-2 justify-end">
                                <button wire:click="$set('showRejectForm', false)" class="px-3 py-1.5 text-sm text-slate-600 font-medium hover:bg-slate-200 rounded-lg">Batal</button>
                                <button wire:click="reject" class="px-4 py-1.5 text-sm bg-rose-600 text-white font-bold rounded-lg hover:bg-rose-700">Submit Penolakan</button>
                            </div>
                        </div>
                    @endif

                @else
                    <div class="p-4 bg-slate-50 border border-slate-100 rounded-xl mb-4">
                        <p class="text-sm text-slate-600">Pengajuan ini telah <strong class="uppercase">{{ $request->status }}</strong> oleh <strong>{{ $request->approvedBy->name ?? 'Sistem' }}</strong> pada {{ $request->approved_at?->format('d/m/Y H:i') ?? '-' }}.</p>
                    </div>

                    <button wire:click="$set('showResetForm', true)" class="px-4 py-2 bg-amber-500 text-white font-semibold rounded-xl hover:bg-amber-600 transition-colors shadow-sm flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Kembalikan ke Pending
                    </button>

                    @if($showResetForm)
                        <div class="mt-4 p-4 bg-amber-50 border border-amber-100 rounded-xl">
                            <label class="block text-sm font-semibold text-amber-900 mb-2">Alasan Pengembalian Status (Wajib)</label>
                            <textarea wire:model="reason" rows="3" class="w-full rounded-lg border-amber-200 focus:ring-amber-500 focus:border-amber-500 text-sm mb-3" placeholder="Berikan alasan mengapa status dikembalikan..."></textarea>
                            @error('reason') <span class="text-rose-500 text-xs mt-1 block mb-3">{{ $message }}</span> @enderror
                            <div class="flex gap-2 justify-end">
                                <button wire:click="$set('showResetForm', false)" class="px-3 py-1.5 text-sm text-slate-600 font-medium hover:bg-slate-200 rounded-lg">Batal</button>
                                <button wire:click="resetToPending" onclick="confirm('Yakin kembalikan status ke Pending? Data presensi yang sudah digenerate akan dihapus kembali.') || event.stopImmediatePropagation()" class="px-4 py-1.5 text-sm bg-amber-600 text-white font-bold rounded-lg hover:bg-amber-700">Submit Reset</button>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Kolom Riwayat Log -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6 sticky top-6">
                <h3 class="text-lg font-bold text-slate-900 mb-6">Riwayat Log</h3>
                
                @if($request->logs->count() > 0)
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @foreach($request->logs as $index => $log)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-slate-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white
                                                @if($log->action === 'created') bg-blue-500 
                                                @elseif($log->action === 'approved') bg-emerald-500 
                                                @elseif($log->action === 'rejected') bg-rose-500 
                                                @elseif($log->action === 'updated') bg-amber-500 
                                                @else bg-slate-500 @endif">
                                                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    @if($log->action === 'created')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                    @elseif($log->action === 'approved')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    @elseif($log->action === 'rejected')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    @elseif($log->action === 'updated')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    @endif
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm font-semibold text-slate-900">{{ ucfirst($log->action) }}</p>
                                                <p class="text-xs text-slate-500 mt-1">Oleh: {{ $log->user?->name ?? 'Sistem' }}</p>
                                                @if($log->reason)
                                                    <p class="text-sm text-slate-600 bg-slate-50 border border-slate-100 p-2 rounded-lg mt-2 italic">"{{ $log->reason }}"</p>
                                                @endif
                                            </div>
                                            <div class="text-right text-xs whitespace-nowrap text-slate-500">
                                                <time datetime="{{ $log->created_at->toIso8601String() }}">{{ $log->created_at->diffForHumans() }}</time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <p class="text-slate-400 italic text-sm">Belum ada riwayat.</p>
                @endif
            </div>
        </div>
    </div>
</div>

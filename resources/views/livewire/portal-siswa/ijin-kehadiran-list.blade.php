<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Pengajuan Ijin / Sakit</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola dan pantau status pengajuan ijin/sakit Anda.</p>
        </div>
        <a href="{{ route('portal-siswa.ijin.form') }}" class="inline-flex items-center gap-2 bg-brand-primary text-white px-4 py-2 rounded-xl text-sm font-semibold shadow-md shadow-brand-primary/20 hover:bg-brand-primary/90 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Pengajuan
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider font-semibold border-b border-slate-200/60">
                    <tr>
                        <th class="px-6 py-4">Tipe & Tanggal</th>
                        <th class="px-6 py-4">Durasi</th>
                        <th class="px-6 py-4">Alasan</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($requests as $request)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900 uppercase">{{ $request->type }}</div>
                            <div class="text-xs mt-1 text-slate-500">
                                {{ $request->start_date->format('d/m/Y') }} 
                                @if($request->start_date->ne($request->end_date))
                                    - {{ $request->end_date->format('d/m/Y') }}
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            {{ $request->duration_days }} Hari
                        </td>
                        <td class="px-6 py-4 max-w-xs truncate" title="{{ $request->reason }}">
                            {{ $request->reason }}
                        </td>
                        <td class="px-6 py-4">
                            @if($request->status === 'pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                    Pending
                                </span>
                            @elseif($request->status === 'approved')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                    Disetujui
                                </span>
                            @elseif($request->status === 'rejected')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                    Ditolak
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right flex items-center justify-end gap-2">
                            <button wire:click="toggleLog('{{ $request->id }}')" class="text-slate-400 hover:text-indigo-600 p-2" title="Lihat Riwayat">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </button>

                            @if($request->status === 'pending')
                                <a href="{{ route('portal-siswa.ijin.form', $request->id) }}" class="text-slate-400 hover:text-amber-600 p-2" title="Edit">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                                <button onclick="confirm('Apakah Anda yakin ingin membatalkan pengajuan ini?') || event.stopImmediatePropagation()" wire:click="deleteRequest('{{ $request->id }}')" class="text-slate-400 hover:text-rose-600 p-2" title="Batalkan">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            @endif

                            @if($request->status === 'approved')
                                <button disabled class="text-slate-300 p-2 cursor-not-allowed" title="Cetak Bukti Ijin (Coming Soon)">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @if($showLogId === $request->id)
                    <tr class="bg-slate-50 border-t-0">
                        <td colspan="5" class="px-6 py-4">
                            <div class="bg-white rounded-xl p-4 border border-slate-200/60 shadow-inner">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Riwayat Log</h4>
                                @if($request->logs->count() > 0)
                                    <ul class="space-y-3 relative before:absolute before:inset-y-0 before:left-[11px] before:w-0.5 before:bg-slate-200">
                                        @foreach($request->logs as $log)
                                        <li class="relative pl-8 text-sm">
                                            <div class="absolute left-0 top-1.5 w-6 h-6 bg-slate-100 rounded-full border-2 border-white flex items-center justify-center z-10 shadow-sm">
                                                <div class="w-2 h-2 rounded-full @if($log->action === 'approved') bg-emerald-500 @elseif($log->action === 'rejected') bg-rose-500 @elseif($log->action === 'updated') bg-amber-500 @else bg-brand-primary @endif"></div>
                                            </div>
                                            <div class="text-slate-900 font-semibold">{{ ucfirst($log->action) }}</div>
                                            <div class="text-xs text-slate-500">{{ $log->created_at->format('d/m/Y H:i') }} - oleh {{ $log->user?->name ?? 'Sistem' }}</div>
                                            @if($log->reason)
                                                <div class="mt-1 bg-slate-50 rounded-lg p-2 text-slate-600 text-xs italic border border-slate-100">
                                                    "{{ $log->reason }}"
                                                </div>
                                            @endif
                                        </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-slate-400 italic text-xs">Belum ada riwayat.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endif
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                <p>Belum ada riwayat pengajuan ijin/sakit.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
        <div class="px-6 py-4 border-t border-slate-200/60 bg-slate-50/50">
            {{ $requests->links() }}
        </div>
        @endif
    </div>
</div>

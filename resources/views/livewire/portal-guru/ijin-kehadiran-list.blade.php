<div>
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Persetujuan Ijin Siswa</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola permohonan ijin dan sakit dari siswa di kelas Anda.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
        <div class="p-4 border-b border-slate-200/60 bg-slate-50/50 flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="w-full md:w-1/3">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama siswa atau NISN..." class="w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-sm">
            </div>
            <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                <select wire:model.live="filterClass" class="rounded-xl border-slate-200 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-sm">
                    <option value="">Semua Kelas</option>
                    @foreach($accessibleClasses as $kelas)
                        <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterStatus" class="rounded-xl border-slate-200 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-sm">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
                <select wire:model.live="filterMonth" class="rounded-xl border-slate-200 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-sm">
                    <option value="">Semua Waktu</option>
                    @foreach($availableMonths as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider font-semibold border-b border-slate-200/60">
                    <tr>
                        <th class="px-6 py-4">Siswa</th>
                        <th class="px-6 py-4">Pengajuan</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($requests as $request)
                    <tr class="hover:bg-slate-50/50 transition-colors @if($request->status === 'pending') bg-amber-50/20 @endif">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900">{{ $request->student->name }}</div>
                            <div class="text-xs mt-1 text-slate-500">{{ $request->student->nisn }} • Kelas {{ $request->student->enrollmentAktif->kelas->name ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-700 uppercase">{{ $request->type }} ({{ $request->duration_days }} Hari)</div>
                            <div class="text-xs text-slate-500 mt-1 max-w-xs truncate" title="{{ $request->reason }}">{{ $request->reason }}</div>
                        </td>
                        <td class="px-6 py-4 text-xs">
                            {{ $request->start_date->format('d/m/Y') }} 
                            @if($request->start_date->ne($request->end_date))
                                - {{ $request->end_date->format('d/m/Y') }}
                            @endif
                            <div class="text-[10px] text-slate-400 mt-1" title="Tanggal Diajukan">{{ $request->created_at->format('d/m/Y H:i') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($request->status === 'pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span>
                                    Menunggu Review
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
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('portal-guru.ijin.detail', $request->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-600 hover:text-brand-primary hover:border-brand-primary hover:bg-indigo-50 transition-all shadow-sm">
                                Detail
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                <p>Belum ada data pengajuan ijin/sakit.</p>
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

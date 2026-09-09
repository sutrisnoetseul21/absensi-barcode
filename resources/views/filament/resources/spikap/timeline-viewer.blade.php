@php
    $logs = $record?->logStatus()->with('changedBy.roles', 'changedBy.teacher')->oldest()->get() ?? collect();
@endphp

@if($logs->isEmpty())
    <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-6 text-center">
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Belum ada riwayat perubahan status penanganan.</p>
    </div>
@else
    <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-gray-200 dark:before:bg-gray-800">
        @foreach($logs as $log)
            @php
                $statusColors = [
                    'diterima' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                    'dalam_investigasi' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                    'selesai' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
                ];
                $colorClass = $statusColors[$log->status_baru] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
                
                $spikapAppName = \App\Models\SpikapNotifSetting::instance()->getNamaAplikasi();
                $actorName = "Sistem Otomatis ({$spikapAppName})";
                $actorRole = 'Sistem';
                if ($log->changedBy) {
                    $actorName = $log->changedBy->teacher?->nama_guru ?? $log->changedBy->name;
                    $roles = $log->changedBy->roles->pluck('name')->toArray();
                    if (in_array('super_admin', $roles) || in_array('spikap_admin', $roles)) {
                        $actorRole = 'Admin';
                    } elseif (in_array('spikap_kepala_sekolah', $roles)) {
                        $actorRole = 'Kepala Sekolah';
                    } elseif (in_array('spikap_guru_bk', $roles)) {
                        $actorRole = 'Guru BK';
                    } elseif (in_array('spikap_wali_kelas', $roles) || in_array('wali_kelas', $roles)) {
                        $actorRole = 'Wali Kelas';
                    } else {
                        $actorRole = 'Petugas';
                    }
                }
            @endphp
            <div class="relative flex items-start gap-4">
                <div class="absolute -left-6 mt-1 flex h-5 w-5 items-center justify-center rounded-full bg-white dark:bg-gray-950 ring-4 ring-white dark:ring-gray-950">
                    <div class="h-2.5 w-2.5 rounded-full {{ $log->status_baru === 'selesai' ? 'bg-emerald-500' : ($log->status_baru === 'dalam_investigasi' ? 'bg-blue-500' : 'bg-amber-500') }}"></div>
                </div>

                <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2 pb-2 border-b border-gray-100 dark:border-gray-800/80">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $colorClass }}">
                                {{ ucfirst(str_replace('_', ' ', $log->status_baru)) }}
                            </span>
                            @if($log->status_lama)
                                <span class="text-xs text-gray-400 dark:text-gray-500">
                                    (sebelumnya: {{ ucfirst(str_replace('_', ' ', $log->status_lama)) }})
                                </span>
                            @endif
                        </div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ $log->created_at?->format('d M Y, H:i') }}
                        </span>
                    </div>

                    <div class="mt-2 text-xs text-gray-600 dark:text-gray-400 flex items-center gap-1.5">
                        <span class="font-medium text-gray-900 dark:text-white">{{ $actorName }}</span>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            {{ $actorRole }}
                        </span>
                    </div>

                    @if($log->catatan)
                        <div class="mt-2 text-sm text-gray-800 dark:text-gray-200 bg-gray-50 dark:bg-gray-800/60 rounded-lg p-3 border-l-2 border-primary-500 leading-relaxed whitespace-pre-line">
                            {{ $log->catatan }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif

<div class="mb-4">
    <div class="p-4 rounded-xl bg-danger-50 dark:bg-danger-500/10 border border-danger-200 dark:border-danger-500/20">
        <div class="flex gap-3">
            <x-filament::icon
                icon="heroicon-o-exclamation-triangle"
                class="w-6 h-6 text-danger-600 dark:text-danger-400 shrink-0 mt-0.5"
            />
            
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-danger-800 dark:text-danger-300">
                    Koneksi WhatsApp Gateway Bermasalah
                </h3>
                
                <div class="mt-1 text-sm text-danger-700 dark:text-danger-400">
                    <p>{{ $connectionError }}</p>
                    <p class="mt-1 font-medium">Pesan baru akan berstatus <strong>Pending</strong> atau <strong>Failed</strong> hingga koneksi diperbaiki.</p>
                </div>
                
                <div class="mt-3">
                    <a href="{{ route('filament.admin.pages.pengaturan-presensi') }}" class="text-sm font-medium text-danger-600 dark:text-danger-400 hover:text-danger-500 underline underline-offset-2">
                        Buka Pengaturan Presensi &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

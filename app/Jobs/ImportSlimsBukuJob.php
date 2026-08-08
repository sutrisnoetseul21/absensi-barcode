<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SlimsBukuImport;
use Filament\Notifications\Notification;
use App\Models\User;

class ImportSlimsBukuJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 Jam timeout maksimal
    public $tries = 1;

    protected string $filePath;
    protected string $userId;

    public function __construct(string $filePath, string $userId)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        \App\Models\EksemplarBuku::$isBulkImporting = true;

        try {
            // Import kedua sheet secara sinkron di background
            Excel::import(new SlimsBukuImport(), $this->filePath);

            // Setelah seluruh eksemplar selesai diimport, rekapitulasi massal jumlah fisik
            \Illuminate\Support\Facades\DB::statement('
                UPDATE inventaris_bukus iv 
                SET jumlah_eksemplar = (
                    SELECT COUNT(*) 
                    FROM eksemplar_bukus eb 
                    WHERE eb.inventaris_buku_id = iv.id
                )
            ');

            // Format ulang nomor inventaris yang tadinya menggunakan fallback SLIMS-
            $inventarisList = \Illuminate\Support\Facades\DB::table('inventaris_bukus')
                ->where('no_inventaris', 'like', 'SLIMS-%')
                ->get();

            foreach ($inventarisList as $inv) {
                $agg = \Illuminate\Support\Facades\DB::table('eksemplar_bukus')
                    ->where('inventaris_buku_id', $inv->id)
                    ->select(\Illuminate\Support\Facades\DB::raw('MIN(kode_eksemplar) as min_kode'), \Illuminate\Support\Facades\DB::raw('MAX(kode_eksemplar) as max_kode'))
                    ->first();
                    
                if ($agg && $agg->min_kode) {
                    $kodeAsal = match ($inv->asal) {
                        'Pembelian' => 'P',
                        'Hibah' => 'H',
                        'Tukar' => 'T',
                        'Terbitan Sendiri' => 'TS',
                        default => 'P'
                    };
                    $tahun = date('Y', strtotime($inv->tanggal_masuk));
                    
                    $noInventaris = "{$agg->min_kode}/{$kodeAsal}/{$tahun} - {$agg->max_kode}/{$kodeAsal}/{$tahun}";
                    if ($agg->min_kode === $agg->max_kode) {
                        $noInventaris = "{$agg->min_kode}/{$kodeAsal}/{$tahun}";
                    }
                    
                    \Illuminate\Support\Facades\DB::table('inventaris_bukus')
                        ->where('id', $inv->id)
                        ->update(['no_inventaris' => $noInventaris]);
                }
            }

            // Trigger auto-sync counter barcode setelah semua data masuk
            \App\Services\BarcodeService::autoSyncBarcodeNumber();

            // Set setup completed flag
            $settings = \App\Models\PengaturanSekolah::current();
            if ($settings) {
                $settings->update(['is_barcode_setup_completed' => true]);
            }

            // Beri notifikasi ke user jika sukses
            $user = User::find($this->userId);
            if ($user) {
                Notification::make()
                    ->title('Import Buku SLiMS Berhasil')
                    ->body('Data Buku dan Eksemplar berhasil diimport sepenuhnya ke ERP. Setup Barcode telah diselesaikan otomatis.')
                    ->success()
                    ->sendToDatabase($user);
            }
        } catch (\Exception $e) {
            $user = User::find($this->userId);
            if ($user) {
                Notification::make()
                    ->title('Import Buku SLiMS Gagal')
                    ->body('Terjadi kesalahan: ' . $e->getMessage())
                    ->danger()
                    ->sendToDatabase($user);
            }
            throw $e;
        } finally {
            \App\Models\EksemplarBuku::$isBulkImporting = false;
            // Selalu hapus file temporary
            @unlink($this->filePath);
        }
    }
}

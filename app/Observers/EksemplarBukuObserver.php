<?php

namespace App\Observers;

use App\Models\EksemplarBuku;
use App\Services\BarcodeService;

class EksemplarBukuObserver
{
    /**
     * Handle the EksemplarBuku "created" event.
     */
    public function created(EksemplarBuku $eksemplarBuku): void
    {
        // Skip self-healing jika pembuatan kode datang dari sistem generate resmi
        // karena sistem resmi sudah menggunakan counter global + lockForUpdate yang aman.
        if (EksemplarBuku::$isGenerating || EksemplarBuku::$isBulkImporting) {
            return;
        }

        // Jika buku dibuat manual (misal lewat import manual atau tinker yang tidak tercover job),
        // kita jalankan self-healing untuk memastikan counter setidaknya mengejar nomor urut tertinggi.
        BarcodeService::autoSyncBarcodeNumber();
    }

    /**
     * Handle the EksemplarBuku "updated" event.
     */
    public function updated(EksemplarBuku $eksemplarBuku): void
    {
        if (EksemplarBuku::$isGenerating || EksemplarBuku::$isBulkImporting) {
            return;
        }

        if ($eksemplarBuku->wasChanged('kode_eksemplar')) {
            BarcodeService::autoSyncBarcodeNumber();
        }
    }
}

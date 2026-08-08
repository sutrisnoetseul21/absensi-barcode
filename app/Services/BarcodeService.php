<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\PengaturanSekolah;

class BarcodeService
{
    /**
     * Get the real maximum barcode number from the eksemplar_bukus table for a specific prefix.
     * This uses a direct SQL query for optimal performance.
     *
     * @param string $prefix
     * @return int
     */
    public static function getRealMaxBarcodeNumber(): int
    {
        // Attempt to use fast SQL native regex extraction (MySQL 8.0+ / MariaDB 10.0.5+)
        try {
            $maxNum = DB::table('eksemplar_bukus')
                ->max(DB::raw("CAST(REGEXP_SUBSTR(kode_eksemplar, '[0-9]+$') AS UNSIGNED)"));
            
            return (int) $maxNum;
        } catch (\Illuminate\Database\QueryException $e) {
            // Fallback for older database versions (e.g. MySQL 5.7) that don't support REGEXP_SUBSTR
            $maxNum = 0;
            
            // Use chunking to prevent memory exhaustion when scanning 100k+ rows
            DB::table('eksemplar_bukus')->select('kode_eksemplar')->chunk(5000, function ($rows) use (&$maxNum) {
                foreach ($rows as $row) {
                    if (preg_match('/[0-9]+$/', $row->kode_eksemplar, $matches)) {
                        $num = (int) $matches[0];
                        if ($num > $maxNum) {
                            $maxNum = $num;
                        }
                    }
                }
            });
            
            return $maxNum;
        }
    }

    /**
     * Automatically sync the last_barcode_number in school_settings based on the real max.
     * This is useful after mass imports or when resetting counters.
     * The counter will ONLY increase, never decrease, to prevent clashing with manual entries.
     *
     * @return void
     */
    public static function autoSyncBarcodeNumber(): void
    {
        DB::transaction(function () {
            // Lock the settings row for update to prevent race conditions during sync
            $settings = PengaturanSekolah::lockForUpdate()->first();
            
            if (!$settings) {
                return;
            }

            $realMax = self::getRealMaxBarcodeNumber();
            
            // Counter should NEVER go down. If admin manually set it to 2000, 
            // and the real max is 1500, it stays at 2000.
            $newMax = max((int) $settings->last_barcode_number, $realMax);
            
            if ($newMax > $settings->last_barcode_number) {
                $settings->update([
                    'last_barcode_number' => $newMax
                ]);
                
                // Clear cache so the updated value reflects across the application
                \Illuminate\Support\Facades\Cache::forget('public_pengaturan_sekolah');
            }
        });
    }
}

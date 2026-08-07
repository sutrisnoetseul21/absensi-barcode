<?php

namespace App\Imports\Sheets;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class SlimsEksemplarSheetImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public function headingRow(): int
    {
        return 4;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function collection(Collection $rows)
    {
        $eksemplarData = [];
        
        // Kumpulkan semua biblio_id yang ada di chunk ini
        $biblioIds = $rows->pluck('biblio_id')->filter()->unique()->toArray();
        
        // Ambil mapping biblio_id -> buku_id (id uuid dari tabel bukus)
        $bukuMap = DB::table('bukus')
            ->whereIn('slims_biblio_id', $biblioIds)
            ->pluck('id', 'slims_biblio_id')
            ->toArray();

        // 1. PROSES INVENTARIS BUKU (Satu per buku_id)
        $inventarisMap = []; // buku_id => inventaris_buku_id
        $groupedRows = $rows->groupBy('biblio_id');
        
        foreach ($groupedRows as $biblioId => $items) {
            $bukuId = $bukuMap[$biblioId] ?? null;
            if (!$bukuId) continue;
            
            // Cek apakah inventaris sudah ada untuk buku ini
            $inventaris = DB::table('inventaris_bukus')->where('buku_id', $bukuId)->first();
            
            if (!$inventaris) {
                // Ambil data dari baris pertama eksemplar untuk buku ini
                $firstItem = $items->first();
                $noInventaris = trim((string)($firstItem['no_inventaris'] ?? ''));
                if (empty($noInventaris)) {
                    $noInventaris = 'SLIMS-' . $biblioId;
                }
                
                // Format tanggal masuk
                $tanggalMasuk = !empty($firstItem['tanggal_masuk']) 
                    ? date('Y-m-d', strtotime($firstItem['tanggal_masuk'])) 
                    : now()->format('Y-m-d');
                    
                $harga = (int)($firstItem['harga'] ?? 0);
                
                $asalRaw = strtolower(trim($firstItem['asal'] ?? 'pembelian'));
                $asal = 'Pembelian';
                if (str_contains($asalRaw, 'hibah')) $asal = 'Hibah';
                elseif (str_contains($asalRaw, 'tukar')) $asal = 'Tukar';
                elseif (str_contains($asalRaw, 'sendiri')) $asal = 'Terbitan Sendiri';
                
                $inventarisId = Str::uuid()->toString();
                DB::table('inventaris_bukus')->insert([
                    'id' => $inventarisId,
                    'buku_id' => $bukuId,
                    'no_inventaris' => $noInventaris,
                    'tanggal_masuk' => $tanggalMasuk,
                    'asal' => $asal,
                    'harga' => $harga,
                    'jumlah_eksemplar' => 0, // Akan dihitung ulang (count) di akhir job
                    'status' => 'aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $inventarisMap[$bukuId] = $inventarisId;
            } else {
                $inventarisMap[$bukuId] = $inventaris->id;
            }
        }

        // 2. PROSES EKSEMPLAR BUKU
        foreach ($rows as $row) {
            $biblioId = $row['biblio_id'] ?? null;
            $kodeEksemplar = trim((string)($row['kode_eksemplar'] ?? ''));
            
            if (!$biblioId || empty($kodeEksemplar)) continue;

            $bukuId = $bukuMap[$biblioId] ?? null;
            if (!$bukuId) continue; // Skip jika buku induk tidak ditemukan

            $inventarisId = $inventarisMap[$bukuId] ?? null;

            $status = strtolower(trim($row['status'] ?? 'tersedia'));
            if (!in_array($status, ['tersedia', 'dipinjam', 'rusak', 'hilang'])) {
                $status = 'tersedia';
            }

            $eksemplarData[] = [
                'id' => Str::uuid()->toString(),
                'buku_id' => $bukuId,
                'inventaris_buku_id' => $inventarisId, // Menyambungkan eksemplar ke inventaris
                'kode_eksemplar' => $kodeEksemplar,
                'status' => $status,
                'kondisi_fisik' => 'baik', // SLiMS tidak menyimpan kondisi spesifik, default baik
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Upsert manual menggunakan kode_eksemplar
        // Walaupun kode_eksemplar sudah UNIQUE, kita pakai looping batch untuk updateOrCreate manual (menghindari limitasi upsert MySQL tertentu)
        foreach ($eksemplarData as $eksemplar) {
            $existing = DB::table('eksemplar_bukus')->where('kode_eksemplar', $eksemplar['kode_eksemplar'])->first();
            
            if ($existing) {
                unset($eksemplar['id']);
                unset($eksemplar['created_at']);
                DB::table('eksemplar_bukus')->where('id', $existing->id)->update($eksemplar);
            } else {
                DB::table('eksemplar_bukus')->insert($eksemplar);
            }
        }
    }
}

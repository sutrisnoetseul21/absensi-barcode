<?php

namespace App\Imports\Sheets;

use App\Models\KategoriBuku;
use App\Models\KlasifikasiDdc;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class SlimsBukuSheetImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected array $kategoriCache = [];
    protected array $ddcCache = [];

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
        if (empty($this->kategoriCache)) {
            $this->kategoriCache = KategoriBuku::pluck('id', 'nama_kategori')->mapWithKeys(function ($item, $key) {
                return [strtolower(trim($key)) => $item];
            })->toArray();
        }

        if (empty($this->ddcCache)) {
            $this->ddcCache = KlasifikasiDdc::pluck('id', 'kode_ddc')->toArray();
        }

        // Cari default kategori (misal: "Non Fiksi" atau ambil yang pertama ada)
        $defaultKategoriId = $this->kategoriCache['non fiksi'] ?? ($this->kategoriCache['fiksi'] ?? reset($this->kategoriCache));

        $bukuData = [];
        
        foreach ($rows as $row) {
            $biblioId = $row['biblio_id'] ?? null;
            if (!$biblioId) continue;

            $jenisKoleksi = strtolower(trim($row['jenis_koleksi'] ?? ''));
            
            // Map jenis koleksi ke kategori_id
            $kategoriId = $defaultKategoriId; // Default
            if ($jenisKoleksi == 'reference' || $jenisKoleksi == 'ensiklopedia') {
                $kategoriId = $this->kategoriCache['referensi'] ?? $defaultKategoriId;
            } elseif ($jenisKoleksi == 'fiction') {
                $kategoriId = $this->kategoriCache['fiksi'] ?? $defaultKategoriId;
            } elseif ($jenisKoleksi == 'textbook') {
                $kategoriId = $this->kategoriCache['non fiksi'] ?? $defaultKategoriId;
            }

            $kodeDdc = trim((string)($row['klasifikasi_ddc'] ?? ''));
            $ddcId = $this->ddcCache[$kodeDdc] ?? null;

            $tahun = (int) ($row['tahun_terbit'] ?? 0);
            if ($tahun < 1000 || $tahun > date('Y') + 10) $tahun = null;

            $bukuData[] = [
                'id' => Str::uuid()->toString(),
                'slims_biblio_id' => $biblioId,
                'judul' => trim($row['judul'] ?? 'Tanpa Judul'),
                'isbn' => trim($row['isbn'] ?? ''),
                'penulis' => trim($row['penulis'] ?? ''),
                'penerbit' => trim($row['penerbit'] ?? ''),
                'tahun_terbit' => $tahun,
                'kategori_id' => $kategoriId,
                'klasifikasi_ddc_id' => $ddcId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Upsert manual karena slims_biblio_id belum di-set UNIQUE constraint di MySQL
        foreach ($bukuData as $buku) {
            $existing = DB::table('bukus')->where('slims_biblio_id', $buku['slims_biblio_id'])->first();
            
            if ($existing) {
                unset($buku['id']);
                unset($buku['created_at']);
                DB::table('bukus')->where('id', $existing->id)->update($buku);
            } else {
                DB::table('bukus')->insert($buku);
            }
        }
    }
}

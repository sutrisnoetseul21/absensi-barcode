<?php

namespace App\Imports;

use App\Support\DdcHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SlimsDdcImport implements ToCollection, WithHeadingRow
{
    public int $baru = 0;
    public int $update = 0;
    public int $skipped = 0;
    public int $errors = 0;
    public array $skippedRows = [];

    // Header ada di baris ke-4 (karena 3 baris pertama adalah judul/keterangan)
    public function headingRow(): int
    {
        return 4;
    }

    public function collection(Collection $rows)
    {
        $baris = 4; // Mulai dari baris ke-4 untuk pelaporan error
        
        foreach ($rows as $row) {
            $baris++;
            
            // Dapatkan key yang relevan dari baris yang diproses oleh WithHeadingRow
            // Maatwebsite Excel biasanya melowercase dan me-replace spasi dengan underscore
            $kodeDdc = $row['kode_ddc'] ?? $row['kode ddc'] ?? null;
            $namaKategori = $row['nama_kategori'] ?? $row['nama kategori'] ?? null;
            
            // Skip jika baris benar-benar kosong
            if (empty($kodeDdc) && empty($namaKategori)) {
                continue;
            }
            
            $kodeDdc = trim((string) $kodeDdc);
            
            if (empty($kodeDdc)) {
                $this->skipped++;
                $this->skippedRows[] = "Baris {$baris}: Kode DDC kosong";
                continue;
            }

            // Validasi format DDC (harus murni digit atau digit dengan 1 desimal point)
            if (!DdcHelper::isValidDdcCode($kodeDdc)) {
                $this->skipped++;
                $this->skippedRows[] = "Baris {$baris}: Format '{$kodeDdc}' tidak valid";
                continue;
            }

            try {
                // Tentukan nama kategori: gunakan dari file, jika kosong generate ulang
                $kategori = !empty($namaKategori) 
                    ? trim($namaKategori) 
                    : DdcHelper::getNamaKategori($kodeDdc);

                // Cek apakah kode sudah ada untuk menentukan status $baru atau $update
                $exists = DB::table('klasifikasi_ddcs')->where('kode_ddc', $kodeDdc)->exists();

                if ($exists) {
                    $this->update++;
                    DB::table('klasifikasi_ddcs')
                        ->where('kode_ddc', $kodeDdc)
                        ->update([
                            'kategori' => $kategori,
                            'updated_at' => now(),
                        ]);
                } else {
                    $this->baru++;
                    DB::table('klasifikasi_ddcs')->insert([
                        'id' => Str::uuid()->toString(),
                        'kode_ddc' => $kodeDdc,
                        'kategori' => $kategori,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                $this->errors++;
            }
        }
    }
}

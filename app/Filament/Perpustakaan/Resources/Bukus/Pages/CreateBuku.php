<?php

namespace App\Filament\Perpustakaan\Resources\Bukus\Pages;

use App\Filament\Perpustakaan\Resources\Bukus\BukuResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBuku extends CreateRecord
{
    protected static string $resource = BukuResource::class;

    protected int $jumlahEksemplar = 1;
    protected string $prefixKode = 'UMM';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->jumlahEksemplar = (int) ($data['jumlah_eksemplar'] ?? 1);
        $this->prefixKode = $data['prefix_kode'] ?? 'UMM';

        unset($data['jumlah_eksemplar']);
        unset($data['prefix_kode']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->getRecord();
        $jumlah = $this->jumlahEksemplar;
        $prefix = $this->prefixKode;
        $asal = $this->data['asal_buku'] ?? 'Pembelian';
        $hargaRaw = $this->data['harga_buku'] ?? null;
        $harga = ($hargaRaw === '' || $hargaRaw === null) ? null : (int) $hargaRaw;

        if ($jumlah > 0) {
            try {
                $generateResult = \App\Models\EksemplarBuku::generateKodeEksemplar($prefix, $jumlah);
                $codes = $generateResult['codes'];
                $startNum = $generateResult['start_num'];
                $endNum = $generateResult['end_num'];
                
                $tahun = now()->format('Y');
                $kodeAsal = match ($asal) {
                    'Pembelian' => 'P',
                    'Hibah' => 'H',
                    'Tukar' => 'T',
                    'Terbitan Sendiri' => 'TS',
                    default => 'P'
                };
                
                $noInventaris = "{$startNum}/{$kodeAsal}/{$tahun} - {$endNum}/{$kodeAsal}/{$tahun}";
                if ($startNum === $endNum) {
                    $noInventaris = "{$startNum}/{$kodeAsal}/{$tahun}";
                }

                $now = now();
                
                // Create InventarisBuku
                $inventaris = \App\Models\InventarisBuku::create([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'buku_id' => $record->id,
                    'no_inventaris' => $noInventaris,
                    'tanggal_masuk' => $now->toDateString(),
                    'asal' => $asal,
                    'harga' => $harga,
                    'jumlah_eksemplar' => $jumlah,
                    'status' => 'aktif'
                ]);

                $inserts = [];
                foreach ($codes as $code) {
                    $inserts[] = [
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'buku_id' => $record->id,
                        'inventaris_buku_id' => $inventaris->id,
                        'kode_eksemplar' => $code,
                        'status' => 'tersedia',
                        'kondisi_fisik' => 'baik',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (count($inserts) > 0) {
                    foreach (array_chunk($inserts, 500) as $chunk) {
                        \App\Models\EksemplarBuku::insert($chunk);
                    }
                    
                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Berhasil Generate')
                        ->body("Berhasil membuat {$jumlah} eksemplar & catatan inventaris.")
                        ->send();
                }
            } catch (\Exception $e) {
                \Filament\Notifications\Notification::make()
                    ->danger()
                    ->title('Gagal Generate Eksemplar Awal')
                    ->body($e->getMessage())
                    ->send();
            }
        }
    }
}

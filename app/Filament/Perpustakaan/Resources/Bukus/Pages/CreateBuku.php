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

        if ($jumlah > 0) {
            try {
                $codes = \App\Models\EksemplarBuku::generateKodeEksemplar($prefix, $jumlah);

                $now = now();
                $inserts = [];

                foreach ($codes as $code) {
                    $inserts[] = [
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'buku_id' => $record->id,
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
                        ->body("Berhasil membuat " . count($inserts) . " eksemplar buku awal.")
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

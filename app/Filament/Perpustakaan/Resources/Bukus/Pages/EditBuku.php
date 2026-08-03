<?php

namespace App\Filament\Perpustakaan\Resources\Bukus\Pages;

use App\Filament\Perpustakaan\Resources\Bukus\BukuResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditBuku extends EditRecord
{
    protected static string $resource = BukuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle sampul_buku
        $sampul = $data['sampul_buku'] ?? null;
        if (is_array($sampul)) {
            $sampul = count($sampul) > 0 ? array_values($sampul)[0] : null;
        }
        if (empty($sampul)) {
            $data['sampul_buku'] = $this->record->sampul_buku;
        } else {
            $data['sampul_buku'] = $sampul;
        }

        // Handle file_pdf
        $pdf = $data['file_pdf'] ?? null;
        if (is_array($pdf)) {
            $pdf = count($pdf) > 0 ? array_values($pdf)[0] : null;
        }
        if (empty($pdf)) {
            $data['file_pdf'] = $this->record->file_pdf;
        } else {
            $data['file_pdf'] = $pdf;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->record->fresh();

        // Fix permission sampul_buku
        if ($record->sampul_buku) {
            $path = Storage::disk('public')->path($record->sampul_buku);
            if (file_exists($path)) @chmod($path, 0644);
        }

        // Fix permission file_pdf
        if ($record->file_pdf) {
            $path = Storage::disk('public')->path($record->file_pdf);
            if (file_exists($path)) @chmod($path, 0644);
        }
    }
}

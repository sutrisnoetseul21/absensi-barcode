<?php

namespace App\Filament\Resources\HariLiburs\Pages;

use App\Filament\Resources\HariLiburs\HariLiburResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHariLibur extends CreateRecord
{
    protected static string $resource = HariLiburResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (request()->has('date')) {
            $data['start_date'] = request()->query('date');
        }
        return $data;
    }

    public function mount(): void
    {
        parent::mount();

        if (request()->has('date')) {
            $this->form->fill([
                'start_date' => request()->query('date'),
            ]);
        }
    }
}

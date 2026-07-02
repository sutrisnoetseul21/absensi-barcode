<?php

namespace App\Filament\Resources\Enrollment\Pages;

use App\Filament\Resources\Enrollment\EnrollmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEnrollments extends ListRecords
{
    protected static string $resource = EnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

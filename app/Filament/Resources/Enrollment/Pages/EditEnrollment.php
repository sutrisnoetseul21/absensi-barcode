<?php

namespace App\Filament\Resources\Enrollment\Pages;

use App\Filament\Resources\Enrollment\EnrollmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEnrollment extends EditRecord
{
    protected static string $resource = EnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make()
                ->before(function (\Filament\Actions\DeleteAction $action, \App\Models\EnrollmentSiswa $record) {
                    if ($record->absensis()->count() > 0) {
                        \Filament\Notifications\Notification::make()
                            ->warning()
                            ->title('Gagal menghapus!')
                            ->body('Pendaftaran siswa ini tidak dapat dihapus karena sudah memiliki data absensi.')
                            ->send();
                        $action->halt();
                    }
                }),
        ];
    }
}

<?php

namespace App\Filament\Akademik\Resources\Siswa\Pages;

use App\Filament\Akademik\Resources\Siswa\SiswaResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSiswa extends EditRecord
{
    protected static string $resource = SiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('cetakBiodata')
                ->label('Cetak Biodata')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->action(function (\App\Models\Siswa $record) {
                    $kepsek = \App\Models\Guru::whereHas('jabatans', function ($q) {
                        $q->where('nama_jabatan', 'like', '%Kepala Sekolah%')
                          ->where(function ($q2) {
                              $q2->whereNull('teacher_jabatan.tanggal_selesai')
                                 ->orWhere('teacher_jabatan.tanggal_selesai', '>=', now()->toDateString());
                          });
                    })->first();

                    $settings = \App\Models\PengaturanSekolah::first();
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.biodata-siswa', [
                        'siswa' => $record,
                        'namaKepsek' => $kepsek ? $kepsek->name : ($settings?->principal_name ?? config('school.kepala_sekolah_nama')),
                        'nipKepsek' => $kepsek ? $kepsek->nip : ($settings?->principal_nip ?? config('school.kepala_sekolah_nip')),
                        'namaKota' => $settings?->tempat_rapor ?? ($settings?->kota ?? config('school.kota')),
                        'tanggalRapor' => $settings?->tanggal_rapor ? $settings->tanggal_rapor->format('Y-m-d') : now()->format('Y-m-d'),
                    ]);
                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'Biodata-' . \Illuminate\Support\Str::slug($record->name) . '.pdf'
                    );
                }),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $userData = [];
        if (!empty($data['name'])) {
            $userData['name'] = $data['name'];
        }
        if (!empty($data['email'])) {
            $userData['email'] = $data['email'];
        }
        if (!empty($data['password'])) {
            $userData['password'] = $data['password'];
            $userData['must_change_password'] = false;
        }

        if ($this->record->user) {
            if (!empty($userData)) {
                $this->record->user->update($userData);
            }
        } else {
            // Jika siswa belum memiliki akun User, buatkan atau tautkan akun
            $email = $userData['email'] ?? ($data['email'] ?? ($this->record->nisn ? $this->record->nisn . '@' . config('school.email_domain') : 'siswa_' . $this->record->id . '@' . config('school.email_domain')));
            $password = $userData['password'] ?? ($this->record->nisn ?: 'password');

            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update(array_merge($userData, [
                    'name' => $data['name'] ?? $this->record->name,
                ]));
            } else {
                $user = User::create([
                    'name'                 => $data['name'] ?? $this->record->name,
                    'email'                => $email,
                    'password'             => $password,
                    'must_change_password' => false,
                ]);
                $user->assignRole('siswa');
            }

            $data['user_id'] = $user->id;
        }

        unset($data['email'], $data['password']);

        return $data;
    }
}

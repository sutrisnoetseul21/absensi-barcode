<?php

namespace App\Filament\Akademik\Resources\Guru\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class GuruForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('photo_path')
                    ->label('Foto Profil Guru')
                    ->image()
                    ->directory('guru-photos')
                    ->disk('public')
                    ->maxSize(2048)
                    ->avatar()
                    ->imageEditor()
                    ->circleCropper(),

                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->placeholder('Contoh: Dr. Budi Santoso, M.Pd.')
                    ->required()
                    ->maxLength(255),

                TextInput::make('nip')
                    ->label('NIP')
                    ->placeholder('Nomor Induk Pegawai (opsional)')
                    ->nullable()
                    ->maxLength(30)
                    ->unique(
                        table: 'teachers',
                        column: 'nip',
                        ignoreRecord: true
                    )
                    ->helperText('Jika diisi, NIP akan digunakan sebagai username login.'),

                TextInput::make('no_hp')
                    ->label('Nomor WhatsApp')
                    ->tel()
                    ->rule(function () {
                        return function (string $attribute, $value, \Closure $fail) {
                            $digits = preg_replace('/\D/', '', $value ?? '');
                            if ($digits) {
                                $normalized = $digits;
                                if (str_starts_with($normalized, '0')) {
                                    $normalized = '62' . substr($normalized, 1);
                                } elseif (!str_starts_with($normalized, '62')) {
                                    $normalized = '62' . $normalized;
                                }
                                if (!preg_match('/^628[0-9]{7,12}$/', $normalized)) {
                                    $fail('Format nomor HP tidak valid. Contoh: 081234567890');
                                }
                            }
                        };
                    })
                    ->helperText('Format bebas, otomatis dinormalisasi ke 62xxx.')
                    ->nullable(),

                TextInput::make('email')
                    ->label('Email Login')
                    ->email()
                    ->required(fn (string $operation): bool => $operation === 'edit')
                    ->maxLength(255)
                    ->helperText('Otomatis di-generate jika dikosongkan saat create.')
                    ->formatStateUsing(fn (?Model $record) => $record?->user?->email),

                TextInput::make('password')
                    ->label(fn (string $operation): string => $operation === 'edit' ? 'Password Baru' : 'Password Login')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn ($state) => filled($state))
                    ->helperText(fn (string $operation): string => $operation === 'edit' ? 'Kosongkan jika tidak ingin mengubah password.' : 'Kosongkan untuk meng-generate random password (akan ditampilkan setelah disimpan).'),
                
                Placeholder::make('mapel_aktif')
                    ->label('Mata Pelajaran yang Diampu (Tahun Ajaran Aktif)')
                    ->content(function (?Model $record) {
                        if (!$record) return '-';
                        $mapel = $record->mapel_aktif;
                        return empty($mapel) ? 'Belum ada mata pelajaran' : implode(', ', $mapel);
                    }),

                Repeater::make('teacherJabatans')
                    ->label('Jabatan Tambahan')
                    ->relationship('teacherJabatans')
                    ->schema([
                        Select::make('jabatan_id')
                            ->label('Jabatan')
                            ->options(\App\Models\Jabatan::pluck('nama_jabatan', 'id'))
                            ->required(),
                        DatePicker::make('tanggal_mulai')
                            ->required(),
                        DatePicker::make('tanggal_selesai')
                            ->nullable(),
                    ])
                    ->columns(3),

                Placeholder::make('semua_jabatan')
                    ->label('Semua Jabatan & Tugas Tambahan (Otomatis)')
                    ->content(function (?Model $record) {
                        if (!$record) return '-';
                        $jabatans = $record->semua_jabatan;
                        return empty($jabatans) ? 'Belum ada jabatan' : implode(', ', $jabatans);
                    })
                    ->helperText('Gabungan dari jabatan fungsional dan penugasan Wali Kelas tahun ajaran aktif.'),
            ]);
    }
}

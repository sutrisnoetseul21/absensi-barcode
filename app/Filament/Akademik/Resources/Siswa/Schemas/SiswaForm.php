<?php

namespace App\Filament\Akademik\Resources\Siswa\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SiswaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ─── SECTION 1: Data Identitas & Biodata ───────────────────
                Section::make('Data Identitas & Biodata')
                    ->description('Informasi dasar dan identitas siswa.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nisn')
                            ->label('NISN')
                            ->required()
                            ->maxLength(20)
                            ->unique(
                                table: 'students',
                                column: 'nisn',
                                ignoreRecord: true
                            ),

                        TextInput::make('nis')
                            ->label('NIS')
                            ->nullable()
                            ->maxLength(20)
                            ->unique(
                                table: 'students',
                                column: 'nis',
                                ignoreRecord: true
                            ),

                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('birth_place')
                            ->label('Tempat Lahir')
                            ->maxLength(100)
                            ->nullable(),

                        DatePicker::make('birth_date')
                            ->label('Tanggal Lahir')
                            ->native(false)
                            ->nullable(),

                        Textarea::make('address')
                            ->label('Alamat Lengkap')
                            ->maxLength(500)
                            ->nullable()
                            ->columnSpanFull(),

                        TextInput::make('no_hp')
                            ->label('Nomor WhatsApp Siswa')
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

                        FileUpload::make('photo_path')
                            ->label('Foto Siswa')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1'])
                            ->imageResizeTargetWidth(400)
                            ->imageResizeTargetHeight(400)
                            ->imageResizeMode('cover')
                            ->imageResizeUpscale(false)
                            ->maxSize(2048)
                            ->disk('public')
                            ->directory('siswa-photos')
                            ->nullable(),
                    ]),

                // ─── SECTION 2: Data Sekolah & Status Keluarga ─────────────
                Section::make('Data Sekolah & Status Keluarga')
                    ->description('Informasi terkait penerimaan siswa dan status dalam keluarga.')
                    ->columns(2)
                    ->schema([
                        Select::make('gender')
                            ->label('Jenis Kelamin')
                            ->options([
                                'L' => 'Laki-laki',
                                'P' => 'Perempuan',
                            ])
                            ->nullable(),

                        Select::make('religion')
                            ->label('Agama')
                            ->options([
                                'Islam'    => 'Islam',
                                'Kristen'  => 'Kristen Protestan',
                                'Katolik'  => 'Katolik',
                                'Hindu'    => 'Hindu',
                                'Buddha'   => 'Buddha',
                                'Konghucu' => 'Konghucu',
                            ])
                            ->nullable()
                            ->searchable(),

                        TextInput::make('previous_school')
                            ->label('Asal Sekolah (SD/MI)')
                            ->maxLength(255)
                            ->nullable()
                            ->columnSpanFull(),

                        DatePicker::make('admission_date')
                            ->label('Tanggal Masuk')
                            ->native(false)
                            ->nullable(),

                        TextInput::make('admission_class')
                            ->label('Kelas Masuk')
                            ->placeholder('Contoh: 7A')
                            ->maxLength(20)
                            ->nullable(),

                        Select::make('family_status')
                            ->label('Status dalam Keluarga')
                            ->options([
                                'Kandung' => 'Anak Kandung',
                                'Tiri'    => 'Anak Tiri',
                                'Angkat'  => 'Anak Angkat',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->nullable(),

                        TextInput::make('child_order')
                            ->label('Anak Ke-')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(20)
                            ->nullable()
                            ->helperText('Urutan anak dalam keluarga (angka).'),
                    ]),

                // ─── SECTION 3: Data Orang Tua / Wali ───────────────────────
                Section::make('Data Orang Tua / Wali')
                    ->description('Informasi data orang tua atau wali siswa serta nomor kontak yang dapat dihubungi.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nama_ayah')
                            ->label('Nama Ayah')
                            ->placeholder('Nama ayah kandung')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('pekerjaan_ayah')
                            ->label('Pekerjaan Ayah')
                            ->placeholder('Contoh: PNS, Wiraswasta, Karyawan')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('nama_ibu')
                            ->label('Nama Ibu')
                            ->placeholder('Nama ibu kandung')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('pekerjaan_ibu')
                            ->label('Pekerjaan Ibu')
                            ->placeholder('Contoh: Ibu Rumah Tangga, Guru, Pedagang')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('nama_wali')
                            ->label('Nama Wali (Opsional)')
                            ->placeholder('Nama wali jika tinggal bersama wali')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('pekerjaan_wali')
                            ->label('Pekerjaan Wali (Opsional)')
                            ->placeholder('Pekerjaan wali')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('no_hp_orang_tua')
                            ->label('Nomor WhatsApp / HP Orang Tua / Wali')
                            ->tel()
                            ->placeholder('Contoh: 081234567890')
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
                            ->helperText('Nomor kontak utama orang tua/wali untuk pemberitahuan presensi/sekolah.')
                            ->columnSpanFull()
                            ->nullable(),
                    ]),

                // ─── SECTION 4: Akun Login ──────────────────────────────────
                Section::make('Akun Login')
                    ->description('Email dan password untuk akses portal siswa.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('email')
                            ->label('Email Login')
                            ->email()
                            ->autocomplete('off')
                            ->required(fn (string $operation): bool => $operation === 'edit')
                            ->maxLength(255)
                            ->helperText('Otomatis di-generate jika dikosongkan saat create.')
                            ->formatStateUsing(fn (?\Illuminate\Database\Eloquent\Model $record) => $record?->user?->email),

                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->label('Password Login')
                            ->autocomplete('new-password')
                            ->dehydrated(fn ($state) => filled($state))
                            ->maxLength(255)
                            ->helperText(fn (string $operation): string => $operation === 'edit'
                                ? 'Biarkan kosong jika tidak ingin mengubah password.'
                                : 'Kosongkan untuk menggunakan NISN sebagai password default.'),
                    ]),
            ]);
    }
}

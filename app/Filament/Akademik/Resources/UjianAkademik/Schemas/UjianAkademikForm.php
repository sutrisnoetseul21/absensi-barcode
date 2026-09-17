<?php

namespace App\Filament\Akademik\Resources\UjianAkademik\Schemas;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use App\Models\UjianAkademik;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UjianAkademikForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ─── SECTION 1: Informasi Pokok Asesmen ─────────────────────
                Section::make('Informasi Utama Ujian')
                    ->description('Identitas ujian akademik, mata pelajaran, dan periode pelaksanaan.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nama_ujian')
                            ->label('Nama Agenda Ujian')
                            ->placeholder('Contoh: UTS MTK GURU CBT (Copy)')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('mata_pelajaran_id')
                            ->label('Mata Pelajaran')
                            ->options(MataPelajaran::pluck('nama_mapel', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state) {
                                    $defaultKkm = UjianAkademik::resolveDefaultKkm((int) $state);
                                    $set('kkm', $defaultKkm);
                                }
                                self::updateAutoCbtEventNama($set, $get);
                            }),

                        Select::make('teacher_id')
                            ->label('Guru Pengampu')
                            ->options(fn (callable $get) => self::resolveGuruOptions($get('mata_pelajaran_id')))
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->reactive()
                            ->helperText('Otomatis difilter berdasarkan Mata Pelajaran yang dipilih.'),

                        Select::make('academic_year_id')
                            ->label('Tahun Ajaran')
                            ->options(TahunAjaran::pluck('name', 'id'))
                            ->default(fn () => PengaturanSekolah::current()?->academic_year_id_active)
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('semester')
                            ->label('Semester')
                            ->options([
                                'ganjil' => 'Ganjil',
                                'genap'  => 'Genap',
                            ])
                            ->default(fn () => PengaturanSekolah::current()?->active_semester ?? 'ganjil')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                self::updateAutoCbtEventNama($set, $get);
                            }),

                        Select::make('classes')
                            ->label('Kelas Sasaran')
                            ->relationship('classes', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Rombongan belajar yang akan mengikuti ujian ini.')
                            ->columnSpanFull(),
                    ]),

                // ─── SECTION 2: Kriteria Penilaian & Jadwal Waktu ──────────
                Section::make('Kriteria Ketuntasan & Waktu Pelaksanaan')
                    ->description('Standar batas kelulusan KKM dan durasi pengerjaan.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('kkm')
                            ->label('KKM (Kriteria Ketuntasan)')
                            ->numeric()
                            ->default(75.00)
                            ->required()
                            ->helperText('Otomatis menyesuaikan dengan standar mapel terpilih.'),

                        TextInput::make('durasi_menit')
                            ->label('Durasi Pengerjaan (Menit)')
                            ->numeric()
                            ->default(90)
                            ->nullable(),

                        TextInput::make('total_soal')
                            ->label('Estimasi Butir Soal')
                            ->numeric()
                            ->default(40)
                            ->nullable(),

                        DateTimePicker::make('tanggal_mulai')
                            ->label('Waktu Mulai')
                            ->native(false)
                            ->default(now())
                            ->required(),

                        DateTimePicker::make('tanggal_selesai')
                            ->label('Waktu Selesai')
                            ->native(false)
                            ->default(now()->addHours(2))
                            ->required(),

                        Select::make('status')
                            ->label('Status Agenda')
                            ->options([
                                'draft'   => 'Draft (Persiapan)',
                                'aktif'   => 'Aktif (Sedang Berlangsung)',
                                'selesai' => 'Selesai',
                                'arsip'   => 'Diarsipkan',
                            ])
                            ->default('draft')
                            ->required(),
                    ]),


                // ─── SECTION 4: Parameter Integrasi ZenCBT ─────────────────
                Section::make('Parameter Integrasi Engine ZenCBT')
                    ->description('Penamaan event asesmen untuk sinkronisasi dengan ZenCBT.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('cbt_event_nama')
                            ->label('Nama Event di ZenCBT')
                            ->placeholder('Contoh: UTS MTK GURU CBT')
                            ->helperText('Ditampilkan di layar jadwal proktor & siswa ZenCBT.')
                            ->nullable(),

                        TextInput::make('cbt_ujian_id')
                            ->label('ID Ujian ZenCBT')
                            ->numeric()
                            ->placeholder('Belum terhubung ke ZenCBT')
                            ->nullable()
                            ->readOnly()
                            ->helperText('ID ini diisi otomatis saat sinkronisasi jadwal dari ZenCBT.'),

                        Textarea::make('keterangan')
                            ->label('Keterangan / Catatan Tambahan')
                            ->placeholder('Catatan tata tertib, materi pengujian, dll.')
                            ->columnSpanFull()
                            ->nullable(),
                    ]),
            ]);
    }

    /**
     * Ambil opsi guru, difilter berdasarkan mata pelajaran jika tersedia.
     */
    private static function resolveGuruOptions(?int $mataPelajaranId): array
    {
        if ($mataPelajaranId) {
            // Filter: hanya guru yang mengajar mapel ini (via Pengajaran)
            $guruIds = \App\Models\Pengajaran::where('mata_pelajaran_id', $mataPelajaranId)
                ->pluck('teacher_id')
                ->unique()
                ->toArray();

            if (!empty($guruIds)) {
                return Guru::whereIn('id', $guruIds)->orderBy('name')->pluck('name', 'id')->toArray();
            }
        }

        return Guru::orderBy('name')->pluck('name', 'id')->toArray();
    }

    /**
     * Helper privat untuk mengisi nama event ZenCBT secara otomatis
     * berdasarkan nama mapel dan semester.
     */
    private static function updateAutoCbtEventNama(callable $set, callable $get): void
    {
        $mapelId = $get('mata_pelajaran_id');
        $mapelName = $mapelId ? MataPelajaran::find($mapelId)?->nama_mapel : '';
        $semester = ucfirst($get('semester') ?? 'ganjil');

        if ($mapelName) {
            $set('cbt_event_nama', "CBT {$semester} - {$mapelName}");
        }
    }
}

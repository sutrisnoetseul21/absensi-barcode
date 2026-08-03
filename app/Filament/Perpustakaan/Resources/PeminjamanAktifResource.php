<?php

namespace App\Filament\Perpustakaan\Resources;

use App\Filament\Perpustakaan\Resources\PeminjamanAktifResource\Pages;
use App\Models\Peminjaman;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class PeminjamanAktifResource extends Resource
{
    protected static ?string $model = Peminjaman::class;

    protected static ?string $slug = 'peminjaman';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';
    
    protected static string | \UnitEnum | null $navigationGroup = 'Sirkulasi';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Peminjaman';
    protected static ?string $pluralModelLabel = 'Peminjaman';
    protected static ?string $navigationLabel = 'Peminjaman';
    
    public static function canCreate(): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        $lamaPinjam = \App\Models\PengaturanSekolah::current()?->lama_pinjam_buku_hari ?? 7;

        return $schema->schema([
            \Filament\Forms\Components\Select::make('peminjam_type')
                ->label('Tipe Anggota')
                ->options([
                    'siswa' => 'Siswa',
                    'guru' => 'Guru / Staff',
                ])
                ->required()
                ->live(),

            \Filament\Forms\Components\Select::make('peminjam_id')
                ->label('Peminjam / Kartu Anggota')
                ->placeholder('Pindai Barcode Kartu atau Ketik Nama / NISN / NIS / NIP')
                ->helperText('Klik kolom ini lalu scan Kartu Anggota atau ketik pencarian.')
                ->options(function ($get) {
                    $type = $get('peminjam_type');
                    if ($type === 'guru') {
                        return \App\Models\Guru::orderBy('name')
                            ->get()
                            ->mapWithKeys(fn ($guru) => [$guru->id => $guru->name . ($guru->nip ? " (NIP: {$guru->nip})" : '')]);
                    }
                    return \App\Models\Siswa::orderBy('name')
                        ->get()
                        ->mapWithKeys(function ($siswa) {
                            $info = [];
                            if ($siswa->nisn) $info[] = "NISN: {$siswa->nisn}";
                            if ($siswa->nis) $info[] = "NIS: {$siswa->nis}";
                            $extra = count($info) > 0 ? ' (' . implode(' | ', $info) . ')' : '';
                            return [$siswa->id => "{$siswa->name}{$extra}"];
                        });
                })
                ->searchable()
                ->preload()
                ->required()
                ->disabled(fn ($get) => ! $get('peminjam_type')),

            \Filament\Forms\Components\Select::make('eksemplar_id')
                ->label('Buku & Eksemplar (Tersedia)')
                ->placeholder('Pindai Barcode Buku atau Ketik Judul / Kode Eksemplar')
                ->helperText('Klik kolom ini lalu scan label barcode di fisik buku. Koleksi yang tidak bisa dipinjam otomatis disembunyikan.')
                ->options(function () {
                    return \App\Models\EksemplarBuku::with('buku.kategoriBuku')
                        ->where('status', 'tersedia')
                        ->get()
                        ->filter(function ($item) {
                            return $item->buku?->kategoriBuku?->is_bisa_dipinjam ?? true;
                        })
                        ->mapWithKeys(function ($item) {
                            $judul = $item->buku->judul ?? 'Tanpa Judul';
                            return [$item->id => "{$judul} - [Kode: {$item->kode_eksemplar}]"];
                        });
                })
                ->searchable()
                ->preload()
                ->required()
                ->rules([
                    function () {
                        return function (string $attribute, $value, \Closure $fail) {
                            $eksemplar = \App\Models\EksemplarBuku::with('buku.kategoriBuku')->find($value);
                            if ($eksemplar) {
                                $isBisaDipinjam = $eksemplar->buku?->kategoriBuku?->is_bisa_dipinjam ?? true;
                                if (!$isBisaDipinjam) {
                                    $fail('Koleksi ini tidak dapat dipinjam. Koleksi khusus/referensi hanya tersedia untuk dibaca di tempat.');
                                }
                            }
                        };
                    }
                ]),

            \Filament\Forms\Components\DatePicker::make('tanggal_pinjam')
                ->label('Tanggal Pinjam')
                ->default(now())
                ->required(),

            \Filament\Forms\Components\DatePicker::make('tanggal_jatuh_tempo')
                ->label('Tanggal Jatuh Tempo')
                ->default(now()->addDays($lamaPinjam))
                ->required(),

            \Filament\Forms\Components\Select::make('status')
                ->options([
                    'dipinjam' => 'Dipinjam',
                    'dikembalikan' => 'Dikembalikan',
                    'hilang' => 'Hilang',
                ])
                ->default('dipinjam')
                ->required(),

            \Filament\Forms\Components\DatePicker::make('tanggal_kembali')
                ->label('Tanggal Kembali')
                ->visible(fn ($get) => $get('status') === 'dikembalikan'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('peminjam.name')
                    ->label('Peminjam')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('peminjam_type')
                    ->label('Tipe Anggota')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'siswa' => 'Siswa',
                        'guru' => 'Guru / Staff',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'siswa' => 'info',
                        'guru' => 'success',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('eksemplar.buku.judul')
                    ->label('Buku')
                    ->description(fn (Peminjaman $record): string => $record->eksemplar->kode_eksemplar ?? '')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('tanggal_pinjam')
                    ->label('Tgl Pinjam')
                    ->date('d M Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('tanggal_jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('tanggal_kembali')
                    ->label('Tgl Kembali')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(function (string $state, Peminjaman $record): string {
                        if ($state === 'dipinjam' && $record->tanggal_jatuh_tempo < Carbon::now()->startOfDay()) {
                            return 'Terlambat';
                        }
                        return ucfirst($state);
                    })
                    ->color(function (string $state, Peminjaman $record): string {
                        if ($state === 'dipinjam' && $record->tanggal_jatuh_tempo < Carbon::now()->startOfDay()) {
                            return 'danger';
                        }
                        return match ($state) {
                            'dipinjam' => 'warning',
                            'dikembalikan' => 'success',
                            'hilang' => 'danger',
                            default => 'gray',
                        };
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('peminjam_type')
                    ->label('Tipe Anggota')
                    ->options([
                        'siswa' => 'Siswa',
                        'guru' => 'Guru',
                    ]),
                    
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'dipinjam' => 'Dipinjam',
                        'dikembalikan' => 'Dikembalikan',
                        'hilang' => 'Hilang',
                    ]),
                    
                Tables\Filters\Filter::make('terlambat')
                    ->label('Hanya Terlambat')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'dipinjam')->where('tanggal_jatuh_tempo', '<', Carbon::now()->startOfDay()))
                    ->toggle(),
            ])
            ->actions([
                Action::make('kembalikan')
                    ->label('Kembalikan')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Kembalikan Buku')
                    ->modalDescription('Apakah Anda yakin buku ini sudah dikembalikan?')
                    ->action(function (Peminjaman $record) {
                        $record->update([
                            'status' => 'dikembalikan',
                            'tanggal_kembali' => now(),
                        ]);

                        if ($record->eksemplar_id) {
                            \App\Models\EksemplarBuku::where('id', $record->eksemplar_id)->update(['status' => 'tersedia']);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Buku berhasil dikembalikan')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Peminjaman $record) => $record->status === 'dipinjam'),
                EditAction::make(),
            ])
            ->bulkActions([
            ]);
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePeminjamanAktifs::route('/'),
        ];
    }
}

<?php

namespace App\Filament\Perpustakaan\Resources;

use App\Filament\Perpustakaan\Resources\PeminjamanAktifResource\Pages;
use App\Models\Peminjaman;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use App\Filament\Traits\HasSimpleRoleAccess;
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
    use HasSimpleRoleAccess;

    protected static function getModuleRolePrefix(): string
    {
        return 'perpustakaan';
    }

    protected static ?string $model = Peminjaman::class;

    protected static ?string $slug = 'peminjaman';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';
    
    protected static string | \UnitEnum | null $navigationGroup = 'Perpustakaan';
    protected static ?int $navigationSort = 5;
    protected static ?string $modelLabel = 'Peminjaman';
    protected static ?string $pluralModelLabel = 'Peminjaman';
    protected static ?string $navigationLabel = 'Peminjaman';

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
                ->live()
                ->afterStateUpdated(fn ($set) => $set('peminjam_id', null)),

            \Filament\Forms\Components\Select::make('peminjam_id')
                ->label('Peminjam / Kartu Anggota')
                ->placeholder('Pindai Barcode Kartu atau Ketik Nama / NISN / NIS / NIP')
                ->helperText('Klik kolom ini lalu scan Kartu Anggota atau ketik pencarian.')
                ->searchable()
                ->getSearchResultsUsing(function (string $search, $get) {
                    $type = $get('peminjam_type');
                    $query = $type === 'guru' ? \App\Models\Guru::query() : \App\Models\Siswa::query();

                    return $query->where(function ($q) use ($search, $type) {
                        $q->where('name', 'like', "%{$search}%");
                        if ($type === 'guru') {
                            $q->orWhere('nip', 'like', "%{$search}%");
                        } else {
                            $q->orWhere('nisn', 'like', "%{$search}%")
                              ->orWhere('nis', 'like', "%{$search}%");
                        }
                    })
                    ->limit(50)
                    ->get()
                    ->mapWithKeys(function ($model) use ($type) {
                        if ($type === 'guru') {
                            return [$model->id => $model->name . ($model->nip ? " (NIP: {$model->nip})" : '')];
                        }
                        $info = [];
                        if ($model->nisn) $info[] = "NISN: {$model->nisn}";
                        if ($model->nis) $info[] = "NIS: {$model->nis}";
                        $extra = count($info) > 0 ? ' (' . implode(' | ', $info) . ')' : '';
                        return [$model->id => "{$model->name}{$extra}"];
                    })->all();
                })
                ->getOptionLabelUsing(function ($value, $get) {
                    $type = $get('peminjam_type');
                    $model = $type === 'guru' ? \App\Models\Guru::find($value) : \App\Models\Siswa::find($value);
                    if (!$model) return null;

                    if ($type === 'guru') {
                        return $model->name . ($model->nip ? " (NIP: {$model->nip})" : '');
                    }
                    $info = [];
                    if ($model->nisn) $info[] = "NISN: {$model->nisn}";
                    if ($model->nis) $info[] = "NIS: {$model->nis}";
                    $extra = count($info) > 0 ? ' (' . implode(' | ', $info) . ')' : '';
                    return "{$model->name}{$extra}";
                })
                ->required()
                ->disabled(fn ($get) => ! $get('peminjam_type')),

            \Filament\Forms\Components\Select::make('eksemplar_id')
                ->label('Buku & Eksemplar (Tersedia)')
                ->placeholder('Pindai Barcode Buku atau Ketik Judul / Kode Eksemplar')
                ->helperText('Klik kolom ini lalu scan label barcode di fisik buku. Koleksi yang tidak bisa dipinjam otomatis disembunyikan.')
                ->searchable()
                ->getSearchResultsUsing(function (string $search) {
                    return \App\Models\EksemplarBuku::with('buku.kategoriBuku')
                        ->where('status', 'tersedia')
                        ->where(function ($q) {
                            $q->whereHas('buku.kategoriBuku', fn ($q2) => $q2->where('is_bisa_dipinjam', true))
                              ->orWhereDoesntHave('buku.kategoriBuku');
                        })
                        ->where(function ($q) use ($search) {
                            $q->where('kode_eksemplar', 'like', "%{$search}%")
                              ->orWhereHas('buku', fn ($q2) => $q2->where('judul', 'like', "%{$search}%"));
                        })
                        ->limit(50)
                        ->get()
                        ->mapWithKeys(function ($item) {
                            $judul = $item->buku->judul ?? 'Tanpa Judul';
                            return [$item->id => "{$judul} - [Kode: {$item->kode_eksemplar}]"];
                        })->all();
                })
                ->getOptionLabelUsing(function ($value) {
                    $item = \App\Models\EksemplarBuku::with('buku')->find($value);
                    if (!$item) return null;
                    $judul = $item->buku->judul ?? 'Tanpa Judul';
                    return "{$judul} - [Kode: {$item->kode_eksemplar}]";
                })
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
                    ->visible(fn (Peminjaman $record) => $record->status === 'dipinjam' && (auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_perpustakaan_editor') || auth()->user()?->hasRole('admin_master_editor'))),
                EditAction::make()
                    ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_perpustakaan_editor') || auth()->user()?->hasRole('admin_master_editor')),
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

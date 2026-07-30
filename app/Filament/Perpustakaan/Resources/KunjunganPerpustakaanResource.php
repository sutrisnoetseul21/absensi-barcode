<?php

namespace App\Filament\Perpustakaan\Resources;

use App\Filament\Perpustakaan\Resources\KunjunganPerpustakaanResource\Pages;
use App\Models\KunjunganPerpustakaan;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class KunjunganPerpustakaanResource extends Resource
{
    protected static ?string $model = KunjunganPerpustakaan::class;

    protected static ?string $slug = 'riwayat-presensi';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-identification';
    
    protected static string | \UnitEnum | null $navigationGroup = 'Sirkulasi';
    protected static ?int $navigationSort = 3;
    protected static ?string $modelLabel = 'Presensi Kunjungan';
    protected static ?string $pluralModelLabel = 'Riwayat Presensi Kunjungan';
    protected static ?string $navigationLabel = 'Riwayat Presensi';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            \Filament\Forms\Components\Select::make('pengunjung_type')
                ->label('Tipe Anggota')
                ->options([
                    'siswa' => 'Siswa',
                    'guru' => 'Guru / Staff',
                ])
                ->required()
                ->live(),

            \Filament\Forms\Components\Select::make('pengunjung_id')
                ->label('Pengunjung')
                ->placeholder('Cari Nama / NISN / NIS / NIP')
                ->options(function ($get) {
                    $type = $get('pengunjung_type');
                    if ($type === 'guru') {
                        return \App\Models\Guru::orderBy('name')->pluck('name', 'id');
                    }
                    return \App\Models\Siswa::orderBy('name')->pluck('name', 'id');
                })
                ->searchable()
                ->required(),

            \Filament\Forms\Components\DatePicker::make('tanggal')
                ->default(now())
                ->required(),

            \Filament\Forms\Components\TimePicker::make('waktu_masuk')
                ->default(now()->format('H:i:s'))
                ->required(),

            \Filament\Forms\Components\TextInput::make('tujuan_kunjungan')
                ->default('Membaca / Belajar')
                ->required(),

            \Filament\Forms\Components\Textarea::make('catatan')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('waktu_masuk')
                    ->label('Waktu Masuk')
                    ->time('H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pengunjung.name')
                    ->label('Pengunjung / Anggota')
                    ->description(function (KunjunganPerpustakaan $record): string {
                        if ($record->pengunjung_type === 'siswa' && $record->pengunjung) {
                            return $record->pengunjung->enrollmentAktif ? 'Kelas ' . $record->pengunjung->enrollmentAktif->kelas->name : 'Siswa';
                        }
                        return 'Guru / Staff';
                    })
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->whereHasMorph('pengunjung', [\App\Models\Siswa::class, \App\Models\Guru::class], function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('pengunjung_type')
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

                Tables\Columns\TextColumn::make('tujuan_kunjungan')
                    ->label('Tujuan')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                Tables\Columns\TextColumn::make('petugas.name')
                    ->label('Petugas')
                    ->placeholder('Kiosk Scanner')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('pengunjung_type')
                    ->label('Tipe Anggota')
                    ->options([
                        'siswa' => 'Siswa',
                        'guru' => 'Guru / Staff',
                    ]),

                Tables\Filters\Filter::make('hari_ini')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query): Builder => $query->whereDate('tanggal', Carbon::today()))
                    ->default(),
            ])
            ->actions([
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageKunjunganPerpustakaans::route('/'),
        ];
    }
}

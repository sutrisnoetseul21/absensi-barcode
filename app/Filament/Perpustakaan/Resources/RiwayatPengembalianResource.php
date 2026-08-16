<?php

namespace App\Filament\Perpustakaan\Resources;

use App\Filament\Perpustakaan\Resources\RiwayatPengembalianResource\Pages;
use App\Models\Peminjaman;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use App\Filament\Traits\HasSimpleRoleAccess;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class RiwayatPengembalianResource extends Resource
{
    use HasSimpleRoleAccess;

    protected static function getModuleRolePrefix(): string
    {
        return 'perpustakaan';
    }

    protected static ?string $model = Peminjaman::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';
    
    protected static string | \UnitEnum | null $navigationGroup = 'Perpustakaan';
    protected static ?int $navigationSort = 6;
    protected static ?string $modelLabel = 'Riwayat Pengembalian';
    protected static ?string $pluralModelLabel = 'Riwayat Pengembalian';
    protected static ?string $navigationLabel = 'Pengembalian';
    protected static bool $shouldRegisterNavigation = false;
    
    // Nonaktifkan create button karena peminjaman via kiosk

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            \Filament\Forms\Components\Select::make('status')
                ->options([
                    'dipinjam' => 'Dipinjam',
                    'dikembalikan' => 'Dikembalikan',
                    'hilang' => 'Hilang',
                ])
                ->required(),
            \Filament\Forms\Components\DatePicker::make('tanggal_pinjam')
                ->required(),
            \Filament\Forms\Components\DatePicker::make('tanggal_jatuh_tempo')
                ->required(),
            \Filament\Forms\Components\DatePicker::make('tanggal_kembali'),
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
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('status', ['dikembalikan', 'hilang']);
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
            'index' => Pages\ManageRiwayatPengembalians::route('/'),
        ];
    }
}

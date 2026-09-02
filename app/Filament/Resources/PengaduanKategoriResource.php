<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengaduanKategoriResource\Pages;
use App\Models\PengaduanKategori;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PengaduanKategoriResource extends Resource
{
    protected static ?string $model = PengaduanKategori::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    protected static string|\UnitEnum|null $navigationGroup = 'Layanan Publik';
    protected static ?string $navigationLabel = 'Kategori Pengaduan';
    protected static ?string $pluralModelLabel = 'Kategori Pengaduan';
    protected static ?string $modelLabel = 'Kategori';
    protected static ?string $slug = 'pengaduan/kategori';
    protected static ?int $navigationSort = 2;

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('nama_kategori')
                    ->label('Nama Kategori')
                    ->placeholder('Contoh: Fasilitas Sekolah, Akademik, dll')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('urutan')
                    ->label('Nomor Urutan Tampilan')
                    ->numeric()
                    ->default(0)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_kategori')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('urutan')
                    ->label('Urutan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pengaduans_count')
                    ->counts('pengaduans')
                    ->label('Jumlah Laporan')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('urutan', 'asc')
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePengaduanKategoris::route('/'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebAplikasiResource\Pages;
use App\Models\WebAplikasi;
use App\Filament\Components\IconPickerField;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class WebAplikasiResource extends Resource
{
    protected static ?string $model = WebAplikasi::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';
    protected static string|\UnitEnum|null $navigationGroup = 'Web Profil Sekolah';
    protected static ?string $navigationLabel = 'Menu Aplikasi';
    protected static ?string $modelLabel = 'Menu Aplikasi';
    protected static ?int $navigationSort = 4;

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Judul / Nama Tautan')
                    ->required(),
                Forms\Components\TextInput::make('url')
                    ->label('URL Tautan')
                    ->url()
                    ->required(),
                IconPickerField::make('icon')
                    ->label('Ikon'),
                Forms\Components\Select::make('icon_color')
                    ->label('Warna Icon')
                    ->options([
                        'text-blue-500'   => '🔵 Biru',
                        'text-green-500'  => '🟢 Hijau',
                        'text-red-500'    => '🔴 Merah',
                        'text-amber-500'  => '🟡 Kuning/Amber',
                        'text-purple-500' => '🟣 Ungu',
                        'text-pink-500'   => '🩷 Pink',
                        'text-teal-500'   => '🩵 Teal',
                        'text-orange-500' => '🟠 Oranye',
                        'text-slate-600'  => '⬛ Abu-abu',
                    ])
                    ->default('text-blue-500')
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif (tampil di beranda)')
                    ->default(true),
                Forms\Components\TextInput::make('order')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable()
                    ->width(80),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Tautan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->limit(40)
                    ->copyable(),
                Tables\Columns\TextColumn::make('icon_color')
                    ->label('Warna'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order')
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
            'index'  => Pages\ListWebAplikasis::route('/'),
            'create' => Pages\CreateWebAplikasi::route('/create'),
            'edit'   => Pages\EditWebAplikasi::route('/{record}/edit'),
        ];
    }
}

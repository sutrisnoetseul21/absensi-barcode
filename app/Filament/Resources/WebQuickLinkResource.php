<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebQuickLinkResource\Pages;
use App\Models\WebQuickLink;
use App\Filament\Components\IconPickerField;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class WebQuickLinkResource extends Resource
{
    protected static ?string $model = WebQuickLink::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-link';
    protected static string|\UnitEnum|null $navigationGroup = 'Web Profil Sekolah';
    protected static ?string $navigationLabel = 'Akses Cepat';
    protected static ?string $modelLabel = 'Akses Cepat';
    protected static ?int $navigationSort = 4;

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Judul / Nama Tautan')
                    ->required(),
                Forms\Components\TextInput::make('description')
                    ->label('Deskripsi Singkat')
                    ->nullable(),
                Forms\Components\TextInput::make('url')
                    ->label('URL Tautan')
                    ->url()
                    ->required(),
                IconPickerField::make('icon'),
                Forms\Components\Select::make('color_class')
                    ->label('Warna Icon')
                    ->options([
                        'bg-blue-500'   => '🔵 Biru',
                        'bg-green-500'  => '🟢 Hijau',
                        'bg-red-500'    => '🔴 Merah',
                        'bg-amber-500'  => '🟡 Kuning/Amber',
                        'bg-purple-500' => '🟣 Ungu',
                        'bg-pink-500'   => '🩷 Pink',
                        'bg-teal-500'   => '🩵 Teal',
                        'bg-orange-500' => '🟠 Oranye',
                        'bg-slate-600'  => '⬛ Abu-abu',
                    ])
                    ->default('bg-blue-500')
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
                Tables\Columns\TextColumn::make('title')
                    ->label('Nama Tautan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->limit(40)
                    ->copyable(),
                Tables\Columns\TextColumn::make('color_class')
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
            'index'  => Pages\ListWebQuickLinks::route('/'),
            'create' => Pages\CreateWebQuickLink::route('/create'),
            'edit'   => Pages\EditWebQuickLink::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebSarpraResource\Pages;
use App\Models\WebSarpra;
use App\Filament\Components\IconPickerField;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WebSarpraResource extends Resource
{
    protected static ?string $model = WebSarpra::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';
    protected static string|\UnitEnum|null $navigationGroup = 'Web Profil Sekolah';
    protected static ?string $navigationLabel = 'Fasilitas / Sarpras';
    protected static ?string $pluralModelLabel = 'Fasilitas / Sarpras';
    protected static ?int $navigationSort = 2;

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('nama_fasilitas')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('deskripsi')
                    ->maxLength(255),
                IconPickerField::make('icon')
                    ->label('Ikon')
                    ->required(),
                Forms\Components\Select::make('color')
                    ->label('Warna Ikon')
                    ->options([
                        'text-red-500' => 'Merah',
                        'text-orange-500' => 'Oranye',
                        'text-yellow-500' => 'Kuning',
                        'text-emerald-500' => 'Hijau Zamrud',
                        'text-nature-500' => 'Hijau Daun',
                        'text-teal-500' => 'Tosca',
                        'text-cyan-500' => 'Cyan',
                        'text-blue-500' => 'Biru',
                        'text-indigo-500' => 'Nila',
                        'text-purple-500' => 'Ungu',
                        'text-pink-500' => 'Merah Muda',
                        'text-gray-500' => 'Abu-abu',
                    ])
                    ->required()
                    ->default('text-nature-500'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('icon')
                    ->label('Ikon')
                    ->formatStateUsing(fn ($state, $record) => new \Illuminate\Support\HtmlString('<div class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-50 border"><i class="' . $state . ' ' . $record->color . ' text-xl"></i></div>'))
                    ->html(),
                Tables\Columns\TextColumn::make('nama_fasilitas')
                    ->searchable(),
                Tables\Columns\TextColumn::make('deskripsi')
                    ->limit(50),
            ])
            ->reorderable('urutan')
            ->defaultSort('urutan')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                    \Filament\Actions\ForceDeleteBulkAction::make(),
                    \Filament\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWebSarpras::route('/'),
        ];
    }
}

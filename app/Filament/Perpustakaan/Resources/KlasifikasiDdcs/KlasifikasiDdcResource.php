<?php

namespace App\Filament\Perpustakaan\Resources\KlasifikasiDdcs;

use App\Filament\Perpustakaan\Resources\KlasifikasiDdcs\Pages\ManageKlasifikasiDdcs;
use App\Models\KlasifikasiDdc;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KlasifikasiDdcResource extends Resource
{
    protected static ?string $model = KlasifikasiDdc::class;

    protected static ?string $slug = 'klasifikasi-ddc';

    protected static ?string $modelLabel = 'Klasifikasi DDC';

    protected static ?string $pluralModelLabel = 'Klasifikasi DDC';

    protected static ?string $navigationLabel = 'Klasifikasi DDC';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'kategori';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode_ddc')
                    ->label('Kode DDC')
                    ->placeholder('Contoh: 297')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),
                TextInput::make('kategori')
                    ->label('Kategori / Subjek')
                    ->placeholder('Contoh: Agama Islam')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('kategori')
            ->columns([
                TextColumn::make('kode_ddc')
                    ->label('Kode DDC')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kategori')
                    ->label('Kategori / Subjek')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageKlasifikasiDdcs::route('/'),
        ];
    }
}

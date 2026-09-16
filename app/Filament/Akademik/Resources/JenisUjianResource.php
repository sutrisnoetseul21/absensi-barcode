<?php

namespace App\Filament\Akademik\Resources;

use App\Filament\Akademik\Resources\JenisUjianResource\Pages;
use App\Models\JenisUjian;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JenisUjianResource extends Resource
{
    protected static ?string $model = JenisUjian::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    
    protected static string|\UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?string $modelLabel = 'Jenis Ujian';
    
    protected static ?string $pluralModelLabel = 'Jenis Ujian';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('kode')
                    ->label('Kode')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),
                TextInput::make('nama')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(100),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode')->searchable()->sortable(),
                TextColumn::make('nama')->searchable()->sortable(),
            ])
            ->filters([
                //
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageJenisUjians::route('/'),
        ];
    }
}

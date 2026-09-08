<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebPillarResource\Pages;
use App\Models\WebPillar;
use App\Filament\Components\IconPickerField;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class WebPillarResource extends Resource
{
    protected static ?string $model = WebPillar::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';
    protected static string|\UnitEnum|null $navigationGroup = 'Web Profil Sekolah';
    protected static ?string $navigationLabel = 'Pilar Keunggulan';
    protected static ?string $modelLabel = 'Pilar Keunggulan';
    protected static ?string $pluralModelLabel = 'Pilar Keunggulan';
    protected static ?int $navigationSort = 3;

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Judul Pilar')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('tag')
                    ->label('Tag Kategori / Badge')
                    ->placeholder('Contoh: Teknologi & Inovasi')
                    ->maxLength(100),
                Forms\Components\Textarea::make('desc')
                    ->label('Deskripsi Singkat')
                    ->rows(3)
                    ->maxLength(1000),
                IconPickerField::make('icon')
                    ->label('Ikon FontAwesome')
                    ->required(),
                Forms\Components\Select::make('color_theme')
                    ->label('Tema Warna')
                    ->options(WebPillar::colorThemes())
                    ->default('blue')
                    ->required(),
                Forms\Components\TextInput::make('link')
                    ->label('URL Tautan (Opsional)')
                    ->url()
                    ->placeholder('https://...'),
                Forms\Components\TextInput::make('urutan')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->label('Tampilkan di Beranda')
                    ->default(true),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('icon')
                    ->label('Ikon')
                    ->formatStateUsing(function ($state, $record) {
                        $theme = $record->theme_styles;
                        return new \Illuminate\Support\HtmlString(
                            '<div class="w-9 h-9 flex items-center justify-center rounded-lg ' . $theme['icon_bg'] . ' shadow-xs"><i class="' . $state . ' text-base"></i></div>'
                        );
                    })
                    ->html(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Pilar')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('tag')
                    ->label('Tag')
                    ->badge()
                    ->color(fn ($record) => match ($record->color_theme) {
                        'emerald', 'teal' => 'success',
                        'amber'           => 'warning',
                        'purple', 'indigo'=> 'info',
                        'rose'            => 'danger',
                        default           => 'primary',
                    }),
                Tables\Columns\TextColumn::make('desc')
                    ->label('Deskripsi')
                    ->limit(40),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif'),
                Tables\Columns\TextColumn::make('urutan')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->reorderable('urutan')
            ->defaultSort('urutan')
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
            'index' => Pages\ManageWebPillars::route('/'),
        ];
    }
}

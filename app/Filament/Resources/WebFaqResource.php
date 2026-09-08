<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebFaqResource\Pages;
use App\Models\WebFaq;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class WebFaqResource extends Resource
{
    protected static ?string $model = WebFaq::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static string|\UnitEnum|null $navigationGroup = 'Layanan Publik';

    protected static ?string $navigationLabel = 'FAQ Sekolah';

    protected static ?string $modelLabel = 'FAQ';

    protected static ?string $pluralModelLabel = 'FAQ Sekolah';

    protected static ?string $slug = 'layanan/faq';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        $defaultCategories = WebFaq::defaultCategories();
        $categoriesOptions = array_combine($defaultCategories, $defaultCategories);

        return $schema
            ->components([
                Forms\Components\Select::make('kategori')
                    ->label('Kategori')
                    ->options(function () use ($categoriesOptions) {
                        try {
                            $existing = WebFaq::query()->distinct()->pluck('kategori', 'kategori')->toArray();
                            return array_merge($categoriesOptions, $existing);
                        } catch (\Throwable $e) {
                            return $categoriesOptions;
                        }
                    })
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('urutan')
                    ->label('Nomor Urut')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Forms\Components\TextInput::make('pertanyaan')
                    ->label('Pertanyaan (Q)')
                    ->placeholder('Misal: Bagaimana cara mendaftar peserta didik baru?')
                    ->required()
                    ->maxLength(500)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('jawaban')
                    ->label('Jawaban (A)')
                    ->placeholder('Tuliskan jawaban yang lengkap, jelas, dan informatif...')
                    ->rows(5)
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif / Tampilkan di Web')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'SPMB' => 'primary',
                        'Layanan Administrasi' => 'info',
                        'Ekstrakurikuler' => 'warning',
                        'Perpustakaan' => 'success',
                        'UKS & Kesehatan' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pertanyaan')
                    ->label('Pertanyaan')
                    ->searchable()
                    ->weight('bold')
                    ->wrap(),
                Tables\Columns\TextColumn::make('jawaban')
                    ->label('Jawaban')
                    ->limit(60)
                    ->wrap(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif'),
                Tables\Columns\TextColumn::make('urutan')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori')
                    ->options(fn () => WebFaq::query()->distinct()->pluck('kategori', 'kategori')->toArray()),
            ])
            ->defaultSort('urutan')
            ->reorderable('urutan')
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
            'index' => Pages\ManageWebFaqs::route('/'),
        ];
    }
}

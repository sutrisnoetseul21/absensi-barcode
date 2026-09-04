<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlumniResource\Pages;
use App\Models\Alumni;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AlumniResource extends Resource
{
    protected static ?string $model = Alumni::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';
    protected static string|\UnitEnum|null $navigationGroup = 'Data Alumni';
    protected static ?string $navigationLabel = 'Data Tracer Alumni';
    protected static ?string $pluralModelLabel = 'Data Alumni';
    protected static ?string $modelLabel = 'Alumni';
    protected static ?string $slug = 'alumni/data';
    protected static ?int $navigationSort = 1;

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('nisn')
                    ->label('NISN')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),
                Forms\Components\TextInput::make('nama')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-Laki',
                        'P' => 'Perempuan',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('tahun_lulus')
                    ->label('Tahun Lulus')
                    ->numeric()
                    ->required()
                    ->default(date('Y')),
                Forms\Components\Toggle::make('melanjutkan')
                    ->label('Melanjutkan Pendidikan')
                    ->default(false)
                    ->reactive(),
                Forms\Components\Select::make('jenjang_id')
                    ->label('Jenjang Lanjutan')
                    ->relationship('jenjang', 'nama_jenjang')
                    ->visible(fn ($get) => (bool) $get('melanjutkan')),
                Forms\Components\TextInput::make('nama_sekolah')
                    ->label('Nama Sekolah / Instansi Lanjutan')
                    ->maxLength(255)
                    ->visible(fn ($get) => (bool) $get('melanjutkan')),
                Forms\Components\FileUpload::make('foto')
                    ->label('Foto Alumni')
                    ->image()
                    ->directory('alumni')
                    ->maxSize(2048)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(url('https://ui-avatars.com/api/?background=EBF4FF&color=7F9CF5&name=Alumni')),
                Tables\Columns\TextColumn::make('nisn')
                    ->label('NISN')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->label('L/P')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'L' => 'info',
                        'P' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('source')
                    ->label('Sumber')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sistem' => 'Lulusan Sistem',
                        'web_mandiri' => 'Alumni Lama (Web)',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'sistem' => 'success',
                        'web_mandiri' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('tahun_lulus')
                    ->label('Tahun Lulus')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\IconColumn::make('melanjutkan')
                    ->label('Lanjut')
                    ->boolean(),
                Tables\Columns\TextColumn::make('jenjang.nama_jenjang')
                    ->label('Jenjang')
                    ->badge()
                    ->color('primary')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('nama_sekolah')
                    ->label('Sekolah / Instansi')
                    ->searchable()
                    ->placeholder('-')
                    ->limit(25),
                Tables\Columns\TextColumn::make('no_hp')
                    ->label('No. HP / WA')
                    ->placeholder('-')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Isi')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tahun_lulus')
                    ->label('Tahun Lulus')
                    ->options(fn () => Alumni::distinct()->orderByDesc('tahun_lulus')->pluck('tahun_lulus', 'tahun_lulus')->toArray()),
                Tables\Filters\SelectFilter::make('jenjang_id')
                    ->label('Jenjang Lanjutan')
                    ->relationship('jenjang', 'nama_jenjang'),
                Tables\Filters\TernaryFilter::make('melanjutkan')
                    ->label('Status Melanjutkan'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAlumnis::route('/'),
        ];
    }
}

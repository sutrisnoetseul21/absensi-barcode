<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengaduanResource\Pages;
use App\Models\Pengaduan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class PengaduanResource extends Resource
{
    protected static ?string $model = Pengaduan::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox-stack';
    protected static string|\UnitEnum|null $navigationGroup = 'Layanan Publik';
    protected static ?string $navigationLabel = 'Data Aspirasi & Pengaduan';
    protected static ?string $pluralModelLabel = 'Data Aspirasi & Pengaduan';
    protected static ?string $modelLabel = 'Aspirasi / Pengaduan';
    protected static ?string $slug = 'pengaduan/data';
    protected static ?int $navigationSort = 1;

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('nama')
                    ->label('Nama Pengirim')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Email Pengirim')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('no_hp')
                    ->label('No. HP / WhatsApp')
                    ->tel()
                    ->maxLength(50),
                Forms\Components\Select::make('pengaduan_kategori_id')
                    ->label('Kategori')
                    ->relationship('kategori', 'nama_kategori')
                    ->required(),
                Forms\Components\Textarea::make('isi_pengaduan')
                    ->label('Isi Pesan / Pengaduan')
                    ->required()
                    ->rows(6)
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->options([
                        'menunggu' => 'Menunggu Review',
                        'diproses' => 'Sedang Diproses',
                        'selesai' => 'Selesai / Ditindaklanjuti',
                    ])
                    ->default('menunggu'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Pengirim')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),
                Tables\Columns\TextColumn::make('no_hp')
                    ->label('No. HP / WA')
                    ->searchable()
                    ->copyable()
                    ->placeholder('-'),
                Tables\Columns\BadgeColumn::make('kategori.nama_kategori')
                    ->label('Kategori')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('isi_pengaduan')
                    ->label('Isi Laporan / Pesan')
                    ->limit(60)
                    ->wrap()
                    ->tooltip(fn (Pengaduan $record): string => $record->isi_pengaduan),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Masuk')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('pengaduan_kategori_id')
                    ->label('Filter Kategori')
                    ->relationship('kategori', 'nama_kategori'),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make()
                    ->label('Baca'),
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
            'index' => Pages\ManagePengaduans::route('/'),
        ];
    }
}

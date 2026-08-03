<?php

namespace App\Filament\Perpustakaan\Resources\Bukus\BukuResource\RelationManagers;

use App\Models\EksemplarBuku;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

use Filament\Schemas\Schema;

class EksemplarBukusRelationManager extends RelationManager
{
    protected static string $relationship = 'eksemplarBukus';

    protected static ?string $recordTitleAttribute = 'kode_eksemplar';
    protected static ?string $title = 'Eksemplar Buku';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('kode_eksemplar')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Select::make('status')
                    ->options([
                        'tersedia' => 'Tersedia',
                        'dipinjam' => 'Dipinjam',
                        'rusak' => 'Rusak',
                        'hilang' => 'Hilang',
                    ])
                    ->default('tersedia')
                    ->required(),
                Select::make('kondisi_fisik')
                    ->options([
                        'baik' => 'Baik',
                        'rusak_ringan' => 'Rusak Ringan',
                        'rusak_berat' => 'Rusak Berat',
                    ])
                    ->default('baik')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('kode_eksemplar')
            ->columns([
                TextColumn::make('kode_eksemplar')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'tersedia' => 'success',
                        'dipinjam' => 'warning',
                        'rusak' => 'danger',
                        'hilang' => 'gray',
                        default => 'primary',
                    }),
                TextColumn::make('kondisi_fisik'),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                Action::make('cetakBarcodeSemua')
                    ->label('Cetak Barcode (Semua)')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn () => route('perpustakaan.cetak-barcode', ['buku' => $this->getOwnerRecord()]))
                    ->openUrlInNewTab(),
            ])
            ->actions([
                Action::make('cetakBarcode')
                    ->label('Cetak Barcode')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->url(fn ($record) => route('perpustakaan.cetak-barcode-eksemplar', ['eksemplar' => $record]))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('cetakBarcodeTerpilih')
                        ->label('Cetak Barcode')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $sessionKey = 'cetak_barcode_ids_' . uniqid();
                            session()->put($sessionKey, $records->pluck('id')->toArray());
                            return redirect()->to(route('perpustakaan.cetak-barcode-massal', ['session_key' => $sessionKey]));
                        }),
                ]),
            ]);
    }
}

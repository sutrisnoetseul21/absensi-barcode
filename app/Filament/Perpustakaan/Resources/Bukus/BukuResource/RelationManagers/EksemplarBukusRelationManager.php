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
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
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
                CreateAction::make(),
                Action::make('generateMassal')
                    ->label('Generate Massal')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('success')
                    ->form([
                        TextInput::make('prefix')
                            ->label('Prefix')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('Contoh: MTK7-'),
                        TextInput::make('mulai_dari')
                            ->label('Mulai Dari Angka')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->minValue(1),
                        TextInput::make('jumlah')
                            ->label('Jumlah Generate')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(500),
                        TextInput::make('panjang_digit')
                            ->label('Panjang Digit Angka')
                            ->numeric()
                            ->default(3)
                            ->required()
                            ->minValue(1)
                            ->maxValue(10),
                    ])
                    ->action(function (array $data, Action $action) {
                        $prefix = $data['prefix'];
                        $mulaiDari = (int) $data['mulai_dari'];
                        $jumlah = (int) $data['jumlah'];
                        $panjangDigit = (int) $data['panjang_digit'];

                        $codes = [];
                        for ($i = $mulaiDari; $i < $mulaiDari + $jumlah; $i++) {
                            $codes[] = $prefix . str_pad((string)$i, $panjangDigit, '0', STR_PAD_LEFT);
                        }

                        // Cek bentrok
                        $existingCodes = EksemplarBuku::whereIn('kode_eksemplar', $codes)->pluck('kode_eksemplar')->toArray();
                        if (count($existingCodes) > 0) {
                            $samples = array_slice($existingCodes, 0, 5);
                            $sampleStr = implode(', ', $samples);
                            if (count($existingCodes) > 5) {
                                $sampleStr .= '... dan lainnya';
                            }
                            
                            Notification::make()
                                ->danger()
                                ->title('Generate Dibatalkan: Kode Bentrok')
                                ->body('Terdapat kode eksemplar yang sudah ada di database: ' . $sampleStr)
                                ->send();
                                
                            $action->halt();
                        }

                        $now = now();
                        $ownerId = $this->getOwnerRecord()->id;
                        $inserts = [];

                        foreach ($codes as $code) {
                            $inserts[] = [
                                'id' => (string) Str::uuid(),
                                'buku_id' => $ownerId,
                                'kode_eksemplar' => $code,
                                'status' => 'tersedia',
                                'kondisi_fisik' => 'baik',
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }

                        try {
                            \Illuminate\Support\Facades\DB::transaction(function () use ($inserts) {
                                foreach (array_chunk($inserts, 500) as $chunk) {
                                    EksemplarBuku::insert($chunk);
                                }
                            });
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Gagal Generate Eksemplar')
                                ->body('Terjadi kesalahan saat menyimpan data. Kemungkinan ada proses lain yang membuat kode serupa secara bersamaan, silakan coba lagi.')
                                ->send();
                            
                            $action->halt();
                        }

                        Notification::make()
                            ->success()
                            ->title('Berhasil Generate')
                            ->body("Berhasil membuat {$jumlah} eksemplar buku.")
                            ->send();
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, $record) {
                        if ($record->peminjamans()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Gagal Menghapus')
                                ->body('Eksemplar ini tidak dapat dihapus karena memiliki riwayat peminjaman.')
                                ->send();
                            $action->halt();
                        }
                    }),
                ForceDeleteAction::make()
                    ->before(function (ForceDeleteAction $action, $record) {
                        if ($record->peminjamans()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Gagal Menghapus Permanen')
                                ->body('Eksemplar ini tidak dapat dihapus permanen karena memiliki riwayat peminjaman.')
                                ->send();
                            $action->halt();
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    \Filament\Tables\Actions\BulkAction::make('cetakBarcodeTerpilih')
                        ->label('Cetak Barcode')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $sessionKey = 'cetak_barcode_ids_' . uniqid();
                            session()->put($sessionKey, $records->pluck('id')->toArray());
                            return redirect()->to(route('perpustakaan.cetak-barcode-massal', ['session_key' => $sessionKey]));
                        }),
                    DeleteBulkAction::make()
                        ->before(function (DeleteBulkAction $action, $records) {
                            foreach ($records as $record) {
                                if ($record->peminjamans()->exists()) {
                                    Notification::make()
                                        ->danger()
                                        ->title('Penghapusan Massal Gagal')
                                        ->body("Eksemplar '{$record->kode_eksemplar}' tidak dapat dihapus karena memiliki riwayat peminjaman.")
                                        ->send();
                                    $action->halt();
                                }
                            }
                        }),
                    ForceDeleteBulkAction::make()
                        ->before(function (ForceDeleteBulkAction $action, $records) {
                            foreach ($records as $record) {
                                if ($record->peminjamans()->exists()) {
                                    Notification::make()
                                        ->danger()
                                        ->title('Penghapusan Permanen Massal Gagal')
                                        ->body("Eksemplar '{$record->kode_eksemplar}' tidak dapat dihapus permanen karena memiliki riwayat peminjaman.")
                                        ->send();
                                    $action->halt();
                                }
                            }
                        }),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}

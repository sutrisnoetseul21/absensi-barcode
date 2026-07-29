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
                Action::make('tambahEksemplar')
                    ->label('Tambah Eksemplar')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->form([
                        Forms\Components\Toggle::make('input_manual')
                            ->label('Input Manual')
                            ->default(false)
                            ->live(),
                        TextInput::make('prefix')
                            ->label('Prefix')
                            ->default('UMM')
                            ->placeholder('Contoh: PAI')
                            ->required(fn ($get) => !$get('input_manual'))
                            ->maxLength(50)
                            ->visible(fn ($get) => !$get('input_manual')),
                        TextInput::make('jumlah')
                            ->label('Jumlah Generate')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required(fn ($get) => !$get('input_manual'))
                            ->visible(fn ($get) => !$get('input_manual')),
                        TextInput::make('kode_eksemplar')
                            ->label('Kode Eksemplar')
                            ->required(fn ($get) => $get('input_manual'))
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->visible(fn ($get) => $get('input_manual')),
                    ])
                    ->action(function (array $data, Action $action) {
                        $ownerId = $this->getOwnerRecord()->id;
                        $now = now();
                        $inserts = [];

                        if ($data['input_manual'] ?? false) {
                            $inserts[] = [
                                'id' => (string) Str::uuid(),
                                'buku_id' => $ownerId,
                                'kode_eksemplar' => $data['kode_eksemplar'],
                                'status' => 'tersedia',
                                'kondisi_fisik' => 'baik',
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        } else {
                            $prefix = $data['prefix'];
                            $jumlah = (int) $data['jumlah'];
                            
                            try {
                                $codes = EksemplarBuku::generateKodeEksemplar($prefix, $jumlah);
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Gagal Generate')
                                    ->body($e->getMessage())
                                    ->send();
                                $action->halt();
                            }
                            
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
                                ->title('Gagal Menyimpan Eksemplar')
                                ->body('Terjadi kesalahan saat menyimpan data.')
                                ->send();
                            
                            $action->halt();
                        }

                        Notification::make()
                            ->success()
                            ->title('Berhasil')
                            ->body("Berhasil menambahkan " . count($inserts) . " eksemplar buku.")
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
                    BulkAction::make('cetakBarcodeTerpilih')
                        ->label('Cetak Barcode')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $sessionKey = 'cetak_barcode_ids_' . uniqid();
                            session()->put($sessionKey, $records->pluck('id')->toArray());
                            return redirect()->to(route('perpustakaan.cetak-barcode-massal', ['session_key' => $sessionKey]));
                        }),
                    BulkAction::make('hapusTerpilih')
                        ->label('Hapus Terpilih')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $failedCodes = [];
                            $deletedCount = 0;
                            
                            foreach ($records as $record) {
                                if ($record->status !== 'tersedia') {
                                    $failedCodes[] = "{$record->kode_eksemplar} (Status: {$record->status})";
                                    continue;
                                }
                                if ($record->peminjamans()->exists()) {
                                    $failedCodes[] = "{$record->kode_eksemplar} (Pernah/sedang dipinjam)";
                                    continue;
                                }
                                
                                $record->delete();
                                $deletedCount++;
                            }
                            
                            if (count($failedCodes) > 0) {
                                Notification::make()
                                    ->danger()
                                    ->title('Sebagian Gagal Dihapus')
                                    ->body("Gagal menghapus eksemplar berikut:\n" . implode("\n", $failedCodes))
                                    ->send();
                            }
                            
                            if ($deletedCount > 0) {
                                Notification::make()
                                    ->success()
                                    ->title('Berhasil Dihapus')
                                    ->body("Berhasil menghapus {$deletedCount} eksemplar.")
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
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

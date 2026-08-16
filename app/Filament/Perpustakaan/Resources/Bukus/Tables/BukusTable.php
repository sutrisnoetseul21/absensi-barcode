<?php

namespace App\Filament\Perpustakaan\Resources\Bukus\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BukusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount([
                'eksemplarBukus as jumlah_eksemplar',
                'eksemplarBukus as jumlah_tersedia' => fn ($q) => $q->where('status', 'tersedia')
            ]))
            ->columns([
                ImageColumn::make('sampul_buku')
                    ->label('Sampul')
                    ->disk('public')
                    ->square()
                    ->size(48)
                    ->defaultImageUrl(url('images/book-placeholder.png')),
                IconColumn::make('file_pdf')
                    ->label('E-Book')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-text')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->getStateUsing(fn ($record) => !empty($record->file_pdf)),
                TextColumn::make('judul')
                    ->searchable(query: function ($query, string $search) {
                        return $query->where('judul', 'like', "%{$search}%")
                            ->orWhereHas('eksemplarBukus', fn ($q) => $q->where('kode_eksemplar', $search));
                    }),
                TextColumn::make('penulis')
                    ->label('Pengarang')
                    ->searchable(),
                TextColumn::make('kategoriBuku.nama_kategori')
                    ->label('Koleksi')
                    ->sortable(),
                TextColumn::make('call_number')
                    ->label('Call Number')
                    ->getStateUsing(fn ($record) => $record->call_number)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('jumlah_eksemplar')
                    ->label('Total Eksemplar')
                    ->sortable(),
                TextColumn::make('jumlah_tersedia')
                    ->label('Tersedia')
                    ->sortable(),
                TextColumn::make('isbn')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // TrashedFilter is removed because it conflicts with the 'Sampah / Dihapus' Tab
            ])
            ->recordActions([
                Action::make('bacaPdfModal')
                    ->label('Baca E-Book')
                    ->icon('heroicon-o-book-open')
                    ->color('success')
                    ->modalHeading(fn ($record) => "Baca E-Book: {$record->judul}")
                    ->modalWidth('6xl')
                    ->modalContent(fn ($record) => new \Illuminate\Support\HtmlString('
                        <div class="w-full h-[70vh] min-h-[500px] bg-slate-950 rounded-xl overflow-hidden shadow-2xl border border-slate-800">
                            <iframe src="' . route('perpustakaan.baca-buku', $record) . '" class="w-full h-full border-0"></iframe>
                        </div>
                    '))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->visible(fn ($record) => !empty($record->file_pdf)),
                Action::make('cetakLabelSpine')
                    ->label('Cetak Label Spine')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('jumlah_cetak')
                            ->label('Jumlah Label yang Dicetak')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(fn ($record) => max($record->eksemplarBukus()->count(), 1))
                            ->default(fn ($record) => max($record->eksemplarBukus()->count(), 1))
                    ])
                    ->action(function ($record, array $data) {
                        return redirect()->to(route('perpustakaan.cetak-label-spine', ['buku' => $record, 'jumlah' => $data['jumlah_cetak']]));
                    }),
                Action::make('cetakBarcode')
                    ->label('Cetak Barcode')
                    ->icon('heroicon-o-qr-code')
                    ->color('success')
                    ->url(fn ($record) => route('perpustakaan.cetak-barcode', ['buku' => $record]))
                    ->openUrlInNewTab(),
                \Filament\Actions\ViewAction::make(),
                EditAction::make()
                    ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_perpustakaan_editor') || auth()->user()?->hasRole('admin_master_editor')),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_perpustakaan_editor') || auth()->user()?->hasRole('admin_master_editor'))
                    ->before(function (DeleteAction $action, $record) {
                        // Tolak jika ada eksemplar yang sedang dipinjam
                        if ($record->eksemplarBukus()->where('status', 'dipinjam')->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Gagal Menghapus')
                                ->body('Buku ini tidak dapat dihapus karena ada eksemplar yang sedang dipinjam oleh siswa.')
                                ->send();
                            $action->halt();
                        }

                        // Tolak jika ada eksemplar yang memiliki riwayat peminjaman
                        if ($record->eksemplarBukus()->whereHas('peminjamans')->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Gagal Menghapus')
                                ->body('Buku ini tidak dapat dihapus karena salah satu eksemplarnya memiliki riwayat peminjaman di masa lalu.')
                                ->send();
                            $action->halt();
                        }
                    })
                    ->after(function ($record) {
                        // Soft delete eksemplar dan update status inventaris
                        $record->eksemplarBukus()->delete();
                        \App\Models\InventarisBuku::where('buku_id', $record->id)->update(['status' => 'dibatalkan']);
                    }),
                RestoreAction::make()
                    ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_perpustakaan_editor') || auth()->user()?->hasRole('admin_master_editor'))
                    ->after(function ($record) {
                        // Restore eksemplar dan update status inventaris menjadi aktif
                        $record->eksemplarBukus()->withTrashed()->restore();
                        \App\Models\InventarisBuku::where('buku_id', $record->id)->update([
                            'status' => 'aktif',
                            'alasan_pembatalan' => null,
                        ]);
                    }),
                ForceDeleteAction::make()
                    ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_perpustakaan_editor') || auth()->user()?->hasRole('admin_master_editor'))
                    ->before(function (ForceDeleteAction $action, $record) {
                        if ($record->eksemplarBukus()->whereHas('peminjamans')->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Gagal Menghapus Permanen')
                                ->body('Buku ini tidak dapat dihapus permanen karena salah satu eksemplarnya memiliki riwayat peminjaman.')
                                ->send();
                            $action->halt();
                        }
                    })
                    ->after(function ($record) {
                        // Hapus fisik eksemplar dan inventaris terkait
                        $record->eksemplarBukus()->withTrashed()->forceDelete();
                        \App\Models\InventarisBuku::where('buku_id', $record->id)->delete();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('cetakBarcodeTerpilih')
                        ->label('Cetak Barcode')
                        ->icon('heroicon-o-qr-code')
                        ->color('success')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $eksemplarIds = \App\Models\EksemplarBuku::whereIn('buku_id', $records->pluck('id'))->pluck('id')->toArray();
                            if (empty($eksemplarIds)) {
                                Notification::make()->warning()->title('Tidak ada eksemplar')->body('Buku yang dipilih belum memiliki eksemplar.')->send();
                                return;
                            }
                            $sessionKey = 'cetak_barcode_ids_' . uniqid();
                            session()->put($sessionKey, $eksemplarIds);
                            return redirect()->to(route('perpustakaan.cetak-barcode-massal', ['session_key' => $sessionKey]));
                        }),
                    BulkAction::make('cetakLabelSpineTerpilih')
                        ->label('Cetak Label Spine')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $eksemplarIds = \App\Models\EksemplarBuku::whereIn('buku_id', $records->pluck('id'))->pluck('id')->toArray();
                            if (empty($eksemplarIds)) {
                                Notification::make()->warning()->title('Tidak ada eksemplar')->body('Buku yang dipilih belum memiliki eksemplar.')->send();
                                return;
                            }
                            $sessionKey = 'cetak_label_spine_ids_' . uniqid();
                            session()->put($sessionKey, $eksemplarIds);
                            return redirect()->to(route('perpustakaan.cetak-label-spine-massal', ['session_key' => $sessionKey]));
                        }),
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_perpustakaan_editor') || auth()->user()?->hasRole('admin_master_editor'))
                        ->before(function (DeleteBulkAction $action, $records) {
                            foreach ($records as $record) {
                                if ($record->eksemplarBukus()->whereHas('peminjamans')->exists()) {
                                    Notification::make()
                                        ->danger()
                                        ->title('Penghapusan Massal Gagal')
                                        ->body("Buku '{$record->judul}' tidak dapat dihapus karena memiliki riwayat peminjaman.")
                                        ->send();
                                    $action->halt();
                                }
                            }
                        }),
                    ForceDeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_perpustakaan_editor') || auth()->user()?->hasRole('admin_master_editor'))
                        ->before(function (ForceDeleteBulkAction $action, $records) {
                            foreach ($records as $record) {
                                if ($record->eksemplarBukus()->whereHas('peminjamans')->exists()) {
                                    Notification::make()
                                        ->danger()
                                        ->title('Penghapusan Permanen Massal Gagal')
                                        ->body("Buku '{$record->judul}' tidak dapat dihapus permanen karena memiliki riwayat peminjaman.")
                                        ->send();
                                    $action->halt();
                                }
                            }
                        }),
                    RestoreBulkAction::make()
                        ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_perpustakaan_editor') || auth()->user()?->hasRole('admin_master_editor')),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources;

use App\Models\Siswa;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class SiswaMutasiResource extends Resource
{
    protected static ?string $model = Siswa::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowRightOnRectangle;

    protected static ?string $navigationLabel = 'Siswa Mutasi';

    protected static ?string $modelLabel = 'Siswa Mutasi';

    protected static ?string $pluralModelLabel = 'Siswa Mutasi';

    protected static ?string $slug = 'siswa-mutasi';

    protected static string|\UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?int $navigationSort = 7;

    /**
     * Hanya Superadmin yang boleh akses menu ini.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'mutasi'))
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(url('https://ui-avatars.com/api/?name=Siswa&color=7F9CF5&background=EBF4FF')),

                TextColumn::make('nisn')
                    ->label('NISN')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kelas_terakhir')
                    ->label('Kelas Terakhir')
                    ->getStateUsing(function (Siswa $record) {
                        $lastEnrollment = $record->enrollments()
                            ->with('kelas', 'tahunAjaran')
                            ->latest()
                            ->first();
                        if (!$lastEnrollment) return '—';
                        return ($lastEnrollment->kelas?->name ?? '—')
                            . ' (TA ' . ($lastEnrollment->tahunAjaran?->name ?? '—') . ')';
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color('warning'),
            ])
            ->recordActions([
                // Kembalikan ke Aktif jika siswa kembali masuk
                Action::make('aktifkan_kembali')
                    ->label('Aktifkan Kembali')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aktifkan Kembali Siswa')
                    ->modalDescription('Siswa ini akan dikembalikan ke status Aktif. Jika sebelumnya ada kelas aktif yang ditandai "pindah", kelas tersebut juga akan dikembalikan menjadi aktif.')
                    ->action(function (Siswa $record) {
                        $record->update(['status' => 'aktif']);
                        
                        // Kembalikan status enrollment yang pindah menjadi aktif
                        $record->enrollments()->where('status', 'pindah')->update(['status' => 'aktif']);

                        Notification::make()
                            ->title('Siswa Diaktifkan Kembali')
                            ->body("Siswa **{$record->name}** telah dikembalikan ke daftar Siswa Aktif.")
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\SiswaMutasiResource\Pages\ListSiswaMutasi::route('/'),
        ];
    }
}

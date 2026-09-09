<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpikapLaporanResource\Pages;
use App\Models\SpikapLaporan;
use App\Models\SpikapLogStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class SpikapLaporanResource extends Resource
{
    protected static ?string $model = SpikapLaporan::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static string|\UnitEnum|null $navigationGroup = 'SPIKAP';

    protected static ?string $navigationLabel = 'Laporan Kasus';

    protected static ?string $pluralModelLabel = 'Laporan Kasus SPIKAP';

    protected static ?string $modelLabel = 'Laporan SPIKAP';

    protected static ?string $slug = 'spikap/laporan';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin()
            || $user->hasRole('super_admin')
            || $user->hasRole('spikap_admin');
    }

    public static function canView(Model $record): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin()
            || $user->hasRole('super_admin')
            || $user->hasRole('spikap_admin');
    }

    public static function canCreate(): bool
    {
        // Laporan hanya dikirimkan oleh siswa melalui formulir Portal Siswa
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        // Pengeditan substansi kasus tidak diizinkan; perubahan status dilakukan lewat aksi audit trail
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin()
            || $user->hasRole('super_admin')
            || $user->hasRole('spikap_admin');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ringkasan Insiden')
                    ->description('Status dan klasifikasi laporan yang dikirimkan oleh siswa.')
                    ->columns(3)
                    ->schema([
                        Placeholder::make('id_label')
                            ->label('Nomor Laporan')
                            ->content(fn ($record) => $record ? "#{$record->id}" : '-'),

                        Placeholder::make('sifat_badge')
                            ->label('Sifat Laporan')
                            ->content(fn ($record) => $record ? new HtmlString(
                                $record->sifat_laporan === 'darurat'
                                    ? '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">🚨 Darurat</span>'
                                    : '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300">Biasa</span>'
                            ) : '-'),

                        Placeholder::make('status_badge')
                            ->label('Status Penanganan')
                            ->content(fn ($record) => $record ? new HtmlString(
                                match ($record->status) {
                                    'diterima' => '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">Diterima</span>',
                                    'dalam_investigasi' => '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300">Dalam Investigasi</span>',
                                    'selesai' => '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">Selesai</span>',
                                    default => e($record->status),
                                }
                            ) : '-'),

                        Placeholder::make('jenis_perundungan_label')
                            ->label('Jenis Perundungan')
                            ->content(fn ($record) => $record?->label_jenis ?? '-'),

                        Placeholder::make('waktu_kejadian_label')
                            ->label('Waktu Kejadian')
                            ->content(fn ($record) => $record?->waktu_kejadian ? $record->waktu_kejadian->format('d M Y, H:i') : 'Tidak dicantumkan'),

                        Placeholder::make('lokasi_kejadian_label')
                            ->label('Lokasi Kejadian')
                            ->content(fn ($record) => $record?->lokasi_kejadian ?: 'Tidak dicantumkan'),

                        Placeholder::make('tujuan_penerima_label')
                            ->label('Tujuan Penerima Awal')
                            ->content(fn ($record) => $record?->sifat_laporan === 'darurat'
                                ? 'Wali Kelas & Kepala Sekolah (Otomatis)'
                                : match ($record?->tujuan_penerima) {
                                    'guru_bk' => 'Guru BK',
                                    'wali_kelas' => 'Wali Kelas',
                                    default => '-',
                                }),

                        Placeholder::make('last_handled_by_label')
                            ->label('Terakhir Ditangani Oleh')
                            ->content(fn ($record) => $record?->lastHandledBy?->nama_guru ?: 'Belum ditangani'),

                        Placeholder::make('created_at_label')
                            ->label('Waktu Dikirimkan')
                            ->content(fn ($record) => $record?->created_at ? $record->created_at->format('d M Y, H:i') : '-'),
                    ]),

                Section::make('Identitas Siswa Pelapor')
                    ->description('Informasi lengkap siswa yang mengirimkan laporan ini.')
                    ->columns(2)
                    ->schema([
                        Placeholder::make('siswa_nama')
                            ->label('Nama Siswa')
                            ->content(fn ($record) => $record?->siswa?->name ?? '-'),

                        Placeholder::make('siswa_nisn')
                            ->label('NISN / NIS')
                            ->content(fn ($record) => ($record?->siswa?->nisn ?? '-') . ' / ' . ($record?->siswa?->nis ?? '-')),

                        Placeholder::make('siswa_kelas')
                            ->label('Kelas / Rombel Aktif')
                            ->content(fn ($record) => $record?->siswa?->enrollmentAktif?->kelas?->name ?? 'Belum terdaftar di rombel aktif'),

                        Placeholder::make('siswa_kontak')
                            ->label('Kontak Siswa & Orang Tua')
                            ->content(fn ($record) => new HtmlString(
                                '<div><strong>HP Siswa:</strong> ' . ($record?->siswa?->no_hp ? '<a href="https://wa.me/' . $record->siswa->no_hp . '" target="_blank" class="text-primary-600 hover:underline">+' . e($record->siswa->no_hp) . '</a>' : '-') . '</div>' .
                                '<div class="mt-1"><strong>HP Orang Tua:</strong> ' . ($record?->siswa?->no_hp_orang_tua ? '<a href="https://wa.me/' . $record->siswa->no_hp_orang_tua . '" target="_blank" class="text-primary-600 hover:underline">+' . e($record->siswa->no_hp_orang_tua) . '</a>' : '-') . '</div>'
                            )),
                    ]),

                Section::make('Uraian Kronologi Kejadian')
                    ->description('Penjelasan rinci yang dituliskan langsung oleh siswa.')
                    ->schema([
                        Placeholder::make('uraian_kejadian_content')
                            ->label('')
                            ->content(fn ($record) => new HtmlString(
                                '<div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-900 dark:text-white leading-relaxed whitespace-pre-line">' .
                                e($record?->uraian_kejadian ?? '') .
                                '</div>'
                            )),
                    ]),

                Section::make('Berkas Bukti Lampiran')
                    ->description('Foto dan/atau video yang diunggah pelapor sebagai bukti kejadian.')
                    ->schema([
                        Placeholder::make('lampiran_component')
                            ->label('')
                            ->content(fn ($record) => new HtmlString(
                                view('filament.resources.spikap.lampiran-viewer', ['record' => $record])->render()
                            )),
                    ]),

                Section::make('Histori Penanganan & Audit Trail')
                    ->description('Catatan tindak lanjut dan riwayat perubahan status oleh tim sekolah.')
                    ->schema([
                        Placeholder::make('timeline_component')
                            ->label('')
                            ->content(fn ($record) => new HtmlString(
                                view('filament.resources.spikap.timeline-viewer', ['record' => $record])->render()
                            )),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->formatStateUsing(fn ($state) => "#{$state}")
                    ->sortable()
                    ->width('70px'),

                TextColumn::make('created_at')
                    ->label('Waktu Lapor')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('sifat_laporan')
                    ->label('Sifat')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'darurat' => 'danger',
                        default   => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'darurat' => '🚨 Darurat',
                        default   => 'Biasa',
                    })
                    ->sortable(),

                TextColumn::make('siswa.name')
                    ->label('Siswa Pelapor')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (SpikapLaporan $record): string => 'NISN: ' . ($record->siswa?->nisn ?? '-') . ' • Kelas: ' . ($record->siswa?->enrollmentAktif?->kelas?->name ?? '-')),

                TextColumn::make('jenis_perundungan')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'fisik'   => 'danger',
                        'verbal'  => 'warning',
                        'sosial'  => 'purple',
                        'digital' => 'info',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn (SpikapLaporan $record): string => $record->label_jenis),

                TextColumn::make('tujuan_penerima')
                    ->label('Tujuan Awal')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (?string $state, SpikapLaporan $record): string => $record->sifat_laporan === 'darurat'
                        ? 'Wali Kelas & KS'
                        : match ($state) {
                            'guru_bk'    => 'Guru BK',
                            'wali_kelas' => 'Wali Kelas',
                            default      => '-',
                        }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'diterima'          => 'warning',
                        'dalam_investigasi' => 'info',
                        'selesai'           => 'success',
                        default             => 'gray',
                    })
                    ->formatStateUsing(fn (SpikapLaporan $record): string => $record->label_status)
                    ->sortable(),

                TextColumn::make('lampiran_count')
                    ->counts('lampiran')
                    ->label('Bukti')
                    ->badge()
                    ->color('gray')
                    ->icon('heroicon-o-paper-clip'),

                TextColumn::make('lastHandledBy.nama_guru')
                    ->label('Ditangani Oleh')
                    ->placeholder('Belum ada')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('sifat_laporan')
                    ->label('Sifat Laporan')
                    ->options([
                        'biasa'   => 'Biasa',
                        'darurat' => '🚨 Darurat',
                    ]),

                SelectFilter::make('status')
                    ->label('Status Penanganan')
                    ->options([
                        'diterima'          => 'Diterima',
                        'dalam_investigasi' => 'Dalam Investigasi',
                        'selesai'           => 'Selesai',
                    ]),

                SelectFilter::make('jenis_perundungan')
                    ->label('Kategori Kasus')
                    ->options([
                        'fisik'   => 'Fisik',
                        'verbal'  => 'Verbal',
                        'sosial'  => 'Sosial',
                        'digital' => 'Digital / Cyberbullying',
                        'lainnya' => 'Lainnya',
                    ]),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')->label('Dari Tanggal'),
                        DatePicker::make('created_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Lihat'),

                Action::make('tindak_lanjut')
                    ->label('Tindak Lanjut')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->visible(fn () => auth()->user()?->isSuperAdmin()
                        || auth()->user()?->hasRole('super_admin')
                        || auth()->user()?->hasRole('spikap_admin')
                        || auth()->user()?->can('spikap.update_status'))
                    ->form([
                        Select::make('status')
                            ->label('Ubah Status Penanganan')
                            ->options([
                                'diterima'          => 'Diterima',
                                'dalam_investigasi' => 'Dalam Investigasi',
                                'selesai'           => 'Selesai',
                            ])
                            ->default(fn (SpikapLaporan $record) => $record->status)
                            ->required(),

                        Textarea::make('catatan')
                            ->label('Catatan Tindak Lanjut & Penanganan')
                            ->placeholder('Jelaskan langkah investigasi, mediasi, atau hasil penyelesaian kasus...')
                            ->required()
                            ->rows(4),
                    ])
                    ->action(function (SpikapLaporan $record, array $data) {
                        $user = auth()->user();
                        $oldStatus = $record->status;
                        $newStatus = $data['status'];
                        $catatan = $data['catatan'];

                        $updateData = ['status' => $newStatus];
                        if ($user->teacher) {
                            $updateData['last_handled_by'] = $user->teacher->id;
                        }
                        $record->update($updateData);

                        SpikapLogStatus::catatGuru(
                            $record->id,
                            $oldStatus,
                            $newStatus,
                            $catatan,
                            $user->id
                        );

                        try {
                            app(\App\Services\SpikapNotificationService::class)->sendStatusUpdateNotificationToSiswaDanOrtu(
                                $record,
                                $newStatus,
                                $catatan
                            );
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error("SPIKAP: gagal kirim WA status update dari Filament: " . $e->getMessage());
                        }

                        Notification::make()
                            ->success()
                            ->title('Status Laporan Berhasil Diperbarui')
                            ->body("Status laporan #{$record->id} kini tercatat sebagai " . ucfirst(str_replace('_', ' ', $newStatus)))
                            ->send();
                    }),

                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->isSuperAdmin()
                        || auth()->user()?->hasRole('super_admin')
                        || auth()->user()?->hasRole('spikap_admin')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->isSuperAdmin()
                            || auth()->user()?->hasRole('super_admin')
                            || auth()->user()?->hasRole('spikap_admin')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getEloquentQuery(): Builder
    {
        // Admin & Super Admin memiliki akses pengawasan penuh atas seluruh laporan
        // dari semua kelas/tipe kasus, sehingga query menampilkan seluruh data tanpa scoping role guru.
        return parent::getEloquentQuery()
            ->with(['siswa.enrollmentAktif.kelas', 'lastHandledBy', 'lampiran']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSpikapLaporans::route('/'),
            'view'  => Pages\ViewSpikapLaporan::route('/{record}'),
        ];
    }
}

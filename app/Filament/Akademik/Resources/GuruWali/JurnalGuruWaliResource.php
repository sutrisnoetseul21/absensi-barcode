<?php

namespace App\Filament\Akademik\Resources\GuruWali;

use App\Enums\KategoriSentimen;
use App\Filament\Akademik\Concerns\InteractsWithGuruWaliPeriod;
use App\Filament\Akademik\Resources\GuruWali\Pages;
use App\Models\JurnalGuruWali;
use App\Models\KelompokGuruWali;
use App\Models\TahunAjaran;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class JurnalGuruWaliResource extends Resource
{
    protected static ?string $model = JurnalGuruWali::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Log Guru Wali';

    protected static ?string $modelLabel = 'Log Guru Wali';

    protected static ?string $pluralModelLabel = 'Log Guru Wali';

    protected static ?string $slug = 'guru-wali/jurnal';

    protected static string|\UnitEnum|null $navigationGroup = 'Guru Wali';

    protected static ?int $navigationSort = 4;

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin() || $user->hasRole(['super_admin', 'admin_portal_akademik'])) {
            return true;
        }

        if ($user->can('portal_guru:supervisi_jurnal')) {
            return true;
        }

        return $user->hasAnyRole([
            'admin_akademik_viewer',
            'admin_akademik_editor',
            'admin_master_viewer',
            'admin_master_editor',
        ]);
    }

    public static function canView(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->isSuperAdmin() || $user->hasRole(['super_admin', 'admin_akademik_editor']);
    }

    public static function canDeleteAny(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->isSuperAdmin() || $user->hasRole(['super_admin', 'admin_akademik_editor']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Sesi Pendampingan')
                    ->description('Rincian pelaksanaan pembinaan siswa oleh Guru Wali.')
                    ->columns(3)
                    ->schema([
                        Placeholder::make('guru_nama')
                            ->label('Guru Wali')
                            ->content(fn ($record) => $record?->guru?->name ?? '-'),

                        Placeholder::make('kelompok_nama')
                            ->label('Kelompok Binaan')
                            ->content(fn ($record) => $record?->kelompok?->nama_kelompok ?? '-'),

                        Placeholder::make('siswa_nama')
                            ->label('Siswa Binaan')
                            ->content(fn ($record) => $record?->siswa
                                ? "{$record->siswa->name} (" . ($record->siswa->nisn ?: '-') . ')'
                                : '-'),

                        Placeholder::make('kelas_nama')
                            ->label('Kelas')
                            ->content(fn ($record) => $record?->kelas?->name ?? $record?->siswa?->getRombelName($record?->academic_year_id) ?? '-'),

                        Placeholder::make('tanggal_waktu_label')
                            ->label('Tanggal & Waktu')
                            ->content(fn ($record) => $record?->tanggal_waktu?->translatedFormat('l, d F Y H:i') ?? '-'),

                        Placeholder::make('jenis_pendampingan')
                            ->label('Jenis & Pilar')
                            ->content(fn ($record) => ($record?->jenis_pendampingan ?? '-') . ' — ' . ($record?->kategori_pendampingan ?? '-')),

                        Placeholder::make('status_sesi')
                            ->label('Status Sesi')
                            ->content(fn ($record) => $record?->status_sesi ?? '-'),

                        Placeholder::make('rujukan_kolaborasi')
                            ->label('Kolaborasi / Rujukan')
                            ->content(fn ($record) => $record?->rujukan_kolaborasi ?? 'Mandiri'),

                        Placeholder::make('privasi_dan_sentimen')
                            ->label('Sentimen & Sifat Catatan')
                            ->content(function ($record) {
                                $sentimen = $record?->kategori_sentimen instanceof \BackedEnum
                                    ? $record->kategori_sentimen->value
                                    : ($record?->kategori_sentimen ?? '-');
                                $sifat = $record?->is_public_note ? 'Publik (Tampil ke Siswa)' : 'Privat (Supervisi & Guru Wali)';

                                return "{$sentimen} · {$sifat}";
                            }),
                    ]),

                Section::make('Catatan Pembahasan & Tindak Lanjut')
                    ->columns(1)
                    ->schema([
                        Placeholder::make('uraian_pembahasan')
                            ->label('Uraian Pembahasan / Kejadian')
                            ->content(fn ($record) => new HtmlString(
                                '<div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200 whitespace-pre-line leading-relaxed">' .
                                e($record?->uraian_pembahasan ?: 'Tidak ada uraian.') .
                                '</div>'
                            )),

                        Placeholder::make('rencana_tindak_lanjut')
                            ->label('Rencana Tindak Lanjut')
                            ->content(fn ($record) => new HtmlString(
                                '<div class="p-4 rounded-xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-900/40 text-amber-900 dark:text-amber-200 whitespace-pre-line leading-relaxed">' .
                                e($record?->rencana_tindak_lanjut ?: 'Belum ada rencana tindak lanjut.') .
                                '</div>'
                            )),
                    ]),

                Section::make('Keterkaitan Konsultasi, Badge & Rujukan BK')
                    ->columns(3)
                    ->schema([
                        Placeholder::make('badge_karakter')
                            ->label('Badge Karakter')
                            ->content(fn ($record) => $record?->badge
                                ? "{$record->badge->nama_badge}" . ($record->badge->catatan_apresiasi ? " — {$record->badge->catatan_apresiasi}" : '')
                                : 'Tidak ada badge disematkan'),

                        Placeholder::make('konsultasi_asal')
                            ->label('Asal Tiket Konsultasi Siswa')
                            ->content(function ($record) {
                                $k = $record?->konsultasi;
                                if (! $k) {
                                    return 'Diinput langsung oleh Guru Wali';
                                }
                                $rating = $k->student_feedback_rating ? " (Rating {$k->student_feedback_rating}/5)" : '';

                                return "{$k->topik_konsultasi}{$rating}";
                            }),

                        Placeholder::make('rujukan_bk_info')
                            ->label('Status Eskalasi Konseling BK')
                            ->content(function ($record) {
                                $bk = $record?->konselingBk;
                                if (! $bk) {
                                    return 'Tidak dirujuk ke BK';
                                }
                                $status = $bk->status_kasus instanceof \BackedEnum ? $bk->status_kasus->value : ($bk->status_kasus ?? '-');

                                return "Dirujuk ({$status}) — {$bk->topik_masalah}";
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal_waktu')
                    ->label('Tanggal & Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('guru.name')
                    ->label('Guru Wali')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kelompok.nama_kelompok')
                    ->label('Kelompok')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                TextColumn::make('siswa.name')
                    ->label('Siswa Binaan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kelas.name')
                    ->label('Kelas')
                    ->badge()
                    ->color('gray')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('tahunAjaran.name')
                    ->label('Tahun Ajaran')
                    ->badge()
                    ->color('gray')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('jenis_pendampingan')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Individu'       => 'primary',
                        'Kelompok Kecil' => 'warning',
                        'Klasikal'       => 'info',
                        default          => 'gray',
                    }),

                TextColumn::make('kategori_pendampingan')
                    ->label('Kategori 4 Pilar')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('kategori_sentimen')
                    ->label('Sentimen')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof KategoriSentimen ? $state->value : ($state ?: '-'))
                    ->color(function ($state) {
                        $val = $state instanceof KategoriSentimen ? $state->value : $state;

                        return match ($val) {
                            'Positif' => 'success',
                            'Negatif' => 'danger',
                            'Netral'  => 'gray',
                            default   => 'gray',
                        };
                    }),

                TextColumn::make('status_sesi')
                    ->label('Status Sesi')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Tuntas / Selesai'   => 'success',
                        'Dalam Pemantauan'   => 'warning',
                        'Bimbingan Lanjutan' => 'danger',
                        default              => 'gray',
                    }),

                TextColumn::make('rujukan_kolaborasi')
                    ->label('Rujukan')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Guru BK'          => 'danger',
                        'Wali Kelas'       => 'warning',
                        'Orang Tua / Wali' => 'info',
                        default            => 'gray',
                    }),

                TextColumn::make('is_public_note')
                    ->label('Privasi')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Publik' : 'Privat')
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_waktu', 'desc')
            ->filters([
                SelectFilter::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->placeholder('Semua Tahun Ajaran (Lintas Tahun)')
                    ->options(fn () => InteractsWithGuruWaliPeriod::getAllGuruWaliAcademicYears())
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;
                        if (! $value) {
                            return $query;
                        }

                        return $query->where('academic_year_id', $value);
                    }),

                SelectFilter::make('kelompok_id')
                    ->label('Kelompok / Guru Wali')
                    ->options(fn () => KelompokGuruWali::with('guru:id,name')
                        ->orderBy('nama_kelompok')
                        ->get()
                        ->mapWithKeys(fn ($k) => [$k->id => "{$k->nama_kelompok} — " . ($k->guru?->name ?? '-')])
                        ->toArray())
                    ->searchable(),

                SelectFilter::make('class_id')
                    ->label('Kelas Siswa')
                    ->relationship('kelas', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('kategori_pendampingan')
                    ->label('Kategori 4 Pilar')
                    ->options([
                        'Akademik'                => 'Akademik',
                        'Karakter & Kedisiplinan' => 'Karakter & Kedisiplinan',
                        'Minat & Bakat / Ekskul'  => 'Minat & Bakat / Ekskul',
                        'Sosial & Psikologis'     => 'Sosial & Psikologis',
                    ]),

                SelectFilter::make('status_sesi')
                    ->label('Status Sesi')
                    ->options([
                        'Tuntas / Selesai'   => 'Tuntas / Selesai',
                        'Dalam Pemantauan'   => 'Dalam Pemantauan',
                        'Bimbingan Lanjutan' => 'Bimbingan Lanjutan',
                    ]),

                SelectFilter::make('rujukan_kolaborasi')
                    ->label('Kolaborasi / Rujukan')
                    ->options([
                        'Mandiri'          => 'Mandiri',
                        'Wali Kelas'       => 'Wali Kelas',
                        'Guru BK'          => 'Guru BK',
                        'Orang Tua / Wali' => 'Orang Tua / Wali',
                    ]),

                Filter::make('tanggal_waktu')
                    ->form([
                        DatePicker::make('dari_tanggal')->label('Dari Tanggal'),
                        DatePicker::make('sampai_tanggal')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari_tanggal'] ?? null, fn ($q, $date) => $q->whereDate('tanggal_waktu', '>=', $date))
                            ->when($data['sampai_tanggal'] ?? null, fn ($q, $date) => $q->whereDate('tanggal_waktu', '<=', $date));
                    }),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('cetak_individual_pdf')
                    ->label('PDF Siswa')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn ($record) => route('akademik.guru-wali.cetak.individual.pdf', [
                        'kelompok_id'      => $record->kelompok_id,
                        'student_id'       => $record->student_id,
                        'academic_year_id' => $record->academic_year_id,
                        'periode'          => 'tahunan',
                    ]))
                    ->openUrlInNewTab(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJurnalGuruWalis::route('/'),
            'view'  => Pages\ViewJurnalGuruWali::route('/{record}'),
        ];
    }
}


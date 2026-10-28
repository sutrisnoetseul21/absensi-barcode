<?php

namespace App\Filament\Akademik\Resources;

use App\Filament\Akademik\Resources\JurnalPembelajaranResource\Pages;
use App\Models\Guru;
use App\Models\JurnalPembelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
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

class JurnalPembelajaranResource extends Resource
{
    protected static ?string $model = JurnalPembelajaran::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Jurnal Mengajar';

    protected static ?string $modelLabel = 'Jurnal Mengajar';

    protected static ?string $pluralModelLabel = 'Jurnal Mengajar';

    protected static ?string $slug = 'jurnal-mengajar';

    protected static string|\UnitEnum|null $navigationGroup = 'Akademik';

    protected static ?int $navigationSort = 8;

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        if ($user->isSuperAdmin() || $user->hasRole('super_admin')) {
            return true;
        }

        if ($user->can('portal_guru:supervisi_jurnal')) {
            return true;
        }

        return $user->hasAnyRole([
            'admin_akademik_viewer',
            'admin_akademik_editor',
            'admin_presensi_viewer',
            'admin_presensi_editor',
        ]);
    }

    public static function canView(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        // Jurnal mengajar hanya diinput oleh guru/pengampu melalui Portal Guru
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin() || $user->hasRole('super_admin') || $user->hasRole('admin_akademik_editor');
    }

    public static function canDelete(Model $record): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin() || $user->hasRole('super_admin') || $user->hasRole('admin_akademik_editor');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Sesi Pembelajaran')
                    ->description('Rincian pelaksanaan jam mengajar di kelas.')
                    ->columns(3)
                    ->schema([
                        Placeholder::make('guru_nama')
                            ->label('Guru Pengampu')
                            ->content(fn ($record) => $record?->teacher?->name ?? '-'),

                        Placeholder::make('mata_pelajaran')
                            ->label('Mata Pelajaran')
                            ->content(fn ($record) => $record?->mataPelajaran?->nama ?? '-'),

                        Placeholder::make('kelas_nama')
                            ->label('Kelas / Rombel')
                            ->content(fn ($record) => $record?->kelas?->name ?? '-'),

                        Placeholder::make('tanggal_label')
                            ->label('Tanggal Pelaksanaan')
                            ->content(fn ($record) => $record?->tanggal ? $record->tanggal->translatedFormat('l, d F Y') : '-'),

                        Placeholder::make('jam_ke_label')
                            ->label('Jam Pelajaran (JP)')
                            ->content(fn ($record) => $record?->jam_ke_label ?? '-'),

                        Placeholder::make('kehadiran_label')
                            ->label('Tingkat Kehadiran')
                            ->content(fn ($record) => $record ? new HtmlString(
                                '<div class="flex items-center gap-2">' .
                                '<span class="font-bold text-slate-900 dark:text-white">' . $record->total_hadir . ' / ' . $record->total_siswa . ' Siswa</span>' .
                                '<span class="px-2 py-0.5 rounded text-xs font-bold ' . ($record->persentase_kehadiran >= 90 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400') . '">' . $record->persentase_kehadiran . '%</span>' .
                                '</div>'
                            ) : '-'),
                    ]),

                Section::make('Materi & Refleksi Pembelajaran')
                    ->description('Substansi materi pokok dan evaluasi tindak lanjut guru.')
                    ->columns(1)
                    ->schema([
                        Placeholder::make('materi')
                            ->label('Materi Pokok / Bahasan')
                            ->content(fn ($record) => new HtmlString('<div class="font-bold text-base text-slate-900 dark:text-white">' . e($record?->materi ?? '-') . '</div>')),

                        Placeholder::make('uraian_materi')
                            ->label('Uraian Aktivitas Pembelajaran')
                            ->content(fn ($record) => new HtmlString(
                                '<div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200 whitespace-pre-line leading-relaxed">' .
                                e($record?->uraian_materi ?? 'Tidak ada uraian.') .
                                '</div>'
                            )),

                        Placeholder::make('refleksi')
                            ->label('Refleksi / Catatan Tindak Lanjut Guru')
                            ->content(fn ($record) => new HtmlString(
                                '<div class="p-4 rounded-xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-900/40 text-amber-900 dark:text-amber-200 whitespace-pre-line leading-relaxed">' .
                                e($record?->refleksi ?: 'Belum ada catatan refleksi.') .
                                '</div>'
                            )),
                    ]),

                Section::make('Presensi Siswa di Kelas')
                    ->description('Daftar status kehadiran setiap siswa pada sesi jam pembelajaran ini.')
                    ->schema([
                        Placeholder::make('presensi_table')
                            ->label('')
                            ->content(fn ($record) => $record ? new HtmlString(
                                view('filament.akademik.resources.jurnal.presensi-table', ['record' => $record])->render()
                            ) : 'Tidak ada data.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('teacher.name')
                    ->label('Guru Pengampu')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('kelas.name')
                    ->label('Kelas')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('mataPelajaran.nama_mapel')
                    ->label('Mata Pelajaran')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('jam_ke_label')
                    ->label('Jam Pelajaran')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('materi')
                    ->label('Materi Pokok')
                    ->limit(35)
                    ->tooltip(fn ($record) => $record->materi)
                    ->searchable(),

                TextColumn::make('kehadiran_rekap')
                    ->label('Kehadiran Siswa')
                    ->state(fn ($record) => "{$record->total_hadir}/{$record->total_siswa} ({$record->persentase_kehadiran}%)")
                    ->badge()
                    ->color(fn ($record) => $record->persentase_kehadiran >= 90 ? 'success' : 'warning'),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                SelectFilter::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->relationship('tahunAjaran', 'name')
                    ->default(fn () => TahunAjaran::where('status', 'aktif')->first()?->id),

                SelectFilter::make('teacher_id')
                    ->label('Guru Pengampu')
                    ->relationship('teacher', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('class_id')
                    ->label('Kelas / Rombel')
                    ->relationship('kelas', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('mata_pelajaran_id')
                    ->label('Mata Pelajaran')
                    ->relationship('mataPelajaran', 'nama_mapel')
                    ->searchable()
                    ->preload(),

                Filter::make('tanggal')
                    ->form([
                        DatePicker::make('dari_tanggal')->label('Dari Tanggal'),
                        DatePicker::make('sampai_tanggal')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari_tanggal'] ?? null, fn ($q, $date) => $q->whereDate('tanggal', '>=', $date))
                            ->when($data['sampai_tanggal'] ?? null, fn ($q, $date) => $q->whereDate('tanggal', '<=', $date));
                    }),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('cetak_pdf')
                    ->label('Cetak PDF')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn ($record) => route('portal-guru.jurnal-mengajar.cetak', [
                        'teacher_id' => $record->teacher_id,
                        'class_id' => $record->class_id,
                        'mata_pelajaran_id' => $record->mata_pelajaran_id,
                        'academic_year_id' => $record->academic_year_id,
                        'date_from' => $record->tanggal->format('Y-m-d'),
                        'date_to' => $record->tanggal->format('Y-m-d'),
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJurnalPembelajarans::route('/'),
            'view'  => Pages\ViewJurnalPembelajaran::route('/{record}'),
        ];
    }
}

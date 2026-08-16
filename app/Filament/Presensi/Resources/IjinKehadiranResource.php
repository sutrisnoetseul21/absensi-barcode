<?php

namespace App\Filament\Presensi\Resources;

use App\Filament\Presensi\Resources\IjinKehadiranResource\Pages;
use App\Models\LeaveRequest;
use App\Models\TahunAjaran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Carbon;

class IjinKehadiranResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Ijin Kehadiran';
    
    protected static ?string $modelLabel = 'Ijin Kehadiran';
    
    protected static ?string $pluralModelLabel = 'Ijin Kehadiran';

    protected static string|\UnitEnum|null $navigationGroup = 'Presensi';
    
    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        return $user->isSuperAdmin() || $user->hasRole('super_admin') || $user->hasAnyRole(['admin_presensi_editor', 'admin_presensi_viewer']);
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        return $user->isSuperAdmin() || $user->hasRole('super_admin') || $user->hasRole('admin_presensi_editor');
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        return $user->isSuperAdmin() || $user->hasRole('super_admin') || $user->hasRole('admin_presensi_editor');
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        return $user->isSuperAdmin() || $user->hasRole('super_admin') || $user->hasRole('admin_presensi_editor');
    }

    public static function canRestore(\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        return $user->isSuperAdmin() || $user->hasRole('super_admin') || $user->hasRole('admin_presensi_editor');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pengajuan')
                    ->schema([
                        Select::make('student_id')
                            ->label('Siswa')
                            ->relationship('student', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn (string $operation) => $operation === 'view'),
                            
                        Select::make('type')
                            ->label('Tipe Ijin')
                            ->options([
                                'ijin' => 'Ijin',
                                'sakit' => 'Sakit',
                            ])
                            ->required()
                            ->disabled(fn (string $operation) => $operation === 'view'),
                            
                        TextInput::make('duration_days')
                            ->label('Durasi (Hari)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->disabled(fn (string $operation) => $operation === 'view')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($set, $get, $state) {
                                $startDate = $get('start_date');
                                if ($startDate && $state) {
                                    $end = Carbon::parse($startDate)->addDays((int)$state - 1)->format('Y-m-d');
                                    $set('end_date', $end);
                                }
                            }),
                            
                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->disabled(fn (string $operation) => $operation === 'view')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($set, $get, $state) {
                                $duration = $get('duration_days');
                                if ($state && $duration) {
                                    $end = Carbon::parse($state)->addDays((int)$duration - 1)->format('Y-m-d');
                                    $set('end_date', $end);
                                }
                            }),
                            
                        DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->disabled() // Auto calculated
                            ->required()
                            ->dehydrated(),
                            
                        Placeholder::make('holidays_info')
                            ->label('')
                            ->content(function (Get $get) {
                                $studentId = $get('student_id');
                                $start = $get('start_date');
                                $end = $get('end_date');
                                if (!$studentId || !$start || !$end) return '';

                                $student = \App\Models\Siswa::find($studentId);
                                if (!$student || !$student->enrollmentAktif) return '';

                                $kalenderService = app(\App\Services\KalenderSekolahService::class);
                                $current = \Carbon\Carbon::parse($start);
                                $endDate = \Carbon\Carbon::parse($end);
                                $msgs = [];

                                while ($current->lessThanOrEqualTo($endDate)) {
                                    if (!$kalenderService->isHariSekolah($current, $student->enrollmentAktif->class_id)) {
                                        $msgs[] = "Catatan: " . $current->translatedFormat('l, d F Y') . " dalam rentang ini adalah hari libur, sehingga otomatis akan tercatat sebagai 'Libur', bukan 'Sakit/Izin'.";
                                    }
                                    $current->addDay();
                                }
                                
                                if (count($msgs) === 0) return '';

                                $html = '<div class="p-3 bg-sky-50 border border-sky-100 rounded-lg"><ul class="space-y-1">';
                                foreach ($msgs as $msg) {
                                    $html .= "<li class='text-xs text-sky-700 flex items-start gap-2'><svg class='w-4 h-4 text-sky-500 flex-shrink-0 mt-0.5' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z\" /></svg><span>{$msg}</span></li>";
                                }
                                $html .= '</ul></div>';
                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->columnSpanFull(),

                            
                        Textarea::make('reason')
                            ->label('Alasan')
                            ->required()
                            ->disabled(fn (string $operation) => $operation === 'view')
                            ->columnSpanFull(),
                            
                        FileUpload::make('file_path')
                            ->label('Upload File / Surat')
                            ->disk('public')
                            ->directory('leave-requests')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(2048)
                            ->disabled(fn (string $operation) => $operation === 'view')
                            ->columnSpanFull(),
                    ])->columns(2),
                    
                Section::make('Status Persetujuan')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->disabled() // Dikendalikan via action approve/reject
                            ->default('pending'),
                            
                        Placeholder::make('approved_by_info')
                            ->label('Disetujui/Ditolak Oleh')
                            ->content(fn ($record) => $record?->approvedBy ? $record->approvedBy->name : '-')
                            ->visible(fn ($record) => $record && $record->status !== 'pending'),
                            
                        Placeholder::make('approved_at_info')
                            ->label('Pada Tanggal')
                            ->content(fn ($record) => $record?->approved_at ? $record->approved_at->format('d/m/Y H:i') : '-')
                            ->visible(fn ($record) => $record && $record->status !== 'pending'),
                    ])->columns(2)->visible(fn (string $operation) => $operation !== 'create'),

                Section::make('Riwayat Audit Log & Alasan Perubahan')
                    ->schema([
                        Placeholder::make('audit_logs')
                            ->label('')
                            ->content(function ($record) {
                                if (!$record) return 'Belum ada riwayat.';
                                $logs = $record->logs()->with('user')->get();
                                if ($logs->isEmpty()) return 'Belum ada catatan log.';

                                $html = '<div class="overflow-x-auto"><table class="w-full text-sm text-left text-slate-300 dark:text-slate-200 border border-slate-700 rounded-lg"><thead class="text-xs uppercase bg-slate-800 text-slate-300"><tr><th class="px-3 py-2 border-b border-slate-700">Waktu</th><th class="px-3 py-2 border-b border-slate-700">Pengguna</th><th class="px-3 py-2 border-b border-slate-700">Aksi</th><th class="px-3 py-2 border-b border-slate-700">Alasan</th></tr></thead><tbody>';

                                foreach ($logs as $log) {
                                    $actionBadge = match ($log->action) {
                                        'created'  => '<span class="px-2 py-0.5 rounded text-xs bg-blue-500/20 text-blue-400 font-semibold">Dibuat</span>',
                                        'approved' => '<span class="px-2 py-0.5 rounded text-xs bg-emerald-500/20 text-emerald-400 font-semibold">Disetujui</span>',
                                        'rejected' => '<span class="px-2 py-0.5 rounded text-xs bg-rose-500/20 text-rose-400 font-semibold">Ditolak</span>',
                                        'updated'  => '<span class="px-2 py-0.5 rounded text-xs bg-amber-500/20 text-amber-400 font-semibold">Diedit</span>',
                                        'deleted'  => '<span class="px-2 py-0.5 rounded text-xs bg-red-500/20 text-red-400 font-semibold">Dihapus</span>',
                                        'restored' => '<span class="px-2 py-0.5 rounded text-xs bg-cyan-500/20 text-cyan-400 font-semibold">Dipulihkan</span>',
                                        default    => e($log->action),
                                    };
                                    $waktu = $log->created_at ? $log->created_at->format('d/m/Y H:i') : '-';
                                    $userName = e($log->user?->name ?? 'Sistem / Siswa');
                                    $alasan = e($log->reason ?? '-');

                                    $html .= "<tr class='border-b border-slate-800 hover:bg-slate-800/50'><td class='px-3 py-2 text-xs'>{$waktu}</td><td class='px-3 py-2 font-medium'>{$userName}</td><td class='px-3 py-2'>{$actionBadge}</td><td class='px-3 py-2 italic'>{$alasan}</td></tr>";
                                }

                                $html .= '</tbody></table></div>';
                                return new \Illuminate\Support\HtmlString($html);
                            }),
                    ])
                    ->visible(fn (string $operation) => $operation !== 'create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sakit' => 'warning',
                        'ijin' => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tgl Mulai')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_days')
                    ->label('Durasi')
                    ->suffix(' Hari'),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Tgl Selesai')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('approvedBy.name')
                    ->label('Approver')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->relationship('academicYear', 'name')
                    ->default(fn () => TahunAjaran::where('status', 'aktif')->first()?->id),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'ijin' => 'Ijin',
                        'sakit' => 'Sakit',
                    ]),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_presensi_editor'))
                    ->form([
                        Select::make('student_id')
                            ->label('Siswa')
                            ->relationship('student', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('type')
                            ->label('Tipe Ijin')
                            ->options([
                                'ijin' => 'Ijin',
                                'sakit' => 'Sakit',
                            ])
                            ->required(),
                        Select::make('status')
                            ->label('Status Persetujuan')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required(),
                        TextInput::make('duration_days')
                            ->label('Durasi (Hari)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($set, $get, $state) {
                                $startDate = $get('start_date');
                                if ($startDate && $state) {
                                    $end = Carbon::parse($startDate)->addDays((int)$state - 1)->format('Y-m-d');
                                    $set('end_date', $end);
                                }
                            }),
                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($set, $get, $state) {
                                $duration = $get('duration_days');
                                if ($state && $duration) {
                                    $end = Carbon::parse($state)->addDays((int)$duration - 1)->format('Y-m-d');
                                    $set('end_date', $end);
                                }
                            }),
                        DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->disabled()
                            ->required()
                            ->dehydrated(),
                            
                        Placeholder::make('holidays_info_edit')
                            ->label('')
                            ->content(function (Get $get) {
                                $studentId = $get('student_id');
                                $start = $get('start_date');
                                $end = $get('end_date');
                                if (!$studentId || !$start || !$end) return '';

                                $student = \App\Models\Siswa::find($studentId);
                                if (!$student || !$student->enrollmentAktif) return '';

                                $kalenderService = app(\App\Services\KalenderSekolahService::class);
                                $current = \Carbon\Carbon::parse($start);
                                $endDate = \Carbon\Carbon::parse($end);
                                $msgs = [];

                                while ($current->lessThanOrEqualTo($endDate)) {
                                    if (!$kalenderService->isHariSekolah($current, $student->enrollmentAktif->class_id)) {
                                        $msgs[] = "Catatan: " . $current->translatedFormat('l, d F Y') . " dalam rentang ini adalah hari libur, sehingga otomatis akan tercatat sebagai 'Libur', bukan 'Sakit/Izin'.";
                                    }
                                    $current->addDay();
                                }
                                
                                if (count($msgs) === 0) return '';

                                $html = '<div class="p-3 bg-sky-50 border border-sky-100 rounded-lg"><ul class="space-y-1">';
                                foreach ($msgs as $msg) {
                                    $html .= "<li class='text-xs text-sky-700 flex items-start gap-2'><svg class='w-4 h-4 text-sky-500 flex-shrink-0 mt-0.5' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z\" /></svg><span>{$msg}</span></li>";
                                }
                                $html .= '</ul></div>';
                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->columnSpan('full'),

                        Textarea::make('reason')
                            ->label('Alasan Pengajuan')
                            ->required(),
                        Textarea::make('alasan_perubahan')
                            ->label('Alasan Perubahan / Edit (Wajib)')
                            ->required()
                            ->placeholder('Masukkan alasan mengapa data/status ini diubah oleh Admin...'),
                    ])
                    ->before(function (\Filament\Actions\EditAction $action, LeaveRequest $record, array $data) {
                        $startDate = $data['start_date'];
                        $endDate = $data['end_date'];
                        $studentId = $data['student_id'];
                        
                        $overlapRecord = LeaveRequest::where('student_id', $studentId)
                            ->where('id', '!=', $record->id)
                            ->whereIn('status', ['pending', 'approved'])
                            ->where(function ($query) use ($startDate, $endDate) {
                                $query->whereBetween('start_date', [$startDate, $endDate])
                                      ->orWhereBetween('end_date', [$startDate, $endDate])
                                      ->orWhere(function ($q) use ($startDate, $endDate) {
                                          $q->where('start_date', '<=', $startDate)
                                            ->where('end_date', '>=', $endDate);
                                      });
                            })
                            ->first();
                            
                        if ($overlapRecord) {
                            $statusLabel = $overlapRecord->status === 'approved' ? 'Approved' : 'Pending';
                            Notification::make()
                                ->title('Validasi Gagal')
                                ->body("Siswa ini sudah memiliki pengajuan ijin/sakit yang berstatus [{$statusLabel}] pada rentang tanggal tersebut.")
                                ->danger()
                                ->send();
                            $action->halt();
                        }

                        $existingAttendance = \App\Models\Presensi::where('student_id', $studentId)
                            ->whereBetween('date', [$startDate, $endDate])
                            ->whereIn('status', ['hadir', 'telat'])
                            ->first();

                        if ($existingAttendance) {
                            $formattedDate = \Illuminate\Support\Carbon::parse($existingAttendance->date)->translatedFormat('d F Y');
                            Notification::make()
                                ->title('Validasi Gagal')
                                ->body("Gagal: Siswa ini sudah memiliki catatan presensi (Hadir/Telat) pada tanggal {$formattedDate}. Silakan periksa kembali tanggal pengajuan.")
                                ->danger()
                                ->send();
                            $action->halt();
                        }

                        session()->put('old_status_' . $record->id, $record->status);
                        session()->put('edit_reason_' . $record->id, $data['alasan_perubahan'] ?? 'Diedit oleh admin');
                    })
                    ->after(function (LeaveRequest $record) {
                        $oldStatus = session()->pull('old_status_' . $record->id);
                        $reason = session()->pull('edit_reason_' . $record->id, 'Diedit oleh admin');
                        $newStatus = $record->status;
                        $user = auth()->user();

                        if ($oldStatus !== $newStatus) {
                            if ($newStatus === 'pending') {
                                $record->updateQuietly([
                                    'approved_by' => null,
                                    'approved_by_type' => null,
                                    'approved_at' => null,
                                ]);
                            } else {
                                $record->updateQuietly([
                                    'approved_by' => $user?->id,
                                    'approved_by_type' => $user ? get_class($user) : null,
                                    'approved_at' => now(),
                                ]);
                            }
                        }

                        $logText = ($oldStatus !== $newStatus)
                            ? "Status diubah dari '" . ucfirst($oldStatus) . "' menjadi '" . ucfirst($newStatus) . "'. Alasan: {$reason}"
                            : $reason;
                        $record->recordLog('updated', $logText);

                        $service = app(\App\Services\LeaveRequestService::class);
                        if ($oldStatus === 'approved' && $newStatus !== 'approved') {
                            $service->removeAttendances($record);
                        } elseif ($newStatus === 'approved') {
                            $service->syncAttendances($record);
                        }
                    }),
                \Filament\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_presensi_editor'))
                    ->form([
                        Textarea::make('alasan_penghapusan')
                            ->label('Alasan Penghapusan (Wajib)')
                            ->required()
                            ->placeholder('Masukkan alasan mengapa pengajuan ini dihapus...'),
                    ])
                    ->before(function (\Filament\Actions\DeleteAction $action, LeaveRequest $record, array $data) {
                        $record->recordLog('deleted', $data['alasan_penghapusan'] ?? 'Dihapus oleh admin');
                        app(\App\Services\LeaveRequestService::class)->removeAttendances($record);
                    }),
                \Filament\Actions\RestoreAction::make()
                    ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_presensi_editor'))
                    ->form([
                        Textarea::make('alasan_pemulihan')
                            ->label('Alasan Pemulihan (Wajib)')
                            ->required()
                            ->placeholder('Masukkan alasan pemulihan data ini...'),
                    ])
                    ->after(function (LeaveRequest $record, array $data) {
                        $record->recordLog('restored', $data['alasan_pemulihan'] ?? 'Dipulihkan oleh admin');
                        if ($record->status === 'approved') {
                            app(\App\Services\LeaveRequestService::class)->syncAttendances($record);
                        }
                    }),
                \Filament\Actions\ForceDeleteAction::make()
                    ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_presensi_editor'))
                    ->before(function (LeaveRequest $record) {
                        app(\App\Services\LeaveRequestService::class)->removeAttendances($record);
                    }),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_presensi_editor')),
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
            'index' => Pages\ListIjinKehadirans::route('/'),
            'create' => Pages\CreateIjinKehadiran::route('/create'),
            'view' => Pages\ViewIjinKehadiran::route('/{record}'),
        ];
    }
}

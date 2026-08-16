<?php

namespace App\Filament\Akademik\Resources\Kelas\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class KelasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Kelas')
                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                    ->size('md')
                    ->icon('heroicon-o-building-office-2')
                    ->iconColor('primary')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('grade_level')
                    ->label('Tingkat / Jenjang')
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        7 => 'info',
                        8 => 'success',
                        9 => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (int $state): string => "Kelas {$state}")
                    ->sortable(),

                TextColumn::make('wali_kelas')
                    ->label('Wali Kelas (Aktif)')
                    ->icon('heroicon-o-user-circle')
                    ->getStateUsing(function (\App\Models\Kelas $record) {
                        $activeTahunAjaranId = \App\Models\PengaturanSekolah::current()?->academic_year_id_active ?? \App\Models\TahunAjaran::aktif()->first()?->id;
                        if (!$activeTahunAjaranId) return 'Tahun Ajaran Belum Aktif';

                        $kelasAjaran = \App\Models\KelasAjaran::where('class_id', $record->id)
                            ->where('academic_year_id', $activeTahunAjaranId)
                            ->first();

                        return $kelasAjaran?->guru?->name ?? 'Belum Ditentukan';
                    })
                    ->badge(fn (string $state): bool => $state === 'Belum Ditentukan' || $state === 'Tahun Ajaran Belum Aktif')
                    ->color(fn (string $state): string => $state === 'Belum Ditentukan' || $state === 'Tahun Ajaran Belum Aktif' ? 'warning' : 'gray'),

                TextColumn::make('jumlah_siswa')
                    ->label('Jumlah Siswa')
                    ->icon('heroicon-o-user-group')
                    ->getStateUsing(function (\App\Models\Kelas $record) {
                        $activeTahunAjaranId = \App\Models\PengaturanSekolah::current()?->academic_year_id_active ?? \App\Models\TahunAjaran::aktif()->first()?->id;
                        if (!$activeTahunAjaranId) return '0 Siswa';

                        $count = \App\Models\EnrollmentSiswa::where('class_id', $record->id)
                            ->where('academic_year_id', $activeTahunAjaranId)
                            ->where('status', 'aktif')
                            ->count();

                        return "{$count} Siswa";
                    })
                    ->badge()
                    ->color('primary')
                    ->tooltip('Klik untuk melihat daftar nama siswa')
                    ->action(
                        Action::make('lihat_siswa')
                            ->modalHeading(fn (\App\Models\Kelas $record): string => "Daftar Siswa - Kelas {$record->name}")
                            ->modalDescription('Daftar siswa yang terdaftar aktif di kelas ini pada tahun ajaran aktif.')
                            ->modalWidth('xl')
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Tutup')
                            ->form(function (\App\Models\Kelas $record): array {
                                $activeTahunAjaranId = \App\Models\PengaturanSekolah::current()?->academic_year_id_active ?? \App\Models\TahunAjaran::aktif()->first()?->id;
                                
                                $enrollments = $activeTahunAjaranId ? \App\Models\EnrollmentSiswa::where('class_id', $record->id)
                                    ->where('academic_year_id', $activeTahunAjaranId)
                                    ->where('status', 'aktif')
                                    ->with('siswa')
                                    ->get() : collect();

                                $countTotal = $enrollments->count();
                                $countL = $enrollments->filter(fn ($e) => in_array(strtolower($e->siswa?->gender ?? ''), ['l', 'laki-laki', 'laki_laki', 'male']))->count();
                                $countP = $enrollments->filter(fn ($e) => in_array(strtolower($e->siswa?->gender ?? ''), ['p', 'perempuan', 'female']))->count();

                                return [
                                    \Filament\Forms\Components\Placeholder::make('daftar_siswa')
                                        ->label('')
                                        ->content(function () use ($enrollments, $countTotal, $countL, $countP) {
                                            if ($enrollments->isEmpty()) {
                                                return new \Illuminate\Support\HtmlString('
                                                    <div style="text-align: center; padding: 40px 20px;">
                                                        <div style="font-size: 3rem; margin-bottom: 12px;">📭</div>
                                                        <h4 style="font-size: 1.1rem; font-weight: 600; color: #4b5563; margin: 0 0 6px 0;">Belum Ada Siswa</h4>
                                                        <p style="font-size: 0.875rem; color: #9ca3af; margin: 0;">Belum ada siswa yang terdaftar di kelas ini untuk tahun ajaran aktif.</p>
                                                    </div>
                                                ');
                                            }

                                            // Stats Header Cards
                                            $html = '<div style="display: flex; gap: 12px; margin-bottom: 16px;">';
                                            
                                            // Card Total
                                            $html .= '  <div style="flex: 1; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1px solid #bfdbfe; border-radius: 10px; padding: 10px 14px; display: flex; align-items: center; gap: 10px;">';
                                            $html .= '      <div style="width: 36px; height: 36px; border-radius: 8px; background: #3b82f6; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: bold; flex-shrink: 0;">👥</div>';
                                            $html .= '      <div>';
                                            $html .= '          <div style="font-size: 0.7rem; color: #1e40af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Total Siswa</div>';
                                            $html .= '          <div style="font-size: 1.2rem; color: #1e3a8a; font-weight: 800;">' . $countTotal . ' <span style="font-size: 0.8rem; font-weight: 500;">Siswa</span></div>';
                                            $html .= '      </div>';
                                            $html .= '  </div>';

                                            // Card Laki-laki
                                            $html .= '  <div style="flex: 1; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #bbf7d0; border-radius: 10px; padding: 10px 14px; display: flex; align-items: center; gap: 10px;">';
                                            $html .= '      <div style="width: 36px; height: 36px; border-radius: 8px; background: #10b981; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: bold; flex-shrink: 0;">👨</div>';
                                            $html .= '      <div>';
                                            $html .= '          <div style="font-size: 0.7rem; color: #166534; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Laki-Laki</div>';
                                            $html .= '          <div style="font-size: 1.2rem; color: #14532d; font-weight: 800;">' . $countL . ' <span style="font-size: 0.8rem; font-weight: 500;">Siswa</span></div>';
                                            $html .= '      </div>';
                                            $html .= '  </div>';

                                            // Card Perempuan
                                            $html .= '  <div style="flex: 1; background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%); border: 1px solid #fbcfe8; border-radius: 10px; padding: 10px 14px; display: flex; align-items: center; gap: 10px;">';
                                            $html .= '      <div style="width: 36px; height: 36px; border-radius: 8px; background: #ec4899; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: bold; flex-shrink: 0;">👩</div>';
                                            $html .= '      <div>';
                                            $html .= '          <div style="font-size: 0.7rem; color: #9d174d; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Perempuan</div>';
                                            $html .= '          <div style="font-size: 1.2rem; color: #831843; font-weight: 800;">' . $countP . ' <span style="font-size: 0.8rem; font-weight: 500;">Siswi</span></div>';
                                            $html .= '      </div>';
                                            $html .= '  </div>';
                                            $html .= '</div>';

                                            // Table Container
                                            $html .= '<div style="overflow-x: auto; overflow-y: auto; max-height: 360px; border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">';
                                            $html .= '<table style="width: 100%; border-collapse: collapse; font-size: 0.875rem; text-align: left;">';
                                            $html .= '<thead style="background: #f8fafc; position: sticky; top: 0; z-index: 10; border-bottom: 2px solid #e2e8f0;">';
                                            $html .= '<tr>';
                                            $html .= '<th style="padding: 10px 14px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.05em; width: 50px;">No</th>';
                                            $html .= '<th style="padding: 10px 14px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.05em;">Nama Siswa</th>';
                                            $html .= '<th style="padding: 10px 14px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.05em;">NISN / NIS</th>';
                                            $html .= '<th style="padding: 10px 14px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.05em; text-align: center;">Gender</th>';
                                            $html .= '<th style="padding: 10px 14px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.05em; text-align: center;">Status</th>';
                                            $html .= '</tr></thead>';
                                            $html .= '<tbody style="background: #ffffff;">';

                                            $bgColors = ['#3b82f6', '#10b981', '#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#14b8a6'];

                                            foreach ($enrollments as $index => $enrollment) {
                                                $siswa = $enrollment->siswa;
                                                $nisn = $siswa?->nisn ?: ($siswa?->nis ?: '—');
                                                $nama = $siswa?->name ?? '—';
                                                $initial = strtoupper(substr($nama, 0, 1));
                                                $avatarBg = $bgColors[$index % count($bgColors)];

                                                $rawGender = strtolower($siswa?->gender ?? '');
                                                $genderBadge = match (true) {
                                                    in_array($rawGender, ['l', 'laki-laki', 'laki_laki', 'male']) => '<span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">👨 L</span>',
                                                    in_array($rawGender, ['p', 'perempuan', 'female']) => '<span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; background: #fdf2f8; color: #db2777; border: 1px solid #fbcfe8; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">👩 P</span>',
                                                    default => '<span style="color: #9ca3af;">—</span>',
                                                };

                                                $borderBottom = $index === $countTotal - 1 ? '' : 'border-bottom: 1px solid #f1f5f9;';

                                                $html .= "<tr style=\"{$borderBottom} transition: background-color 0.15s ease;\" onmouseover=\"this.style.backgroundColor='#f8fafc'\" onmouseout=\"this.style.backgroundColor='#ffffff'\">";
                                                $html .= '<td style="padding: 10px 14px; color: #64748b; font-weight: 500;">' . ($index + 1) . '</td>';
                                                $html .= '<td style="padding: 10px 14px;">';
                                                $html .= '    <div style="display: flex; align-items: center; gap: 10px;">';
                                                $html .= '        <div style="width: 32px; height: 32px; border-radius: 50%; background: ' . $avatarBg . '; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; flex-shrink: 0; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">' . htmlspecialchars($initial) . '</div>';
                                                $html .= '        <div style="font-weight: 600; color: #0f172a; font-size: 0.875rem;">' . htmlspecialchars($nama) . '</div>';
                                                $html .= '    </div>';
                                                $html .= '</td>';
                                                $html .= '<td style="padding: 10px 14px;"><span style="font-family: monospace; font-size: 0.825rem; background: #f1f5f9; color: #334155; padding: 3px 8px; border-radius: 6px; font-weight: 600;">' . htmlspecialchars($nisn) . '</span></td>';
                                                $html .= '<td style="padding: 10px 14px; text-align: center;">' . $genderBadge . '</td>';
                                                $html .= '<td style="padding: 10px 14px; text-align: center;"><span style="display: inline-flex; align-items: center; gap: 6px; padding: 2px 8px; background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;"><span style="width: 6px; height: 6px; border-radius: 50%; background: #16a34a;"></span> Aktif</span></td>';
                                                $html .= '</tr>';
                                            }

                                            $html .= '</tbody></table></div>';

                                            return new \Illuminate\Support\HtmlString($html);
                                        })
                                ];
                            })
                    ),
            ])
            ->headerActions([])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('grade_level')
                    ->label('Filter Tingkat')
                    ->options([
                        7 => 'Kelas 7',
                        8 => 'Kelas 8',
                        9 => 'Kelas 9',
                    ])
                    ->native(false),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('assign_wali_kelas')
                    ->label('Wali Kelas')
                    ->icon('heroicon-o-user-plus')
                    ->color('warning')
                    ->button()
                    ->outlined()
                    ->size('sm')
                    ->form([
                        \Filament\Forms\Components\Select::make('teacher_id')
                            ->label('Pilih Wali Kelas')
                            ->options(\App\Models\Guru::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->fillForm(function (\App\Models\Kelas $record): array {
                        $activeTahunAjaranId = \App\Models\PengaturanSekolah::current()?->academic_year_id_active ?? \App\Models\TahunAjaran::aktif()->first()?->id;
                        $kelasAjaran = $activeTahunAjaranId ? \App\Models\KelasAjaran::where('class_id', $record->id)->where('academic_year_id', $activeTahunAjaranId)->first() : null;
                        return [
                            'teacher_id' => $kelasAjaran?->teacher_id,
                        ];
                    })
                    ->action(function (\App\Models\Kelas $record, array $data): void {
                        $activeTahunAjaranId = \App\Models\PengaturanSekolah::current()?->academic_year_id_active ?? \App\Models\TahunAjaran::aktif()->first()?->id;
                        if (!$activeTahunAjaranId) {
                            \Filament\Notifications\Notification::make()->title('Gagal')->body('Tidak ada tahun ajaran aktif.')->danger()->send();
                            return;
                        }
                        
                        \App\Models\KelasAjaran::updateOrCreate(
                            ['class_id' => $record->id, 'academic_year_id' => $activeTahunAjaranId],
                            ['teacher_id' => $data['teacher_id']]
                        );

                        \Filament\Notifications\Notification::make()->title('Wali kelas berhasil disimpan')->success()->send();
                    })
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),

                Action::make('assign_pembelajaran')
                    ->label('Pembelajaran')
                    ->icon('heroicon-o-book-open')
                    ->color('info')
                    ->button()
                    ->outlined()
                    ->size('sm')
                    ->modalHeading(fn (\App\Models\Kelas $record): string => "Kelola Pembelajaran - Kelas {$record->name}")
                    ->modalDescription('Tentukan guru pengajar untuk setiap mata pelajaran pada kelas ini untuk tahun ajaran aktif.')
                    ->modalWidth('2xl')
                    ->form(function (\App\Models\Kelas $record): array {
                        $mapels = \App\Models\MataPelajaran::orderBy('nama_mapel')->get();
                        $gurus = \App\Models\Guru::pluck('name', 'id')->toArray();
                        
                        $fields = [];
                        foreach ($mapels as $mapel) {
                            $fields[] = \Filament\Forms\Components\Select::make("assignments.{$mapel->id}")
                                ->label($mapel->nama_mapel)
                                ->options($gurus)
                                ->searchable()
                                ->preload()
                                ->placeholder('— Belum Ditentukan —');
                        }
                        
                        return [
                            \Filament\Schemas\Components\Grid::make(2)
                                ->schema($fields),
                        ];
                    })
                    ->fillForm(function (\App\Models\Kelas $record): array {
                        $activeTahunAjaranId = \App\Models\PengaturanSekolah::current()?->academic_year_id_active ?? \App\Models\TahunAjaran::aktif()->first()?->id;
                        $kelasAjaran = $activeTahunAjaranId ? \App\Models\KelasAjaran::where('class_id', $record->id)->where('academic_year_id', $activeTahunAjaranId)->first() : null;

                        $existingAssignments = [];
                        if ($kelasAjaran) {
                            $existingAssignments = \App\Models\Pengajaran::where('class_academic_year_id', $kelasAjaran->id)
                                ->pluck('teacher_id', 'mata_pelajaran_id')
                                ->toArray();
                        }

                        return [
                            'assignments' => $existingAssignments,
                        ];
                    })
                    ->action(function (\App\Models\Kelas $record, array $data): void {
                        $activeTahunAjaranId = \App\Models\PengaturanSekolah::current()?->academic_year_id_active ?? \App\Models\TahunAjaran::aktif()->first()?->id;
                        if (!$activeTahunAjaranId) {
                            \Filament\Notifications\Notification::make()->title('Gagal')->body('Tidak ada tahun ajaran aktif.')->danger()->send();
                            return;
                        }

                        $kelasAjaran = \App\Models\KelasAjaran::firstOrCreate(
                            ['class_id' => $record->id, 'academic_year_id' => $activeTahunAjaranId],
                            ['teacher_id' => null]
                        );

                        $assignments = $data['assignments'] ?? [];
                        foreach ($assignments as $mapelId => $teacherId) {
                            if (empty($teacherId)) {
                                \App\Models\Pengajaran::where('class_academic_year_id', $kelasAjaran->id)
                                    ->where('mata_pelajaran_id', $mapelId)
                                    ->delete();
                            } else {
                                \App\Models\Pengajaran::updateOrCreate(
                                    [
                                        'class_academic_year_id' => $kelasAjaran->id,
                                        'mata_pelajaran_id' => $mapelId,
                                    ],
                                    [
                                        'teacher_id' => $teacherId,
                                    ]
                                );
                            }
                        }

                        \Filament\Notifications\Notification::make()->title('Pembelajaran kelas berhasil disimpan')->success()->send();
                    })
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),

                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit Kelas')
                    ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_akademik_editor') || auth()->user()?->hasRole('admin_master_editor'))
                    ->modalDescription(function (\App\Models\Kelas $record) {
                        $hasPengajaran = $record->pengajarans()->exists() ?? false;
                        $hasSiswa = $record->enrollments()->exists() ?? false;
                        
                        if ($hasPengajaran || $hasSiswa) {
                            return new \Illuminate\Support\HtmlString('<span style="color: #ef4444; font-weight: bold;">⚠️ Peringatan: Kelas ini sudah terisi (' . ($hasSiswa ? 'Siswa' : '') . ($hasPengajaran && $hasSiswa ? ' & ' : '') . ($hasPengajaran ? 'Pembelajaran' : '') . '). Mengubah data dapat merusak riwayat akademik!</span>');
                        }
                        return null;
                    })
                    ->before(function (\App\Models\Kelas $record, \Filament\Actions\EditAction $action) {
                        $hasPengajaran = $record->pengajarans()->exists() ?? false;
                        $hasSiswa = $record->enrollments()->exists() ?? false;

                        if ($hasSiswa || $hasPengajaran) {
                            \Filament\Notifications\Notification::make()
                                ->title('Perubahan Ditolak')
                                ->body('Kelas tidak dapat diubah karena sudah memiliki data Siswa atau Pembelajaran aktif.')
                                ->danger()
                                ->persistent()
                                ->send();
                            
                            $action->cancel();
                        }
                    }),

                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Hapus Kelas')
                    ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_akademik_editor') || auth()->user()?->hasRole('admin_master_editor'))
                    ->before(function (\App\Models\Kelas $record, DeleteAction $action) {
                        $hasPengajaran = $record->pengajarans()->exists() ?? false;
                        $hasSiswa = $record->enrollments()->exists() ?? false;

                        if ($hasSiswa || $hasPengajaran) {
                            \Filament\Notifications\Notification::make()
                                ->title('Akses Ditolak')
                                ->body('Kelas tidak dapat dihapus! Anda harus mengosongkan/menghapus data Siswa dan Pembelajaran yang terkait dengan kelas ini terlebih dahulu.')
                                ->danger()
                                ->persistent()
                                ->send();
                            
                            $action->cancel();
                        }
                    }),

                RestoreAction::make()
                    ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_akademik_editor') || auth()->user()?->hasRole('admin_master_editor'))
                    ->iconButton(),
            ])
            ->defaultGroup('grade_level')
            ->groups([
                Group::make('grade_level')
                    ->label('Jenjang')
                    ->getTitleFromRecordUsing(fn (\App\Models\Kelas $record): string => "Kelas {$record->grade_level}")
                    ->collapsible(),
            ])
            ->defaultSort('name', 'asc');
    }
}

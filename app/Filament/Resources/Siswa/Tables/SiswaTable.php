<?php

namespace App\Filament\Resources\Siswa\Tables;

use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SiswaTable
{
    public static function configure(Table $table): Table
    {
        // Ambil tahun ajaran aktif dari pengaturan sekolah
        $activeYearId = PengaturanSekolah::current()?->academic_year_id_active;

        return $table
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(url('https://ui-avatars.com/api/?name=Siswa&color=7F9CF5&background=EBF4FF')),

                TextColumn::make('nisn')
                    ->label('NISN')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kelas_aktif')
                    ->label('Kelas (Aktif)')
                    ->getStateUsing(function (Siswa $record, $livewire) {
                        $targetYearId = $livewire->tableFilters['academic_year_id']['value'] ?? null;
                        $activeYearId = $targetYearId ?: (PengaturanSekolah::current()?->academic_year_id_active);
                        if (!$activeYearId) return '—';
                        
                        $enrollment = $record->enrollments()->where('academic_year_id', $activeYearId)->first();
                        if (!$enrollment) return '—';

                        $className = $enrollment->kelas?->name ?? '—';
                        if ($enrollment->status !== 'aktif') {
                            $statusLabel = match ($enrollment->status) {
                                'naik' => 'Naik',
                                'lulus' => 'Lulus',
                                'tinggal' => 'Tinggal',
                                'pindah' => 'Pindah',
                                default => $enrollment->status,
                            };
                            return $className . ' (' . ucfirst($statusLabel) . ')';
                        }

                        return $className;
                    }),

                TextColumn::make('barcode_code')
                    ->label('Barcode')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Barcode disalin'),

                TextColumn::make('username')
                    ->label('Username Login')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Username disalin'),

                IconColumn::make('must_change_password')
                    ->label('Ganti Password?')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('warning')
                    ->falseColor('success'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                // 0. Luluskan Kelas 9 Massal
                Action::make('luluskan_kelas_9')
                    ->label('Luluskan Kelas 9')
                    ->icon('heroicon-o-academic-cap')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Meluluskan Siswa Kelas 9')
                    ->modalDescription('Tindakan ini akan meluluskan seluruh siswa kelas tingkat 9 secara massal pada tahun ajaran yang dipilih.')
                    ->form([
                        \Filament\Forms\Components\Select::make('academic_year_id')
                            ->label('Tahun Ajaran')
                            ->options(TahunAjaran::pluck('name', 'id')->toArray())
                            ->default(fn () => \App\Models\PengaturanSekolah::current()?->academic_year_id_active)
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $yearId = $data['academic_year_id'];
                        $tahunAjaran = \App\Models\TahunAjaran::find($yearId);
                        
                        $enrollments = \App\Models\EnrollmentSiswa::where('academic_year_id', $yearId)
                            ->where('status', 'aktif')
                            ->whereHas('kelas', function ($q) {
                                $q->where('grade_level', 9);
                            })
                            ->get();

                        $count = 0;
                        foreach ($enrollments as $enrollment) {
                            $enrollment->update(['status' => 'lulus']);
                            $count++;
                        }

                        $yearName = $tahunAjaran?->name ?? '';
                        \Filament\Notifications\Notification::make()
                            ->title('Kelulusan Massal Berhasil')
                            ->body("Berhasil meluluskan **{$count}** siswa kelas 9 untuk Tahun Ajaran **{$yearName}**.")
                            ->success()
                            ->send();
                    }),

                // 0.1 Batalkan Kelulusan Massal
                Action::make('batalkan_kelulusan_massal')
                    ->label('Batalkan Kelulusan')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Kelulusan')
                    ->modalDescription('Tindakan ini akan memulihkan status kelulusan seluruh siswa di tahun ajaran yang dipilih kembali menjadi Aktif.')
                    ->form([
                        \Filament\Forms\Components\Select::make('academic_year_id')
                            ->label('Tahun Ajaran')
                            ->options(TahunAjaran::pluck('name', 'id')->toArray())
                            ->default(fn () => \App\Models\PengaturanSekolah::current()?->academic_year_id_active)
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $yearId = $data['academic_year_id'];
                        $tahunAjaran = \App\Models\TahunAjaran::find($yearId);

                        $enrollments = \App\Models\EnrollmentSiswa::where('academic_year_id', $yearId)
                            ->where('status', 'lulus')
                            ->get();

                        $count = 0;
                        foreach ($enrollments as $enrollment) {
                            $enrollment->update(['status' => 'aktif']);
                            $count++;
                        }

                        $yearName = $tahunAjaran?->name ?? '';
                        \Filament\Notifications\Notification::make()
                            ->title('Pembatalan Kelulusan Berhasil')
                            ->body("Berhasil membatalkan kelulusan **{$count}** siswa untuk Tahun Ajaran **{$yearName}** kembali menjadi Aktif.")
                            ->success()
                            ->send();
                    }),

                // 1. Download Template Siswa Baru
                Action::make('download_template_siswa_baru')
                    ->label('Template Siswa Baru')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(fn () => \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\SiswaBaruTemplateExport, 'template_siswa_baru.xlsx')),

                // 2. Import Siswa Baru
                Action::make('import_siswa_baru')
                    ->label('Import Siswa Baru')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\FileUpload::make('file')
                            ->label('Pilih file Excel (.xlsx)')
                            ->disk('local')
                            ->directory('imports')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->required()
                            ->live(),

                        \Filament\Forms\Components\Placeholder::make('preview')
                            ->label('Preview Data')
                            ->hidden(fn (\Filament\Schemas\Components\Utilities\Get $get) => empty($get('file')))
                            ->content(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                $file = $get('file');
                                if (is_array($file)) {
                                    $file = array_values($file)[0] ?? null;
                                }
                                if (!$file) return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500">File belum diunggah secara sempurna.</p>');

                                if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                    $filePath = $file->getRealPath();
                                } else {
                                    $filePath = storage_path('app/private/' . $file);
                                    if (!file_exists($filePath)) {
                                        $filePath = storage_path('app/' . $file);
                                    }
                                }

                                if (!file_exists($filePath)) {
                                    return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500">Mencari file...</p>');
                                }

                                try {
                                    $data = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass, $filePath);
                                    if (empty($data[0])) {
                                        return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500">File kosong.</p>');
                                    }

                                    $sheet = $data[0];
                                    $headers = $sheet[0] ?? [];
                                    $allRows = array_slice($sheet, 1);

                                    if (empty($headers)) return '';

                                    // Validasi kecocokan header
                                    if (empty($headers) || strtolower(trim((string)($headers[0] ?? ''))) !== 'nisn' || strtolower(trim((string)($headers[1] ?? ''))) !== 'nama siswa' || strtolower(trim((string)($headers[3] ?? ''))) !== 'kelas') {
                                        return new \Illuminate\Support\HtmlString('<p style="color: #b91c1c; font-weight: 600; padding: 10px; background-color: #fee2e2; border: 1px solid #fca5a5; border-radius: 6px;">⚠️ Berkas yang diunggah bukan template Siswa Baru yang valid. Silakan gunakan template yang sesuai.</p>');
                                    }

                                    // Filter baris kosong
                                    $rows = [];
                                    foreach ($allRows as $row) {
                                        $nisn = trim((string) ($row[0] ?? ''));
                                        if ($nisn !== '') {
                                            $rows[] = $row;
                                        }
                                    }

                                    if (empty($rows)) {
                                        return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500">Tidak ada baris data siswa yang terisi.</p>');
                                    }

                                    $existingStudents = Siswa::pluck('name', 'nisn')->toArray();
                                    $existingClasses = Kelas::pluck('name')->toArray();

                                    $html = '<div style="overflow-x: auto; overflow-y: auto; max-height: 250px; width: 100%; margin-top: 10px; margin-bottom: 10px; border: 1px solid #e5e7eb; border-radius: 8px;">';
                                    $html .= '<table style="display: table; width: 100%; border-collapse: collapse; font-size: 0.875rem; text-align: left;">';
                                    
                                    // Header
                                    $html .= '<thead style="display: table-header-group; background-color: #f3f4f6; position: sticky; top: 0; z-index: 10;">';
                                    $html .= '<tr style="display: table-row;">';
                                    foreach ($headers as $th) {
                                        $html .= '<th style="display: table-cell; padding: 10px 12px; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb; border-right: 1px solid #e5e7eb; background-color: #f3f4f6;">' . htmlspecialchars((string) $th ?? '') . '</th>';
                                    }
                                    $html .= '</tr></thead>';
                                    
                                    // Body
                                    $html .= '<tbody style="display: table-row-group; background-color: #ffffff;">';
                                    foreach ($rows as $row) {
                                        $html .= '<tr style="display: table-row; border-bottom: 1px solid #e5e7eb;">';
                                        
                                        $nisnVal = trim((string) ($row[0] ?? ''));
                                        $nameVal = trim((string) ($row[1] ?? ''));
                                        $passVal = trim((string) ($row[2] ?? ''));
                                        $classVal = trim((string) ($row[3] ?? ''));

                                        // NISN
                                        $nisnHtml = htmlspecialchars($nisnVal);
                                        if (isset($existingStudents[$nisnVal])) {
                                            $dbName = $existingStudents[$nisnVal];
                                            $nisnHtml .= ' <span style="color: #4b5563; font-size: 0.75rem;">(Update: ' . htmlspecialchars($dbName) . ')</span>';
                                        }
                                        $html .= '<td style="display: table-cell; padding: 10px 12px; color: #4b5563; border-right: 1px solid #e5e7eb;">' . $nisnHtml . '</td>';

                                        // Nama
                                        $html .= '<td style="display: table-cell; padding: 10px 12px; color: #4b5563; border-right: 1px solid #e5e7eb;">' . htmlspecialchars($nameVal) . '</td>';

                                        // Password
                                        $html .= '<td style="display: table-cell; padding: 10px 12px; color: #6b7280; border-right: 1px solid #e5e7eb;">' . htmlspecialchars($passVal === '' ? 'password (default)' : $passVal) . '</td>';

                                        // Kelas
                                        $classHtml = '';
                                        if (in_array($classVal, $existingClasses)) {
                                            $classHtml = '<span style="color: #10b981; font-weight: 500;">✓ ' . htmlspecialchars($classVal) . '</span>';
                                        } else {
                                            $classHtml = '<span style="display: inline-block; color: #b91c1c; background-color: #fee2e2; border: 1px solid #fca5a5; padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 0.75rem;">⚠️ ' . htmlspecialchars($classVal === '' ? 'Kosong' : $classVal) . ' (Tidak terdaftar)</span>';
                                        }
                                        $html .= '<td style="display: table-cell; padding: 10px 12px; border-right: 1px solid #e5e7eb;">' . $classHtml . '</td>';

                                        $html .= '</tr>';
                                    }
                                    $html .= '</tbody></table></div>';
                                    return new \Illuminate\Support\HtmlString($html);

                                } catch (\Exception $e) {
                                    return new \Illuminate\Support\HtmlString('<p class="text-sm text-red-500">Gagal membaca file: ' . $e->getMessage() . '</p>');
                                }
                            }),
                    ])
                    ->action(function (array $data) {
                        $filePath = storage_path('app/private/' . $data['file']);
                        if (!file_exists($filePath)) {
                            $filePath = storage_path('app/' . $data['file']);
                        }

                        // Validasi header & kelas sebelum import
                        try {
                            $parsedData = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass, $filePath);
                            if (!empty($parsedData[0])) {
                                $sheet = $parsedData[0];
                                $headers = $sheet[0] ?? [];

                                if (empty($headers) || strtolower(trim((string)($headers[0] ?? ''))) !== 'nisn' || strtolower(trim((string)($headers[1] ?? ''))) !== 'nama siswa' || strtolower(trim((string)($headers[3] ?? ''))) !== 'kelas') {
                                    Notification::make()->title('Import Gagal')->body('Format berkas salah. Gunakan template Siswa Baru.')->danger()->send();
                                    return;
                                }

                                $rows = array_slice($sheet, 1);
                                $existingClasses = Kelas::pluck('name')->toArray();
                                $invalidClasses = [];

                                foreach ($rows as $row) {
                                    $nisn = trim((string) ($row[0] ?? ''));
                                    if ($nisn === '') continue;

                                    $classVal = trim((string) ($row[3] ?? ''));
                                    if (!in_array($classVal, $existingClasses)) {
                                        $invalidClasses[] = $classVal === '' ? 'Kosong' : $classVal;
                                    }
                                }

                                if (!empty($invalidClasses)) {
                                    Notification::make()
                                        ->title('Import Gagal')
                                        ->body('Terdapat kelas yang tidak terdaftar di sistem: **' . implode(', ', array_unique($invalidClasses)) . '**. Harap perbaiki berkas Anda.')
                                        ->danger()
                                        ->persistent()
                                        ->send();
                                    return;
                                }
                            }
                        } catch (\Exception $e) {
                            Notification::make()->title('Import Gagal')->body('Gagal membaca file untuk validasi.')->danger()->send();
                            return;
                        }

                        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\SiswaBaruImport, $filePath);
                        Notification::make()->title('Import Siswa Baru Berhasil')->success()->send();
                    }),

                // 3. Download Template Naik Kelas (Siswa Lama)
                Action::make('download_template_naik_kelas')
                    ->label('Template Naik Kelas')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->modalHeading('Unduh Template Naik Kelas')
                    ->modalDescription('Pilih Tahun Ajaran asal siswa saat ini dan Tahun Ajaran tujuan kenaikan kelas.')
                    ->form([
                        \Filament\Forms\Components\Select::make('source_academic_year_id')
                            ->label('Dari Tahun Ajaran')
                            ->options(TahunAjaran::orderedByYear()->pluck('name', 'id')->toArray())
                            ->default(fn () => \App\Models\PengaturanSekolah::current()?->academic_year_id_active)
                            ->required()
                            ->live(),

                        \Filament\Forms\Components\Select::make('target_academic_year_id')
                            ->label('Ke Tahun Ajaran (Tujuan)')
                            ->options(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                $sourceId = $get('source_academic_year_id');
                                if (!$sourceId) return [];
                                $source = TahunAjaran::find($sourceId);
                                if (!$source) return [];
                                // Hanya tampilkan TP berikutnya langsung (start_year = source end_year)
                                return TahunAjaran::where('start_year', $source->end_year)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->required()
                            ->helperText('Hanya Tahun Ajaran yang langsung berurutan yang bisa dipilih.'),
                    ])
                    ->action(function (array $data) {
                        $sourceId  = $data['source_academic_year_id'];
                        $targetId  = $data['target_academic_year_id'];

                        $source = TahunAjaran::find($sourceId);
                        $target = TahunAjaran::find($targetId);

                        // Guard: harus berurutan
                        if (!$source || !$target || $target->start_year !== $source->end_year) {
                            Notification::make()->title('Gagal')->body('Tahun ajaran tujuan harus berurutan langsung setelah tahun ajaran asal.')->danger()->send();
                            return;
                        }

                        // Guard: pastikan semua siswa kelas 9 di TP asal sudah lulus
                        $belumLulus = \App\Models\EnrollmentSiswa::where('academic_year_id', $sourceId)
                            ->where('status', 'aktif')
                            ->whereHas('kelas', fn($q) => $q->where('grade_level', 9))
                            ->count();

                        if ($belumLulus > 0) {
                            Notification::make()
                                ->title('Kelas 9 Belum Diluluskan')
                                ->body("Masih ada **{$belumLulus}** siswa kelas 9 yang belum diluluskan di Tahun Ajaran **{$source->name}**. Harap luluskan mereka terlebih dahulu sebelum menaikkan kelas.")
                                ->danger()
                                ->persistent()
                                ->send();
                            return;
                        }

                        $safeName = str_replace('/', '-', $source->name) . '_ke_' . str_replace('/', '-', $target->name);
                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\SiswaNaikKelasExport($sourceId, $targetId),
                            'template_naik_kelas_' . $safeName . '.xlsx'
                        );
                    }),

                // 4. Upload Naik Kelas
                Action::make('import_naik_kelas')
                    ->label('Naik Kelas Massal')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('warning')
                    ->modalHeading('Naik Kelas Massal')
                    ->form([
                        \Filament\Forms\Components\Select::make('source_academic_year_id')
                            ->label('Dari Tahun Ajaran')
                            ->options(TahunAjaran::orderedByYear()->pluck('name', 'id')->toArray())
                            ->default(fn () => \App\Models\PengaturanSekolah::current()?->academic_year_id_active)
                            ->required()
                            ->live(),

                        \Filament\Forms\Components\Select::make('target_academic_year_id')
                            ->label('Ke Tahun Ajaran (Tujuan)')
                            ->options(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                $sourceId = $get('source_academic_year_id');
                                if (!$sourceId) return [];
                                $source = TahunAjaran::find($sourceId);
                                if (!$source) return [];
                                return TahunAjaran::where('start_year', $source->end_year)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->required()
                            ->live()
                            ->helperText('Hanya Tahun Ajaran yang langsung berurutan yang bisa dipilih.'),


                        \Filament\Forms\Components\FileUpload::make('file')
                            ->label('Pilih file Excel (.xlsx)')
                            ->disk('local')
                            ->directory('imports')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->required()
                            ->live(),

                        \Filament\Forms\Components\Placeholder::make('preview')
                            ->label('Preview Data Kenaikan Kelas')
                            ->hidden(fn (\Filament\Schemas\Components\Utilities\Get $get) => empty($get('file')) || empty($get('target_academic_year_id')))
                            ->content(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                $file = $get('file');
                                if (is_array($file)) {
                                    $file = array_values($file)[0] ?? null;
                                }
                                if (!$file) return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500">File belum diunggah secara sempurna.</p>');

                                if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                    $filePath = $file->getRealPath();
                                } else {
                                    $filePath = storage_path('app/private/' . $file);
                                    if (!file_exists($filePath)) {
                                        $filePath = storage_path('app/' . $file);
                                    }
                                }

                                if (!file_exists($filePath)) {
                                    return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500">Mencari file...</p>');
                                }

                                try {
                                    $data = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass, $filePath);
                                    if (empty($data[0])) {
                                        return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500">File kosong.</p>');
                                    }

                                    $sheet = $data[0];
                                    $headers = $sheet[0] ?? [];
                                    $allRows = array_slice($sheet, 1);

                                    if (empty($headers)) return '';

                                    // Validasi kecocokan header
                                    if (empty($headers) || strtolower(trim((string)($headers[0] ?? ''))) !== 'nisn' || strtolower(trim((string)($headers[1] ?? ''))) !== 'nama siswa' || !str_starts_with(strtolower(trim((string)($headers[5] ?? ''))), 'kelas baru')) {
                                        return new \Illuminate\Support\HtmlString('<p style="color: #b91c1c; font-weight: 600; padding: 10px; background-color: #fee2e2; border: 1px solid #fca5a5; border-radius: 6px;">⚠️ Berkas yang diunggah bukan template Naik Kelas yang valid. Silakan gunakan template yang sesuai.</p>');
                                    }

                                    // Filter baris kosong
                                    $rows = [];
                                    foreach ($allRows as $row) {
                                        $nisn = trim((string) ($row[0] ?? ''));
                                        if ($nisn !== '') {
                                            $rows[] = $row;
                                        }
                                    }

                                    if (empty($rows)) {
                                        return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500">Tidak ada baris data siswa yang terisi.</p>');
                                    }

                                    $existingStudents = Siswa::pluck('name', 'nisn')->toArray();
                                    $existingClasses = Kelas::pluck('name')->toArray();

                                    $html = '<div style="overflow-x: auto; overflow-y: auto; max-height: 250px; width: 100%; margin-top: 10px; margin-bottom: 10px; border: 1px solid #e5e7eb; border-radius: 8px;">';
                                    $html .= '<table style="display: table; width: 100%; border-collapse: collapse; font-size: 0.875rem; text-align: left;">';
                                    
                                    // Header
                                    $html .= '<thead style="display: table-header-group; background-color: #f3f4f6; position: sticky; top: 0; z-index: 10;">';
                                    $html .= '<tr style="display: table-row;">';
                                    foreach ($headers as $th) {
                                        $html .= '<th style="display: table-cell; padding: 10px 12px; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb; border-right: 1px solid #e5e7eb; background-color: #f3f4f6;">' . htmlspecialchars((string) $th ?? '') . '</th>';
                                    }
                                    $html .= '</tr></thead>';
                                    
                                    // Body
                                    $html .= '<tbody style="display: table-row-group; background-color: #ffffff;">';
                                    foreach ($rows as $row) {
                                        $html .= '<tr style="display: table-row; border-bottom: 1px solid #e5e7eb;">';
                                        
                                        $nisnVal = trim((string) ($row[0] ?? ''));
                                        $nameVal = trim((string) ($row[1] ?? ''));
                                        $c7Val = trim((string) ($row[2] ?? ''));
                                        $c8Val = trim((string) ($row[3] ?? ''));
                                        $c9Val = trim((string) ($row[4] ?? ''));
                                        $newClassVal = trim((string) ($row[5] ?? ''));

                                        // NISN
                                        $nisnHtml = htmlspecialchars($nisnVal);
                                        if (isset($existingStudents[$nisnVal])) {
                                            $nisnHtml = '<span style="color: #10b981; font-weight: 500;">✓ ' . $nisnHtml . '</span>';
                                        } else {
                                            $nisnHtml = '<span style="display: inline-block; color: #b91c1c; background-color: #fee2e2; border: 1px solid #fca5a5; padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 0.75rem;">⚠️ ' . $nisnHtml . ' (Tidak terdaftar)</span>';
                                        }
                                        $html .= '<td style="display: table-cell; padding: 10px 12px; border-right: 1px solid #e5e7eb;">' . $nisnHtml . '</td>';

                                        // Nama
                                        $html .= '<td style="display: table-cell; padding: 10px 12px; color: #4b5563; border-right: 1px solid #e5e7eb;">' . htmlspecialchars($nameVal) . '</td>';

                                        // Riwayat Kelas 7, 8, 9
                                        $html .= '<td style="display: table-cell; padding: 10px 12px; color: #6b7280; border-right: 1px solid #e5e7eb;">' . htmlspecialchars($c7Val) . '</td>';
                                        $html .= '<td style="display: table-cell; padding: 10px 12px; color: #6b7280; border-right: 1px solid #e5e7eb;">' . htmlspecialchars($c8Val) . '</td>';
                                        $html .= '<td style="display: table-cell; padding: 10px 12px; color: #6b7280; border-right: 1px solid #e5e7eb;">' . htmlspecialchars($c9Val) . '</td>';

                                        // Kelas Baru
                                        $newClassHtml = '';
                                        if (in_array($newClassVal, $existingClasses)) {
                                            $newClassHtml = '<span style="color: #10b981; font-weight: 500;">✓ ' . htmlspecialchars($newClassVal) . '</span>';
                                        } else {
                                            $newClassHtml = '<span style="display: inline-block; color: #b91c1c; background-color: #fee2e2; border: 1px solid #fca5a5; padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 0.75rem;">⚠️ ' . htmlspecialchars($newClassVal === '' ? 'Kosong' : $newClassVal) . ' (Tidak terdaftar)</span>';
                                        }
                                        $html .= '<td style="display: table-cell; padding: 10px 12px; border-right: 1px solid #e5e7eb;">' . $newClassHtml . '</td>';

                                        $html .= '</tr>';
                                    }
                                    $html .= '</tbody></table></div>';
                                    return new \Illuminate\Support\HtmlString($html);

                                } catch (\Exception $e) {
                                    return new \Illuminate\Support\HtmlString('<p class="text-sm text-red-500">Gagal membaca file: ' . $e->getMessage() . '</p>');
                                }
                            }),
                    ])
                    ->action(function (array $data) {
                        $filePath = storage_path('app/private/' . $data['file']);
                        if (!file_exists($filePath)) {
                            $filePath = storage_path('app/' . $data['file']);
                        }

                        // Validasi NISN & Kelas Baru sebelum memproses
                        try {
                            $parsedData = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass, $filePath);
                            if (!empty($parsedData[0])) {
                                $sheet = $parsedData[0];
                                $headers = $sheet[0] ?? [];

                                if (empty($headers) || strtolower(trim((string)($headers[0] ?? ''))) !== 'nisn' || strtolower(trim((string)($headers[1] ?? ''))) !== 'nama siswa' || !str_starts_with(strtolower(trim((string)($headers[5] ?? ''))), 'kelas baru')) {
                                    Notification::make()->title('Import Gagal')->body('Format berkas salah. Gunakan template Naik Kelas.')->danger()->send();
                                    return;
                                }

                                $rows = array_slice($sheet, 1);
                                $existingStudents = Siswa::pluck('name', 'nisn')->toArray();
                                $existingClasses = Kelas::pluck('name')->toArray();
                                
                                $invalidNisns = [];
                                $invalidClasses = [];

                                foreach ($rows as $row) {
                                    $nisnVal = trim((string) ($row[0] ?? ''));
                                    if ($nisnVal === '') continue;

                                    if (!isset($existingStudents[$nisnVal])) {
                                        $invalidNisns[] = $nisnVal;
                                    }

                                    $newClassVal = trim((string) ($row[5] ?? ''));
                                    if (!in_array($newClassVal, $existingClasses)) {
                                        $invalidClasses[] = $newClassVal === '' ? 'Kosong' : $newClassVal;
                                    }
                                }

                                if (!empty($invalidNisns) || !empty($invalidClasses)) {
                                    $msg = '';
                                    if (!empty($invalidNisns)) {
                                        $msg .= 'Siswa tidak terdaftar (NISN): **' . implode(', ', array_unique($invalidNisns)) . '**. ';
                                    }
                                    if (!empty($invalidClasses)) {
                                        $msg .= 'Kelas Baru tidak valid: **' . implode(', ', array_unique($invalidClasses)) . '**. ';
                                    }

                                    Notification::make()
                                        ->title('Import Gagal')
                                        ->body($msg . 'Harap perbaiki berkas Anda.')
                                        ->danger()
                                        ->persistent()
                                        ->send();
                                    return;
                                }
                            }
                        } catch (\Exception $e) {
                            Notification::make()->title('Import Gagal')->body('Gagal membaca file untuk validasi.')->danger()->send();
                            return;
                        }

                        // Guard: harus berurutan
                        $sourceId = $data['source_academic_year_id'];
                        $targetId = $data['target_academic_year_id'];
                        $source = TahunAjaran::find($sourceId);
                        $target = TahunAjaran::find($targetId);

                        if (!$source || !$target || $target->start_year !== $source->end_year) {
                            Notification::make()->title('Import Gagal')->body('Tahun ajaran tujuan harus berurutan langsung setelah tahun ajaran asal.')->danger()->send();
                            return;
                        }

                        // Guard: kelas 9 di TP asal harus sudah semua lulus
                        $belumLulus = \App\Models\EnrollmentSiswa::where('academic_year_id', $sourceId)
                            ->where('status', 'aktif')
                            ->whereHas('kelas', fn($q) => $q->where('grade_level', 9))
                            ->count();

                        if ($belumLulus > 0) {
                            Notification::make()
                                ->title('Kelas 9 Belum Diluluskan')
                                ->body("Masih ada **{$belumLulus}** siswa kelas 9 yang belum diluluskan di Tahun Ajaran **{$source->name}**. Harap luluskan mereka terlebih dahulu.")
                                ->danger()
                                ->persistent()
                                ->send();
                            return;
                        }

                        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\SiswaNaikKelasImport($sourceId, $targetId), $filePath);
                        Notification::make()->title('Kenaikan Kelas Massal Berhasil')->body("Siswa dari TP **{$source->name}** berhasil dinaikkan ke TP **{$target->name}**.")->success()->send();
                    }),
            ])
            ->filters([
                TrashedFilter::make(),

                // Filter Status Pendaftaran
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'aktif' => 'Aktif',
                        'naik' => 'Naik Kelas',
                        'lulus' => 'Lulus',
                        'tinggal' => 'Tinggal Kelas',
                        'pindah' => 'Pindah Sekolah',
                    ])
                    ->query(function (Builder $query, array $data, $livewire) {
                        if (!empty($data['value'])) {
                            $targetYearId = $livewire->tableFilters['academic_year_id']['value'] ?? null;
                            $activeYearId = $targetYearId ?: (\App\Models\PengaturanSekolah::current()?->academic_year_id_active);
                            
                            $query->whereHas('enrollments', function (Builder $query) use ($data, $activeYearId) {
                                $query->where('academic_year_id', $activeYearId)
                                      ->where('status', $data['value']);
                            });
                        }
                    }),

                // Filter Tahun Ajaran
                SelectFilter::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->options(TahunAjaran::pluck('name', 'id')->toArray())
                    ->default($activeYearId)
                    ->query(function (Builder $query, array $data) {
                        // Query hanya menerapkan filter jika ada nilainya, 
                        // tapi kita tangani ini secara gabungan di filter Kelas jika diperlukan.
                        // Di sini kita biarkan kosong jika kita ingin menggabungkannya.
                        // Namun lebih baik ditangani melalui relasi whereHas
                        if (!empty($data['value'])) {
                            $query->whereHas('enrollments', function (Builder $query) use ($data) {
                                $query->where('academic_year_id', $data['value']);
                            });
                        }
                    }),

                // Filter Kelas
                SelectFilter::make('class_id')
                    ->label('Kelas')
                    ->options(Kelas::pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('enrollments', function (Builder $query) use ($data) {
                                $query->where('class_id', $data['value']);
                            });
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make(),

                // Custom Action: Reset Password
                Action::make('resetPassword')
                    ->label('Reset Password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reset Password Siswa')
                    ->modalDescription(fn (Siswa $record): string => "Password baru akan di-generate untuk {$record->name} ({$record->nisn}). Pastikan catat password sebelum menutup dialog ini.")
                    ->action(function (Siswa $record): void {
                        $newPassword = Str::random(8);

                        $record->update([
                            'password'             => $newPassword, // otomatis di-hash via cast 'hashed'
                            'must_change_password' => true,
                        ]);

                        Notification::make()
                            ->title('Password berhasil direset')
                            ->body("Username: **{$record->username}**\nPassword baru: **{$newPassword}**\n\nSiswa wajib ganti password saat login berikutnya.")
                            ->success()
                            ->persistent()
                            ->send();
                    }),

                // Custom Action: Batalkan Kelulusan
                Action::make('batalkan_kelulusan')
                    ->label('Batalkan Kelulusan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Kelulusan Siswa')
                    ->modalDescription('Apakah Anda yakin ingin membatalkan kelulusan siswa ini dan mengembalikan statusnya menjadi "Aktif"?')
                    ->visible(function (Siswa $record, $livewire) {
                        $targetYearId = $livewire->tableFilters['academic_year_id']['value'] ?? null;
                        $activeYearId = $targetYearId ?: (\App\Models\PengaturanSekolah::current()?->academic_year_id_active);
                        if (!$activeYearId) return false;
                        
                        $enrollment = $record->enrollments()->where('academic_year_id', $activeYearId)->first();
                        return $enrollment && $enrollment->status === 'lulus';
                    })
                    ->action(function (Siswa $record, $livewire) {
                        $targetYearId = $livewire->tableFilters['academic_year_id']['value'] ?? null;
                        $activeYearId = $targetYearId ?: (\App\Models\PengaturanSekolah::current()?->academic_year_id_active);
                        
                        $enrollment = $record->enrollments()->where('academic_year_id', $activeYearId)->first();
                        if ($enrollment) {
                            $enrollment->update(['status' => 'aktif']);
                            Notification::make()
                                ->title('Kelulusan Dibatalkan')
                                ->body("Kelulusan siswa **{$record->name}** telah dibatalkan. Status pendaftaran dikembalikan menjadi **Aktif**.")
                                ->success()
                                ->send();
                        }
                    }),

                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}

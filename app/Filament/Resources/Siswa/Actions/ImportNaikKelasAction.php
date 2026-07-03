<?php

namespace App\Filament\Resources\Siswa\Actions;

use Filament\Actions\Action;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\PengaturanSekolah;
use Filament\Notifications\Notification;

class ImportNaikKelasAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'import_naik_kelas';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->visible(fn () => PengaturanSekolah::current()?->enable_promotion_features ?? false)
            ->label('Naik Kelas Massal')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('warning')
            ->modalHeading('Naik Kelas Massal')
            ->form([
                \Filament\Forms\Components\Select::make('source_academic_year_id')
                    ->label('Dari Tahun Ajaran')
                    ->options(TahunAjaran::orderedByYear()->pluck('name', 'id')->toArray())
                    ->default(fn () => PengaturanSekolah::current()?->academic_year_id_active)
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
                        $studentIds = Siswa::pluck('id', 'nisn')->toArray();
                        $existingClasses = Kelas::pluck('name')->toArray();
                        $classGradeLevels = Kelas::pluck('grade_level', 'name')->toArray();
                        $sourceYearId = $data['source_academic_year_id'];
                        $oldEnrollments = \App\Models\EnrollmentSiswa::where('academic_year_id', $sourceYearId)->with('kelas')->get()->keyBy('student_id');
                        
                        $invalidNisns = [];
                        $invalidClasses = [];
                        $invalidJumps = [];

                        foreach ($rows as $row) {
                            $nisnVal = trim((string) ($row[0] ?? ''));
                            if ($nisnVal === '') continue;

                            if (!isset($existingStudents[$nisnVal])) {
                                $invalidNisns[] = $nisnVal;
                            }

                            $newClassVal = trim((string) ($row[5] ?? ''));
                            if (!in_array($newClassVal, $existingClasses)) {
                                $invalidClasses[] = $newClassVal === '' ? 'Kosong' : $newClassVal;
                            } else {
                                // Validate jumping grade level > 1
                                if (isset($studentIds[$nisnVal])) {
                                    $sId = $studentIds[$nisnVal];
                                    if (isset($oldEnrollments[$sId])) {
                                        $oldClass = $oldEnrollments[$sId]->kelas;
                                        $oldGrade = $oldClass ? $oldClass->grade_level : null;
                                        $newGrade = $classGradeLevels[$newClassVal] ?? null;

                                        if ($oldGrade !== null && $newGrade !== null) {
                                            if (($newGrade - $oldGrade) > 1) {
                                                $invalidJumps[] = $nisnVal . ' (Naik dari kelas ' . $oldGrade . ' ke ' . $newGrade . ')';
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if (!empty($invalidNisns) || !empty($invalidClasses) || !empty($invalidJumps)) {
                            $msg = '';
                            if (!empty($invalidNisns)) {
                                $msg .= 'Siswa tidak terdaftar (NISN): **' . implode(', ', array_unique($invalidNisns)) . '**. ';
                            }
                            if (!empty($invalidClasses)) {
                                $msg .= 'Kelas Baru tidak valid: **' . implode(', ', array_unique($invalidClasses)) . '**. ';
                            }
                            if (!empty($invalidJumps)) {
                                $msg .= 'Siswa melompat lebih dari 1 tingkat kelas (tidak valid): **' . implode(', ', array_unique($invalidJumps)) . '**. ';
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
            });
    }
}

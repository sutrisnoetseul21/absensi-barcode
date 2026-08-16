<?php

namespace App\Filament\Akademik\Resources\Kelas\Pages;

use App\Filament\Akademik\Resources\Kelas\KelasResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KelasTemplateExport;
use App\Imports\KelasImport;
use App\Models\Guru;

class ListKelas extends ListRecords
{
    protected static string $resource = KelasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Kelas')
                ->icon('heroicon-o-plus')
                ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_akademik_editor') || auth()->user()?->hasRole('admin_master_editor')),

            Action::make('download_template')
                ->label('Download Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning')
                ->url(route('admin.kelas.download-template'))
                ->openUrlInNewTab()
                ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_akademik_editor') || auth()->user()?->hasRole('admin_master_editor')),

            Action::make('import_kelas')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_akademik_editor') || auth()->user()?->hasRole('admin_master_editor'))
                ->form([
                    FileUpload::make('file')
                        ->label('Pilih file Excel (.xlsx)')
                        ->disk('local')
                        ->directory('imports')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                        ->required()
                        ->live(),

                    Placeholder::make('preview')
                        ->label('Preview Data (5 Baris Pertama)')
                        ->hidden(fn (\Filament\Schemas\Components\Utilities\Get $get) => empty($get('file')))
                        ->content(function (\Filament\Schemas\Components\Utilities\Get $get) {
                            $file = $get('file');
                            if (is_array($file)) {
                                $file = array_values($file)[0] ?? null;
                            }
                            if (!$file) return new HtmlString('<p class="text-sm text-gray-500">File belum diunggah secara sempurna.</p>');

                            if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                $filePath = $file->getRealPath();
                            } else {
                                $filePath = storage_path('app/private/' . $file);
                                if (!file_exists($filePath)) {
                                    $filePath = storage_path('app/' . $file);
                                }
                            }

                            if (!file_exists($filePath)) {
                                return new HtmlString('<p class="text-sm text-gray-500">Mencari file...</p>');
                            }

                            try {
                                $data = Excel::toArray(new \stdClass, $filePath);
                                if (empty($data[0])) {
                                    return new HtmlString('<p class="text-sm text-gray-500">File kosong.</p>');
                                }

                                $sheet = $data[0];
                                $headers = $sheet[0] ?? [];
                                
                                if (empty($headers) || strtolower(trim((string)($headers[0] ?? ''))) !== 'nama kelas' || strtolower(trim((string)($headers[1] ?? ''))) !== 'tingkat (7, 8, 9)') {
                                    return new HtmlString('<p style="color: #b91c1c; font-weight: 600; padding: 10px; background-color: #fee2e2; border: 1px solid #fca5a5; border-radius: 6px;">⚠️ Berkas yang diunggah bukan template Kelas yang valid. Silakan unduh template yang sesuai.</p>');
                                }

                                $allRows = array_slice($sheet, 1);

                                if (empty($headers)) return '';

                                $rows = [];
                                foreach ($allRows as $row) {
                                    $className = trim((string) ($row[0] ?? ''));
                                    if ($className !== '') {
                                        $rows[] = $row;
                                    }
                                }

                                if (empty($rows)) {
                                    return new HtmlString('<p class="text-sm text-gray-500">Tidak ada baris data kelas yang terisi.</p>');
                                }

                                $guruNames = Guru::pluck('name')->toArray();
                                $guruNamesLower = array_map('strtolower', $guruNames);

                                $html = '<div style="overflow-x: auto; overflow-y: auto; max-height: 250px; width: 100%; margin-top: 10px; margin-bottom: 10px; border: 1px solid #e5e7eb; border-radius: 8px;">';
                                $html .= '<table style="display: table; width: 100%; border-collapse: collapse; font-size: 0.875rem; text-align: left;">';
                                
                                $html .= '<thead style="display: table-header-group; background-color: #f3f4f6; position: sticky; top: 0; z-index: 10;">';
                                $html .= '<tr style="display: table-row;">';
                                foreach ($headers as $th) {
                                    $html .= '<th style="display: table-cell; padding: 10px 12px; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb; border-right: 1px solid #e5e7eb; background-color: #f3f4f6;">' . htmlspecialchars((string) $th ?? '') . '</th>';
                                }
                                $html .= '</tr></thead>';
                                
                                $html .= '<tbody style="display: table-row-group; background-color: #ffffff;">';
                                foreach ($rows as $row) {
                                    $html .= '<tr style="display: table-row; border-bottom: 1px solid #e5e7eb;">';
                                    foreach ($row as $index => $td) {
                                        $val = trim((string) ($td ?? ''));
                                        
                                        if ($index === 1) {
                                            $gradeInt = filter_var($val, FILTER_VALIDATE_INT);
                                            if ($gradeInt !== false && in_array($gradeInt, [7, 8, 9])) {
                                                $tdContent = '<span style="color: #10b981; font-weight: 500;">✓ ' . htmlspecialchars($val) . '</span>';
                                            } else {
                                                $tdContent = '<span style="display: inline-block; color: #b91c1c; background-color: #fee2e2; border: 1px solid #fca5a5; padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 0.75rem;" title="Tingkat tidak valid. Hanya boleh angka 7, 8, atau 9.">⚠️ ' . htmlspecialchars($val === '' ? 'Kosong' : $val) . ' (Tidak valid)</span>';
                                            }
                                        }
                                        elseif ($index === 2 && $val !== '' && $val !== '-') {
                                            $exists = in_array(strtolower($val), $guruNamesLower);
                                            if ($exists) {
                                                $tdContent = '<span style="color: #10b981; font-weight: 500;">✓ ' . htmlspecialchars($val) . '</span>';
                                            } else {
                                                $tdContent = '<span style="display: inline-block; color: #b91c1c; background-color: #fee2e2; border: 1px solid #fca5a5; padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 0.75rem;" title="Nama guru ini tidak terdaftar di sistem. Harap sesuaikan dengan daftar guru yang ada.">⚠️ ' . htmlspecialchars($val) . ' (Tidak terdaftar)</span>';
                                            }
                                        } else {
                                            $tdContent = htmlspecialchars($val === '' ? '—' : $val);
                                        }

                                        $html .= '<td style="display: table-cell; padding: 10px 12px; color: #4b5563; border-right: 1px solid #e5e7eb;">' . $tdContent . '</td>';
                                    }
                                    $html .= '</tr>';
                                }
                                $html .= '</tbody></table></div>';
                                $html .= '<p style="font-size: 0.75rem; color: #6b7280; margin-top: 4px;">* Menampilkan seluruh data kelas yang terisi pada Excel (maksimal 33 baris).</p>';

                                return new HtmlString($html);
                            } catch (\Exception $e) {
                                return new HtmlString('<p class="text-sm text-red-500">Gagal membaca file: ' . $e->getMessage() . '</p>');
                            }
                        }),
                ])
                ->action(function (array $data) {
                    $filePath = storage_path('app/private/' . $data['file']);
                    if (!file_exists($filePath)) {
                        $filePath = storage_path('app/' . $data['file']);
                    }

                    try {
                        $parsedData = Excel::toArray(new \stdClass, $filePath);
                        if (!empty($parsedData[0])) {
                            $sheet = $parsedData[0];
                            $headers = $sheet[0] ?? [];

                            if (empty($headers) || strtolower(trim((string)($headers[0] ?? ''))) !== 'nama kelas' || strtolower(trim((string)($headers[1] ?? ''))) !== 'tingkat (7, 8, 9)') {
                                Notification::make()
                                    ->title('Import Gagal')
                                    ->body('Format berkas tidak sesuai. Silakan gunakan template Kelas yang diunduh dari menu Kelas.')
                                    ->danger()
                                    ->persistent()
                                    ->send();
                                return;
                            }

                            $rows = array_slice($sheet, 1);
                            
                            $guruNames = Guru::pluck('name')->toArray();
                            $guruNamesLower = array_map('strtolower', $guruNames);
                            
                            $invalidGurus = [];
                            $invalidGrades = [];
                            foreach ($rows as $row) {
                                $className = trim((string) ($row[0] ?? ''));
                                if ($className === '') {
                                    continue;
                                }

                                $tingkat = trim((string) ($row[1] ?? ''));
                                $gradeInt = filter_var($tingkat, FILTER_VALIDATE_INT);
                                if ($gradeInt === false || !in_array($gradeInt, [7, 8, 9])) {
                                    $invalidGrades[] = $tingkat === '' ? 'Kosong' : $tingkat;
                                }

                                $namaGuru = trim((string) ($row[2] ?? ''));
                                if ($namaGuru !== '' && $namaGuru !== '-' && $namaGuru !== '—') {
                                    if (!in_array(strtolower($namaGuru), $guruNamesLower)) {
                                        $invalidGurus[] = $namaGuru;
                                    }
                                }
                            }
                            
                            if (!empty($invalidGrades) || !empty($invalidGurus)) {
                                $bodyMessage = '';
                                if (!empty($invalidGrades)) {
                                    $bodyMessage .= 'Tingkat kelas tidak valid: **' . implode(', ', array_unique($invalidGrades)) . '** (Harus 7, 8, atau 9). ';
                                }
                                if (!empty($invalidGurus)) {
                                    $bodyMessage .= 'Wali kelas tidak terdaftar: **' . implode(', ', array_unique($invalidGurus)) . '**. ';
                                }

                                Notification::make()
                                    ->title('Import Gagal')
                                    ->body($bodyMessage . 'Harap perbaiki file Excel Anda.')
                                    ->danger()
                                    ->persistent()
                                    ->send();
                                return;
                            }
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Import Gagal')
                            ->body('Gagal membaca file Excel untuk validasi: ' . $e->getMessage())
                            ->danger()
                            ->send();
                        return;
                    }

                    Excel::import(new KelasImport, $filePath);
                    Notification::make()
                        ->title('Import berhasil')
                        ->success()
                        ->send();
                }),
        ];
    }
}

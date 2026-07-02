<?php

namespace App\Filament\Resources\Kelas\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class KelasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Kelas')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('grade_level')
                    ->label('Tingkat')
                    ->sortable()
                    ->formatStateUsing(fn (int $state): string => "Kelas {$state}"),

                TextColumn::make('wali_kelas')
                    ->label('Wali Kelas (Aktif)')
                    ->getStateUsing(function (\App\Models\Kelas $record) {
                        $activeTahunAjaranId = \App\Models\PengaturanSekolah::current()?->academic_year_id_active;
                        if (!$activeTahunAjaranId) return '—';

                        $kelasAjaran = $record->kelasAjarans()->where('academic_year_id', $activeTahunAjaranId)->first();
                        return $kelasAjaran?->guru?->name ?? '—';
                    }),
            ])
            ->headerActions([
                Action::make('download_template')
                    ->label('Download Template')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn () => \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\KelasTemplateExport, 'template_kelas.xlsx')),

                Action::make('import_kelas')
                    ->label('Import Excel')
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
                            ->label('Preview Data (5 Baris Pertama)')
                            ->hidden(fn (\Filament\Schemas\Components\Utilities\Get $get) => empty($get('file')))
                            ->content(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                $file = $get('file');
                                if (is_array($file)) {
                                    $file = array_values($file)[0] ?? null;
                                }
                                if (!$file) return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500">File belum diunggah secara sempurna.</p>');

                                // Sometimes the livewire temporary file is given directly. If it is a string path:
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
                                    
                                    // Validasi kecocokan header
                                    if (empty($headers) || strtolower(trim((string)($headers[0] ?? ''))) !== 'nama kelas' || strtolower(trim((string)($headers[1] ?? ''))) !== 'tingkat (7, 8, 9)') {
                                        return new \Illuminate\Support\HtmlString('<p style="color: #b91c1c; font-weight: 600; padding: 10px; background-color: #fee2e2; border: 1px solid #fca5a5; border-radius: 6px;">⚠️ Berkas yang diunggah bukan template Kelas yang valid. Silakan unduh template yang sesuai.</p>');
                                    }

                                    $allRows = array_slice($sheet, 1); // skip header

                                    if (empty($headers)) return '';

                                    // Filter baris kosong (di mana nama kelas kosong)
                                    $rows = [];
                                    foreach ($allRows as $row) {
                                        $className = trim((string) ($row[0] ?? ''));
                                        if ($className !== '') {
                                            $rows[] = $row;
                                        }
                                    }

                                    if (empty($rows)) {
                                        return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500">Tidak ada baris data kelas yang terisi.</p>');
                                    }

                                    // Ambil semua nama guru untuk validasi
                                    $guruNames = \App\Models\Guru::pluck('name')->toArray();
                                    $guruNamesLower = array_map('strtolower', $guruNames);

                                    // Render table dengan style inline yang sangat defensif agar tidak terpengaruh CSS reset
                                    // Menggunakan max-height: 250px dan overflow-y: auto agar jika > 5 baris otomatis bisa di-scroll secara vertikal
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
                                        foreach ($row as $index => $td) {
                                            $val = trim((string) ($td ?? ''));
                                            
                                            // Cek jika kolom ini adalah kolom Tingkat (indeks ke-1)
                                            if ($index === 1) {
                                                $gradeInt = filter_var($val, FILTER_VALIDATE_INT);
                                                if ($gradeInt !== false && in_array($gradeInt, [7, 8, 9])) {
                                                    $tdContent = '<span style="color: #10b981; font-weight: 500;">✓ ' . htmlspecialchars($val) . '</span>';
                                                } else {
                                                    $tdContent = '<span style="display: inline-block; color: #b91c1c; background-color: #fee2e2; border: 1px solid #fca5a5; padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 0.75rem;" title="Tingkat tidak valid. Hanya boleh angka 7, 8, atau 9.">⚠️ ' . htmlspecialchars($val === '' ? 'Kosong' : $val) . ' (Tidak valid)</span>';
                                                }
                                            }
                                            // Cek jika kolom ini adalah kolom Wali Kelas (indeks ke-2)
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

                        // Validasi nama guru & tingkat sebelum benar-benar di-import
                        try {
                            $parsedData = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass, $filePath);
                            if (!empty($parsedData[0])) {
                                $sheet = $parsedData[0];
                                $headers = $sheet[0] ?? [];

                                // Validasi kecocokan header
                                if (empty($headers) || strtolower(trim((string)($headers[0] ?? ''))) !== 'nama kelas' || strtolower(trim((string)($headers[1] ?? ''))) !== 'tingkat (7, 8, 9)') {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Import Gagal')
                                        ->body('Format berkas tidak sesuai. Silakan gunakan template Kelas yang diunduh dari menu Kelas.')
                                        ->danger()
                                        ->persistent()
                                        ->send();
                                    return;
                                }

                                $rows = array_slice($sheet, 1); // lewati header
                                
                                $guruNames = \App\Models\Guru::pluck('name')->toArray();
                                $guruNamesLower = array_map('strtolower', $guruNames);
                                
                                $invalidGurus = [];
                                $invalidGrades = [];
                                foreach ($rows as $row) {
                                    $className = trim((string) ($row[0] ?? ''));
                                    if ($className === '') {
                                        continue; // skip empty rows
                                    }

                                    // Validasi Tingkat
                                    $tingkat = trim((string) ($row[1] ?? ''));
                                    $gradeInt = filter_var($tingkat, FILTER_VALIDATE_INT);
                                    if ($gradeInt === false || !in_array($gradeInt, [7, 8, 9])) {
                                        $invalidGrades[] = $tingkat === '' ? 'Kosong' : $tingkat;
                                    }

                                    // Validasi Wali Kelas
                                    $namaGuru = trim((string) ($row[2] ?? ''));
                                    if ($namaGuru !== '' && $namaGuru !== '-' && $namaGuru !== '—') {
                                        if (!in_array(strtolower($namaGuru), $guruNamesLower)) {
                                            $invalidGurus[] = $namaGuru;
                                        }
                                    }
                                }
                                
                                // Jika ada error tingkat atau guru, batalkan proses import
                                if (!empty($invalidGrades) || !empty($invalidGurus)) {
                                    $bodyMessage = '';
                                    if (!empty($invalidGrades)) {
                                        $bodyMessage .= 'Tingkat kelas tidak valid: **' . implode(', ', array_unique($invalidGrades)) . '** (Harus 7, 8, atau 9). ';
                                    }
                                    if (!empty($invalidGurus)) {
                                        $bodyMessage .= 'Wali kelas tidak terdaftar: **' . implode(', ', array_unique($invalidGurus)) . '**. ';
                                    }

                                    \Filament\Notifications\Notification::make()
                                        ->title('Import Gagal')
                                        ->body($bodyMessage . 'Harap perbaiki file Excel Anda.')
                                        ->danger()
                                        ->persistent()
                                        ->send();
                                    return;
                                }
                            }
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Import Gagal')
                                ->body('Gagal membaca file Excel untuk validasi: ' . $e->getMessage())
                                ->danger()
                                ->send();
                            return;
                        }

                        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\KelasImport, $filePath);
                        \Filament\Notifications\Notification::make()
                            ->title('Import berhasil')
                            ->success()
                            ->send();
                    }),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('grade_level');
    }
}

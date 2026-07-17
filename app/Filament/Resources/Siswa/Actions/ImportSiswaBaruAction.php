<?php

namespace App\Filament\Resources\Siswa\Actions;

use App\Models\Siswa;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

/**
 * Action untuk import siswa baru dari file Excel.
 *
 * Sesuai arsitektur pisah total (Refactoring Tahap 3):
 * - Action ini HANYA mengisi tabel students (Master Data)
 * - Tidak ada lagi logika enrollment ke kelas
 * - Template Excel tidak lagi memiliki kolom Kelas
 * - Pendaftaran kelas dilakukan terpisah via EnrollmentResource
 */
class ImportSiswaBaruAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'import_siswa_baru';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Import Siswa Baru')
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

                            $sheet   = $data[0];
                            $headers = $sheet[0] ?? [];
                            $allRows = array_slice($sheet, 1);

                            if (empty($headers)) return '';

                            // Validasi kecocokan header (7 kolom, tanpa Kelas)
                            if (
                                strtolower(trim((string)($headers[0] ?? ''))) !== 'nisn' ||
                                strtolower(trim((string)($headers[1] ?? ''))) !== 'nis'  ||
                                strtolower(trim((string)($headers[2] ?? ''))) !== 'nama siswa'
                            ) {
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

                            $html  = '<div style="overflow-x: auto; overflow-y: auto; max-height: 250px; width: 100%; margin-top: 10px; margin-bottom: 10px; border: 1px solid #e5e7eb; border-radius: 8px;">';
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

                                $nisnVal       = trim((string) ($row[0] ?? ''));
                                $nisVal        = trim((string) ($row[1] ?? ''));
                                $nameVal       = trim((string) ($row[2] ?? ''));
                                $tempatLahirVal = trim((string) ($row[3] ?? ''));
                                $tglLahirVal   = trim((string) ($row[4] ?? ''));
                                $alamatVal     = trim((string) ($row[5] ?? ''));
                                $passVal       = trim((string) ($row[6] ?? ''));

                                // NISN — tandai jika sudah ada di DB (update)
                                $nisnHtml = htmlspecialchars($nisnVal);
                                if (isset($existingStudents[$nisnVal])) {
                                    $dbName   = $existingStudents[$nisnVal];
                                    $nisnHtml .= ' <span style="color: #4b5563; font-size: 0.75rem;">(Update: ' . htmlspecialchars($dbName) . ')</span>';
                                }
                                $html .= '<td style="display: table-cell; padding: 10px 12px; color: #4b5563; border-right: 1px solid #e5e7eb;">' . $nisnHtml . '</td>';
                                $html .= '<td style="display: table-cell; padding: 10px 12px; color: #4b5563; border-right: 1px solid #e5e7eb;">' . htmlspecialchars($nisVal) . '</td>';
                                $html .= '<td style="display: table-cell; padding: 10px 12px; color: #4b5563; border-right: 1px solid #e5e7eb;">' . htmlspecialchars($nameVal) . '</td>';
                                $html .= '<td style="display: table-cell; padding: 10px 12px; color: #4b5563; border-right: 1px solid #e5e7eb;">' . htmlspecialchars($tempatLahirVal) . '</td>';
                                $html .= '<td style="display: table-cell; padding: 10px 12px; color: #4b5563; border-right: 1px solid #e5e7eb;">' . htmlspecialchars($tglLahirVal) . '</td>';
                                $html .= '<td style="display: table-cell; padding: 10px 12px; color: #4b5563; border-right: 1px solid #e5e7eb;">' . htmlspecialchars($alamatVal) . '</td>';
                                $html .= '<td style="display: table-cell; padding: 10px 12px; color: #6b7280; border-right: 1px solid #e5e7eb;">' . htmlspecialchars($passVal === '' ? 'NISN (default)' : $passVal) . '</td>';

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

                // Validasi header sebelum import
                try {
                    $parsedData = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass, $filePath);
                    if (!empty($parsedData[0])) {
                        $headers = $parsedData[0][0] ?? [];

                        if (
                            strtolower(trim((string)($headers[0] ?? ''))) !== 'nisn' ||
                            strtolower(trim((string)($headers[1] ?? ''))) !== 'nis'  ||
                            strtolower(trim((string)($headers[2] ?? ''))) !== 'nama siswa'
                        ) {
                            Notification::make()->title('Import Gagal')->body('Format berkas salah. Gunakan template Siswa Baru.')->danger()->send();
                            return;
                        }
                    }
                } catch (\Exception $e) {
                    Notification::make()->title('Import Gagal')->body('Gagal membaca file untuk validasi.')->danger()->send();
                    return;
                }

                \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\SiswaBaruImport, $filePath);
                Notification::make()
                    ->title('Import Siswa Baru Berhasil')
                    ->body('Data siswa berhasil diimpor. Silakan daftarkan siswa ke kelas melalui menu Pendaftaran Kelas.')
                    ->success()
                    ->send();
            });
    }
}

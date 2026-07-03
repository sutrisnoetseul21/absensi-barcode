<?php

namespace App\Filament\Resources\Guru\Tables;

use App\Models\Guru;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class GuruTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Guru')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->default('—'),

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
                Action::make('download_template')
                    ->label('Download Template')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn () => \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\GuruTemplateExport, 'template_guru.xlsx')),

                Action::make('import_guru')
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
                                    
                                    // Validasi kecocokan header
                                    if (empty($headers) || strtolower(trim((string)($headers[0] ?? ''))) !== 'nama guru') {
                                        return new \Illuminate\Support\HtmlString('<p style="color: #b91c1c; font-weight: 600; padding: 10px; background-color: #fee2e2; border: 1px solid #fca5a5; border-radius: 6px;">⚠️ Berkas yang diunggah bukan template Guru yang valid. Silakan unduh template yang sesuai.</p>');
                                    }

                                    $allRows = array_slice($sheet, 1);

                                    if (empty($headers)) return '';

                                    // Filter baris kosong (di mana nama guru kosong)
                                    $rows = [];
                                    foreach ($allRows as $row) {
                                        $name = trim((string) ($row[0] ?? ''));
                                        if ($name !== '') {
                                            $rows[] = $row;
                                        }
                                    }

                                    if (empty($rows)) {
                                        return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500">Tidak ada baris data guru yang terisi.</p>');
                                    }

                                    // Ambil semua NIP untuk validasi duplikat
                                    $existingNips = \App\Models\Guru::whereNotNull('nip')->pluck('name', 'nip')->toArray();

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
                                        
                                        $nameVal = trim((string) ($row[0] ?? ''));
                                        $nipVal = trim((string) ($row[1] ?? ''));
                                        $passVal = trim((string) ($row[2] ?? ''));

                                        // Column 0: Nama Guru
                                        $html .= '<td style="display: table-cell; padding: 10px 12px; color: #4b5563; border-right: 1px solid #e5e7eb;">' . htmlspecialchars($nameVal) . '</td>';

                                        // Column 1: NIP
                                        $nipHtml = '';
                                        if ($nipVal !== '' && $nipVal !== '-') {
                                            // Cek jika NIP bentrok dengan Guru yang beda namanya di DB
                                            if (isset($existingNips[$nipVal])) {
                                                $dbName = $existingNips[$nipVal];
                                                if (strtolower($dbName) !== strtolower($nameVal)) {
                                                    $nipHtml = '<span style="display: inline-block; color: #b91c1c; background-color: #fee2e2; border: 1px solid #fca5a5; padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 0.75rem;" title="NIP ini sudah terdaftar untuk guru: ' . htmlspecialchars($dbName) . '">⚠️ ' . htmlspecialchars($nipVal) . ' (Milik: ' . htmlspecialchars($dbName) . ')</span>';
                                                } else {
                                                    $nipHtml = '<span style="color: #4b5563;">✓ ' . htmlspecialchars($nipVal) . ' (Update)</span>';
                                                }
                                            } else {
                                                $nipHtml = '<span style="color: #10b981; font-weight: 500;">✓ ' . htmlspecialchars($nipVal) . '</span>';
                                            }
                                        } else {
                                            $nipHtml = '<span style="color: #9ca3af;">—</span>';
                                        }
                                        $html .= '<td style="display: table-cell; padding: 10px 12px; border-right: 1px solid #e5e7eb;">' . $nipHtml . '</td>';

                                        // Column 2: Password
                                        $passHtml = htmlspecialchars($passVal === '' ? 'password (default)' : $passVal);
                                        $html .= '<td style="display: table-cell; padding: 10px 12px; color: #6b7280; border-right: 1px solid #e5e7eb;">' . $passHtml . '</td>';

                                        $html .= '</tr>';
                                    }
                                    $html .= '</tbody></table></div>';
                                    $html .= '<p style="font-size: 0.75rem; color: #6b7280; margin-top: 4px;">* Menampilkan seluruh data guru yang terisi pada Excel.</p>';

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

                        // Validasi konflik NIP sebelum import
                        try {
                            $parsedData = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass, $filePath);
                            if (!empty($parsedData[0])) {
                                $sheet = $parsedData[0];
                                $headers = $sheet[0] ?? [];

                                // Validasi kecocokan header
                                if (empty($headers) || strtolower(trim((string)($headers[0] ?? ''))) !== 'nama guru') {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Import Gagal')
                                        ->body('Format berkas tidak sesuai. Silakan gunakan template Guru yang diunduh dari menu Guru.')
                                        ->danger()
                                        ->persistent()
                                        ->send();
                                    return;
                                }

                                $rows = array_slice($sheet, 1);
                                
                                $existingNips = \App\Models\Guru::whereNotNull('nip')->pluck('name', 'nip')->toArray();
                                
                                $conflicts = [];
                                foreach ($rows as $row) {
                                    $nameVal = trim((string) ($row[0] ?? ''));
                                    if ($nameVal === '') continue;

                                    $nipVal = trim((string) ($row[1] ?? ''));
                                    if ($nipVal !== '' && $nipVal !== '-') {
                                        if (isset($existingNips[$nipVal])) {
                                            $dbName = $existingNips[$nipVal];
                                            if (strtolower($dbName) !== strtolower($nameVal)) {
                                                $conflicts[] = "NIP **{$nipVal}** terdaftar sebagai **{$dbName}** (Excel: **{$nameVal}**)";
                                            }
                                        }
                                    }
                                }
                                
                                if (!empty($conflicts)) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Import Gagal')
                                        ->body('Terdapat konflik NIP di file Excel:<br/>' . implode('<br/>', $conflicts))
                                        ->danger()
                                        ->persistent()
                                        ->send();
                                    return;
                                }
                            }
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Import Gagal')
                                ->body('Gagal membaca file Excel untuk validasi NIP.')
                                ->danger()
                                ->send();
                            return;
                        }

                        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\GuruImport, $filePath);
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
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),

                // Custom Action: Reset Password
                Action::make('resetPassword')
                    ->label('Reset Password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reset Password Guru')
                    ->modalDescription(fn (Guru $record): string => "Password baru akan di-generate untuk {$record->name}. Pastikan catat password sebelum menutup dialog ini.")
                    ->action(function (Guru $record): void {
                        $newPassword = Str::random(8);

                        $record->update([
                            'password'             => $newPassword, // otomatis di-hash via cast 'hashed'
                            'must_change_password' => true,
                        ]);

                        Notification::make()
                            ->title('Password berhasil direset')
                            ->body("Username: **{$record->username}**\nPassword baru: **{$newPassword}**\n\nGuru wajib ganti password saat login berikutnya.")
                            ->success()
                            ->persistent() // tidak auto-dismiss, harus diklik manual
                            ->send();
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

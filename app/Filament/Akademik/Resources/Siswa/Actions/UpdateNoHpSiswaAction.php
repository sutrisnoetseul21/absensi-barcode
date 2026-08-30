<?php

namespace App\Filament\Akademik\Resources\Siswa\Actions;

use App\Imports\SiswaUpdateNoHpImport;
use App\Models\Siswa;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;

class UpdateNoHpSiswaAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'update_nohp_siswa';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Update No. HP Siswa & Ortu')
            ->icon('heroicon-o-phone')
            ->color('success')
            ->modalHeading('Update Cepat Nomor HP Siswa & Orang Tua')
            ->modalDescription('Perbarui nomor WhatsApp siswa dan orang tua/wali secara massal menggunakan file Excel.')
            ->modalWidth('4xl')
            ->form([
                // ── Tombol Download Data No HP ───────────────────────────────
                \Filament\Forms\Components\Placeholder::make('download_nohp_box')
                    ->label('')
                    ->content(new \Illuminate\Support\HtmlString('
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background-color: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 8px; margin-bottom: 4px;">
                            <div>
                                <span style="font-weight: 700; color: #065F46; display: block; font-size: 0.9rem;">Unduh data kontak siswa terkini</span>
                                <span style="font-size: 0.8rem; color: #047857;">File Excel berisi nama siswa, kelas, No HP Siswa, dan No HP Orang Tua yang siap diedit.</span>
                            </div>
                            <a href="' . route('admin.siswa.download-nohp') . '" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background-color: #059669; color: white; border-radius: 6px; font-weight: 600; font-size: 0.825rem; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05); white-space: nowrap;">
                                <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Download Data Kontak (.xlsx)
                            </a>
                        </div>
                    ')),

                \Filament\Forms\Components\FileUpload::make('file')
                    ->label('Pilih file Excel kontak yang telah diperbarui (.xlsx)')
                    ->disk('local')
                    ->directory('imports')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                    ])
                    ->required()
                    ->live()
                    ->helperText('Pastikan tidak mengubah kolom ID pada file Excel yang diunduh.'),

                \Filament\Forms\Components\Placeholder::make('preview')
                    ->label('Preview Update Kontak')
                    ->hidden(fn (\Filament\Schemas\Components\Utilities\Get $get) => empty($get('file')))
                    ->content(function (\Filament\Schemas\Components\Utilities\Get $get) {
                        $file = $get('file');
                        if (is_array($file)) {
                            $file = array_values($file)[0] ?? null;
                        }
                        if (!$file) {
                            return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500">File belum diunggah secara sempurna.</p>');
                        }

                        $filePath = $this->resolveFilePath($file);

                        if (!$filePath) {
                            return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500">Mencari file...</p>');
                        }

                        try {
                            $data = Excel::toArray(new \stdClass, $filePath);
                            if (empty($data[0])) {
                                return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500">File kosong.</p>');
                            }

                            $sheet   = $data[0];
                            $headers = $sheet[0] ?? [];
                            $allRows = array_slice($sheet, 1);

                            if (strtolower(trim((string)($headers[0] ?? ''))) !== 'id') {
                                return new \Illuminate\Support\HtmlString(
                                    '<div style="color: #B91C1C; font-weight: 600; padding: 12px; background-color: #FEE2E2; border: 1px solid #FCA5A5; border-radius: 6px;">'
                                    . '⚠️ Berkas tidak valid. Pastikan Anda mengunggah file hasil dari tombol <strong>Download Data Kontak</strong>.'
                                    . '</div>'
                                );
                            }

                            $rows = array_filter($allRows, fn($row) => trim((string)($row[0] ?? '')) !== '');
                            if (empty($rows)) {
                                return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500">Tidak ada baris data siswa yang terisi ID-nya.</p>');
                            }

                            $html  = '<div style="margin-bottom: 12px; display: flex; gap: 12px; flex-wrap: wrap;">';
                            $tableRows = '';

                            foreach (array_slice($rows, 0, 100) as $row) {
                                $id      = trim((string)($row[0] ?? ''));
                                $nisn    = trim((string)($row[1] ?? ''));
                                $name    = trim((string)($row[3] ?? ''));
                                $kelas   = trim((string)($row[4] ?? ''));
                                $noHp    = trim((string)($row[5] ?? ''));
                                $noHpOrtu= trim((string)($row[6] ?? ''));

                                $tableRows .= '<tr style="border-bottom: 1px solid #E5E7EB;">';
                                $tableRows .= '<td style="padding: 8px 12px; color: #374151; border-right: 1px solid #E5E7EB; white-space: nowrap;">' . htmlspecialchars($nisn) . '</td>';
                                $tableRows .= '<td style="padding: 8px 12px; color: #374151; border-right: 1px solid #E5E7EB;">' . htmlspecialchars($name) . '</td>';
                                $tableRows .= '<td style="padding: 8px 12px; color: #374151; border-right: 1px solid #E5E7EB; text-align: center;">' . htmlspecialchars($kelas ?: '-') . '</td>';
                                $tableRows .= '<td style="padding: 8px 12px; color: #047857; font-weight: 600; border-right: 1px solid #E5E7EB; white-space: nowrap;">' . htmlspecialchars($noHp ?: '-') . '</td>';
                                $tableRows .= '<td style="padding: 8px 12px; color: #1D4ED8; font-weight: 600; white-space: nowrap;">' . htmlspecialchars($noHpOrtu ?: '-') . '</td>';
                                $tableRows .= '</tr>';
                            }
                            
                            $html .= $this->badgeHtml("✅ Ditemukan " . count($rows) . " baris data", '#166534', '#D1FAE5');
                            if (count($rows) > 100) {
                                $html .= $this->badgeHtml("⚠️ Menampilkan 100 baris pertama", '#92400E', '#FEF3C7');
                            }
                            $html .= '</div>';

                            $html .= '<div style="overflow-x: auto; overflow-y: auto; max-height: 320px; border: 1px solid #E5E7EB; border-radius: 8px;">';
                            $html .= '<table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">';
                            $html .= '<thead style="position: sticky; top: 0; z-index: 10; background-color: #F3F4F6;">';
                            $html .= '<tr>';
                            $html .= '<th style="padding: 10px 12px; font-weight: 600; color: #374151; border-bottom: 2px solid #E5E7EB; border-right: 1px solid #E5E7EB; text-align: left;">NISN</th>';
                            $html .= '<th style="padding: 10px 12px; font-weight: 600; color: #374151; border-bottom: 2px solid #E5E7EB; border-right: 1px solid #E5E7EB; text-align: left;">Nama Siswa</th>';
                            $html .= '<th style="padding: 10px 12px; font-weight: 600; color: #374151; border-bottom: 2px solid #E5E7EB; border-right: 1px solid #E5E7EB; text-align: center;">Kelas</th>';
                            $html .= '<th style="padding: 10px 12px; font-weight: 600; color: #047857; border-bottom: 2px solid #E5E7EB; border-right: 1px solid #E5E7EB; text-align: left;">No HP Siswa</th>';
                            $html .= '<th style="padding: 10px 12px; font-weight: 600; color: #1D4ED8; border-bottom: 2px solid #E5E7EB; text-align: left;">No HP Ortu / Wali</th>';
                            $html .= '</tr></thead>';
                            $html .= '<tbody>' . $tableRows . '</tbody>';
                            $html .= '</table></div>';

                            return new \Illuminate\Support\HtmlString($html);

                        } catch (\Exception $e) {
                            return new \Illuminate\Support\HtmlString(
                                '<p class="text-sm text-red-500">Gagal membaca file: ' . htmlspecialchars($e->getMessage()) . '</p>'
                            );
                        }
                    }),
            ])
            ->action(function (array $data) {
                $file     = $data['file'];
                if (is_array($file)) {
                    $file = array_values($file)[0] ?? null;
                }

                $filePath = $this->resolveFilePath($file);
                if (!$filePath) {
                    Notification::make()->title('File tidak ditemukan')->danger()->send();
                    return;
                }

                try {
                    $parsed  = Excel::toArray(new \stdClass, $filePath);
                    $headers = $parsed[0][0] ?? [];

                    if (strtolower(trim((string)($headers[0] ?? ''))) !== 'id') {
                        Notification::make()
                            ->title('Update Gagal')
                            ->body('Format berkas salah. Kolom pertama harus berupa ID.')
                            ->danger()
                            ->send();
                        return;
                    }
                } catch (\Exception $e) {
                    Notification::make()->title('Update Gagal')->body('Gagal membaca file.')->danger()->send();
                    return;
                }

                $importer = new SiswaUpdateNoHpImport();
                Excel::import($importer, $filePath);
                $summary  = $importer->getSummary();

                $body = "✅ {$summary['berhasil']} kontak siswa berhasil diperbarui | ℹ️ {$summary['skip']} data dilewati (tidak ada perubahan atau tidak valid).";

                Notification::make()
                    ->title("Proses Update Nomor HP Selesai")
                    ->body($body)
                    ->success()
                    ->persistent()
                    ->send();
            });
    }

    private function resolveFilePath(mixed $file): ?string
    {
        if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
            return $file->getRealPath();
        }

        if (is_string($file)) {
            $path = storage_path('app/private/' . $file);
            if (file_exists($path)) return $path;
            $path = storage_path('app/' . $file);
            if (file_exists($path)) return $path;
        }

        return null;
    }

    private function badgeHtml(string $label, string $textColor, string $bgColor): string
    {
        return '<span style="display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; color: ' . $textColor . '; background-color: ' . $bgColor . ';">' . $label . '</span>';
    }
}

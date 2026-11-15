<?php

namespace App\Filament\Akademik\Resources\Siswa\Actions;

use App\Imports\SiswaBaruImport;
use App\Exports\SiswaImportLaporanExport;
use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use App\Models\Kelas;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Action untuk import siswa baru (PPDB) dari file Excel.
 *
 * Fitur:
 * - Template 8 kolom: NISN, NIS, Nama, Tempat Lahir, Tgl Lahir, Alamat, Password, Kelas (opsional)
 * - Skip duplikat NISN/NIS (baik kembar dalam file maupun sudah ada di DB)
 * - Assign kelas ke tahun ajaran aktif jika kolom Kelas diisi dan valid
 * - Preview per-baris sebelum import (7 kondisi status)
 * - Laporan Excel hasil import bisa didownload setelah proses selesai
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

        $this
            ->label('Import Siswa + Kelas')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('success')
            ->modalHeading('Import Siswa Baru (+ Kelas)')
            ->modalDescription('Unggah berkas Excel data siswa baru. Siswa akan otomatis didaftarkan dan di-assign ke kelas sesuai tahun ajaran aktif.')
            ->modalWidth('5xl')
            ->form([
                // ── Tombol Download Template Resmi ────────────────────────────
                \Filament\Forms\Components\Placeholder::make('download_template_box')
                    ->label('')
                    ->content(new \Illuminate\Support\HtmlString('
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background-color: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px; margin-bottom: 4px;">
                            <div>
                                <span style="font-weight: 700; color: #1E40AF; display: block; font-size: 0.9rem;">Belum memiliki format template Excel?</span>
                                <span style="font-size: 0.8rem; color: #3B82F6;">Unduh template resmi sistem yang sudah sesuai dengan kolom kelas dan data siswa.</span>
                            </div>
                            <a href="' . route('admin.siswa.download-template') . '" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background-color: #2563EB; color: white; border-radius: 6px; font-weight: 600; font-size: 0.825rem; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05); white-space: nowrap;">
                                <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Download Template Siswa Baru (.xlsx)
                            </a>
                        </div>
                    ')),

                // ── Cek TA Aktif ───────────────────────────────────────────────
                \Filament\Forms\Components\Placeholder::make('info_ta_aktif')
                    ->label('')
                    ->content(function () {
                        $setting   = PengaturanSekolah::current();
                        $activeYear = $setting?->tahunAjaranAktif;

                        if (!$activeYear) {
                            return new \Illuminate\Support\HtmlString(
                                '<div style="padding: 12px 16px; background-color: #FEF2F2; border: 1px solid #FCA5A5; border-radius: 8px; color: #B91C1C; font-weight: 600;">'
                                . '⚠️ <strong>Tidak ada Tahun Ajaran Aktif.</strong> Siswa bisa diimport, tetapi tidak akan bisa di-assign ke kelas. '
                                . 'Aktifkan Tahun Ajaran terlebih dahulu di menu <strong>Pengaturan Sekolah</strong>.'
                                . '</div>'
                            );
                        }

                        return new \Illuminate\Support\HtmlString(
                            '<div style="padding: 12px 16px; background-color: #F0FDF4; border: 1px solid #86EFAC; border-radius: 8px; color: #166534;">'
                            . '✅ Tahun Ajaran Aktif: <strong>' . htmlspecialchars($activeYear->name) . '</strong>. '
                            . 'Siswa yang memiliki kolom Kelas valid akan otomatis didaftarkan ke tahun ajaran ini.'
                            . '</div>'
                        );
                    }),

                // ── Upload File ────────────────────────────────────────────────
                \Filament\Forms\Components\FileUpload::make('file')
                    ->label('Pilih file Excel (.xlsx)')
                    ->disk('local')
                    ->directory('imports')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                    ])
                    ->required()
                    ->live()
                    ->helperText('Unggah file Excel yang telah diisi sesuai format template di atas.'),

                // ── Preview ────────────────────────────────────────────────────
                \Filament\Forms\Components\Placeholder::make('preview')
                    ->label('Preview Data')
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

                            // Validasi header (pastikan urutan: NISN, NIS, Nama Siswa, Kelas)
                            if (!$this->isValidTemplateHeaders($headers)) {
                                return new \Illuminate\Support\HtmlString(
                                    '<div style="color: #B91C1C; font-weight: 600; padding: 12px; background-color: #FEE2E2; border: 1px solid #FCA5A5; border-radius: 6px;">'
                                    . '⚠️ Berkas yang diunggah bukan template Siswa Baru terbaru yang valid (kolom ke-4 harus Kelas). Silakan gunakan tombol <strong>Download Template Siswa Baru</strong> di atas.'
                                    . '</div>'
                                );
                            }

                            // Kumpulkan data referensi untuk preview
                            $existingNisns   = Siswa::withTrashed()->pluck('nisn')->filter()->map(fn($v) => (string)$v)->flip()->toArray();
                            $existingNises   = Siswa::withTrashed()->whereNotNull('nis')->pluck('nis')->filter()->map(fn($v) => (string)$v)->flip()->toArray();
                            $validKelasMap   = Kelas::pluck('name')
                                ->mapWithKeys(fn ($n) => [mb_strtolower(trim((string) $n)) => (string) $n])
                                ->toArray();
                            $activeYear      = PengaturanSekolah::current()?->tahunAjaranAktif;

                            // Hitung NISN kembar dalam file
                            $nisnCount = [];
                            foreach ($allRows as $row) {
                                $n = trim((string)($row[0] ?? ''));
                                if ($n !== '') $nisnCount[$n] = ($nisnCount[$n] ?? 0) + 1;
                            }
                            $dupNisns = array_keys(array_filter($nisnCount, fn($c) => $c > 1));

                            // Filter baris tidak kosong sambil mempertahankan nomor baris Excel
                            $rows = array_filter($allRows, fn($row) => trim((string)($row[0] ?? '')) !== '' || trim((string)($row[2] ?? '')) !== '');

                            if (empty($rows)) {
                                return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500">Tidak ada baris data siswa yang terisi.</p>');
                            }

                            // Hitung statistik preview & kumpulkan item agar yang error/warning tampil paling depan
                            $countBerhasil   = 0;
                            $countTanpaKelas = 0;
                            $countWarning    = 0;
                            $countSkip       = 0;
                            $previewItems    = [];

                            foreach ($rows as $origIdx => $row) {
                                $excelRowNo = (int) $origIdx + 2;
                                $nisn       = trim((string)($row[0] ?? ''));
                                $nis        = trim((string)($row[1] ?? ''));
                                $name       = trim((string)($row[2] ?? ''));
                                $kelas      = trim((string)($row[3] ?? ''));
                                $kelasKey   = mb_strtolower($kelas);
                                $kelasDisplay = $kelas !== '' ? ($validKelasMap[$kelasKey] ?? $kelas) : '';

                                // Tentukan status preview (case-insensitive untuk kelas)
                                [$statusHtml, $statusType] = $this->determinePreviewStatus(
                                    $nisn, $nis, $name, $kelas,
                                    $existingNisns, $existingNises,
                                    $dupNisns, $validKelasMap, $activeYear
                                );

                                match ($statusType) {
                                    'berhasil'             => $countBerhasil++,
                                    'berhasil_tanpa_kelas' => $countTanpaKelas++,
                                    'warning'              => $countWarning++,
                                    'skip'                 => $countSkip++,
                                    default                => null,
                                };

                                $priority = match ($statusType) {
                                    'skip'                 => 0,
                                    'warning'              => 1,
                                    'berhasil_tanpa_kelas' => 2,
                                    default                => 3,
                                };

                                $previewItems[] = [
                                    'priority'      => $priority,
                                    'row_no'        => $excelRowNo,
                                    'nisn'          => $nisn,
                                    'nis'           => $nis,
                                    'name'          => $name,
                                    'kelas_display' => $kelasDisplay,
                                    'status_html'   => $statusHtml,
                                    'status_type'   => $statusType,
                                ];
                            }

                            usort($previewItems, fn (array $a, array $b): int => [$a['priority'], $a['row_no']] <=> [$b['priority'], $b['row_no']]);

                            // Build HTML tabel
                            $html  = '<div style="margin-bottom: 12px; display: flex; gap: 12px; flex-wrap: wrap;">';

                            $tableRows = '';
                            foreach ($previewItems as $item) {
                                $rowBg = match ($item['status_type']) {
                                    'skip'    => 'background-color: #FEF2F2;',
                                    'warning' => 'background-color: #FFFBEB;',
                                    default   => '',
                                };

                                $tableRows .= '<tr style="border-bottom: 1px solid #E5E7EB; ' . $rowBg . '">';
                                $tableRows .= '<td style="padding: 8px 12px; color: #374151; border-right: 1px solid #E5E7EB; white-space: nowrap;">'
                                    . htmlspecialchars($item['nisn'])
                                    . '<div style="font-size: 10px; color: #9CA3AF;">Baris #' . $item['row_no'] . '</div>'
                                    . '</td>';
                                $tableRows .= '<td style="padding: 8px 12px; color: #374151; border-right: 1px solid #E5E7EB; white-space: nowrap;">' . htmlspecialchars($item['nis']) . '</td>';
                                $tableRows .= '<td style="padding: 8px 12px; color: #374151; border-right: 1px solid #E5E7EB;">' . htmlspecialchars($item['name']) . '</td>';
                                $tableRows .= '<td style="padding: 8px 12px; color: #374151; border-right: 1px solid #E5E7EB; text-align: center;">' . ($item['kelas_display'] !== '' ? htmlspecialchars($item['kelas_display']) : '<span style="color:#9CA3AF">—</span>') . '</td>';
                                $tableRows .= '<td style="padding: 8px 12px;">' . $item['status_html'] . '</td>';
                                $tableRows .= '</tr>';
                            }

                            // Badge ringkasan
                            $html .= $this->badgeHtml("✅ {$countBerhasil} Berhasil + Kelas", '#166534', '#D1FAE5');
                            $html .= $this->badgeHtml("✅ {$countTanpaKelas} Berhasil (tanpa kelas)", '#1D4ED8', '#DBEAFE');
                            $html .= $this->badgeHtml("⚠️ {$countWarning} Peringatan", '#92400E', '#FEF3C7');
                            $html .= $this->badgeHtml("❌ {$countSkip} Di-Skip", '#B91C1C', '#FEE2E2');
                            $html .= '</div>';

                            // Tabel data
                            $html .= '<div style="overflow-x: auto; overflow-y: auto; max-height: 320px; border: 1px solid #E5E7EB; border-radius: 8px;">';
                            $html .= '<table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">';
                            $html .= '<thead style="position: sticky; top: 0; z-index: 10; background-color: #F3F4F6;">';
                            $html .= '<tr>';
                            foreach (['NISN', 'NIS', 'Nama Siswa', 'Kelas', 'Status Preview'] as $th) {
                                $html .= '<th style="padding: 10px 12px; font-weight: 600; color: #374151; border-bottom: 2px solid #E5E7EB; border-right: 1px solid #E5E7EB; text-align: left;">' . $th . '</th>';
                            }
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

                // Validasi header sebelum import
                try {
                    $parsed  = Excel::toArray(new \stdClass, $filePath);
                    $headers = $parsed[0][0] ?? [];

                    if (!$this->isValidTemplateHeaders($headers)) {
                        Notification::make()
                            ->title('Import Gagal')
                            ->body('Format berkas salah atau menggunakan template versi lama. Download ulang template Import Siswa + Kelas.')
                            ->danger()
                            ->send();
                        return;
                    }
                } catch (\Exception) {
                    Notification::make()->title('Import Gagal')->body('Gagal membaca file.')->danger()->send();
                    return;
                }

                // Jalankan import
                $importer = new SiswaBaruImport();
                Excel::import($importer, $filePath);
                $results  = $importer->getResults();
                $summary  = $importer->getSummary();

                // Simpan laporan di cache (30 menit) untuk tombol download
                $reportKey = 'import_laporan_' . auth()->id();
                cache()->put($reportKey, $results, now()->addMinutes(30));

                $totalBerhasil = $summary['berhasil'] + $summary['tanpaKelas'];
                $body = "✅ {$summary['berhasil']} siswa berhasil (+ kelas) | "
                    . "✅ {$summary['tanpaKelas']} berhasil (tanpa kelas) | "
                    . "⚠️ {$summary['warning']} peringatan | "
                    . "❌ {$summary['skip']} di-skip.";

                Notification::make()
                    ->title("Import Selesai — {$totalBerhasil} siswa berhasil ditambahkan")
                    ->body($body)
                    ->success()
                    ->actions([
                        \Filament\Actions\Action::make('download_laporan')
                            ->label('Download Laporan Excel')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->url(route('admin.import.download-laporan'))
                            ->openUrlInNewTab(),
                    ])
                    ->persistent()
                    ->send();
            });
    }

    // ── Helper: resolve path file upload ──────────────────────────────────────

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

    // ── Helper: tentukan status preview per baris ──────────────────────────────

    /**
     * @return array{0: string, 1: string} [HTML badge, status key]
     */
    private function determinePreviewStatus(
        string $nisn,
        string $nis,
        string $name,
        string $kelas,
        array  $existingNisns,
        array  $existingNises,
        array  $dupNisns,
        array  $validKelasMap,
        mixed  $activeYear,
    ): array {
        if ($nisn === '') {
            return [$this->statusBadge('❌ Skip — NISN kosong', '#B91C1C', '#FEE2E2'), 'skip'];
        }

        if ($name === '') {
            return [$this->statusBadge('❌ Skip — Nama kosong', '#B91C1C', '#FEE2E2'), 'skip'];
        }

        if (in_array($nisn, $dupNisns)) {
            return [$this->statusBadge('❌ Skip — NISN kembar dalam file', '#B91C1C', '#FEE2E2'), 'skip'];
        }

        if (isset($existingNisns[$nisn])) {
            return [$this->statusBadge('❌ Skip — NISN sudah ada di DB', '#B91C1C', '#FEE2E2'), 'skip'];
        }

        if ($nis !== '' && isset($existingNises[$nis])) {
            return [$this->statusBadge('❌ Skip — NIS sudah ada di DB', '#B91C1C', '#FEE2E2'), 'skip'];
        }

        // Siswa baru — cek kelas (case-insensitive)
        if ($kelas === '') {
            return [$this->statusBadge('✅ Berhasil (tanpa kelas)', '#1D4ED8', '#DBEAFE'), 'berhasil_tanpa_kelas'];
        }

        if (!isset($validKelasMap[mb_strtolower($kelas)])) {
            return [$this->statusBadge('⚠️ Siswa disimpan — kelas tidak valid', '#92400E', '#FEF3C7'), 'warning'];
        }

        if (!$activeYear) {
            return [$this->statusBadge('⚠️ Siswa disimpan — tidak ada TA aktif', '#92400E', '#FEF3C7'), 'warning'];
        }

        return [$this->statusBadge('✅ Berhasil + Kelas', '#166534', '#D1FAE5'), 'berhasil'];
    }

    private function statusBadge(string $label, string $textColor, string $bgColor): string
    {
        return '<span style="display: inline-block; padding: 3px 10px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; color: ' . $textColor . '; background-color: ' . $bgColor . ';">' . $label . '</span>';
    }

    private function isValidTemplateHeaders(array $headers): bool
    {
        $normalize = static fn (mixed $value): string => trim(str_replace('*', '', strtolower((string) ($value ?? ''))));

        return $normalize($headers[0] ?? '') === 'nisn'
            && $normalize($headers[1] ?? '') === 'nis'
            && $normalize($headers[2] ?? '') === 'nama siswa'
            && str_starts_with($normalize($headers[3] ?? ''), 'kelas');
    }

    private function badgeHtml(string $label, string $textColor, string $bgColor): string
    {
        return '<span style="display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; color: ' . $textColor . '; background-color: ' . $bgColor . ';">' . $label . '</span>';
    }
}

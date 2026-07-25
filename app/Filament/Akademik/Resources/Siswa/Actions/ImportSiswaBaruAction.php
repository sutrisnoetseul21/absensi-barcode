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
            ->modalHeading('Import Siswa Baru (PPDB)')
            ->modalWidth('5xl')
            ->form([
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
                    ->helperText('Gunakan tombol "Template Siswa Baru" untuk mengunduh template yang benar.'),

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

                            // Validasi header (8 kolom, cek NISN dan NIS dan Nama Siswa)
                            if (
                                strtolower(trim((string)($headers[0] ?? ''))) !== 'nisn' ||
                                strtolower(trim((string)($headers[1] ?? ''))) !== 'nis'  ||
                                strtolower(trim((string)($headers[2] ?? ''))) !== 'nama siswa'
                            ) {
                                return new \Illuminate\Support\HtmlString(
                                    '<div style="color: #B91C1C; font-weight: 600; padding: 12px; background-color: #FEE2E2; border: 1px solid #FCA5A5; border-radius: 6px;">'
                                    . '⚠️ Berkas yang diunggah bukan template Siswa Baru yang valid. Silakan gunakan tombol <strong>Template Siswa Baru</strong> untuk mengunduh template yang benar.'
                                    . '</div>'
                                );
                            }

                            // Kumpulkan data referensi untuk preview
                            $existingNisns   = Siswa::withTrashed()->pluck('nisn')->filter()->map(fn($v) => (string)$v)->flip()->toArray();
                            $existingNises   = Siswa::withTrashed()->whereNotNull('nis')->pluck('nis')->filter()->map(fn($v) => (string)$v)->flip()->toArray();
                            $validKelasNames = Kelas::pluck('name')->toArray();
                            $activeYear      = PengaturanSekolah::current()?->tahunAjaranAktif;

                            // Hitung NISN kembar dalam file
                            $nisnCount = [];
                            foreach ($allRows as $row) {
                                $n = trim((string)($row[0] ?? ''));
                                if ($n !== '') $nisnCount[$n] = ($nisnCount[$n] ?? 0) + 1;
                            }
                            $dupNisns = array_keys(array_filter($nisnCount, fn($c) => $c > 1));

                            // Filter baris tidak kosong
                            $rows = array_filter($allRows, fn($row) => trim((string)($row[0] ?? '')) !== '' || trim((string)($row[2] ?? '')) !== '');

                            if (empty($rows)) {
                                return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500">Tidak ada baris data siswa yang terisi.</p>');
                            }

                            // Hitung statistik preview
                            $countBerhasil   = 0;
                            $countTanpaKelas = 0;
                            $countWarning    = 0;
                            $countSkip       = 0;

                            // Build HTML tabel
                            $html  = '<div style="margin-bottom: 12px; display: flex; gap: 12px; flex-wrap: wrap;">';

                            // Ringkasan statistik akan dihitung di dalam loop
                            $tableRows = '';
                            foreach ($rows as $row) {
                                $nisn     = trim((string)($row[0] ?? ''));
                                $nis      = trim((string)($row[1] ?? ''));
                                $name     = trim((string)($row[2] ?? ''));
                                $kelas    = trim((string)($row[7] ?? ''));

                                // Tentukan status preview
                                [$statusHtml, $statusType] = $this->determinePreviewStatus(
                                    $nisn, $nis, $name, $kelas,
                                    $existingNisns, $existingNises,
                                    $dupNisns, $validKelasNames, $activeYear
                                );

                                match ($statusType) {
                                    'berhasil'           => $countBerhasil++,
                                    'berhasil_tanpa_kelas' => $countTanpaKelas++,
                                    'warning'            => $countWarning++,
                                    'skip'               => $countSkip++,
                                    default              => null,
                                };

                                $tableRows .= '<tr style="border-bottom: 1px solid #E5E7EB;">';
                                $tableRows .= '<td style="padding: 8px 12px; color: #374151; border-right: 1px solid #E5E7EB; white-space: nowrap;">' . htmlspecialchars($nisn) . '</td>';
                                $tableRows .= '<td style="padding: 8px 12px; color: #374151; border-right: 1px solid #E5E7EB; white-space: nowrap;">' . htmlspecialchars($nis) . '</td>';
                                $tableRows .= '<td style="padding: 8px 12px; color: #374151; border-right: 1px solid #E5E7EB;">' . htmlspecialchars($name) . '</td>';
                                $tableRows .= '<td style="padding: 8px 12px; color: #374151; border-right: 1px solid #E5E7EB; text-align: center;">' . ($kelas !== '' ? htmlspecialchars($kelas) : '<span style="color:#9CA3AF">—</span>') . '</td>';
                                $tableRows .= '<td style="padding: 8px 12px;">' . $statusHtml . '</td>';
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

                    if (
                        strtolower(trim((string)($headers[0] ?? ''))) !== 'nisn' ||
                        strtolower(trim((string)($headers[1] ?? ''))) !== 'nis'  ||
                        strtolower(trim((string)($headers[2] ?? ''))) !== 'nama siswa'
                    ) {
                        Notification::make()
                            ->title('Import Gagal')
                            ->body('Format berkas salah. Gunakan template Import Siswa + Kelas.')
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
        array  $validKelasNames,
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

        // Siswa baru — cek kelas
        if ($kelas === '') {
            return [$this->statusBadge('✅ Berhasil (tanpa kelas)', '#1D4ED8', '#DBEAFE'), 'berhasil_tanpa_kelas'];
        }

        if (!in_array($kelas, $validKelasNames)) {
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

    private function badgeHtml(string $label, string $textColor, string $bgColor): string
    {
        return '<span style="display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; color: ' . $textColor . '; background-color: ' . $bgColor . ';">' . $label . '</span>';
    }
}

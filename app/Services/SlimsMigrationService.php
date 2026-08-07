<?php

namespace App\Services;

use App\Models\Buku;
use App\Models\EksemplarBuku;
use App\Models\InventarisBuku;
use App\Models\KategoriBuku;
use App\Models\KlasifikasiDdc;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * SlimsMigrationService v2
 *
 * Versi yang sudah direvisi berdasarkan analisis masalah di:
 * docs/export-slims-erp/rencana-redesign-import-v2.md
 *
 * Perubahan utama dari v1:
 * - importDdc(): sumber dari biblio.classification (bukan mst_topic), nama auto-mapping DDC standar
 * - importBuku(): simpan slims_biblio_id di tabel bukus ERP
 * - importEksemplar(): lookup via DB::table('bukus') bukan Cache Laravel
 * - importBukuDanEksemplar(): method baru, jalankan buku+eksemplar sekaligus
 * - Progress disimpan ke Cache setiap chunk untuk ditampilkan di halaman proses
 *
 * Kebijakan: OVERWRITE (updateOrCreate) — bukan skip.
 * Urutan import: importDdc() → importBukuDanEksemplar()
 */
class SlimsMigrationService
{
    public function __construct(
        protected SlimsConnectionService $slimsConn
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // 1. IMPORT DDC (biblio.classification → klasifikasi_ddcs)
    // ─────────────────────────────────────────────────────────────────────────

    public function importDdc(): array
    {
        set_time_limit(300);
        $slims  = $this->slimsConn->getConnection();
        $result = ['baru' => 0, 'diupdate' => 0, 'error' => 0, 'pesan_error' => []];

        // Ambil distinct classification dari biblio (bukan mst_topic!)
        $ddcList = $slims->table('biblio')
            ->select('classification')
            ->whereNotNull('classification')
            ->where('classification', '!=', '')
            ->whereRaw("UPPER(classification) != 'NONE'")
            ->distinct()
            ->orderBy('classification')
            ->get();

        $total = $ddcList->count();
        $this->simpanProgress('ddc', 0, $total, 0, 0, 0, 0, 'berjalan');

        foreach ($ddcList as $i => $row) {
            try {
                $kodeDdc = trim($row->classification);
                $namaDdc = self::getNamaDdc($kodeDdc);

                $existing = KlasifikasiDdc::where('kode_ddc', $kodeDdc)->first();

                if ($existing) {
                    $existing->update(['kategori' => $namaDdc]);
                    $result['diupdate']++;
                } else {
                    KlasifikasiDdc::create([
                        'id'       => Str::uuid(),
                        'kode_ddc' => $kodeDdc,
                        'kategori' => $namaDdc,
                    ]);
                    $result['baru']++;
                }

                // Update progress setiap 50 item
                if (($i + 1) % 50 === 0 || ($i + 1) === $total) {
                    $this->simpanProgress('ddc', $i + 1, $total, 0, 0, $result['error'], 0, 'berjalan');
                }
            } catch (\Exception $e) {
                Log::error("SLiMS Import DDC Error (kode={$row->classification}): " . $e->getMessage());
                $result['error']++;
                $result['pesan_error'][] = "kode={$row->classification}: " . $e->getMessage();
            }
        }

        $this->simpanProgress('ddc', $total, $total, 0, 0, $result['error'], 0, 'selesai');
        return $result;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. IMPORT BUKU + EKSEMPLAR (biblio+item → bukus+eksemplar_bukus)
    // ─────────────────────────────────────────────────────────────────────────

    public function importBukuDanEksemplar(): array
    {
        set_time_limit(3600);
        $slims  = $this->slimsConn->getConnection();

        $result = [
            'buku'  => ['baru' => 0, 'diupdate' => 0, 'error' => 0, 'pesan_error' => []],
            'eksemplar' => ['baru' => 0, 'diupdate' => 0, 'dilewati' => 0, 'inventaris_dibuat' => 0, 'error' => 0, 'pesan_error' => []],
        ];

        // === TAHAP 1: IMPORT BUKU ===

        $totalBuku = $slims->table('biblio')->count();
        $this->simpanProgress('buku', 0, $totalBuku, 0, 0, 0, 0, 'berjalan_buku');

        // Pre-load kategori map
        $kategoriMap = KategoriBuku::pluck('id', 'nama_kategori')->toArray();
        foreach (['Non Fiksi', 'Fiksi', 'Referensi'] as $namaKat) {
            if (!isset($kategoriMap[$namaKat])) {
                $kat = KategoriBuku::create([
                    'id'                => Str::uuid(),
                    'nama_kategori'     => $namaKat,
                    'is_bisa_dipinjam'  => true,
                    'is_buku_pelajaran' => $namaKat === 'Non Fiksi',
                    'kode_prefix'       => match($namaKat) {
                        'Fiksi'    => 'F',
                        'Referensi'=> 'R',
                        default    => 'SR',
                    },
                ]);
                $kategoriMap[$namaKat] = $kat->id;
            }
        }

        // Pre-load map author per biblio
        $authorsMap = $slims->table('biblio_author')
            ->join('mst_author', 'biblio_author.author_id', '=', 'mst_author.author_id')
            ->select('biblio_author.biblio_id', DB::raw('GROUP_CONCAT(mst_author.author_name ORDER BY biblio_author.level SEPARATOR ", ") as penulis'))
            ->groupBy('biblio_author.biblio_id')
            ->get()
            ->pluck('penulis', 'biblio_id')
            ->toArray();

        // Pre-load coll_type per biblio (dari item pertama)
        $collTypeMap = $slims->table('item')
            ->select('biblio_id', DB::raw('MIN(coll_type_id) as coll_type_id'))
            ->whereNotNull('coll_type_id')
            ->groupBy('biblio_id')
            ->get()
            ->pluck('coll_type_id', 'biblio_id')
            ->toArray();

        $bukuSelesai = 0;

        $slims->table('biblio')
            ->leftJoin('mst_publisher', 'biblio.publisher_id', '=', 'mst_publisher.publisher_id')
            ->select('biblio.biblio_id', 'biblio.title', 'biblio.isbn_issn', 'biblio.publish_year',
                     'biblio.classification', 'mst_publisher.publisher_name')
            ->orderBy('biblio.biblio_id')
            ->chunk(200, function ($biblioBatch) use (
                &$result, &$bukuSelesai, $totalBuku,
                $kategoriMap, $authorsMap, $collTypeMap
            ) {
                foreach ($biblioBatch as $biblio) {
                    try {
                        $penulis    = $authorsMap[$biblio->biblio_id] ?? null;
                        $collTypeId = $collTypeMap[$biblio->biblio_id] ?? null;
                        $kategoriId = $this->mapKategoriId($collTypeId, $kategoriMap);

                        $rawTahun    = trim($biblio->publish_year ?? '');
                        $tahunTerbit = (is_numeric($rawTahun) && (int)$rawTahun >= 1000 && (int)$rawTahun <= 2099)
                            ? (int) $rawTahun : null;

                        $lokasiRak = ($biblio->classification && strtoupper($biblio->classification) !== 'NONE')
                            ? trim($biblio->classification) : null;

                        $isbn = $biblio->isbn_issn ? trim($biblio->isbn_issn) : null;

                        // Cari DDC yang cocok
                        $ddcId = null;
                        if ($lokasiRak) {
                            $ddc = KlasifikasiDdc::where('kode_ddc', $lokasiRak)->first();
                            $ddcId = $ddc?->id;
                        }

                        $dataBuku = [
                            'kategori_id'     => $kategoriId,
                            'judul'           => trim($biblio->title),
                            'penulis'         => $penulis,
                            'penerbit'        => $biblio->publisher_name ? trim($biblio->publisher_name) : null,
                            'tahun_terbit'    => $tahunTerbit,
                            'isbn'            => $isbn,
                            'lokasi_rak'      => $lokasiRak,
                            'klasifikasi_ddc_id' => $ddcId,
                            'slims_biblio_id' => $biblio->biblio_id,
                            'updated_at'      => now(),
                        ];

                        // Cari berdasarkan slims_biblio_id (paling akurat)
                        $existing = Buku::withTrashed()->where('slims_biblio_id', $biblio->biblio_id)->first();

                        if ($existing) {
                            $existing->restore();
                            $existing->update($dataBuku);
                            $result['buku']['diupdate']++;
                        } else {
                            // Fallback: cari via ISBN atau judul+penerbit
                            if ($isbn) {
                                $existing = Buku::withTrashed()->where('isbn', $isbn)->first();
                            }
                            if (!$existing) {
                                $existing = Buku::withTrashed()
                                    ->where('judul', trim($biblio->title))
                                    ->where('penerbit', $biblio->publisher_name ? trim($biblio->publisher_name) : null)
                                    ->first();
                            }

                            if ($existing) {
                                $existing->restore();
                                $existing->update($dataBuku);
                                $result['buku']['diupdate']++;
                            } else {
                                Buku::create(array_merge($dataBuku, [
                                    'id'         => Str::uuid(),
                                    'created_at' => now(),
                                ]));
                                $result['buku']['baru']++;
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error("SLiMS Import Buku Error (biblio_id={$biblio->biblio_id}): " . $e->getMessage());
                        $result['buku']['error']++;
                        $result['buku']['pesan_error'][] = "biblio_id={$biblio->biblio_id}: " . $e->getMessage();
                    }

                    $bukuSelesai++;
                }

                // Simpan progress setiap chunk
                $this->simpanProgress('buku', $bukuSelesai, $totalBuku, 0, 0, $result['buku']['error'], 0, 'berjalan_buku');
            });

        $this->simpanProgress('buku', $totalBuku, $totalBuku, 0, 0, $result['buku']['error'], 0, 'berjalan_eksemplar');

        // === TAHAP 2: IMPORT EKSEMPLAR ===
        // Lookup mapping dari tabel bukus langsung — TANPA Cache!

        $biblioToBukuId = DB::table('bukus')
            ->whereNotNull('slims_biblio_id')
            ->pluck('id', 'slims_biblio_id')
            ->toArray();

        $totalEks   = $slims->table('item')->whereNotNull('item_code')->where('item_code', '!=', '')->count();
        $eksSelesai = 0;
        $inventarisDibuat = [];

        $this->simpanProgress('buku', $totalBuku, $totalBuku, $eksSelesai, $totalEks, $result['buku']['error'], 0, 'berjalan_eksemplar');

        $slims->table('item')
            ->whereNotNull('item_code')
            ->where('item_code', '!=', '')
            ->orderBy('biblio_id')
            ->orderBy('item_id')
            ->chunk(500, function ($items) use (
                &$result, &$eksSelesai, &$inventarisDibuat,
                $totalBuku, $totalEks, $biblioToBukuId
            ) {
                foreach ($items as $item) {
                    try {
                        $bukuId = $biblioToBukuId[$item->biblio_id] ?? null;

                        if (!$bukuId) {
                            $result['eksemplar']['dilewati']++;
                            $eksSelesai++;
                            continue;
                        }

                        $status       = $this->mapItemStatus($item->item_status_id);
                        $existing     = EksemplarBuku::withTrashed()->where('kode_eksemplar', $item->item_code)->first();

                        $dataEksemplar = [
                            'buku_id'        => $bukuId,
                            'kode_eksemplar' => $item->item_code,
                            'status'         => $status,
                            'kondisi_fisik'  => 'baik',
                            'updated_at'     => now(),
                        ];

                        if ($existing) {
                            $existing->restore();

                            // Jika pindah inventaris, hapus dulu dari inventaris lama
                            $inventariBukuId = $this->getOrCreateInventaris($bukuId, $item, $inventarisDibuat, $result);
                            $existing->update(array_merge($dataEksemplar, ['inventaris_buku_id' => $inventariBukuId]));
                            $result['eksemplar']['diupdate']++;
                        } else {
                            $inventariBukuId = $this->getOrCreateInventaris($bukuId, $item, $inventarisDibuat, $result);

                            EksemplarBuku::create(array_merge($dataEksemplar, [
                                'id'                 => Str::uuid(),
                                'inventaris_buku_id' => $inventariBukuId,
                                'created_at'         => now(),
                            ]));
                            $result['eksemplar']['baru']++;
                        }

                        if ($inventariBukuId) {
                            InventarisBuku::where('id', $inventariBukuId)->increment('jumlah_eksemplar');
                        }
                    } catch (\Exception $e) {
                        Log::error("SLiMS Import Eksemplar Error (item_id={$item->item_id}): " . $e->getMessage());
                        $result['eksemplar']['error']++;
                        $result['eksemplar']['pesan_error'][] = "item_id={$item->item_id} (kode={$item->item_code}): " . $e->getMessage();
                    }

                    $eksSelesai++;
                }

                $this->simpanProgress('buku', $totalBuku, $totalBuku, $eksSelesai, $totalEks, $result['buku']['error'], $result['eksemplar']['error'], 'berjalan_eksemplar');
            });

        // Rekap jumlah_eksemplar dari COUNT aktual (memastikan akurat)
        DB::statement('
            UPDATE inventaris_bukus iv
            SET jumlah_eksemplar = (
                SELECT COUNT(*) FROM eksemplar_bukus eb WHERE eb.inventaris_buku_id = iv.id AND eb.deleted_at IS NULL
            )
        ');

        $this->simpanProgress('buku', $totalBuku, $totalBuku, $totalEks, $totalEks, $result['buku']['error'], $result['eksemplar']['error'], 'selesai');

        return $result;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. IMPORT SEMUA (DDC → Buku+Eksemplar)
    // ─────────────────────────────────────────────────────────────────────────

    public function importSemua(): array
    {
        set_time_limit(3600);

        $result = [];
        $result['ddc']  = $this->importDdc();
        $result['buku'] = $this->importBukuDanEksemplar();

        $finalReport = [
            'jenis'  => 'Semua',
            'hasil'  => $result,
            'selesai_pada' => now()->toDateTimeString(),
        ];
        Cache::put('slims_last_report', $finalReport, now()->addHours(12));

        return $result;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PROGRESS TRACKING
    // ─────────────────────────────────────────────────────────────────────────

    public function simpanProgress(
        string $fase,
        int $bukuSelesai, int $bukuTotal,
        int $eksSelesai, int $eksTotal,
        int $errorBuku, int $errorEks,
        string $status = 'berjalan'
    ): void {
        Cache::put('slims_import_progress', [
            'fase'          => $fase,
            'status'        => $status,
            'buku_selesai'  => $bukuSelesai,
            'buku_total'    => $bukuTotal,
            'eks_selesai'   => $eksSelesai,
            'eks_total'     => $eksTotal,
            'error_buku'    => $errorBuku,
            'error_eks'     => $errorEks,
            'updated_at'    => now()->toDateTimeString(),
        ], now()->addHours(2));
    }

    public function getProgress(): ?array
    {
        return Cache::get('slims_import_progress');
    }

    public function resetProgress(): void
    {
        Cache::forget('slims_import_progress');
        Cache::forget('slims_last_report');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PREVIEW DATA (untuk halaman preview sebelum import)
    // ─────────────────────────────────────────────────────────────────────────

    public function getPreviewDdc(): array
    {
        $slims = $this->slimsConn->getConnection();
        $total = $slims->table('biblio')
            ->select('classification')
            ->whereNotNull('classification')
            ->where('classification', '!=', '')
            ->whereRaw("UPPER(classification) != 'NONE'")
            ->distinct()
            ->count();

        $samples = $slims->table('biblio')
            ->select('classification')
            ->whereNotNull('classification')
            ->where('classification', '!=', '')
            ->whereRaw("UPPER(classification) != 'NONE'")
            ->distinct()
            ->orderBy('classification')
            ->limit(15)
            ->get()
            ->map(fn($r) => [
                'kode_ddc' => $r->classification,
                'kategori' => self::getNamaDdc($r->classification),
            ])
            ->toArray();

        return ['total' => $total, 'samples' => $samples];
    }

    public function getPreviewBuku(): array
    {
        $slims     = $this->slimsConn->getConnection();
        $totalBuku = $slims->table('biblio')->count();
        $totalEks  = $slims->table('item')->whereNotNull('item_code')->where('item_code', '!=', '')->count();

        $samples = $slims->table('biblio')
            ->leftJoin('mst_publisher', 'biblio.publisher_id', '=', 'mst_publisher.publisher_id')
            ->select('biblio.biblio_id', 'biblio.title', 'biblio.isbn_issn', 'biblio.classification', 'mst_publisher.publisher_name')
            ->orderBy('biblio.biblio_id')
            ->limit(10)
            ->get()
            ->map(function ($b) use ($slims) {
                $jumlahEks = $slims->table('item')->where('biblio_id', $b->biblio_id)->count();
                return [
                    'biblio_id'      => $b->biblio_id,
                    'judul'          => $b->title,
                    'isbn'           => $b->isbn_issn ?? '-',
                    'penerbit'       => $b->publisher_name ?? '-',
                    'ddc'            => $b->classification ?? '-',
                    'jumlah_eksemplar' => $jumlahEks,
                ];
            })
            ->toArray();

        return [
            'total_buku'       => $totalBuku,
            'total_eksemplar'  => $totalEks,
            'samples'          => $samples,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPER: Mapping DDC Code → Nama Kategori
    // ─────────────────────────────────────────────────────────────────────────

    public static function getNamaDdc(string $kode): string
    {
        $bersih = trim($kode);
        // Ambil hanya angkanya (hilangkan suffix seperti "2x5", "SR 300 SUW s", dll)
        preg_match('/^(\d+\.?\d*)/', $bersih, $matches);
        if (empty($matches[1])) {
            return 'Lain-lain';
        }

        $angka = (float) $matches[1];

        // Sub-kelas spesifik berdasarkan data SLiMS perpustakaan ini
        $spesifik = [
            297  => 'Agama Islam',
            300  => 'Ilmu Sosial Umum',
            323  => 'Kewarganegaraan',
            370  => 'Pendidikan',
            398  => 'Folklore & Cerita Rakyat',
            410  => 'Bahasa Indonesia',
            413  => 'Kamus & Ensiklopedia Bahasa',
            420  => 'Bahasa Inggris',
            500  => 'Ilmu Pengetahuan Alam',
            507  => 'Prakarya & Sains Terapan',
            510  => 'Matematika',
            520  => 'Astronomi',
            530  => 'Fisika',
            540  => 'Kimia',
            550  => 'Ilmu Bumi & Geologi',
            570  => 'Biologi',
            580  => 'Botani & Tumbuhan',
            590  => 'Zoologi & Hewan',
            610  => 'Kedokteran & Kesehatan',
            613  => 'Kesehatan & Olahraga',
            620  => 'Teknik & Rekayasa',
            630  => 'Pertanian',
            640  => 'Kerumahtanggaan',
            650  => 'Manajemen & Bisnis',
            700  => 'Seni & Hiburan',
            707  => 'Kesenian & Seni Budaya',
            780  => 'Musik',
            790  => 'Olahraga & Rekreasi',
            796  => 'Pendidikan Jasmani & Olahraga',
            800  => 'Kesusastraan',
            810  => 'Sastra Indonesia',
            811  => 'Puisi Indonesia',
            813  => 'Fiksi & Novel',
            820  => 'Sastra Inggris',
            900  => 'Sejarah & Geografi',
            920  => 'Biografi',
        ];

        // Cari kecocokan spesifik terdekat (urut dari yang paling spesifik)
        $intAngka = (int) $angka;
        if (isset($spesifik[$intAngka])) {
            return $spesifik[$intAngka];
        }

        // Fallback ke kelas utama (kelipatan 100)
        $kelas = intdiv($intAngka, 100) * 100;
        return match(true) {
            $kelas === 0   => 'Karya Umum & Komputer',
            $kelas === 100 => 'Filsafat & Psikologi',
            $kelas === 200 => 'Agama',
            $kelas === 300 => 'Ilmu Sosial',
            $kelas === 400 => 'Bahasa & Linguistik',
            $kelas === 500 => 'Ilmu Pengetahuan Murni',
            $kelas === 600 => 'Ilmu Terapan & Teknologi',
            $kelas === 700 => 'Kesenian & Olahraga',
            $kelas === 800 => 'Kesusastraan',
            $kelas === 900 => 'Sejarah, Geografi & Biografi',
            default        => 'Lain-lain',
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPER: Mapping Status Item SLiMS → Status ERP
    // ─────────────────────────────────────────────────────────────────────────

    private function mapItemStatus(?string $statusId): string
    {
        return match($statusId) {
            'R'   => 'rusak',
            'MIS' => 'hilang',
            default => 'tersedia',
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPER: Mapping coll_type_id → kategori_id ERP
    // ─────────────────────────────────────────────────────────────────────────

    private function mapKategoriId(?int $collTypeId, array $kategoriMap): ?string
    {
        return match($collTypeId) {
            1, 4    => $kategoriMap['Referensi'] ?? $kategoriMap['Non Fiksi'] ?? null,
            3       => $kategoriMap['Fiksi'] ?? null,
            default => $kategoriMap['Non Fiksi'] ?? null,
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPER: Get or Create Inventaris per Buku
    // ─────────────────────────────────────────────────────────────────────────

    private function getOrCreateInventaris(
        string $bukuId,
        object $item,
        array &$inventarisDibuat,
        array &$result
    ): ?string {
        // Sudah dibuat dalam sesi ini? Return langsung
        if (isset($inventarisDibuat[$bukuId])) {
            return $inventarisDibuat[$bukuId];
        }

        // Cek di DB
        $existing = InventarisBuku::where('buku_id', $bukuId)->first();
        if ($existing) {
            $inventarisDibuat[$bukuId] = $existing->id;
            return $existing->id;
        }

        // Buat baru — hitung nomor inventaris dari rentang item_code
        $slims = $this->slimsConn->getConnection();
        $items = $slims->table('item')
            ->where('biblio_id', $item->biblio_id)
            ->orderBy('item_id')
            ->get(['item_code', 'inventory_code']);

        $first = $items->first();
        $last  = $items->last();

        $getKode = fn($row) => ($row && !empty(trim($row->inventory_code)))
            ? trim($row->inventory_code)
            : ($row ? trim($row->item_code) : null);

        $firstKode = $getKode($first);
        $lastKode  = $getKode($last);

        $tanggalMasuk = $item->received_date ?? now()->toDateString();
        $tahun        = date('Y', strtotime($tanggalMasuk));

        $asal = 'Pembelian';

        // Format kode inventaris: tambahkan /P/YEAR jika belum berformat
        $formatKode = function (?string $kode) use ($tahun, &$asal): string {
            if (!$kode) return 'SLIMS-' . $tahun;
            if (str_contains($kode, '/')) {
                $upper = strtoupper($kode);
                if (str_contains($upper, '/H/')) $asal = 'Hibah';
                elseif (str_contains($upper, '/T/')) $asal = 'Tukar';
                elseif (str_contains($upper, '/TS/')) $asal = 'Terbitan Sendiri';
                return $kode;
            }
            return "{$kode}/P/{$tahun}";
        };

        $firstFormatted = $formatKode($firstKode);
        $lastFormatted  = $formatKode($lastKode);

        $noInventaris = ($firstKode === $lastKode || $firstKode === null)
            ? $firstFormatted
            : "{$firstFormatted} - {$lastFormatted}";

        try {
            $inventaris = InventarisBuku::create([
                'id'               => Str::uuid(),
                'buku_id'          => $bukuId,
                'no_inventaris'    => $noInventaris,
                'tanggal_masuk'    => $tanggalMasuk,
                'asal'             => $asal,
                'harga'            => $item->price ?? 0,
                'jumlah_eksemplar' => 0,
                'status'           => 'aktif',
            ]);

            $inventarisDibuat[$bukuId] = $inventaris->id;
            $result['eksemplar']['inventaris_dibuat']++;
            return $inventaris->id;
        } catch (\Exception $e) {
            Log::error("SLiMS Create Inventaris Error (buku_id={$bukuId}): " . $e->getMessage());
            return null;
        }
    }
}

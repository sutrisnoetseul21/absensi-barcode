<?php

namespace App\Services;

use App\Models\EksemplarBuku;
use App\Models\InventarisBuku;
use App\Models\KategoriBuku;
use App\Models\KlasifikasiDdc;
use App\Models\Buku;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * SlimsMigrationService
 *
 * Menangani proses import data dari database SLiMS ke database ERP.
 * Kebijakan: OVERWRITE (updateOrInsert) — bukan skip.
 * Semua operasi dalam DB::transaction() untuk keamanan rollback.
 *
 * Urutan import yang benar: importDdc() → importBuku() → importEksemplar()
 *
 * Referensi mapping: docs/export-slims-erp/mapping-data-slims-erp.md
 */
class SlimsMigrationService
{
    public function __construct(
        protected SlimsConnectionService $slimsConn
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // 1. IMPORT DDC (mst_topic → klasifikasi_ddcs)
    // ─────────────────────────────────────────────────────────────────────────

    public function importDdc(): array
    {
        $slims  = $this->slimsConn->getConnection();
        $result = ['baru' => 0, 'diupdate' => 0, 'error' => 0, 'pesan_error' => []];

        $topics = $slims->table('mst_topic')
            ->whereNotNull('topic')
            ->where('topic', '!=', '')
            ->orderBy('topic_id')
            ->get();

        DB::transaction(function () use ($topics, &$result) {
            foreach ($topics as $topic) {
                try {
                    // Jika classification kosong, pakai "T{topic_id}" sebagai fallback
                    $kodeDdc = (isset($topic->classification) && trim($topic->classification) !== '')
                        ? trim($topic->classification)
                        : 'T' . $topic->topic_id;

                    $existing = KlasifikasiDdc::where('kode_ddc', $kodeDdc)->first();

                    if ($existing) {
                        $existing->update(['kategori' => trim($topic->topic)]);
                        $result['diupdate']++;
                    } else {
                        KlasifikasiDdc::create([
                            'id'       => Str::uuid(),
                            'kode_ddc' => $kodeDdc,
                            'kategori' => trim($topic->topic),
                        ]);
                        $result['baru']++;
                    }
                } catch (\Exception $e) {
                    $result['error']++;
                    $result['pesan_error'][] = "topic_id={$topic->topic_id}: " . $e->getMessage();
                }
            }
        });

        return $result;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. IMPORT BUKU (biblio → bukus)
    // ─────────────────────────────────────────────────────────────────────────

    public function importBuku(): array
    {
        $slims  = $this->slimsConn->getConnection();
        $result = ['baru' => 0, 'diupdate' => 0, 'error' => 0, 'pesan_error' => []];

        // Pre-load kategori map dari ERP
        $kategoriMap = KategoriBuku::pluck('id', 'nama_kategori')->toArray();

        // Pastikan kategori default ada
        if (!isset($kategoriMap['Non Fiksi'])) {
            $kat = KategoriBuku::create([
                'id'             => Str::uuid(),
                'nama_kategori'  => 'Non Fiksi',
                'is_bisa_dipinjam' => true,
                'is_buku_pelajaran' => false,
                'kode_prefix'    => 'SR',
            ]);
            $kategoriMap['Non Fiksi'] = $kat->id;
        }
        if (!isset($kategoriMap['Fiksi'])) {
            $kat = KategoriBuku::create([
                'id'             => Str::uuid(),
                'nama_kategori'  => 'Fiksi',
                'is_bisa_dipinjam' => true,
                'is_buku_pelajaran' => false,
                'kode_prefix'    => 'SR',
            ]);
            $kategoriMap['Fiksi'] = $kat->id;
        }
        if (!isset($kategoriMap['Referensi'])) {
            $kat = KategoriBuku::create([
                'id'             => Str::uuid(),
                'nama_kategori'  => 'Referensi',
                'is_bisa_dipinjam' => false,
                'is_buku_pelajaran' => false,
                'kode_prefix'    => 'RF',
            ]);
            $kategoriMap['Referensi'] = $kat->id;
        }

        // Ambil semua biblio dengan join penerbit dan author (batch 200)
        $slims->table('biblio')
            ->leftJoin('mst_publisher', 'biblio.publisher_id', '=', 'mst_publisher.publisher_id')
            ->select('biblio.*', 'mst_publisher.publisher_name')
            ->orderBy('biblio.biblio_id')
            ->chunk(200, function ($biblios) use ($slims, &$result, $kategoriMap) {
                // Ambil authors untuk batch ini sekaligus
                $biblioIds = $biblios->pluck('biblio_id')->toArray();
                $authorsMap = $slims->table('biblio_author')
                    ->join('mst_author', 'biblio_author.author_id', '=', 'mst_author.author_id')
                    ->whereIn('biblio_author.biblio_id', $biblioIds)
                    ->orderBy('biblio_author.biblio_id')
                    ->orderBy('biblio_author.level')
                    ->get(['biblio_author.biblio_id', 'mst_author.author_name'])
                    ->groupBy('biblio_id')
                    ->map(fn($group) => $group->pluck('author_name')->implode(', '));

                // Ambil coll_type_id per biblio (pakai item pertama)
                $collTypeMap = $slims->table('item')
                    ->whereIn('biblio_id', $biblioIds)
                    ->whereNotNull('coll_type_id')
                    ->select('biblio_id', 'coll_type_id')
                    ->get()
                    ->unique('biblio_id')
                    ->pluck('coll_type_id', 'biblio_id');

                // Proses setiap buku SATU PER SATU tanpa nested transaction
                // agar satu error tidak rollback seluruh chunk
                foreach ($biblios as $biblio) {
                    try {
                        $isbn        = trim($biblio->isbn_issn ?? '');
                        $judul       = trim($biblio->title ?? '');
                        $penerbit    = trim($biblio->publisher_name ?? '');
                        $penulis     = $authorsMap[$biblio->biblio_id] ?? null;
                        $collTypeId  = $collTypeMap[$biblio->biblio_id] ?? null;
                        $kategoriId  = $this->mapKategoriId($collTypeId, $kategoriMap);
                        $tahunTerbit = is_numeric($biblio->publish_year) ? (int) $biblio->publish_year : null;
                        $lokasiRak   = ($biblio->classification && strtoupper($biblio->classification) !== 'NONE')
                            ? trim($biblio->classification)
                            : null;

                        // Tentukan kondisi pencarian duplikat
                        if ($isbn !== '') {
                            $existing = Buku::withTrashed()->where('isbn', $isbn)->first();
                        } else {
                            $existing = Buku::withTrashed()
                                ->where('judul', $judul)
                                ->where('penerbit', $penerbit ?: null)
                                ->first();
                        }

                        $data = [
                            'kategori_id'  => $kategoriId,
                            'judul'        => $judul,
                            'penulis'      => $penulis,
                            'penerbit'     => $penerbit ?: null,
                            'tahun_terbit' => $tahunTerbit,
                            'isbn'         => $isbn ?: null,
                            'lokasi_rak'   => $lokasiRak,
                            'mapel_id'     => null,
                            'grade_level'  => null,
                            'updated_at'   => now(),
                        ];

                        if ($existing) {
                            if ($existing->trashed()) {
                                $existing->restore();
                            }
                            $existing->update($data);
                            // Simpan mapping biblio_id → buku uuid di cache
                            cache()->put("slims_biblio_{$biblio->biblio_id}", $existing->id, now()->addHours(6));
                            $result['diupdate']++;
                        } else {
                            $bukuId = Str::uuid()->toString();
                            Buku::create(array_merge($data, [
                                'id'         => $bukuId,
                                'created_at' => now(),
                            ]));
                            cache()->put("slims_biblio_{$biblio->biblio_id}", $bukuId, now()->addHours(6));
                            $result['baru']++;
                        }
                    } catch (\Exception $e) {
                        $result['error']++;
                        $result['pesan_error'][] = "biblio_id={$biblio->biblio_id}: " . $e->getMessage();
                    }
                }
            });

        return $result;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. IMPORT EKSEMPLAR (item → eksemplar_bukus + inventaris_bukus)
    // ─────────────────────────────────────────────────────────────────────────

    public function importEksemplar(): array
    {
        $slims  = $this->slimsConn->getConnection();
        $result = [
            'baru'              => 0,
            'diupdate'          => 0,
            'error'             => 0,
            'dilewati'          => 0,
            'inventaris_dibuat' => 0,
            'pesan_error'       => [],
        ];

        // Tracker inventaris yang sudah dibuat per buku_id di sesi ini
        $inventarisDibuat = [];

        $slims->table('item')
            ->whereNotNull('item_code')
            ->where('item_code', '!=', '')
            ->orderBy('biblio_id')
            ->orderBy('item_id')
            ->chunk(500, function ($items) use (&$result, &$inventarisDibuat) {
                foreach ($items as $item) {
                    try {
                        // Ambil buku_id dari cache, VERIFIKASI keberadaannya di DB
                        $bukuId = cache()->get("slims_biblio_{$item->biblio_id}");

                        if (!$bukuId) {
                            // Cache kosong — cari di DB berdasarkan biblio_id yang sempat diimport
                            // (tidak ada cara langsung, lewati saja)
                            $result['dilewati']++;
                            continue;
                        }

                        // Verifikasi buku_id BENAR-BENAR ada di DB (bukan orphan cache)
                        if (!Buku::withTrashed()->where('id', $bukuId)->exists()) {
                            $result['dilewati']++;
                            continue;
                        }

                        $status   = $this->mapItemStatus($item->item_status_id);
                        $existing = EksemplarBuku::withTrashed()
                            ->where('kode_eksemplar', $item->item_code)
                            ->first();

                        $dataEksemplar = [
                            'buku_id'        => $bukuId,
                            'kode_eksemplar' => $item->item_code,
                            'status'         => $status,
                            'kondisi_fisik'  => 'baik',
                            'updated_at'     => now(),
                        ];

                        if ($existing) {
                            if ($existing->trashed()) {
                                $existing->restore();
                            }
                            $existing->update($dataEksemplar);
                            $inventariBukuId = $existing->inventaris_buku_id;
                            $result['diupdate']++;
                        } else {
                            $eksemplarId = Str::uuid()->toString();

                            // Cari/buat inventaris untuk buku ini
                            $inventariBukuId = $this->getOrCreateInventaris(
                                $bukuId,
                                $item,
                                $inventarisDibuat,
                                $result
                            );

                            EksemplarBuku::create(array_merge($dataEksemplar, [
                                'id'                 => $eksemplarId,
                                'inventaris_buku_id' => $inventariBukuId,
                                'created_at'         => now(),
                            ]));
                            $result['baru']++;
                        }

                        // Update jumlah_eksemplar di inventaris
                        if ($inventariBukuId) {
                            InventarisBuku::where('id', $inventariBukuId)->increment('jumlah_eksemplar');
                        }
                    } catch (\Exception $e) {
                        $result['error']++;
                        $result['pesan_error'][] = "item_id={$item->item_id} (kode={$item->item_code}): " . $e->getMessage();
                    }
                }
            });

        return $result;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. IMPORT SEMUA (DDC → Buku → Eksemplar)
    // ─────────────────────────────────────────────────────────────────────────

    public function importSemua(): array
    {
        $ddcResult        = $this->importDdc();
        $bukuResult       = $this->importBuku();
        $eksemplarResult  = $this->importEksemplar();

        return [
            'ddc'       => $ddcResult,
            'buku'      => $bukuResult,
            'eksemplar' => $eksemplarResult,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPER METHODS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Mapping coll_type_id SLiMS → UUID kategori_bukus ERP.
     * Lihat: docs/export-slims-erp/mapping-data-slims-erp.md
     */
    private function mapKategoriId(int|null $collTypeId, array $kategoriMap): string
    {
        return match ($collTypeId) {
            1, 4    => $kategoriMap['Referensi'],   // Reference & Ensiklopedia
            3       => $kategoriMap['Fiksi'],        // Fiction
            default => $kategoriMap['Non Fiksi'],   // Textbook (2) & NULL
        };
    }

    /**
     * Mapping item_status_id SLiMS → status enum ERP.
     * Lihat: docs/export-slims-erp/mapping-data-slims-erp.md
     */
    private function mapItemStatus(string|null $statusId): string
    {
        return match ($statusId) {
            'R'     => 'rusak',
            'MIS'   => 'hilang',
            default => 'tersedia',  // NULL, '0', 'NL', dll
        };
    }

    /**
     * Dapatkan inventaris_buku_id yang ada, atau buat yang baru (1 per buku_id).
     */
    private function getOrCreateInventaris(
        string $bukuId,
        object $item,
        array  &$inventarisDibuat,
        array  &$result
    ): string {
        // Sudah dibuat di sesi ini?
        if (isset($inventarisDibuat[$bukuId])) {
            return $inventarisDibuat[$bukuId];
        }

        // Sudah ada di database?
        $existing = InventarisBuku::where('buku_id', $bukuId)->first();
        if ($existing) {
            $inventarisDibuat[$bukuId] = $existing->id;
            return $existing->id;
        }

        // Ambil semua item SLiMS untuk biblio ini guna mengetahui rentang (range) kode inventaris
        $slims = $this->slimsConn->getConnection();
        $items = $slims->table('item')->where('biblio_id', $item->biblio_id)->orderBy('item_id')->get(['item_code', 'inventory_code']);
        
        $first = $items->first();
        $last = $items->last();

        $firstNo = ($first && isset($first->inventory_code) && trim($first->inventory_code) !== '') 
            ? trim($first->inventory_code) 
            : ($first ? trim($first->item_code) : 'SLIMS-' . $item->biblio_id);
            
        $lastNo = ($last && isset($last->inventory_code) && trim($last->inventory_code) !== '') 
            ? trim($last->inventory_code) 
            : ($last ? trim($last->item_code) : 'SLIMS-' . $item->biblio_id);

        if ($firstNo === $lastNo) {
            $noInventaris = $firstNo;
        } else {
            $noInventaris = "{$firstNo} - {$lastNo}";
        }

        // Deteksi Asal Buku berdasarkan kode di SLiMS (jika menggunakan standar /P/, /H/, dll)
        $asal = 'Pembelian';
        $upperFirst = strtoupper($firstNo);
        if (str_contains($upperFirst, '/H/')) {
            $asal = 'Hibah';
        } elseif (str_contains($upperFirst, '/T/')) {
            $asal = 'Tukar';
        } elseif (str_contains($upperFirst, '/TS/')) {
            $asal = 'Terbitan Sendiri';
        } elseif (str_contains($upperFirst, '/P/')) {
            $asal = 'Pembelian';
        }

        $tanggalMasuk = $item->received_date ?? now()->toDateString();
        $harga        = $item->price ?? 0;

        $inventaris = InventarisBuku::create([
            'id'               => Str::uuid(),
            'buku_id'          => $bukuId,
            'no_inventaris'    => $noInventaris,
            'tanggal_masuk'    => $tanggalMasuk,
            'asal'             => $asal,
            'harga'            => $harga,
            'jumlah_eksemplar' => 0, // akan di-increment per eksemplar
            'status'           => 'aktif',
        ]);

        $inventarisDibuat[$bukuId] = $inventaris->id;
        $result['inventaris_dibuat']++;

        return $inventaris->id;
    }
}

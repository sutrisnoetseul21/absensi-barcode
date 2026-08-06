<?php

namespace App\Services;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use PDO;
use PDOException;

/**
 * SlimsConnectionService
 *
 * Mengelola koneksi dinamis ke database SLiMS menggunakan config dari session.
 * Tidak menyentuh .env sama sekali — koneksi dibuat runtime dari form UI.
 */
class SlimsConnectionService
{
    const SESSION_KEY = 'slims_db_config';
    const CONNECTION_NAME = 'slims_dynamic';

    /**
     * Tes koneksi ke database SLiMS menggunakan config dari form UI.
     * Jika berhasil, simpan config ke session dan return true.
     * Jika gagal, return string pesan error.
     */
    public function testConnection(array $config): true|string
    {
        try {
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
            $pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT            => 5,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            // Verifikasi tabel utama SLiMS ada
            $tables = $pdo->query("SHOW TABLES LIKE 'biblio'")->fetchAll();
            if (empty($tables)) {
                return "Koneksi berhasil, tetapi database '{$config['database']}' bukan database SLiMS (tabel 'biblio' tidak ditemukan).";
            }

            // Simpan ke session
            session([self::SESSION_KEY => [
                'host'     => $config['host'],
                'port'     => $config['port'],
                'database' => $config['database'],
                'username' => $config['username'],
                'password' => $config['password'],
            ]]);

            return true;
        } catch (PDOException $e) {
            return "Koneksi gagal: " . $e->getMessage();
        }
    }

    /**
     * Cek apakah sesi koneksi SLiMS masih aktif.
     */
    public function isConnected(): bool
    {
        return session()->has(self::SESSION_KEY);
    }

    /**
     * Ambil nama database SLiMS dari session.
     */
    public function getDatabaseName(): string
    {
        return session(self::SESSION_KEY . '.database', '');
    }

    /**
     * Buat dan return koneksi Illuminate ke database SLiMS dari session config.
     *
     * @throws \RuntimeException jika belum terkoneksi
     */
    public function getConnection(): Connection
    {
        $config = session(self::SESSION_KEY);

        if (!$config) {
            throw new \RuntimeException('Belum ada konfigurasi koneksi SLiMS. Silakan tes koneksi terlebih dahulu.');
        }

        // Daftarkan koneksi dinamis ke config runtime
        config(['database.connections.' . self::CONNECTION_NAME => [
            'driver'    => 'mysql',
            'host'      => $config['host'],
            'port'      => $config['port'],
            'database'  => $config['database'],
            'username'  => $config['username'],
            'password'  => $config['password'],
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
        ]]);

        // Purge agar tidak pakai koneksi cache lama
        DB::purge(self::CONNECTION_NAME);

        return DB::connection(self::CONNECTION_NAME);
    }

    /**
     * Ambil statistik jumlah data dari database SLiMS.
     * Digunakan untuk tampilkan preview di UI setelah koneksi berhasil.
     */
    public function getStats(): array
    {
        try {
            $conn = $this->getConnection();

            return [
                'biblio'     => $conn->table('biblio')->count(),
                'item'       => $conn->table('item')->count(),
                'mst_topic'  => $conn->table('mst_topic')->count(),
                'publisher'  => $conn->table('mst_publisher')->count(),
                'author'     => $conn->table('mst_author')->count(),
                'coll_type'  => $conn->table('mst_coll_type')->count(),
            ];
        } catch (\Exception $e) {
            return [
                'biblio'    => 0,
                'item'      => 0,
                'mst_topic' => 0,
                'publisher' => 0,
                'author'    => 0,
                'coll_type' => 0,
            ];
        }
    }

    /**
     * Hapus config koneksi SLiMS dari session.
     */
    public function forgetConnection(): void
    {
        session()->forget(self::SESSION_KEY);
        DB::purge(self::CONNECTION_NAME);
    }
}

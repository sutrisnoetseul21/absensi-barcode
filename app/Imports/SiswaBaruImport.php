<?php

namespace App\Imports;

use App\Models\EnrollmentSiswa;
use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

/**
 * Import Siswa Baru (PPDB) — mengisi tabel students + opsional student_enrollments.
 *
 * Kolom template (8 kolom):
 *   A: NISN  |  B: NIS  |  C: Nama Siswa  |  D: Tempat Lahir
 *   E: Tanggal Lahir  |  F: Alamat  |  G: Password  |  H: Kelas (Opsional)
 *
 * Aturan:
 *   - NISN atau NIS sudah ada di DB                  → SKIP + catat di laporan (status: skip)
 *   - NISN kembar dalam satu file                    → kedua baris SKIP + catat (status: skip)
 *   - Kelas diisi dan valid di DB + TA aktif ada     → INSERT students + INSERT enrollment
 *   - Kelas kosong                                   → INSERT students saja (status: berhasil_tanpa_kelas)
 *   - Kelas diisi tapi tidak valid di DB             → INSERT students, skip enrollment (status: warning)
 *   - Tidak ada tahun ajaran aktif saat kelas diisi  → INSERT students, skip enrollment (status: warning)
 */
class SiswaBaruImport implements ToCollection
{
    /** @var array<int, array> Laporan hasil import per baris */
    private array $results = [];

    public function collection(Collection $rows): void
    {
        // ─── Tweak Performa: Hindari Timeout Hashing Bcrypt ───────────────────
        // Menurunkan cost bcrypt sementara menjadi 4 (sangat cepat) untuk import.
        // Saat siswa login nanti, Laravel otomatis me-rehash kembali ke cost default (12).
        \Illuminate\Support\Facades\Config::set('hashing.bcrypt.rounds', 4);
        set_time_limit(300); // 5 menit

        // ─── Pra-proses: kumpulkan data referensi dari DB ─────────────────────
        $existingNisns  = Siswa::withTrashed()->pluck('nisn')->filter()->map(fn($v) => (string)$v)->flip()->toArray();
        $existingNises  = Siswa::withTrashed()->whereNotNull('nis')->pluck('nis')->filter()->map(fn($v) => (string)$v)->flip()->toArray();
        $validKelasMap  = Kelas::pluck('id', 'name')->toArray(); // ['7A' => uuid, ...]
        $activeYearId   = PengaturanSekolah::current()?->academic_year_id_active;

        // ─── Deteksi NISN kembar dalam file ───────────────────────────────────
        $nisnCountInFile = [];
        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // skip header
            $nisn = trim((string)($row[0] ?? ''));
            if ($nisn === '') continue;
            $nisnCountInFile[$nisn] = ($nisnCountInFile[$nisn] ?? 0) + 1;
        }
        $duplicateNisnsInFile = array_keys(array_filter($nisnCountInFile, fn($count) => $count > 1));

        // ─── Proses setiap baris ───────────────────────────────────────────────
        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // skip header

            $nisn         = trim((string)($row[0] ?? ''));
            $nis          = trim((string)($row[1] ?? ''));
            $name         = trim((string)($row[2] ?? ''));
            $birth_place  = trim((string)($row[3] ?? ''));
            $birth_date   = $this->parseBirthDate(trim((string)($row[4] ?? '')));
            $address      = trim((string)($row[5] ?? ''));
            $passwordVal  = trim((string)($row[6] ?? ''));
            $kelasName    = trim((string)($row[7] ?? ''));

            // Baris kosong (NISN dan Nama kosong) → lewati tanpa laporan
            if ($nisn === '' && $name === '') {
                continue;
            }

            $baseRow = [
                'nisn'    => $nisn,
                'nis'     => $nis,
                'name'    => $name,
                'kelas'   => $kelasName,
            ];

            // Validasi: NISN wajib ada
            if ($nisn === '') {
                $this->results[] = array_merge($baseRow, [
                    'status'       => 'skip',
                    'status_label' => '❌ Skip',
                    'keterangan'   => 'NISN kosong, baris dilewati.',
                ]);
                continue;
            }

            // Validasi: Nama wajib ada
            if ($name === '') {
                $this->results[] = array_merge($baseRow, [
                    'status'       => 'skip',
                    'status_label' => '❌ Skip',
                    'keterangan'   => 'Nama siswa kosong, baris dilewati.',
                ]);
                continue;
            }

            // Validasi: NISN kembar dalam file
            if (in_array($nisn, $duplicateNisnsInFile)) {
                $this->results[] = array_merge($baseRow, [
                    'status'       => 'skip',
                    'status_label' => '❌ Skip',
                    'keterangan'   => "NISN {$nisn} muncul lebih dari 1x dalam file ini. Semua baris dengan NISN ini dilewati.",
                ]);
                continue;
            }

            // Validasi: NISN sudah ada di DB
            if (isset($existingNisns[$nisn])) {
                $this->results[] = array_merge($baseRow, [
                    'status'       => 'skip',
                    'status_label' => '❌ Skip',
                    'keterangan'   => "NISN {$nisn} sudah terdaftar di database.",
                ]);
                continue;
            }

            // Validasi: NIS sudah ada di DB (jika NIS diisi)
            if ($nis !== '' && isset($existingNises[$nis])) {
                $this->results[] = array_merge($baseRow, [
                    'status'       => 'skip',
                    'status_label' => '❌ Skip',
                    'keterangan'   => "NIS {$nis} sudah digunakan oleh siswa lain di database.",
                ]);
                continue;
            }

            // ─── Simpan siswa ke tabel students ───────────────────────────────
            $password = $passwordVal !== '' ? $passwordVal : $nisn;

            $siswa = Siswa::create([
                'nisn'                 => $nisn,
                'nis'                  => $nis ?: null,
                'name'                 => $name,
                'birth_place'          => $birth_place ?: null,
                'birth_date'           => $birth_date,
                'address'              => $address ?: null,
                'password'             => $password,
                'must_change_password' => false,
            ]);

            // Tandai NISN sudah diproses agar baris berikutnya tidak duplikat
            $existingNisns[$nisn] = true;
            if ($nis !== '') $existingNises[$nis] = true;

            // ─── Proses kolom Kelas ────────────────────────────────────────────
            if ($kelasName === '') {
                // Tidak ada kelas → siswa disimpan saja
                $this->results[] = array_merge($baseRow, [
                    'status'       => 'berhasil_tanpa_kelas',
                    'status_label' => '✅ Berhasil (tanpa kelas)',
                    'keterangan'   => 'Siswa berhasil disimpan. Daftarkan ke kelas via menu Pendaftaran Kelas.',
                ]);
                continue;
            }

            // Kelas diisi — cek validitas nama kelas
            if (!isset($validKelasMap[$kelasName])) {
                $this->results[] = array_merge($baseRow, [
                    'status'       => 'warning',
                    'status_label' => '⚠️ Berhasil (kelas tidak valid)',
                    'keterangan'   => "Siswa disimpan, tetapi kelas \"{$kelasName}\" tidak ditemukan di sistem. Daftarkan ke kelas secara manual.",
                ]);
                continue;
            }

            // Kelas valid — cek apakah ada tahun ajaran aktif
            if (!$activeYearId) {
                $this->results[] = array_merge($baseRow, [
                    'status'       => 'warning',
                    'status_label' => '⚠️ Berhasil (tidak ada TA aktif)',
                    'keterangan'   => "Siswa disimpan, tetapi tidak ada Tahun Ajaran Aktif yang bisa digunakan untuk enrollment.",
                ]);
                continue;
            }

            // Cek apakah kelas sudah terdaftar di class_academic_year untuk TA aktif
            // (opsional guard — kelas mungkin ada tapi belum di-assign ke TA ini)
            $classId = $validKelasMap[$kelasName];

            // Buat enrollment
            EnrollmentSiswa::create([
                'student_id'       => $siswa->id,
                'class_id'         => $classId,
                'academic_year_id' => $activeYearId,
                'status'           => 'aktif',
            ]);

            $this->results[] = array_merge($baseRow, [
                'status'       => 'berhasil',
                'status_label' => '✅ Berhasil + Kelas',
                'keterangan'   => "Siswa berhasil disimpan dan didaftarkan ke kelas {$kelasName}.",
            ]);
        }
    }

    /** Kembalikan laporan hasil import */
    public function getResults(): array
    {
        return $this->results;
    }

    /** Statistik ringkas */
    public function getSummary(): array
    {
        $berhasil    = count(array_filter($this->results, fn($r) => $r['status'] === 'berhasil'));
        $tanpaKelas  = count(array_filter($this->results, fn($r) => $r['status'] === 'berhasil_tanpa_kelas'));
        $warning     = count(array_filter($this->results, fn($r) => $r['status'] === 'warning'));
        $skip        = count(array_filter($this->results, fn($r) => $r['status'] === 'skip'));

        return compact('berhasil', 'tanpaKelas', 'warning', 'skip');
    }

    private function parseBirthDate(string $value): ?string
    {
        if ($value === '') return null;
        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }
}

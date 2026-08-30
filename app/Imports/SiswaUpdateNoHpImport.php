<?php

namespace App\Imports;

use App\Models\Siswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class SiswaUpdateNoHpImport implements ToCollection
{
    private array $results = [];

    public function collection(Collection $rows): void
    {
        set_time_limit(300);

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // skip header

            $id              = trim((string)($row[0] ?? ''));
            $nisn            = trim((string)($row[1] ?? ''), " '\"\t\n\r\0\x0B");
            $nis             = trim((string)($row[2] ?? ''), " '\"\t\n\r\0\x0B");
            $name            = trim((string)($row[3] ?? ''));
            $kelas           = trim((string)($row[4] ?? ''));
            $no_hp           = trim((string)($row[5] ?? ''));
            $no_hp_orang_tua = trim((string)($row[6] ?? ''));

            if ($id === '') {
                continue; // Skip baris tanpa ID
            }

            $siswa = Siswa::find($id);

            if (!$siswa) {
                $this->results[] = [
                    'nisn'         => $nisn,
                    'name'         => $name,
                    'status'       => 'skip',
                    'status_label' => '❌ Tidak Ditemukan',
                    'keterangan'   => 'Data siswa dengan ID tersebut tidak ditemukan di database.',
                ];
                continue;
            }

            // Normalisasi nomor HP
            $cleanNoHp = $no_hp !== '' ? preg_replace('/\D/', '', $no_hp) : '';
            if ($cleanNoHp !== '') {
                if (str_starts_with($cleanNoHp, '0')) {
                    $cleanNoHp = '62' . substr($cleanNoHp, 1);
                } elseif (!str_starts_with($cleanNoHp, '62')) {
                    $cleanNoHp = '62' . $cleanNoHp;
                }
            }

            $cleanNoHpOrtu = $no_hp_orang_tua !== '' ? preg_replace('/\D/', '', $no_hp_orang_tua) : '';
            if ($cleanNoHpOrtu !== '') {
                if (str_starts_with($cleanNoHpOrtu, '0')) {
                    $cleanNoHpOrtu = '62' . substr($cleanNoHpOrtu, 1);
                } elseif (!str_starts_with($cleanNoHpOrtu, '62')) {
                    $cleanNoHpOrtu = '62' . $cleanNoHpOrtu;
                }
            }

            $changes = [];

            // Cek update No HP Siswa
            if ($cleanNoHp !== '' && $siswa->no_hp !== $cleanNoHp) {
                $siswa->no_hp = $cleanNoHp;
                $changes[] = 'No HP Siswa (' . $cleanNoHp . ')';
            } elseif ($cleanNoHp === '' && $siswa->no_hp !== null && $no_hp === '-') {
                $siswa->no_hp = null;
                $changes[] = 'No HP Siswa dikosongkan';
            }

            // Cek update No HP Orang Tua
            if ($cleanNoHpOrtu !== '' && $siswa->no_hp_orang_tua !== $cleanNoHpOrtu) {
                $siswa->no_hp_orang_tua = $cleanNoHpOrtu;
                $changes[] = 'No HP Ortu (' . $cleanNoHpOrtu . ')';
            } elseif ($cleanNoHpOrtu === '' && $siswa->no_hp_orang_tua !== null && $no_hp_orang_tua === '-') {
                $siswa->no_hp_orang_tua = null;
                $changes[] = 'No HP Ortu dikosongkan';
            }

            if (!empty($changes)) {
                $siswa->save();
                $this->results[] = [
                    'nisn'         => $siswa->nisn,
                    'name'         => $siswa->name,
                    'status'       => 'berhasil',
                    'status_label' => '✅ Diperbarui',
                    'keterangan'   => 'Berhasil memperbarui: ' . implode(', ', $changes),
                ];
            } else {
                $this->results[] = [
                    'nisn'         => $siswa->nisn,
                    'name'         => $siswa->name,
                    'status'       => 'skip',
                    'status_label' => 'ℹ️ Tidak Ada Perubahan',
                    'keterangan'   => 'Nomor HP sama dengan yang sudah tersimpan.',
                ];
            }
        }
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function getSummary(): array
    {
        $berhasil = count(array_filter($this->results, fn($r) => $r['status'] === 'berhasil'));
        $skip     = count(array_filter($this->results, fn($r) => $r['status'] === 'skip'));

        return compact('berhasil', 'skip');
    }
}

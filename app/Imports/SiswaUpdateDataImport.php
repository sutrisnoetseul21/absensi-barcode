<?php

namespace App\Imports;

use App\Models\Siswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Carbon\Carbon;

class SiswaUpdateDataImport implements ToCollection
{
    private array $results = [];

    public function collection(Collection $rows): void
    {
        set_time_limit(300);

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // skip header

            $id               = trim((string)($row[0] ?? ''));
            $nisn             = trim((string)($row[1] ?? ''), " '\"\t\n\r\0\x0B");
            $nis              = trim((string)($row[2] ?? ''), " '\"\t\n\r\0\x0B");
            $name             = trim((string)($row[3] ?? ''));
            $birth_place      = trim((string)($row[4] ?? ''));
            $birth_date       = $this->parseDate(trim((string)($row[5] ?? '')));
            $gender           = strtoupper(trim((string)($row[6] ?? '')));
            $religion         = trim((string)($row[7] ?? ''));
            $address          = trim((string)($row[8] ?? ''));
            $no_hp            = trim((string)($row[9] ?? ''));
            $previous_school  = trim((string)($row[10] ?? ''));
            $admission_date   = $this->parseDate(trim((string)($row[11] ?? '')));
            $admission_class  = trim((string)($row[12] ?? ''));
            $family_status    = trim((string)($row[13] ?? ''));
            $child_order_raw  = trim((string)($row[14] ?? ''));
            $child_order      = is_numeric($child_order_raw) ? (int)$child_order_raw : null;
            $nama_ayah        = trim((string)($row[15] ?? ''));
            $pekerjaan_ayah   = trim((string)($row[16] ?? ''));
            $nama_ibu         = trim((string)($row[17] ?? ''));
            $pekerjaan_ibu    = trim((string)($row[18] ?? ''));
            $nama_wali        = trim((string)($row[19] ?? ''));
            $pekerjaan_wali   = trim((string)($row[20] ?? ''));
            $no_hp_orang_tua  = trim((string)($row[21] ?? ''));

            if ($id === '') {
                continue; // Skip baris yang tidak ada ID
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

            // Cek perubahan
            $changes = [];
            
            // Catatan: Pembaruan NISN, NIS, dan Nama dinonaktifkan (di-hidden/diabaikan)
            // agar data sensitif/utama ini tidak berubah saat guru mengupdate biodata.


            if ($birth_place !== '' && $siswa->birth_place !== $birth_place) {
                $siswa->birth_place = $birth_place;
                $changes[] = 'Tempat Lahir';
            }

            $currentBirthDate = $siswa->birth_date ? $siswa->birth_date->format('Y-m-d') : null;
            if ($birth_date !== null && $currentBirthDate !== $birth_date) {
                $siswa->birth_date = $birth_date;
                $changes[] = 'Tanggal Lahir';
            }

            if ($gender !== '' && in_array($gender, ['L', 'P']) && $siswa->gender !== $gender) {
                $siswa->gender = $gender;
                $changes[] = 'Jenis Kelamin';
            }

            if ($religion !== '' && $siswa->religion !== $religion) {
                $siswa->religion = $religion;
                $changes[] = 'Agama';
            }

            if ($address !== '' && $siswa->address !== $address) {
                $siswa->address = $address;
                $changes[] = 'Alamat';
            }

            if ($no_hp !== '' && $siswa->no_hp !== $no_hp) {
                $siswa->no_hp = $no_hp;
                $changes[] = 'No HP Siswa';
            }

            if ($previous_school !== '' && $siswa->previous_school !== $previous_school) {
                $siswa->previous_school = $previous_school;
                $changes[] = 'Asal Sekolah';
            }

            $currentAdmissionDate = $siswa->admission_date ? $siswa->admission_date->format('Y-m-d') : null;
            if ($admission_date !== null && $currentAdmissionDate !== $admission_date) {
                $siswa->admission_date = $admission_date;
                $changes[] = 'Tanggal Masuk';
            }

            if ($admission_class !== '' && $siswa->admission_class !== $admission_class) {
                $siswa->admission_class = $admission_class;
                $changes[] = 'Kelas Masuk';
            }

            if ($family_status !== '' && $siswa->family_status !== $family_status) {
                $siswa->family_status = $family_status;
                $changes[] = 'Status Keluarga';
            }

            if ($child_order !== null && $siswa->child_order !== $child_order) {
                $siswa->child_order = $child_order;
                $changes[] = 'Anak Ke-';
            }

            if ($nama_ayah !== '' && $siswa->nama_ayah !== $nama_ayah) {
                $siswa->nama_ayah = $nama_ayah;
                $changes[] = 'Nama Ayah';
            }

            if ($pekerjaan_ayah !== '' && $siswa->pekerjaan_ayah !== $pekerjaan_ayah) {
                $siswa->pekerjaan_ayah = $pekerjaan_ayah;
                $changes[] = 'Pekerjaan Ayah';
            }

            if ($nama_ibu !== '' && $siswa->nama_ibu !== $nama_ibu) {
                $siswa->nama_ibu = $nama_ibu;
                $changes[] = 'Nama Ibu';
            }

            if ($pekerjaan_ibu !== '' && $siswa->pekerjaan_ibu !== $pekerjaan_ibu) {
                $siswa->pekerjaan_ibu = $pekerjaan_ibu;
                $changes[] = 'Pekerjaan Ibu';
            }

            if ($nama_wali !== '' && $siswa->nama_wali !== $nama_wali) {
                $siswa->nama_wali = $nama_wali;
                $changes[] = 'Nama Wali';
            }

            if ($pekerjaan_wali !== '' && $siswa->pekerjaan_wali !== $pekerjaan_wali) {
                $siswa->pekerjaan_wali = $pekerjaan_wali;
                $changes[] = 'Pekerjaan Wali';
            }

            if ($no_hp_orang_tua !== '' && $siswa->no_hp_orang_tua !== $no_hp_orang_tua) {
                $siswa->no_hp_orang_tua = $no_hp_orang_tua;
                $changes[] = 'No HP Ortu';
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
                    'keterangan'   => 'Data sama dengan di database.',
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

    private function parseDate(string $value): ?string
    {
        if ($value === '') return null;
        try {
            return Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d');
        } catch (\Exception $e) {
            try {
                return Carbon::parse($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }
    }
}
